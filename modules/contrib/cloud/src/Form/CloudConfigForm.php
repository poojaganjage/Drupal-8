<?php

namespace Drupal\cloud\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for cloud service provider edit forms.
 *
 * @ingroup cloud
 */
class CloudConfigForm extends CloudContentForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\cloud\Entity\CloudConfig */
    $form = parent::buildForm($form, $form_state);

    $weight = -50;

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => $weight++,
      ];
    }

    $form['cloud_context'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Cloud Service Provider ID'),
      '#description' => $this->t('A unique ID for the cloud service provider.'),
      '#default_value' => $this->entity->getCloudContext(),
      '#disabled' => !$this->entity->isNew(),
      '#machine_name' => [
        'exists' => [$this->entity, 'checkCloudContext'],
      ],
    ];

    $this->addOthersFieldset($form, $weight++);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') !== FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $entity->setRevisionUserId($this->currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    parent::save($form, $form_state);
    $form_state->setRedirect('entity.cloud_config.canonical', ['cloud_config' => $entity->id()]);
  }

}
