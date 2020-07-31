<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to delete cloud server template type entities.
 */
class CloudServerTemplateTypeDeleteForm extends CloudContentDeleteForm {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Constructs a new CloudServerTemplateTypeDeleteForm.
   *
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   */
  public function __construct(Messenger $messenger) {
    parent::__construct($messenger);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.cloud_server_template_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    $t_args = ['@type' => $this->entity->bundle(), '@label' => $this->entity->label()];
    $this->messenger->addStatus($this->t('The @type @label has been deleted.', $t_args));

    $form_state->setRedirectUrl($this->getCancelUrl());

    // Clear block and menu cache.
    CloudContentEntityBase::updateCache();
  }

}
