<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Volume Attach form.
 */
class VolumeAttachForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;

    return $this->t('Are you sure you want to attach volume: %name?', [
      '%name' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Attach');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $entity = $this->entity;
    return $this->t('<h2>Volume Information:</h2><ul><li>Volume id: %id</li><li>Volume name: %name</li></ul>', [
      '%id' => $entity->getVolumeId(),
      '%name' => $entity->getName(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $instances = [];
    $results = $this->getInstances($this->entity->getAvailabilityZone(), $module_name);

    foreach ($results ?: [] as $result) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\Instance $result */
      $instances[$result->getInstanceId()] = $this->t('%name - %instance_id', [
        '%name' => $result->getName(),
        '%instance_id' => $result->getInstanceId(),
      ]);
    }
    if (count($results) > 0) {
      $form['device_name'] = [
        '#title' => $this->t('Device Name'),
        '#type' => 'textfield',
        '#description' => $this->t('The device name (for example, /dev/sdh or xvdh).'),
        '#required' => TRUE,
      ];

      $form['instance_id'] = [
        '#type' => 'select',
        '#title' => $this->t('Instance ID'),
        '#options' => $instances,
      ];
    }
    else {
      $form['message'] = [
        '#markup' => '<h1>' . $this->t('No instances available in the availability zone: %zone.  Volume cannot be attached.', ['%zone' => $this->entity->getAvailabilityZone()]) . '</h1>',
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $entity = $this->entity;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $results = $this->getInstances($this->entity->getAvailabilityZone(), $module_name);
    if (count($results) === 0) {
      unset($actions['submit']);
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* @var \Drupal\aws_cloud\Entity\Ec2\Volume $entity */
    $entity = $this->entity;

    $instance_id = $form_state->getValue('instance_id');
    $volume_id = $entity->getVolumeId();
    $device_name = $form_state->getValue('device_name');

    $this->ec2Service->setCloudContext($this->entity->getCloudContext());
    $result = $this->ec2Service->attachVolume([
      'InstanceId' => $instance_id,
      'VolumeId' => $volume_id,
      'Device' => $device_name,
    ]);

    if ($result !== NULL) {
      // Set the instance_id in the volume entity and save.
      $entity->setAttachmentInformation($instance_id);
      $entity->setState($result['State']);
      $entity->save();
      $this->clearCacheValues();
      $this->messenger->addStatus($this->t('The Volume %volume is attaching to %instance.', ['%volume' => $volume_id, '%instance' => $instance_id]));

      $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
    }
  }

  /**
   * Query DB for aws_cloud_instances that are in the same zone as the volume.
   *
   * This method respects instance visibility.
   *
   * @param string $zone
   *   The Availability Zone String.
   * @param string $module_name
   *   Module name.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The Instance Entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getInstances($zone, $module_name) {

    $account = \Drupal::currentUser();
    $properties = [
      'availability_zone' => $zone,
    ];

    // Get cloud service provider name.
    $cloud_name = str_replace('_', ' ', $module_name);

    if (!$account->hasPermission("view any {$cloud_name} instance")) {
      $properties['uid'] = $account->id();
    }

    return $this->entityTypeManager
      ->getStorage("{$module_name}_instance")
      ->loadByProperties($properties);
  }

}
