<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Service\Util\EntityLinkWithNameHtmlGenerator;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\aws_cloud\Entity\Ec2\Volume;

/**
 * Form controller for the CloudScripting entity edit forms.
 *
 * @ingroup aws_cloud
 */
class VolumeEditForm extends AwsCloudContentForm {

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
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Volume */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $weight = -50;

    // Get module name.
    $module_name = $this->getModuleName($entity);

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
      '#required'      => TRUE,
    ];

    $form['volume']['volume_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Volume ID')),
      '#markup'        => $entity->getVolumeId(),
    ];

    $form['volume']['attachment_information'] = $this->entityLinkRenderer->renderFormElements(
      $entity->getAttachmentInformation(),
      "{$module_name}_instance",
      'instance_id',
      ['#title' => $this->getItemTitle($this->t('Instance ID'))],
      '',
      EntityLinkWithNameHtmlGenerator::class
    );

    $form['volume']['snapshot_id'] = $this->entityLinkRenderer->renderFormElements(
      $entity->getSnapshotId(),
      "{$module_name}_snapshot",
      'snapshot_id',
      ['#title' => $this->getItemTitle($this->t('Snapshot ID'))]
    );

    $form['volume']['snapshot_name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Snapshot Name')),
      '#markup'        => $entity->getSnapshotName(),
    ];

    $form['volume']['size'] = [
      '#type'          => 'textfield',
      '#title'         => $this->getItemTitle($this->t('Size (GiB)')),
      '#default_value' => $entity->getSize(),
      '#size'          => 60,
    ];

    $form['volume']['volume_type'] = [
      '#type'          => 'select',
      '#title'         => $this->getItemTitle($this->t('Volume Type')),
      '#options'       => [
        'gp2' => $this->t('General Purpose SSD (gp2)'),
        'standard' => $this->t('Magnetic (standard)'),
        'io1' => $this->t('Provisioned IOPS SSD (io1)'),
        'sc1' => $this->t('Cold HDD (sc1)'),
        'st1' => $this->t('Throughput Optimized HDD (st1)'),
      ],
      '#default_value' => $entity->getVolumeType(),
    ];

    $form['volume']['iops'] = [
      '#type'          => 'textfield',
      '#title'         => $this->getItemTitle($this->t('IOPS')),
      '#default_value' => $entity->getIops(),
      '#size'          => 60,
    ];

    $form['volume']['availability_zone'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Availability Zone')),
      '#markup'        => $entity->getAvailabilityZone(),
    ];

    $form['volume']['encrypted'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Encrypted')),
      '#markup'        => $entity->getEncrypted() === 0 ? 'Off' : 'On',
    ];

    $form['volume']['state'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Status')),
      '#markup'        => $entity->getState(),
    ];

    $form['volume']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Call copyFormItemValues() to ensure the form array is intact.
    $this->copyFormItemValues($form);

    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    $this->ec2Service->setCloudContext($entity->getCloudContext());

    $old_entity = $this->entityTypeManager
      ->getStorage('aws_cloud_volume')
      ->load($entity->id());

    if ($entity->save()) {
      // Update name.
      $tag_map = [];
      $tag_map['Name'] = $entity->getName();
      $this->setTagsInAws($entity->getVolumeId(), $tag_map);

      // Update volume type.
      if ($entity->getEntityTypeId() === 'aws_cloud_volume') {
        if (!empty($entity)
        && !empty($old_entity)
        && $this->isVolumeChanged($entity, $old_entity)) {
          $params = [
            'VolumeId' => $entity->getVolumeId(),
            'VolumeType' => $entity->getVolumeType(),
            'Size' => $entity->getSize(),
          ];

          // Only if the type is io1, the iops can be modified.
          if ($entity->getVolumeType === 'io1' && $entity->getIops()) {
            $params['Iops'] = $entity->getIops();
          }

          $this->ec2Service->modifyVolume($params);
        }
      }

      $this->processOperationStatus($entity, 'updated');
      $this->clearCacheValues();
    }
    else {

      $this->processOperationErrorStatus($entity, 'updated');
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list",
      [
        'cloud_context' => $entity->getCloudContext(),
      ]);
  }

  /**
   * Check Whether the volume changed or not.
   *
   * @param \Drupal\aws_cloud\Entity\Ec2\Volume $new_volume
   *   The new volume.
   * @param \Drupal\aws_cloud\Entity\Ec2\Volume $old_volume
   *   The old volume.
   *
   * @return bool
   *   Whether the volume changed or not.
   */
  private function isVolumeChanged(Volume $new_volume, Volume $old_volume) {
    return $old_volume->getVolumeType() !== $new_volume->getVolumeType()
      || $old_volume->getSize() !== $new_volume->getSize()
      || $old_volume->getIops() !== $new_volume->getIops();
  }

}
