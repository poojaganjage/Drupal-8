<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Entity\Ec2\Volume;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Volume entity create form.
 *
 * @ingroup aws_cloud
 */
class VolumeCreateForm extends AwsCloudContentForm {

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

    $this->ec2Service->setCloudContext($cloud_context);

    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Volume */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    // Use the value of parameter snapshot_id as the default value.
    $snapshot_id = $this->getRequest()->query->get('snapshot_id');
    $snapshot = NULL;
    if (!empty($snapshot_id)) {
      $snapshots = $this->entityTypeManager
        ->getStorage("{$module_name}_snapshot")
        ->loadByProperties([
          'cloud_context' => $cloud_context,
          'snapshot_id' => $snapshot_id,
        ]);

      if (!empty($snapshots)) {
        $snapshot = reset($snapshots);
      }
    }

    $weight = -50;

    $form['volume'] = [
      '#type' => 'details',
      '#title' => $this->t('@title', ['@title' => $entity->getEntityType()->getSingularLabel()]),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['volume']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#weight'        => -5,
      '#required'      => TRUE,
    ];

    $form['volume']['snapshot_id'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Snapshot ID'),
      '#options'       => $this->getSnapshotOptions($cloud_context, $module_name),
      '#default_value' => $snapshot_id,
      '#weight'        => -5,
      '#required'      => FALSE,
      '#empty_value'   => '',
    ];

    $form['volume']['size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Size (GiB)'),
      '#size'          => 60,
      '#default_value' => $snapshot ? $snapshot->getSize() : '',
      '#required'      => TRUE,
    ];

    $form['volume']['volume_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Volume Type'),
      '#options' => [
        'gp2' => $this->t('General Purpose SSD (gp2)'),
        'standard' => $this->t('Magnetic (standard)'),
        'io1' => $this->t('Provisioned IOPS SSD (io1)'),
        'sc1' => $this->t('Cold HDD (sc1)'),
        'st1' => $this->t('Throughput Optimized HDD (st1)'),
      ],
    ];

    $form['volume']['iops'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('IOPS'),
      '#size'          => 60,
      '#default_value' => $entity->getIops(),
      '#required'      => FALSE,
    ];

    $availability_zones = $this->ec2Service->getAvailabilityZones();
    $form['volume']['availability_zone'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Availability Zone'),
      '#options'       => $availability_zones,
      // Pick up the first availability zone in the array.
      '#default_value' => array_shift($availability_zones),
      '#required'      => TRUE,
    ];

    $form['volume']['kms_key_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('KMS Key ID'),
      '#size'          => 60,
      '#default_value' => $entity->getKmsKeyId(),
      '#required'      => FALSE,
    ];

    $form['volume']['encrypted'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Encrypted'),
      '#size'          => 60,
      '#default_value' => $entity->getEncrypted(),
      '#required'      => FALSE,

    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Add validation for volume types.  for io1 type,
    // iops must be set.  For other volume types, iops cannot
    // be set.
    if ($form_state->getValue('volume_type') === 'io1') {
      // Check if there is an iops value.
      if (empty($form_state->getValue('iops'))) {
        $form_state->setErrorByName('iops', $this->t('Please specify an iops value.  The value must be a minimum of 100.'));
      }

      // Check if iops is an integer.
      if (!is_numeric($form_state->getValue('iops'))) {
        $form_state->setErrorByName('iops', $this->t('IOPS must be an integer.'));
      }
      // Check if iops is greater than 100.
      $iops = (int) $form_state->getValue('iops');
      if ($iops < 100) {
        $form_state->setErrorByName('iops', $this->t('IOPS must be a minimum of 100.'));
      }
    }
    else {
      if (!empty($form_state->getValue('iops'))) {
        $form_state->setErrorByName('iops', $this->t('IOPS cannot be set unless volume type is "Provisioned IOPS SSD".'));
      }
    }
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

    $params = [
      'Size'             => $entity->getSize(),
      'AvailabilityZone' => $entity->getAvailabilityZone(),
      'VolumeType'       => $entity->getVolumeType(),
      'Encrypted'        => $entity->getEncrypted() ? TRUE : FALSE,
    ];

    if ($entity->getVolumeType() === 'io1') {
      $params['Iops'] = (int) $entity->getIops();
    }

    if (!empty($entity->getKmsKeyId())) {
      $params['KmsKeyId'] = $entity->getKmsKeyId();
    }

    if (!empty($entity->getSnapshotId())) {
      $params['SnapshotId'] = $entity->getSnapshotId();
    }

    $result = $this->ec2Service->createVolume($params);

    if (isset($result['VolumeId'])
      && ($entity->setVolumeId($result['VolumeId']))
      && ($entity->setCreated($result['CreateTime']))
      && ($entity->setState($result['State']))
      && ($entity->setSnapshotName($this->getSnapshotName($entity->getSnapshotId())))
      && ($entity->setVolumeType($result['VolumeType']))
      && ($entity->save())) {

      // Create tags.
      $this->setTagsInAws($entity->getVolumeId(), [
        Volume::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
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
   * Get Snapshot Name.
   *
   * @param string $snapshot_id
   *   Snapshot ID.
   *
   * @return string
   *   Snapshot Name.
   */
  private function getSnapshotName($snapshot_id) {
    $snapshot_name = '';
    if (empty($snapshot_id)) {
      return $snapshot_name;
    }

    $result = $this->ec2Service->describeSnapshots(['SnapshotIds' => [$snapshot_id]]);
    if (isset($result['Snapshots'][0])) {
      $snapshot = $result['Snapshots'][0];
      foreach ($snapshot['Tags'] ?: [] as $tag) {
        if ($tag['Key'] === 'Name') {
          $snapshot_name = $tag['Value'];
          break;
        }
      }
    }

    return $snapshot_name;
  }

  /**
   * Helper function to get snapshot options.
   *
   * @param string $cloud_context
   *   Cloud context to use in the query.
   * @param string $module_name
   *   Module name.
   *
   * @return array
   *   Snapshot options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getSnapshotOptions($cloud_context, $module_name) {
    $options = [];
    $params = [
      'cloud_context' => $cloud_context,
    ];

    // Get cloud service provider name.
    $cloud_name = str_replace('_', ' ', $module_name);
    if (!$this->currentUser->hasPermission("view any {$cloud_name} snapshot")) {
      $params['uid'] = $this->currentUser->id();
    }

    $snapshots = $this->entityTypeManager
      ->getStorage("{$module_name}_snapshot")
      ->loadByProperties($params);
    foreach ($snapshots ?: [] as $snapshot) {
      if ($snapshot->getName() !== $snapshot->getSnapshotId()) {
        $options[$snapshot->getSnapshotId()] = "{$snapshot->getName()} ({$snapshot->getSnapshotId()})";
      }
      else {
        $options[$snapshot->getSnapshotId()] = $snapshot->getSnapshotId();
      }
    }
    return $options;
  }

}
