<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CloudProjectTypeForm.
 */
class CloudProjectTypeForm extends EntityForm {

  use CloudContentEntityTrait;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Constructs a new CloudProjectTypeForm.
   *
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   */
  public function __construct(Messenger $messenger) {
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
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $cloud_project_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cloud_project_type->label(),
      '#description' => $this->t('Label for the cloud project type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cloud_project_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\cloud\Entity\CloudProjectType::load',
      ],
      '#disabled' => !$cloud_project_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cloud_project_type = $this->entity;
    $status = $cloud_project_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->processOperationStatus($cloud_project_type, 'created');
        break;

      default:
        $this->processOperationStatus($cloud_project_type, 'updated');
    }
    $form_state->setRedirectUrl($cloud_project_type->toUrl('collection'));

    // Clear block and menu cache.
    CloudContentEntityBase::updateCache();
  }

}
