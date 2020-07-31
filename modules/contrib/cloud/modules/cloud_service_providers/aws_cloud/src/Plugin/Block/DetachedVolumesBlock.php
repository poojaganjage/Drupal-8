<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block displaying unused volumes.
 *
 * @Block(
 *   id = "aws_cloud_detached_volumes_block",
 *   admin_label = @Translation("Detached Volumes"),
 *   category = @Translation("AWS Cloud")
 * )
 */
class DetachedVolumesBlock extends BulkDeleteBlock {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'unused_volume';
  }

  /**
   * Build a bulk delete form.
   *
   * @return array
   *   Form array.
   */
  protected function buildBulkForm() {
    $entity_type_id = $this->entityTypeId;
    $this->setEntityTypeId('aws_cloud_volume');
    try {
      $this->setDeleteActions([
        'type' => $this->entityTypeId,
        'id' => 'aws_cloud_volume_delete_action',
      ]);
    }
    catch (AwsCloudBlockException $e) {
      // If there is an exception, show user the error.
      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addError("An error occurred: {$e->getMessage()}");
      return [];
    }
    // Load the entities for bulk operation.
    $this->entities = $this->getUnusedVolumes();
    $form['unused_volumes'] = $this->buildFieldSet($this->t('Unused Volumes'));
    if (!empty($this->entities)) {
      // Ensure a consistent container for filters/operations
      // in the view header.
      $form['unused_volumes'] += $this->buildTableHeader();

      $unused_days = $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_unused_volume_criteria');
      $form['unused_volumes']['#description'] = [
        '#markup' => $this->t('The following volumes have been detached for more than %num days', ['%num' => $unused_days]),
      ];

      foreach ($this->entities ?: [] as $volume) {
        $row_text = '';
        $days_running = $volume->daysRunning();
        $link_text = $this->t('@volume (%unused @days)', [
          '@volume' => $volume->getName(),
          '%unused' => $days_running,
          '@days' => $this->formatPlural($days_running, 'day', 'days'),
        ]);

        try {
          $row_text = $volume->toLink($link_text)->toRenderable();
        }
        catch (\Exception $e) {
          // Something happened to the rendering of the link.
          // Default to the link text.
          // NOTE: $this->messenger() is correct.
          // cf. MessengerTrait::messenger() MessengerInterface.
          $this->messenger()->addError("An error occurred: {$e->getMessage()}");
          $row_text = $link_text;
        }
        $form['unused_volumes'][$this->entitiesKey][$volume->id()] = $this->buildTableRow($volume->id(), $row_text);

      }
      $form['unused_volumes']['actions'] = $this->buildActions();
    }
    else {
      $form['unused_volumes']['message'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Great job! You have no detached volumes.'),
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
          $this->t('No items selected.  Cannot perform bulk volume delete operation.')
        );
      }
    }
  }

  /**
   * Get a list of unused volumes.
   *
   * @return array
   *   Array of unused volumes.
   */
  private function getUnusedVolumes() {
    $volumes = aws_cloud_get_unused_volumes($this->configuration['cloud_context']);
    if (!$this->currentUser->hasPermission('view any aws cloud volume')) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\Volume $volume */
      foreach ($volumes ?: [] as $key => $volume) {
        // Only return volumes the user has access to.
        if ($volume->getOwnerId() !== $this->currentUser->id()) {
          unset($volumes[$key]);
        }
      }
    }
    return $volumes;
  }

}
