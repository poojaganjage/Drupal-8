<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\system\ActionConfigEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract base class for blocks that needs a bulk delete form.
 */
abstract class BulkDeleteBlock extends BlockBase implements ContainerFactoryPluginInterface, FormInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The action storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $actionStorage;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The bulk operations.
   *
   * @var \Drupal\system\Entity\Action[]
   */
  protected $actions;

  /**
   * The entities being listed.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities = [];

  /**
   * Entity key for bulk form.
   *
   * @var string
   */
  protected $entitiesKey = 'entities';

  /**
   * Entity type Id.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Flag used to determine if checkboxes should be included.
   *
   * @var bool
   */
  protected $includeCheckbox = FALSE;

  /**
   * Creates a ResourcesBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The action storage.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AccountInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
    CloudConfigPluginManagerInterface $cloud_config_plugin_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    try {
      $this->actionStorage = $this->entityTypeManager->getStorage('action');
    }
    catch (\Exception $e) {
      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addError("An error occurred: {$e->getMessage()}");
    }
    $this->formBuilder = $form_builder;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'cloud_context' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['cloud_context'] = [
      '#type' => 'select',
      '#title' => $this->t('Cloud Service Provider'),
      '#description' => $this->t('Select cloud service provider.'),
      '#options' => $this->getCloudConfigs(),
      '#default_value' => $this->configuration['cloud_context'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['cloud_context']
      = $form_state->getValue('cloud_context');
  }

  /**
   * Set the entity type id.
   *
   * @param string $entity_type_id
   *   Entity type id to set.
   */
  protected function setEntityTypeId($entity_type_id) {
    $this->entityTypeId = $entity_type_id;
  }

  /**
   * Load the cloud configs as an array for use in a select dropdown.
   *
   * @return array
   *   An array of cloud configs.
   */
  private function getCloudConfigs() {
    $cloud_configs = ['' => $this->t('All AWS Cloud regions')];
    $configs = $this->cloudConfigPluginManager->loadConfigEntities('aws_cloud');
    foreach ($configs ?: [] as $config) {
      $cloud_configs[$config->getCloudContext()] = $config->getName();
    }
    return $cloud_configs;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->formBuilder->getForm($this);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cloud_context = $this->configuration['cloud_context'] ?? '';
    if (!empty($cloud_context)) {
      $this->setIncludeCheckbox(TRUE);
    }
    return $this->buildBulkForm();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entities = [];
    $selected = array_filter($form_state->getValue($this->entitiesKey));
    $action = $this->actions[$form_state->getValue('action')];
    $count = 0;
    $cloud_context = $this->configuration['cloud_context'] ?? '';

    // Do not continue with submit if there is no cloud_context.
    if (empty($cloud_context)) {
      return;
    }

    foreach ($selected ?: [] as $id) {
      $entity = $this->entities[$id];
      // Skip execution if the user did not have access.
      if (!$action->getPlugin()->access($entity)) {
        // NOTE: $this->messenger() is correct.
        // cf. MessengerTrait::messenger() MessengerInterface.
        $this->messenger()->addError($this->t('No access to execute %action on the @entity_type_label %entity_label.', [
          '%action' => $action->label(),
          '@entity_type_label' => $entity->getEntityType()->getSingularLabel(),
          '%entity_label' => $entity->label(),
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
      $form_state->setRedirect(
        $operation_definition['confirm_form_route_name'],
        ['cloud_context' => $cloud_context],
        []
      );
    }
    else {
      // NOTE: $this->messenger() is correct.
      // cf. MessengerTrait::messenger() MessengerInterface.
      $this->messenger()->addWarning($this->formatPlural($count, '%action was not applied to @count item.', '%action was applied to @count items.', [
        '%action' => $action->label(),
      ]));
    }
  }

  /**
   * Set the actions that can be performed.
   *
   * @param array $params
   *   The params to used to query the Action Storage entities.
   *
   * @throws \Drupal\aws_cloud\Plugin\Block\AwsCloudBlockException
   */
  protected function setDeleteActions(array $params) {
    if (empty($this->entityTypeId)) {
      throw new AwsCloudBlockException($this->t('No entity type id found.'));
    }
    $entity_type_id = $this->entityTypeId;
    $this->actions = array_filter(
      $this->actionStorage
        ->loadByProperties($params),
      static function (ActionConfigEntityInterface $action) use ($entity_type_id) {
        return $action->getType() === $entity_type_id;
      }
    );
  }

  /**
   * Get the delete action.
   *
   * @return array
   *   Array of delete actions.
   */
  protected function getDeleteActions() {
    $action_options = [];
    foreach ($this->actions ?: [] as $id => $action) {
      $action_options[$id] = $action->label();
    }
    return $action_options;
  }

  /**
   * Build the table header.
   *
   * @return array
   *   Table header.
   */
  protected function buildTableHeader() {
    $header = [];
    $header['header'] = [
      '#type' => 'container',
      '#weight' => -100,
      '#attributes' => [
        'class' => [
          'actions',
        ],
      ],
    ];
    if ($this->includeCheckbox() === TRUE) {
      $header['header']['action'] = [
        'action' => [
          '#type' => 'select',
          '#title' => $this->t('Action'),
          '#options' => $this->getDeleteActions(),
        ],
      ];
      $header['header']['action-button'] = $this->buildActions();
    }
    $header[$this->entitiesKey] = [
      '#type' => 'table',
      '#header' => [
        ['data' => $this->t('Name')],
      ],
      '#tableselect' => $this->includeCheckbox() === TRUE ? TRUE : FALSE,
      '#attached' => [
        'library' => ['core/drupal.tableselect'],
      ],
    ];

    return $header;
  }

  /**
   * Helper method to build the actions array.
   *
   * @return array
   *   Actions array.
   */
  protected function buildActions() {
    return $this->includeCheckbox() === TRUE ? [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Apply to selected items'),
        '#button_type' => 'primary',
      ],
    ] : [];
  }

  /**
   * Helper method to build a table row.
   *
   * @param string $id
   *   Id for select box column.
   * @param string $row_text
   *   Text to display for name column.
   *
   * @return array
   *   An array of rows.
   */
  protected function buildTableRow($id, $row_text) {
    $row = [];
    if ($this->includeCheckbox() === TRUE) {
      $row['select'] = [
        '#id' => 'edit-entities-' . $id,
        '#type' => 'checkbox',
        '#title' => $this->t('Update this item'),
        '#title_display' => 'invisible',
        '#return_value' => $id,
        '#wrapper_attributes' => [
          'class' => ['table-select'],
        ],
        '#attributes' => [
          'data-drupal-selector' => 'edit-entities',
        ],
        '#parents' => [
          'entities',
          $id,
        ],
      ];
    }
    $row['name']['data'] = $row_text;
    return $row;
  }

  /**
   * Check whether to include bulk delete checkboxes.
   *
   * @return bool
   *   TRUE to include or FALSE not to.
   */
  public function includeCheckbox(): bool {
    return $this->includeCheckbox;
  }

  /**
   * Set the include_checkbox flag.
   *
   * @param bool $includeCheckbox
   *   TRUE|FALSE on whether to include checkbox.
   */
  public function setIncludeCheckbox(bool $includeCheckbox) {
    $this->includeCheckbox = $includeCheckbox;
  }

  /**
   * Build the opening fieldset form element.
   *
   * @param string $title
   *   Title to use for the fieldset.
   *
   * @return array
   *   Form element array.
   */
  public function buildFieldSet($title) {
    return [
      '#type' => 'details',
      '#title' => $title,
      '#open' => TRUE,
      '#attributes' => [
        'class' => [
          'bulk-delete-form',
        ],
      ],
    ];
  }

  /**
   * All extending classes will implement buildBulkForm().
   *
   * @return array
   *   Form array containing bulk form.
   */
  abstract protected function buildBulkForm();

}
