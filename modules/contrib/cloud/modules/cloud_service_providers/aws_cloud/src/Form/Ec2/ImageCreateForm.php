<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\aws_cloud\Entity\Ec2\Image;

/**
 * Form controller for the Image entity create form.
 *
 * @ingroup aws_cloud
 */
class ImageCreateForm extends AwsCloudContentForm {

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
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Image */
    $form = parent::buildForm($form, $form_state);

    $this->ec2Service->setCloudContext($cloud_context);

    $entity = $this->entity;

    $weight = -50;

    $form['image'] = [
      '#type' => 'details',
      '#title' => $entity->getEntityType()->getSingularLabel(),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['image']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => FALSE,
    ];

    $form['image']['instance_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Instance ID'),
      '#maxlength'     => 60,
      '#size'          => 60,
      '#default_value' => $entity->getInstanceId(),
      '#required'      => TRUE,
    ];

    $form['image']['description'] = [
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
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    /* @var \Drupal\aws_cloud\Entity\Ec2\Image $entity */
    $entity = $this->entity;

    $result = $this->ec2Service->createImage([
      'InstanceId'  => $entity->getInstanceId(),
      'Name'        => $entity->getName(),
      'Description' => $entity->getDescription(),
    ]);

    $this->cloudConfigPluginManager->setCloudContext($entity->getCloudContext());
    $account_id = $this->cloudConfigPluginManager->loadConfigEntity()->get('field_account_id')->value;
    if (!empty($entity) && !empty($result['ImageId'])
      && ($entity->setName($form_state->getValue('name')))
      && ($entity->set('ami_name', $form_state->getValue('name')))
      && ($entity->setImageId($result['ImageId']))
      && ($entity->set('account_id', $account_id))
      && ($entity->save())) {

      $this->setTagsInAws($entity->getImageId(), [
        $entity->getEntityTypeId() . '_' . Image::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      $this->processOperationStatus($entity, 'created');

      $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
    }
    else {

      $this->processOperationErrorStatus($entity, 'created');
    }

  }

}
