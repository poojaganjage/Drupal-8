<?php

namespace Drupal\rate;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Datetime\DateFormatter;

/**
 * Form controller for vote type forms.
 */
class RateWidgetForm extends EntityForm {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs the VoteTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, EntityFieldManagerInterface $entity_field_manager, DateFormatter $date_formatter) {
    $this->entityTypeManager = $entity_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $rate_widget = $this->entity;

    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add rate widget');
    }
    else {
      $form['#title'] = $this->t('Edit %label rate widget', ['%label' => $rate_widget->label()]);
    }

    $form['label'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#default_value' => $rate_widget->label(),
      '#description' => $this->t('The human-readable name of this rate widget. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $rate_widget->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => ['Drupal\rate\Entity\RateWidget', 'load'],
        'source' => ['label'],
      ],
      '#description' => $this->t('A unique machine-readable name for this rate widget. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    // Widget types for the widget select table field.
    $widget_type_options = [
      'fivestar' => 'Fivestar',
      'numberupdown' => 'Number Up / Down',
      'thumbsup' => 'Thumbs Up',
      'thumbsupdown' => 'Thumbs Up / Down',
      'yesno' => 'Yes / No',
    ];

    $form['template'] = [
      '#type' => 'select',
      '#title' => $this->t('Template'),
      '#empty_value' => '',
      '#required' => TRUE,
      '#options' => $widget_type_options,
      '#default_value' => $rate_widget->get('template'),
    ];

    // Get the number of option table items (rows), default to 1.
    $options_table_items = $form_state->get('options_table_items');
    // We have to ensure that there is at least one name field.
    if ($options_table_items === NULL) {
      $options_table_rows = $form_state->set('options_table_items', 1);
      $options_table_items = 1;
    }
    // Check, if we have options in the config.
    if ($options_table_items < count($rate_widget->get('options'))) {
      $options_table_rows = $form_state->set('options_table_items', count($rate_widget->get('options')));
      $options_table_items = count($rate_widget->get('options'));
    }

    $form['#tree'] = TRUE;
    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Options'),
      '#description' => $this->t('Define the available voting options/buttons. To delete an option - delete its values in the fields <i>value, label</i> and <i>class</i> and save the rate widget.'),
      '#open' => TRUE,
      '#prefix' => '<div id="options-table-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['options']['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Value'),
        $this->t('Label'),
        $this->t('Class'),
      ],
      '#responsive' => TRUE,
    ];

    $rate_widget_options = $rate_widget->get('options');

    if (count($rate_widget_options) == 0) {
      $form['options']['table'][0]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),
        '#title_display' => 'invisible',
        '#default_value' => '',
        '#size' => 8,
      ];
      $form['options']['table'][0]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#title_display' => 'invisible',
        '#default_value' => '',
        '#size' => 40,
      ];
      $form['options']['table'][0]['class'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Class'),
        '#title_display' => 'invisible',
        '#default_value' => '',
        '#size' => 40,
      ];
    }
    else {
      for ($i = 0; $i < $options_table_items; $i++) {
        $form['options']['table'][$i]['value'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Value'),
          '#title_display' => 'invisible',
          '#default_value' => $rate_widget_options[$i]['value'] ? $rate_widget_options[$i]['value'] : '',
          '#size' => 8,
        ];
        $form['options']['table'][$i]['label'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Label'),
          '#title_display' => 'invisible',
          '#default_value' => $rate_widget_options[$i]['label'],
          '#size' => 40,
        ];
        $form['options']['table'][$i]['class'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Class'),
          '#title_display' => 'invisible',
          '#default_value' => $rate_widget_options[$i]['class'] ? $rate_widget_options[$i]['class'] : '',
          '#size' => 40,
        ];
      }
    }
    $form['options']['actions'] = [
      '#type' => 'actions',
      '#prefix' => '<div id="action-buttons-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['options']['actions']['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another option'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'options-table-wrapper',
      ],
    ];
    $form['entities'] = [
      '#type' => 'details',
      '#title' => $this->t('Entities'),
      '#description' => $this->t('Select the entities and/or comments on those entities, on which to enable this widget.'),
      '#open' => TRUE,
      '#prefix' => '<div id="options-table-wrapper">',
      '#suffix' => '</div>',
    ];
    $comment_module_enabled = \Drupal::service('module_handler')->moduleExists('comment');
    $comment_header = ($comment_module_enabled) ? $this->t('Comment') : $this->t('Comment (disabled)');
    $form['entities']['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Entity Type'),
        $this->t('Entity'),
        $comment_header,
      ],
      '#responsive' => TRUE,
    ];
    $entity_types = $this->entityTypeManager->getDefinitions();
    $entity_type_ids = array_keys($entity_types);
    $rate_widget_entities = $rate_widget->get('entity_types') ? $rate_widget->get('entity_types') : [];
    $rate_widget_comments = $rate_widget->get('comment_types') ? $rate_widget->get('comment_types') : [];

    foreach ($entity_types as $entity_type_id => $entity_type) {
      // Only allow voting on content entities.
      // Also, don't allow voting on votes, that would be weird.
      if ($entity_type->getBundleOf() && $entity_type->getBundleOf() != 'vote') {
        $bundles = $this->entityTypeManager->getStorage($entity_type_id)->loadMultiple();
        $content_entitites_with_bundles[] = $entity_type->getBundleOf();
        if (!empty($bundles)) {
          foreach ($bundles as $bundle) {
            $row = [];
            $return_value = $entity_type->getBundleOf() . '.' . $bundle->id();
            $row['entity_type'] = ['#plain_text' => $bundle->label()];
            $row['entity_type_id'] = ['#plain_text' => $entity_type->getBundleOf()];
            // Entity column.
            $row['entity_enabled'] = [
              '#type' => 'checkbox',
              '#empty_value' => '',
              '#required' => FALSE,
              '#default_value' => in_array($return_value, $rate_widget_entities),
              '#return_value' => $return_value,
            ];
            // Comment column.
            $row['comment_enabled'] = [
              '#type' => 'checkbox',
              '#empty_value' => '',
              '#required' => FALSE,
              '#default_value' => in_array($return_value, $rate_widget_comments),
              '#return_value' => $return_value,
              '#disabled' => !$comment_module_enabled,
            ];
            $form['entities']['table'][] = $row;
          }
        }
      }
      elseif ($entity_type->getGroup() == 'content' && !in_array($entity_type->getBundleEntityType(), $entity_type_ids) && $entity_type_id != 'vote_result') {
        $row = [];
        $return_value = $entity_type_id . '.' . $entity_type_id;
        $row['entity_type'] = ['#plain_text' => $entity_type->getLabel()->__toString()];
        $row['entity_type_id'] = ['#plain_text' => $entity_type_id];
        // Entity column.
        $row['entity_enabled'] = [
          '#type' => 'checkbox',
          '#empty_value' => '',
          '#required' => FALSE,
          '#default_value' => in_array($return_value, $rate_widget_entities),
          '#return_value' => $return_value,
        ];
        // Comment column.
        $row['comment_enabled'] = [
          '#type' => 'checkbox',
          '#empty_value' => '',
          '#required' => FALSE,
          '#default_value' => in_array($return_value, $rate_widget_comments),
          '#return_value' => $return_value,
          '#disabled' => !$comment_module_enabled,
        ];
        $form['entities']['table'][] = $row;
      }
    }

    // Voting settings.
    $voting = $rate_widget->get('voting');
    $form['voting'] = [
      '#type' => 'details',
      '#title' => $this->t('Voting settings'),
      '#open' => TRUE,
    ];
    $form['voting']['use_deadline'] = [
      '#type' => 'checkbox',
      '#title' => t('Use a vote deadline'),
      '#default_value' => isset($voting['use_deadline']) ? $voting['use_deadline'] : 0,
      '#description' => t('Enables a deadline date field on the respective node. If deadline is set and date passed, the widget will be shown as disabled.'),
    ];
    // Additional settings for rollover 'Never' or 'Immediately'.
    // Work in progress for both options in votingapi module.
    // See https://www.drupal.org/project/votingapi/issues/3060468 (Reg. user).
    // See https://www.drupal.org/project/votingapi/issues/2791129 (Anonymous).
    // @TODO: When those options get committed in votingapi - rewrite this.
    $unit_options = [
      300,
      900,
      1800,
      3600,
      10800,
      21600,
      32400,
      43200,
      86400,
      172800,
      345600,
      604800,
    ];
    $options = [];
    foreach ($unit_options as $option) {
      // $options[$option] = $this->dateFormatter->formatInterval($option);
      $options[$option] = method_exists($option, 'language') ? $this->dateFormatter->formatInterval($option) : '';
    }
    $options[0] = $this->t('Immediately');
    $options[-1] = $this->t('Never');
    $options[-2] = $this->t('Votingapi setting');

    $form['voting']['anonymous_window'] = [
      '#type' => 'select',
      '#title' => $this->t('Anonymous vote rollover'),
      '#description' => $this->t('The amount of time that must pass before two anonymous votes from the same computer are considered unique. Setting this to <i>never</i> will eliminate most double-voting, but will make it impossible for multiple anonymous on the same computer (like internet cafe customers) from casting votes.'),
      '#options' => $options,
      '#default_value' => isset($voting['anonymous_window']) ? $voting['anonymous_window'] : -2,
    ];
    $form['voting']['user_window'] = [
      '#type' => 'select',
      '#title' => $this->t('Registered user vote rollover'),
      '#description' => $this->t('The amount of time that must pass before two registered user votes from the same user ID are considered unique. Setting this to <i>never</i> will eliminate most double-voting for registered users.'),
      '#options' => $options,
      '#default_value' => isset($voting['user_window']) ? $voting['user_window'] : -2,
    ];

    // Display settings.
    $display = $rate_widget->get('display');
    $form['display'] = [
      '#type' => 'details',
      '#title' => $this->t('Display settings'),
      '#open' => TRUE,
    ];
    $form['display']['display_label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => isset($display['display_label']) ? $display['display_label'] : '',
      '#description' => t('Optional label for the rate widget.'),
    ];
    $form['display']['label_class'] = [
      '#type' => 'textfield',
      '#title' => t('Label classes'),
      '#default_value' => isset($display['label_class']) ? $display['label_class'] : '',
      '#description' => t('Enter classes, separated with space.'),
    ];
    $options = [
      'inline' => $this->t('Inline with the widget'),
      'above' => $this->t('Above the widget'),
      'hidden' => $this->t('Hide the label'),
    ];
    $form['display']['label_position'] = [
      '#type' => 'radios',
      '#title' => t('Position of the label'),
      '#options' => $options,
      '#default_value' => isset($display['label_position']) ? $display['label_position'] : 'above',
    ];
    $form['display']['description'] = [
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#default_value' => isset($display['description']) ? $display['description'] : '',
      '#description' => t('Optional description which will be visible on the rate widget.'),
    ];
    $form['display']['description_class'] = [
      '#type' => 'textfield',
      '#title' => t('Description classes'),
      '#default_value' => isset($display['description_class']) ? $display['description_class'] : '',
      '#description' => t('Enter classes, separated with space.'),
    ];
    $options = [
      'below' => $this->t('Under the widget'),
      'right' => $this->t('To the right of the widget'),
      'hidden' => $this->t('Hide the description'),
    ];
    $form['display']['description_position'] = [
      '#type' => 'radios',
      '#title' => t('Position of the description'),
      '#options' => $options,
      '#default_value' => isset($display['description_position']) ? $display['description_position'] : 'below',
    ];
    $form['display']['readonly'] = [
      '#type' => 'checkbox',
      '#title' => t('Read only widget'),
      '#default_value' => isset($display['readonly']) ? $display['readonly'] : 0,
    ];

    // Results settings.
    $results = $rate_widget->get('results');
    $form['results'] = [
      '#type' => 'details',
      '#title' => $this->t('Results'),
      '#description' => $this->t('Note that these settings do not apply for rate widgets inside views. Widgets in views will display the average voting when a relationship to the voting results is used and the users vote in case of a relationship to the votes.'),
      '#open' => TRUE,
    ];
    $options = [
      'below' => $this->t('Under the widget (or option)'),
      'right' => $this->t('To the right of the widget'),
      'hidden' => $this->t('Hide the results summary'),
    ];
    $form['results']['result_position'] = [
      '#type' => 'radios',
      '#title' => t('Position of the results summary'),
      '#options' => $options,
      '#default_value' => isset($results['result_position']) ? $results['result_position'] : 'right',
    ];
    $options = [
      'user_vote_empty' => $this->t('User vote if available, empty otherwise'),
      'user_vote_average' => $this->t('User vote if available, average otherwise'),
      'vote_average' => $this->t('Average rating'),
    ];
    $form['results']['result_type'] = [
      '#type' => 'radios',
      '#title' => t('Which rating should be displayed?'),
      '#options' => $options,
      '#default_value' => isset($results['result_type']) ? $results['result_type'] : 'user_vote_empty',
    ];
    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['options'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $options_table_rows = $form_state->get('options_table_items');
    $add_button = $options_table_rows + 1;
    $form_state->set('options_table_items', $add_button);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save rate widget');
    $actions['delete']['#value'] = $this->t('Delete rate widget');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $rate_widget = $this->entity;
    $rate_widget->set('id', trim($rate_widget->id()));
    $rate_widget->set('label', trim($rate_widget->label()));
    $rate_widget->set('template', $rate_widget->get('template'));

    // Prepare the options for saving.
    $options = $rate_widget->get('options');
    unset($options['actions']);
    $options = $options['table'];

    // Remove empty optons.
    foreach ($options as $key => $value) {
      if ($options[$key]['value'] == NULL) {
        unset($options[$key]);
      }
    }
    // Reindex and set the options.
    $options = array_values($options);
    $rate_widget->set('options', $options);

    // Prepare the entities for saving.
    $entities = $rate_widget->get('entities');
    $entities = $entities['table'];

    // Remove empty entities.
    foreach ($entities as $key => $value) {
      if ($entities[$key]['entity_enabled'] == NULL && $entities[$key]['comment_enabled'] == NULL) {
        unset($entities[$key]);
      }
    }
    // Reindex and set the entities.
    $entities = array_values($entities);
    $entity_types = [];
    $comment_types = [];

    // Save current entities to remove rate_vote_deadline field.
    $current_entities = $rate_widget->get('entity_types');
    $rate_widget->set('entities', $entities);

    // Split the values in separate arrays for compatibility with D7.
    foreach ($entities as $key => $value) {
      if ($value['entity_enabled'] && $value['comment_enabled']) {
        $entity_types[] = $value['entity_enabled'];
        $comment_types[] = $value['comment_enabled'];
      }
      elseif ($value['entity_enabled']) {
        $entity_types[] = $value['entity_enabled'];
      }
      elseif ($value['comment_enabled']) {
        $comment_types[] = $value['comment_enabled'];
      }
    }
    $rate_widget->set('entity_types', $entity_types);
    $rate_widget->set('comment_types', $comment_types);

    // Remove field_rate_vote_deadline if rate widget was detached.
    $removed_entities = array_diff($current_entities, $rate_widget->get('entity_types'));
    foreach ($removed_entities as $key => $entity) {
      $parameter = explode('.', $entity);
      if (!empty(FieldConfig::loadByName($parameter[0], $parameter[1], 'field_rate_vote_deadline'))) {
        FieldConfig::loadByName($parameter[0], $parameter[1], 'field_rate_vote_deadline')->delete();
      }
    }
    // Set the voting, display and results settings.
    $voting = ($rate_widget->get('voting')) ? $rate_widget->get('voting') : [];
    $display = ($rate_widget->get('display')) ? $rate_widget->get('display') : [];
    $results = ($rate_widget->get('results')) ? $rate_widget->get('results') : [];

    $rate_widget->set('voting', $voting);
    $rate_widget->set('display', $display);
    $rate_widget->set('results', $results);

    // Save the widget.
    $status = $rate_widget->save();

    $t_args = ['%name' => $rate_widget->label()];

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('The rate widget %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('The rate widget %name has been added.', $t_args));
      $context = array_merge($t_args, ['link' => $rate_widget->toLink($this->t('View'), 'collection')->toString()]);
      $this->logger('rate_widget')->notice('Added rate widget %name.', $context);
    }
    $this->entityFieldManager->clearCachedFieldDefinitions();
    $form_state->setRedirect('entity.rate_widget.collection');
  }

}
