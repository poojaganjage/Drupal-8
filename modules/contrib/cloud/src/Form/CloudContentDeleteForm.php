<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityDeleteFormTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for the Cloud entity delete confirm forms.
 *
 * @ingroup cloud
 *
 * @TODO Re-evaluate and streamline the entity deletion form class hierarchy in
 *   https://www.drupal.org/node/2491057.
 */
class CloudContentDeleteForm extends ContentEntityConfirmFormBase {

  use CloudContentEntityTrait;
  use EntityDeleteFormTrait;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Constructs a CloudContentDeleteForm object.
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
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getCancelUrl() {
    return $this->entity->toUrl('canonical', ['cloud_context', $this->entity->getCloudContext()]);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $entity->delete();

    $this->messenger->addStatus($this->getDeletionMessage());
    $this->logDeletionMessage();

    $form_state->setRedirectUrl($this->getCancelUrl());

    // Clear block and menu cache.
    CloudContentEntityBase::updateCache();
  }

  /**
   * Helper method to clear cache values.
   */
  protected function clearCacheValues(): void {
    $this->pluginCacheClearer->clearCachedDefinitions();
    $this->cacheRender->invalidateAll();
  }

}
