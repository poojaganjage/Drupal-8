<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the CloudScripting entity edit forms.
 *
 * @ingroup aws_cloud
 */
class SnapshotEditForm extends AwsCloudContentForm {

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
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Snapshot */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $weight = -50;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $form['snapshot'] = [
      '#type' => 'details',
      '#title' => $this->t('Snapshot'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['snapshot']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => TRUE,
    ];

    $form['snapshot']['description'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Description')),
      '#markup'        => $entity->getDescription(),
    ];

    $form['snapshot']['snapshot_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Snapshot ID')),
      '#markup'        => $entity->getSnapshotId(),
    ];

    $form['snapshot']['volume_id'] = $this->entityLinkRenderer->renderFormElements(
      $entity->getVolumeId(),
      "{$module_name}_volume",
      'volume_id',
      ['#title' => $this->getItemTitle($this->t('Volume ID'))]
    );

    $form['snapshot']['size'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Size (GB)')),
      '#markup'        => $entity->getSize(),
    ];

    $form['snapshot']['status'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Status')),
      '#markup'        => $entity->getStatus(),
    ];

    $form['snapshot']['progress'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Progress')),
      '#markup'        => $entity->getProgress(),
    ];

    $form['snapshot']['encrypted'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Encrypted'),
      '#default_value' => FALSE,
      '#required'      => FALSE,
    ];

    $form['snapshot']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);
    $form['actions']['#weight'] = $weight++;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $entity = $this->entity;
    $this->setTagsInAws($this->entity->getSnapshotId(), [
      "{$entity->getEntityTypeId()}_{Snapshot::TAG_CREATED_BY_UID}" => $entity->getOwner()->id(),
      'Name' => $this->entity->getName(),
    ]);
    $this->clearCacheValues();
  }

}
