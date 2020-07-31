<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;

/**
 * Class KeyPairImportForm.
 *
 * Responsible for key importing via user uploaded public key.
 *
 * @package Drupal\aws_cloud\Form\Ec2
 */
class KeyPairImportForm extends AwsCloudContentForm {

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

    $form['key_pair']['key_pair_public_key'] = [
      '#type' => 'file',
      '#title' => 'Public Key',
      '#description' => $this->t('Upload your public key.'),
      '#weight' => -4,
    ];

    $form['langcode'] = [
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Custom validation for file upload.
    $all_files = $this->getRequest()->files->get('files', []);
    if (!empty($all_files['key_pair_public_key'])) {
      $file_upload = $all_files['key_pair_public_key'];
      if ($file_upload->isValid()) {
        $form_state->setValue('key_pair_public_key', $file_upload->getRealPath());
        return;
      }
    }

    $form_state->setErrorByName('key_pair_public_key', $this->t('The file could not be uploaded.'));
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

    /* @var \Drupal\aws_cloud\Entity\Ec2\KeyPair $entity */
    $entity = $this->entity;

    if ($path = $form_state->getValue('key_pair_public_key')) {
      $handle = fopen($path, 'r');
      $key_material = fread($handle, filesize($path));
      fclose($handle);

      $result = $this->ec2Service->importKeyPair([
        'KeyName' => $entity->getKeyPairName(),
        'PublicKeyMaterial' => $key_material,
      ]);

      // Following AWS specification and not storing key material.
      if (!empty($result)
      && (!empty($entity)
      && $entity->setKeyFingerprint($result['KeyFingerprint'])
      && $entity->save())) {

        // Use the custom message since Key Pair uses 'key_pair_name' for its
        // own label.  So don't change the following code.
        $this->messenger->addStatus($this->t('The @type %label has been imported.', [
          '@type' => $entity->getEntityType()->getSingularLabel(),
          '%label' => $entity->toLink($entity->getKeyPairName())->toString(),
        ]));
        $this->logOperationMessage($entity, 'imported');

        $form_state->setRedirect("entity.{$entity->getEntityTypeId()}.canonical", [
          'cloud_context' => $entity->getCloudContext(),
          $entity->getEntityTypeId() => $entity->id(),
        ]);

        $this->clearCacheValues();
      }
      else {

        $this->processOperationErrorMessage('aws_cloud', $entity, 'create');

        $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
      }
    }
  }

}
