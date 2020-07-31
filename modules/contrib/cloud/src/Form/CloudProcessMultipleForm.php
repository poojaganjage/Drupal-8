<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides an entities deletion confirmation form.
 */
abstract class CloudProcessMultipleForm extends ConfirmFormBase implements BaseFormIdInterface {

  use CloudContentEntityTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The tempstore.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The selection, in the entity_id => langcodes format.
   *
   * @var array
   */
  protected $selection = [];

  /**
   * The entity type definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The temp store key name.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreKey;

  /**
   * Constructs a new CloudProcessMultiple object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(AccountInterface $current_user,
                              EntityTypeManager $entity_type_manager,
                              PrivateTempStoreFactory $temp_store_factory,
                              MessengerInterface $messenger,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {

    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $entity_type_id = $this->getRouteMatch()->getParameter('entity_type_id');
    $this->tempStoreKey = $this->currentUser->id() . ':' . $entity_type_id;
    $this->tempStore = $temp_store_factory->get($this->tempStoreKey);
    $this->messenger = $messenger;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
      $container->get('messenger'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {

    return 'cloud_process_multiple_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {

    // Get entity type ID from the route because ::buildForm has not yet been
    // called.
    $entity_type_id = $this->getRouteMatch()->getParameter('entity_type_id');
    return $entity_type_id . '_process_multiple_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {

    return $this->formatPlural(count($this->selection),
      'Are you sure you want to process this @item?',
      'Are you sure you want to process these @items?', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {

    $route = \Drupal::routeMatch();
    $cloud_context = $route->getParameter('cloud_context');
    return new Url(
      'entity.' . $this->entityTypeId . '.collection',
      ['cloud_context' => $cloud_context]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL) {

    $this->entityTypeId = $entity_type_id;
    $this->entityType = $this->entityTypeManager->getDefinition($this->entityTypeId);
    $this->selection = $this->tempStore->get($this->tempStoreKey);
    if (empty($this->entityTypeId) || empty($this->selection)) {
      return new RedirectResponse($this->getCancelUrl()
        ->setAbsolute()
        ->toString());
    }

    $items = [];
    $entities = $this->entityTypeManager->getStorage($entity_type_id)->loadMultiple(array_keys($this->selection));
    foreach ($this->selection ?: [] as $id => $selected_langcodes) {
      $entity = $entities[$id];
      foreach ($selected_langcodes ?: [] as $langcode) {
        $key = $id . ':' . $langcode;
        if ($entity instanceof TranslatableInterface) {
          $entity = $entity->getTranslation($langcode);
          $default_key = $id . ':' . $entity->getUntranslated()->language()->getId();

          // Build a nested list of translations that will be processed if the
          // entity has multiple translations.
          $entity_languages = $entity->getTranslationLanguages();
          if (count($entity_languages) > 1 && $entity->isDefaultTranslation()) {
            $names = [];
            foreach ($entity_languages ?: [] as $translation_langcode => $language) {
              $names[] = $language->getName();
              unset($items[$id . ':' . $translation_langcode]);
            }
            $items[$default_key] = [
              'label' => [
                '#markup' => $this->t(
                  '@label (Original translation) - <em>The following @entity_type translations will be processed:</em>',
                  [
                    '@label' => $entity->label(),
                    '@entity_type' => $this->entityType->getSingularLabel(),
                  ]),
              ],
              'deleted_translations' => [
                '#theme' => 'item_list',
                '#items' => $names,
              ],
            ];
          }
          elseif (!isset($items[$default_key])) {
            $items[$key] = $entity->label();
          }
        }
        elseif (!isset($items[$key])) {
          $items[$key] = $entity->label();
        }
      }
    }

    $form = parent::buildForm($form, $form_state);
    $form['entities'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = $this->entityTypeManager->getStorage($this->entityTypeId);
    $entities = $storage->loadMultiple(array_keys($this->selection));
    $total_count = 0;

    foreach ($entities ?: [] as $entity) {
      if ($this->process($entity)) {
        $total_count++;
      }
    }

    if ($total_count) {
      $this->messenger->addStatus($this->getProcessedMessage($total_count));
    }

    $this->tempStore->delete($this->currentUser->id());
    $form_state->setRedirectUrl($this->getCancelUrl());
    CloudContentEntityBase::updateCache();
  }

  /**
   * Process a Cloud Resource.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   An entity object.
   *
   * @return bool
   *   Succeeded or failed.
   */
  abstract protected function processCloudResource(CloudContentEntityBase $entity);

  /**
   * Process entity.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   An entity object.
   *
   * @return bool
   *   Succeeded or failed.
   */
  abstract protected function processEntity(CloudContentEntityBase $entity);

  /**
   * Process an entity and related cloud resource.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   An entity object.
   *
   * @return bool
   *   Succeeded or failed.
   */
  protected function process(CloudContentEntityBase $entity) {

    try {
      if ($this->processCloudResource($entity)) {

        $this->processEntity($entity);
        $this->processOperationStatus($entity, 'processed');

        return TRUE;
      }

      $this->processOperationErrorStatus($entity, 'processed');

      return FALSE;
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * Returns the message to show the user after an item was processed.
   *
   * @param int $count
   *   Count of processed translations.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The item processed message.
   */
  protected function getProcessedMessage($count) {

    return $this->formatPlural($count, 'Processed @count item.', 'Processed @count items.');
  }

  /**
   * Returns the message to show the user when an item has not been processed.
   *
   * @param int $count
   *   Count of processed translations.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The item inaccessible message.
   */
  protected function getInaccessibleMessage($count) {

    return $this->formatPlural($count,
      '@count item has not been processed because you do not have the necessary permissions.',
      '@count items have not been processed because you do not have the necessary permissions.'
    );
  }

}
