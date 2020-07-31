<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides a block displaying snapshot information.
 *
 * @Block(
 *   id = "aws_cloud_snapshots_block",
 *   admin_label = @Translation("Snapshot Blocks"),
 *   category = @Translation("AWS Cloud")
 * )
 */
class SnapshotsBlock extends BulkDeleteBlock {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->configuration['snapshot_block_type'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['snapshot_block_type'] = 'orphaned_snapshots';
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $unused_days = $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_stale_snapshot_criteria');
    $form['snapshot_block_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Snapshot Blocks to Show'),
      '#options' => [
        'orphaned_snapshots' => $this->t(
          'Show the snapshots orphaned from AMI Images.'
        ),
        'disassociated_snapshots' => $this->t(
          'Show the snapshots disassociated from volumes.'
        ),
        'stale_snapshots' => $this->t(
          'Show snapshots created more than %d days ago',
          [
            '%d' => Link::createFromRoute($unused_days, 'aws_cloud.settings.options')->toString(),
          ]
        ),
      ],
      '#description' => $this->t('Choose the Snapshot information to display.'),
      '#default_value' => $this->configuration['snapshot_block_type'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['snapshot_block_type']
      = $form_state->getValue('snapshot_block_type');
  }

  /**
   * Build a bulk delete form.
   *
   * @return array
   *   Form array.
   */
  protected function buildBulkForm() {
    $this->setEntityTypeId('aws_cloud_snapshot');
    try {
      $this->setDeleteActions([
        'type' => $this->entityTypeId,
        'id' => 'aws_cloud_snapshot_delete_action',
      ]);
    }
    catch (AwsCloudBlockException $e) {
      // If there is an exception, show user the error.
      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addError("An error occurred: {$e->getMessage()}");
      return [];
    }

    $type = $this->configuration['snapshot_block_type'];
    // Build the snapshot rows depending on the snapshot_block_type selected.
    $rows = $this->buildSnapshotRows();
    $form_text = $this->getBulkFormDefaultText($type);
    $form[$type] = $this->buildFieldSet($form_text['title'] ?? '');
    if (!empty($this->entities)) {
      $form[$type] += $this->buildTableHeader();
      $form[$type]['#description'] = $form_text['description'] ?? '';
      if (!empty($rows)) {
        $form[$type][$this->entitiesKey] += $rows;
      }
      $form[$type]['actions'] = $this->buildActions();
    }
    else {
      $form[$type]['message'] = [
        '#type' => 'markup',
        '#markup' => $form_text['no_entities'] ?? '',
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity_keys = $form_state->getValue($this->entitiesKey);
    if (!empty($entity_keys)) {
      $selected = array_filter($form_state->getValue($this->entitiesKey));
      if (empty($selected)) {
        $form_state->setErrorByName(
          $this->entitiesKey,
          $this->t('No items selected.  Cannot perform bulk snapshot delete operation.')
        );
      }
    }
  }

  /**
   * Get a list of stale snapshots.
   *
   * @return array
   *   Array of stale snapshots.
   */
  private function getStaleSnapshots() {
    $snapshots = aws_cloud_get_stale_snapshots($this->configuration['cloud_context']);
    if (!$this->currentUser->hasPermission('view any aws cloud snapshot')) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\Snapshot $snapshot */
      foreach ($snapshots ?: [] as $key => $snapshot) {
        // Only return volumes the user has access to.
        if ($snapshot->getOwnerId() !== $this->currentUser->id()) {
          unset($snapshots[$key]);
        }
      }
    }
    return $snapshots;
  }

  /**
   * Helper method to retrieve Images associated with a snapshot.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   An array snapshot_ids
   */
  private function getImagesWithSnapshotIds() {
    $cloud_context = $this->configuration['cloud_context'] ?? '';
    $image_snapshot_ids = [];
    try {
      $query = $this->entityTypeManager
        ->getStorage('aws_cloud_image')
        ->getQuery()
        ->condition('status', 'available');
      if (!empty($cloud_context)) {
        $query->condition('cloud_context', $cloud_context);
      }
      $and = $query->andConditionGroup();
      $and->exists('block_device_mappings.snapshot_id');

      $ids = $query->condition($and)->execute();
      if (!empty($ids)) {
        $images = $this->entityTypeManager
          ->getStorage('aws_cloud_image')
          ->loadMultiple($ids);

        // Loop through the image block_device_mappings field and
        // find any snapshot_ids.
        foreach ($images ?: [] as $image) {
          $block_device_mappings = $image->getBlockDeviceMappings();
          foreach ($block_device_mappings ?: [] as $block_device) {
            $snapshot_id = $block_device->getSnapshotId();
            if (!empty($snapshot_id)) {
              $image_snapshot_ids[] = $block_device->getSnapshotId();
            }
          }
        }
      }
    }
    catch (\Exception $e) {
      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addError("An error occurred: {$e->getMessage()}");
    }
    return $image_snapshot_ids;
  }

  /**
   * Query for a list of orphaned snapshots.
   *
   * The query takes a list of ami images that contains snapshot_ids.  It
   * searches the aws_cloud_snapshot table to find any snapshots that do
   * not match the snapshot_ids from the $images array.
   *
   * @param array $image_snapshot_ids
   *   An array of snapshot_ids.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   An array of snapshots.
   */
  private function getOrphanedSnapshots(array $image_snapshot_ids) {
    $cloud_context = $this->configuration['cloud_context'] ?? '';
    $snapshots = [];

    try {
      if (!empty($image_snapshot_ids)) {
        $entity_id = 'aws_cloud_snapshot';
        $query = $this->entityTypeManager
          ->getStorage($entity_id)
          ->getQuery()
          ->condition('snapshot_id', $image_snapshot_ids, 'NOT IN');

        if (!empty($cloud_context)) {
          $query->condition('cloud_context', $cloud_context);
        }
        $ids = $query->execute();
        if (!empty($ids)) {
          $snapshots = $this->entityTypeManager
            ->getStorage($entity_id)
            ->loadMultiple($ids);
          if (!$this->currentUser->hasPermission('view any aws cloud snapshot')) {
            foreach ($snapshots ?: [] as $key => $snapshot) {
              if ($snapshot->getOwnerId() !== $this->currentUser->id()) {
                unset($snapshots[$key]);
              }
            }
          }
        }
      }
    }
    catch (\Exception $e) {
      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addError("An error occurred: {$e->getMessage()}");
    }

    return $snapshots;
  }

  /**
   * Get a list of disassociated snapshots.
   *
   * @return array
   *   Array of disassociated snapshots.
   */
  private function getDisassociatedSnapshots() {
    $snapshots = aws_cloud_get_unused_snapshots($this->configuration['cloud_context']);
    if (!$this->currentUser->hasPermission('view any aws cloud snapshot')) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\Snapshot $snapshot */
      foreach ($snapshots ?: [] as $key => $snapshot) {
        // Only return volumes the user has access to.
        if ($snapshot->getOwnerId() !== $this->currentUser->id()) {
          unset($snapshots[$key]);
        }
      }
    }
    return $snapshots;
  }

  /**
   * Generate a snapshot link that includes days running text.
   *
   * @param bool $show_days
   *   TRUE to show running days, FALSE to only show link.
   *
   * @return array
   *   Render array containing snapshot rows.
   */
  private function buildSnapshotLinks($show_days = FALSE) {
    $rows = [];
    /* @var \Drupal\aws_cloud\Entity\Ec2\Snapshot $snapshot */
    foreach ($this->entities ?: [] as $snapshot) {
      $link_text = $this->t('@snapshot (%running @days)', [
        '@snapshot' => $snapshot->getName(),
        '%running' => $snapshot->daysRunning(),
        '@days' => $this->formatPlural($snapshot->daysRunning(), 'day', 'days'),
      ]);
      try {
        if ($show_days === TRUE) {
          $row_text = $snapshot->toLink($link_text)->toRenderable();
        }
        else {
          $row_text = $snapshot->toLink($snapshot->getName())->toRenderable();
        }
      }
      catch (\Exception $e) {
        // NOTE: $this->messenger() is correct.
        // cf. MessengerTrait::messenger() MessengerInterface.
        $this->messenger()->addError("An error occurred: {$e->getMessage()}");
        $row_text = $link_text;
      }
      // Build the form row.
      $rows[$snapshot->id()] = $this->buildTableRow($snapshot->id(), $row_text);
    }

    return $rows;

  }

  /**
   * Get the bulk form header/footer text.
   *
   * @param string $type
   *   The block type used to look up the default text array.
   *
   * @return array
   *   Default text array.
   */
  private function getBulkFormDefaultText($type) {
    $unused_days = $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_stale_snapshot_criteria');
    $text = [
      'orphaned_snapshots' => [
        'title' => $this->t('Orphaned Snapshots'),
        'description' => $this->t('The following snapshots are orphaned.'),
        'no_entities' => $this->t('Great job! You have no orphaned snapshots.'),
      ],
      'disassociated_snapshots' => [
        'title' => $this->t('Disassociated Snapshots'),
        'description' => $this->t('The following snapshots are not associated with a volume.'),
        'no_entities' => $this->t('Great job! You have no disassociated snapshots.'),
      ],
      'stale_snapshots' => [
        'title' => $this->t('Stale Snapshots'),
        'description' => $this->t('The following snapshots have been running for more than %num days.', ['%num' => $unused_days]),
        'no_entities' => $this->t('Great job! You have no stale snapshots.'),
      ],
    ];
    return $text[$type] ?? [];
  }

  /**
   * Build all the snapshot rows.
   *
   * @return array
   *   An array of rows
   */
  private function buildSnapshotRows() {
    $rows = [];
    $type = $this->configuration['snapshot_block_type'];

    // Build the rows to display.
    if ($type === 'orphaned_snapshots') {
      $image_snapshot_ids = $this->getImagesWithSnapshotIds();
      if (!empty($image_snapshot_ids)) {
        $this->entities = $this->getOrphanedSnapshots($image_snapshot_ids);
        if (!empty($this->entities)) {
          $rows = $this->buildSnapshotLinks(TRUE);
        }
      }
    }
    elseif ($type === 'disassociated_snapshots') {
      $this->entities = $this->getDisassociatedSnapshots();
      if (!empty($this->entities)) {
        $rows = $this->buildSnapshotLinks();
      }
    }
    elseif ($type === 'stale_snapshots') {
      $this->entities = $this->getStaleSnapshots();
      if (!empty($this->entities)) {
        $rows = $this->buildSnapshotLinks(TRUE);
      }
    }
    return $rows;
  }

}
