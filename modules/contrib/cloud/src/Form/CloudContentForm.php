<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Messenger\Messenger;
use Drupal\Component\Datetime\TimeInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the Cloud entity edit forms.
 *
 * @ingroup cloud
 */
class CloudContentForm extends ContentEntityForm {

  use CloudContentEntityTrait;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Constructs a CloudContentForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository,
                              EntityTypeBundleInfoInterface $entity_type_bundle_info,
                              TimeInterface $time,
                              Messenger $messenger) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->has('datetime.time') ? $container->get('datetime.time') : NULL,
      $container->get('messenger')
    );
  }

  /**
   * Override actions()
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $entity = $this->entity;
    foreach ($actions ?: [] as $key => $action) {
      if (isset($actions[$key]['#url'])
      && method_exists($this->entity, 'cloud_context')) {
        $actions[$key]['#url']->setRouteParameter('cloud_context', $entity->getCloudContext());
      }
    }
    return $actions;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::submit().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return entity
   *   return entity object.
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // Build the entity object from the submitted values.
    $entity = parent::submit($form, $form_state);

    return $entity;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $status = $entity->save();

    // Add a updated status message and the log.
    switch ($status) {
      case SAVED_NEW:
        $passive_operation = 'created';
        break;

      case SAVED_DELETED:
        $passive_operation = 'deleted';
        break;

      default:
      case SAVED_UPDATED:
        $passive_operation = 'updated';
    }
    $this->processOperationStatus($entity, $passive_operation);

    // Clear block and menu cache.
    CloudContentEntityBase::updateCache();

    return $status;
  }

  /**
   * Add the build array of fieldset others.
   *
   * @param array &$form
   *   The form array.
   * @param int $weight
   *   The weight of the fieldset.  The parameter's default value is 1
   *   to put the "Others" fieldset in between the main items and button(s)
   *   (e.g. "Save") if the parameter is omitted since 0 is the default value
   *   of the #weight attribute.
   * @param string $cloud_context
   *   The cloud context.
   */
  protected function addOthersFieldset(array &$form, $weight = 1, $cloud_context = '') {

    $form['others'] = [
      '#type'          => 'details',
      '#title'         => $this->t('Others'),
      '#open'          => FALSE,
      '#weight'       => $weight,
    ];

    $form['others']['cloud_context'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Cloud Service Provider ID'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => !$this->entity->isNew()
      ? $this->entity->getCloudContext()
      : $cloud_context,
      '#required'      => TRUE,
      '#disabled'      => TRUE,
    ];

    $form['others']['langcode'] = [
      '#title'         => $this->t('Language'),
      '#type'          => 'language_select',
      '#default_value' => $this->entity->getUntranslated()->language()->getId(),
      '#languages'     => Language::STATE_ALL,
      '#attributes'    => ['readonly' => 'readonly'],
      '#disabled'      => FALSE,
    ];

    $form['others']['uid'] = $form['uid'];
  }

}
