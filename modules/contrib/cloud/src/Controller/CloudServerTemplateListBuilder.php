<?php

namespace Drupal\cloud\Controller;

use Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\system\ActionConfigEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for CloudServerTemplate entity.
 *
 * @ingroup cloud_server_template
 */
class CloudServerTemplateListBuilder extends CloudContentListBuilder implements FormInterface {

  /**
   * Cloud server template plugin manager.
   *
   * @var \Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManager
   */
  private $cloudServerTemplatePluginManager;

  /**
   * The key to use for the form element containing the entities.
   *
   * @var string
   */
  protected $entitiesKey = 'entities';

  /**
   * The entities being listed.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities = [];

  /**
   * The bulk operations.
   *
   * @var \Drupal\system\Entity\Action[]
   */
  protected $actions;

  /**
   * The action storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $actionStorage;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('current_route_match'),
      $container->get('current_user'),
      $container->get('plugin.manager.cloud_server_template_plugin'),
      $container->get('entity_type.manager')->getStorage('action'),
      $container->get('form_builder')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The currently active route match object.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManager $cloud_server_template_plugin_manager
   *   Cloud server template plugin manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $action_storage
   *   The action storage.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, RouteMatchInterface $route_match, AccountProxyInterface $current_user, CloudServerTemplatePluginManager $cloud_server_template_plugin_manager, EntityStorageInterface $action_storage, FormBuilderInterface $form_builder) {

    parent::__construct($entity_type, $storage, $route_match, $current_user);

    $this->entityTypeId = $entity_type->id();
    $this->entityType = $entity_type;
    $this->cloudServerTemplatePluginManager = $cloud_server_template_plugin_manager;
    $this->actionStorage = $action_storage;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Filter the actions to only include those for this entity type.
    $entity_type_id = $this->entityTypeId;
    $this->actions = array_filter($this->actionStorage->loadMultiple(), static function (ActionConfigEntityInterface $action) use ($entity_type_id) {
      return $action->getType() === $entity_type_id;
    });
    $this->entities = $this->load();
    if ($this->entities) {
      return $this->formBuilder->getForm($this);
    }
    return parent::render();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->entityTypeId . '_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form[$this->entitiesKey] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#empty' => $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]),
      '#tableselect' => TRUE,
      '#attached' => [
        'library' => ['core/drupal.tableselect'],
      ],
    ];

    $this->entities = $this->load();
    foreach ($this->entities ?: [] as $entity) {
      $form[$this->entitiesKey][$entity->id()] = $this->buildRow($entity);
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply to selected items'),
      '#button_type' => 'primary',
    ];

    // Ensure a consistent container for filters/operations in the view header.
    $form['header'] = [
      '#type' => 'container',
      '#weight' => -100,
    ];

    $action_options = [];
    foreach ($this->actions ?: [] as $id => $action) {
      $action_options[$id] = $action->label();
    }
    $form['header']['action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#options' => $action_options,
    ];
    // Duplicate the form actions into the action container in the header.
    $form['header']['actions'] = $form['actions'];

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $form['pager'] = [
        '#type' => 'pager',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    $header = [
      // The header gives the table the information it needs in order to make
      // the query calls for ordering. TableSort uses the field information
      // to know what database column to sort by.
      ['data' => $this->t('Name'), 'specifier' => 'name', 'field' => 'name'],
    ];

    // Call the plugin to build the header rows.
    $header = array_merge(
      $header,
      $this->cloudServerTemplatePluginManager->buildListHeader(
        $this->routeMatch->getParameter('cloud_context')
      ));
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $header = $this->buildHeader();
    $query = $this->getStorage()->getQuery();

    // Get cloud_context from a path.
    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    if (isset($cloud_context)) {
      $query->tableSort($header)
        ->condition('cloud_context', $cloud_context);
    }
    else {
      $query->tableSort($header);
    }

    // Only return templates the current user owns.
    if (!$this->currentUser->hasPermission('view any published cloud server templates')) {
      if ($this->currentUser->hasPermission('view own published cloud server templates')) {
        $query->condition('uid', $this->currentUser->id());
      }
      else {
        // Don't return any results if the user does not have any of
        // the above conditions.
        return [];
      }
    }

    $keys = $query->execute();
    return $this->storage->loadMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    // Build our own select box so it fits in the theme css.
    $row['select'] = [
      '#id' => 'edit-entities-' . $entity->id(),
      '#type' => 'checkbox',
      '#title' => $this->t('Update this item'),
      '#title_display' => 'invisible',
      '#return_value' => $entity->id(),
      '#wrapper_attributes' => [
        'class' => ['table-select'],
      ],
      '#attributes' => [
        'data-drupal-selector' => 'edit-entities',
      ],
      '#parents' => [
        'entities',
        $entity->id(),
      ],
    ];
    $row['name']['data'] = Link::createFromRoute(
      $entity->label(),
      'entity.cloud_server_template.canonical',
      [
        'cloud_server_template' => $entity->id(),
        'cloud_context' => $entity->getCloudContext(),
      ]
    )->toRenderable();

    // Call the plugin to build each row.
    $row += $this->cloudServerTemplatePluginManager->buildListRow($entity);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->hasLinkTemplate('launch')) {
      if ($this->currentUser->hasPermission('launch cloud server template')) {
        $operations['launch'] = [
          'title' => $this->t('Launch'),
          'url' => $entity->toUrl('launch'),
          'weight' => 100,
        ];
      }
    }
    if ($entity->hasLinkTemplate('copy')) {
      if ($entity->access('update')) {
        $operations['copy'] = [
          'title' => $this->t('Copy'),
          'url' => $entity->toUrl('copy'),
          'weight' => 100,
        ];
      }
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $selected = array_filter($form_state->getValue($this->entitiesKey));
    if (empty($selected)) {
      $form_state->setErrorByName($this->entitiesKey, $this->t('No items selected.'));
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected = array_filter($form_state->getValue($this->entitiesKey));
    $entities = [];
    $action = $this->actions[$form_state->getValue('action')];
    $count = 0;
    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    foreach ($selected ?: [] as $id) {
      $entity = $this->entities[$id];
      // Skip execution if the user did not have access.
      if (!$action->getPlugin()->access($entity)) {
        // NOTE: $this->messenger() is correct.
        // cf. MessengerTrait::messenger() MessengerInterface.
        $this->messenger()->addError($this->t('No access to execute %action on the @type %label.', [
          '%action' => $action->label(),
          '@type' => $entity->getEntityType()->getLabel(),
          '%label' => $entity->toLink($this->t('View'))->toString(),
        ]));
        continue;
      }

      $count++;
      $entities[$id] = $entity;
    }

    // Don't perform any action unless there are some elements affected.
    // @see https://www.drupal.org/project/drupal/issues/3018148
    if (!$count) {
      return;
    }

    $action->execute($entities);

    $operation_definition = $action->getPluginDefinition();
    if (!empty($operation_definition['confirm_form_route_name'])) {
      $options = [
        'query' => $this->getDestinationArray(),
      ];
      $form_state->setRedirect($operation_definition['confirm_form_route_name'], ['cloud_context' => $cloud_context], $options);
    }
    else {
      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addStatus($this->formatPlural($count, '%action was applied to @count item.', '%action was applied to @count items.', [
        '%action' => $action->label(),
      ]));
    }
  }

}
