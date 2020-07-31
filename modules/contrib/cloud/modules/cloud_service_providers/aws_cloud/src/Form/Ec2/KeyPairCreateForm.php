<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the KeyPair entity create form.
 *
 * @ingroup aws_cloud
 */
class KeyPairCreateForm extends AwsCloudContentForm {

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
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\KeyPair */
    $form = parent::buildForm($form, $form_state);

    $this->ec2Service->setCloudContext($cloud_context);
    $entity = $this->entity;

    $weight = -50;

    $form['key_pair'] = [
      '#type' => 'details',
      '#title' => $entity->getEntityType()->getSingularLabel(),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['key_pair']['key_pair_name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Key Pair Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->getKeyPairName(),
      '#required'      => TRUE,
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

    /* @var \Drupal\aws_cloud\Entity\Ec2\KeyPair $entity */
    $entity = $this->entity;

    $result = $this->ec2Service->createKeyPair([
      'KeyName' => $entity->getKeyPairName(),
    ]);

    // Following AWS specification and not storing key material.
    // Prompt user to download it.
    if (!empty($result['KeyName'])
    && (!empty($entity)
    && $entity->setKeyFingerprint($result['KeyFingerprint'])
    && $entity->save())) {

      // Save the file to temp.
      $entity->saveKeyFile($result['KeyMaterial']);

      $this->processOperationStatus($entity, 'created');
      $this->clearCacheValues();

      $form_state->setRedirect("entity.{$entity->getEntityTypeId()}.canonical", ['cloud_context' => $entity->getCloudContext(), $entity->getEntityTypeId() => $entity->id()]);
    }
    else {

      // Use the custom message since Key Pair uses 'key_pair_name' for its own
      // label.  So don't change the following code.
      $this->messenger->addStatus($this->t('The @type @label could not be created.', [
        '@type' => $entity->getEntityType()->getSingularLabel(),
        '@label' => $entity->getKeyPairName(),
      ]));
      $this->logOperationErrorMessage($entity, 'created');

      $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
    }

  }

}
