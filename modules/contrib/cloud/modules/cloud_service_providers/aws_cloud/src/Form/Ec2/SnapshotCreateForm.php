<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Snapshot;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Snapshot entity create form.
 *
 * @ingroup aws_cloud
 */
class SnapshotCreateForm extends AwsCloudContentForm {

  use CloudContentEntityTrait;

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
   *
   * @param array $form
   *   Array of form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param string $cloud_context
   *   A cloud_context string value from URL "path".
   *
   * @return array
   *   Array of form object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Snapshot */
    $form = parent::buildForm($form, $form_state);
    $this->ec2Service->setCloudContext($cloud_context);
    $entity = $this->entity;

    $weight = -50;

    $form['snapshot'] = [
      '#type' => 'details',
      '#title' => $this->t('AWS Cloud Snapshot'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['snapshot']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => FALSE,
    ];

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $volume_id = $this->getRequest()->query->get('volume_id');

    $form['snapshot']['volume_id'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Volume ID'),
      '#options'       => $this->getVolumeOptions($cloud_context, $module_name),
      '#default_value' => $volume_id,
      '#required'      => TRUE,
    ];

    $form['snapshot']['description'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Description'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->getDescription(),
      '#required'      => FALSE,
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    return $form;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * @param array $form
   *   Array of form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    if (!empty($entity->getVolumeId())) {
      $result = $this->ec2Service->createSnapshot([
        'VolumeId'    => $entity->getVolumeId(),
        'Description' => $entity->getDescription(),
      ]);
    }

    if (isset($result['SnapshotId'])
    && ($entity->setSnapshotId($result['SnapshotId']))
    && ($entity->setStatus($result['State']))
    && ($entity->setStarted(strtotime($result['StartTime'])))
    && ($entity->setEncrypted($result['Encrypted']))
    && ($entity->save())) {

      if (empty($entity->getName())) {
        $entity->setName($result['SnapshotId']);
        $entity->save();
      }

      $this->setTagsInAws($entity->getSnapshotId(), [
        $entity->getEntityTypeId() . '_' . Snapshot::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      $this->processOperationStatus($entity, 'created');

      $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list",
        [
          'cloud_context' => $entity->getCloudContext(),
        ]);
      $this->clearCacheValues();
    }
    else {

      $this->processOperationErrorStatus($entity, 'created');
    }

  }

  /**
   * Helper function to get volume options.
   *
   * @param string $cloud_context
   *   Cloud context to use in the query.
   * @param string $module_name
   *   Module name.
   *
   * @return array
   *   Volume options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getVolumeOptions($cloud_context, $module_name) {
    $options = [];
    $params = [
      'cloud_context' => $cloud_context,
    ];

    // Get cloud service provider name.
    $cloud_name = str_replace('_', ' ', $module_name);

    if (!$this->currentUser->hasPermission("view any {$cloud_name} volume")) {
      $params['uid'] = $this->currentUser->id();
    }

    $volumes = $this->entityTypeManager
      ->getStorage("{$module_name}_volume")
      ->loadByProperties($params);

    foreach ($volumes ?: [] as $volume) {
      if ($volume->getName() !== $volume->getVolumeId()) {
        $options[$volume->getVolumeId()] = "{$volume->getName()} ({$volume->getVolumeId()})";
      }
      else {
        $options[$volume->getVolumeId()] = $volume->getVolumeId();
      }
    }
    return $options;
  }

}
