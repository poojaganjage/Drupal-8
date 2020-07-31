<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\aws_cloud\Entity\Ec2\ElasticIp;

/**
 * Form controller for the ElasticIp entity create form.
 *
 * @ingroup aws_cloud
 */
class ElasticIpCreateForm extends AwsCloudContentForm {

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
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\ElasticIp */
    $form = parent::buildForm($form, $form_state);

    $this->ec2Service->setCloudContext($cloud_context);

    $entity = $this->entity;

    $weight = -50;

    $form['elastic_ip'] = [
      '#type' => 'details',
      '#title' => $this->t('@title', ['@title' => $entity->getEntityType()->getSingularLabel()]),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['elastic_ip']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => TRUE,
    ];

    $form['elastic_ip']['domain'] = [
      '#type'          => 'select',
      '#options'       => [
        'standard' => 'standard',
        'vpc' => 'vpc',
      ],
      '#title'         => $this->t('Domain (standard | vpc)'),
      '#default_value' => 'standard',
      '#required'      => TRUE,
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    return $form;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    $result = $this->ec2Service->allocateAddress([
      'Domain' => $entity->getDomain(),
    ]);

    if (isset($result['PublicIp'])
    && ($entity->setPublicIp($result['PublicIp']))
    && ($entity->setAllocationId($result['AllocationId']))
    && ($entity->setDomain($result['Domain']))
    && ($entity->save())) {

      // Update the tags.
      if ($entity->getEntityTypeId() === 'aws_cloud_elastic_ip') {
        $this->setTagsInAws($entity->getAllocationId(), [
          ElasticIp::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
          'Name' => $entity->getName(),
        ]);
      }
      else {
        $entity->setName($result['PublicIp'])->save();
      }

      $this->processOperationStatus($entity, 'created');
      $this->clearCacheValues();
    }
    else {
      $this->processOperationErrorStatus($entity, 'created');
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

}
