<?php

namespace Drupal\smart_content\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\smart_content\Condition\ConditionManager;
use Drupal\smart_content\Entity\SegmentSetConfig;
use Drupal\smart_content\Segment;
use Drupal\smart_content\SegmentSet;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity form for "smart_content_segment_set" entities.
 *
 * @package Drupal\smart_content\Form
 */
class SegmentSetConfigEntityForm extends EntityForm {

  /**
   * SmartSegmentSet entity.
   *
   * @var \Drupal\smart_content\Entity\SegmentSetConfig
   */
  protected $entity;

  /**
   * The segment uuid.
   *
   * @var string
   */
  protected $default;

  /**
   * Condition plugin manager.
   *
   * @var \Drupal\smart_content\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConditionManager $condition_manager) {
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.smart_content.condition')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareEntity() {
    $this->stubEntity($this->entity);
  }

  /**
   * Stub entity with minimal segments and segment conditions.
   *
   * @param \Drupal\smart_content\Entity\SegmentSetConfig $entity
   *   The segment set entity.
   */
  public function stubEntity(SegmentSetConfig $entity) {
    $segment_set = $entity->getSegmentSet();
    // Stub segment if none exist.
    foreach ($segment_set->getSegments() as $segment) {
      if (is_null($segment->getLabel())) {
        $segment->setLabel(static::getUniqueSegmentLabel($segment_set));
      }
      if ($segment->getConditions()->count() === 0) {
        /** @var \Drupal\smart_content\Condition\ConditionInterface $group_condition */
        $group_condition = $this->conditionManager->createInstance('group');
        $segment->appendCondition($group_condition);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $smart_content_segment_set = $this->entity;
    $form['#tree'] = TRUE;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $smart_content_segment_set->label(),
      '#description' => $this->t("Label for the SegmentSet."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $smart_content_segment_set->id(),
      '#machine_name' => [
        'exists' => '\Drupal\smart_content\Entity\SegmentSetConfig::load',
      ],
      '#disabled' => !$smart_content_segment_set->isNew(),
    ];

    $form['#process'][] = [$this, 'buildWidget'];

    return $form;
  }

  /**
   * Processing callback for entity form.
   */
  public function buildWidget(array $element, FormStateInterface $form_state, array $form) {
    $wrapper_id = Html::getUniqueId('segment-set-wrapper');
    // We load and store the SegmentSet instance on $form_state to keep track of
    // the latest instance because nested plugins are expected to update the
    // instance as necessary. We use the elements '#parents' to keep track of
    // where the SegmentSet is stored in the case of multiple SegmentSet forms
    // potentially appearing on a single page.
    if (!$segment_set = static::getSegmentSetState($form_state, $element['#parents'])) {
      $segment_set = $this->entity->getSegmentSet();
    }
    static::saveSegmentSetState($form_state, $element['#parents'], $segment_set);

    $element['segment_set_settings'] = [
      '#type' => 'container',
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $element['segment_set_settings']['segments'] = [
      '#type' => 'table',
      '#header' => ['', $this->t('Weight'), ''],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $wrapper_id . '-order-weight',
        ],
      ],
    ];

    $default = $segment_set->getDefaultSegment();

    foreach ($segment_set->getSegments() as $uuid => $segment) {
      $element['segment_set_settings']['segments'][$uuid]['#attributes']['class'][] = 'draggable';
      $element['segment_set_settings']['segments'][$uuid]['segment_settings']['#type'] = 'fieldset';
      $element['segment_set_settings']['segments'][$uuid]['uuid'] = [
        '#type' => 'value',
        '#value' => $uuid,
      ];
      $element['segment_set_settings']['segments'][$uuid]['segment_settings']['#title'] = 'Segment';
      $element['segment_set_settings']['segments'][$uuid]['segment_settings']['#title_display'] = 'invisible';

      $element['segment_set_settings']['segments'][$uuid]['segment_settings']['label'] = [
        '#title' => $this->t('Segment'),
        '#type' => 'textfield',
        '#default_value' => $segment->getLabel(),
        '#required' => TRUE,
      ];

      foreach ($segment->getConditions() as $condition_id => $condition) {
        SegmentSetConfigEntityForm::pluginForm($condition, $element, $form_state, [
          'segment_set_settings',
          'segments',
          $uuid,
          'segment_settings',
          'condition_settings',
          $condition_id,
          'plugin_form',
        ]);
      }

      $disabled = ($default && $default->getUuid() != $segment->getUuid()) ? 'disabled' : '';

      $element['segment_set_settings']['segments'][$uuid]['segment_settings']['additional_settings'] = [
        '#type' => 'container',
        '#weight' => 10,
        '#attributes' => [
          'class' => ['segment-additional-settings-container'],
          'disabled' => [$disabled],
        ],
      ];

      $element['segment_set_settings']['segments'][$uuid]['segment_settings']['additional_settings']['default'] = [
        '#type' => 'checkbox',
        '#attributes' => [
          'class' => ['smart-segments-default-' . $segment->getUuid()],
          'disabled' => [$disabled],
        ],
        '#title' => $this->t('Set as default segment'),
        '#default_value' => $segment->isDefault(),
      ];
      $element['segment_set_settings']['segments'][$uuid]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#attributes' => ['class' => [$wrapper_id . '-order-weight']],
      ];

      $element['segment_set_settings']['segments'][$uuid]['remove_segment'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove Segment'),
        '#name' => 'remove_segment__' . $uuid,
        '#submit' => [[$this, 'removeElementSegment']],
        '#attributes' => [
          'class' => [
            'align-right',
            'remove-segment',
            'remove-button',
          ],
        ],
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => [$this, 'removeElementSegmentAjax'],
          'wrapper' => $wrapper_id,
        ],
      ];
    }
    $element['segment_set_settings']['add_segment'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Segment'),
      '#name' => 'add_segment',
      '#submit' => [[$this, 'addElementSegment']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'addElementSegmentAjax'],
        'wrapper' => $wrapper_id,
      ],
    ];

    $element['#attached']['library'][] = 'smart_content/form';

    return $element;
  }

  /**
   * Provides a '#submit' callback for adding a Segment.
   */
  public function addElementSegment(array &$form, FormStateInterface $form_state) {
    $parents = array_slice($form_state->getTriggeringElement()['#parents'], 0, -2);
    if (!$segment_set = static::getSegmentSetState($form_state, $parents)) {
      $segment_set = $this->entity->getSegmentSet();
    }

    $full_input = $form_state->getUserInput();
    $input = NestedArray::getValue($full_input, $parents);
    $this->mapFormSegmentWeights($segment_set, $input);

    $segment_set->setSegment(Segment::fromArray());
    $this->entity->setSegmentSet($segment_set);
    $this->stubEntity($this->entity);
    $form_state->setRebuild();
  }

  /**
   * Provides an '#ajax' callback for adding a Segment.
   */
  public function addElementSegmentAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
  }

  /**
   * Provides a '#submit' callback for removing a Segment.
   */
  public function removeElementSegment(array &$form, FormStateInterface $form_state) {
    $parents = array_slice($form_state->getTriggeringElement()['#parents'],
      0, -4);
    if (!$segment_set = static::getSegmentSetState($form_state, $parents)) {
      $segment_set = $this->entity->getSegmentSet();
    }
    list($action, $name) = explode('__', $form_state->getTriggeringElement()['#name']);
    $full_input = $form_state->getUserInput();
    $input = NestedArray::getValue($full_input, $parents);
    $this->mapFormSegmentWeights($segment_set, $input);
    $segment_set->removeSegment($name);
    $this->entity->setSegmentSet($segment_set);

    $this->stubEntity($this->entity);
    $form_state->setRebuild();
  }

  /**
   * Maps form values to segment set.
   *
   * @param \Drupal\smart_content\SegmentSet $segment_set
   *   The segment set object.
   * @param array $values
   *   The form values.
   */
  public function mapFormSegmentWeights(SegmentSet $segment_set, array $values) {
    // We know the segment order matches the form order, so loop through
    // segments and get form values.
    foreach ($segment_set->getSegments() as $segment_id => $segment) {
      $segment->setWeight((int) $values['segment_set_settings']['segments'][$segment_id]['weight']);
    }
    $segment_set->sortSegments();
  }

  /**
   * Provides an '#ajax' callback for removing a Segment.
   */
  public function removeElementSegmentAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$segment_set = static::getSegmentSetState($form_state, $form['#parents'])) {
      $segment_set = $this->entity->getSegmentSet();
    }
    static::fixSegmentStorageIdMismatch($form, $form_state, ['segment_set_settings', 'segments']);
    foreach ($segment_set->getSegments() as $segment_id => $segment) {
      foreach ($segment->getConditions() as $condition_id => $condition) {
        self::pluginFormValidate($condition, $form, $form_state, [
          'segment_set_settings',
          'segments',
          $segment_id,
          'segment_settings',
          'condition_settings',
          $condition_id,
          'plugin_form',
        ]);
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * Correct mismatched segment UUID in form_state values and input.
   *
   * Because segments are stubbed and storage is rebuilt the first time,
   * segments may be re-stubbed and associated uuid's lost, which can cause
   * form_state inputs and values to be mismatched.  This function attempts to
   * copy the orphaned data to the proper segment.  It really is only necessary
   * for the first ajax submit on a given form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $relative_parents
   *   Array of keys for accessing segment data.
   */
  public static function fixSegmentStorageIdMismatch(array $form, FormStateInterface $form_state, array $relative_parents) {
    $values = $form_state->getValues();
    $input = $form_state->getUserInput();

    $absolute_parents = array_merge($form['#parents'], $relative_parents);
    $orphaned_settings = [];
    $orphaned_input = [];
    foreach (NestedArray::getValue($values, $absolute_parents) as $uuid => $settings) {
      $absolute_segment_parents = array_merge($absolute_parents, [$uuid]);
      if (!isset($settings['uuid'])) {
        $orphaned_settings = $settings;
        $orphaned_input = NestedArray::getValue($input, $absolute_segment_parents);
        NestedArray::unsetValue($values, $absolute_segment_parents);
        NestedArray::unsetValue($input, $absolute_segment_parents);
      }
      elseif (!empty($orphaned_settings)) {
        NestedArray::setValue($values, $absolute_segment_parents, $orphaned_settings + $settings);
        NestedArray::setValue($input, $absolute_segment_parents, $orphaned_input);
        $orphaned_settings = [];
        $orphaned_input = [];
      }
    }
    $form_state->setValues($values);
    $form_state->setUserInput($input);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->isValidationComplete()) {
      return;
    }
    if (!$segment_set = static::getSegmentSetState($form_state, $form['#parents'])) {
      $segment_set = $this->entity->getSegmentSet();
    }
    $entity = $this->entity;
    $entity->setSegmentSet($segment_set);
    $values = $form_state->getValues();

    $entity->set('label', $values['label']);
    $entity->set('id', $values['id']);
    $has_default = FALSE;
    foreach ($segment_set->getSegments() as $segment_id => $segment) {
      $segment->setLabel($values['segment_set_settings']['segments'][$segment_id]['segment_settings']['label']);
      if ($values['segment_set_settings']['segments'][$segment_id]['segment_settings']['additional_settings']['default']) {
        $has_default = TRUE;
        $segment_set->setDefaultSegment($segment->getUuid());
      }

      foreach ($segment->getConditions() as $condition_id => $condition) {
        self::pluginFormSubmit($condition, $form, $form_state, [
          'segment_set_settings',
          'segments',
          $segment_id,
          'segment_settings',
          'condition_settings',
          $condition_id,
          'plugin_form',
        ]);
      }
    }

    $this->mapFormSegmentWeights($segment_set, $values);

    if (!$has_default) {
      $segment_set->unsetDefaultSegment();
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $smart_content_segment_set = $this->entity;
    $status = $smart_content_segment_set->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()
          ->addMessage($this->t('Created the %label SegmentSet.', [
            '%label' => $smart_content_segment_set->label(),
          ]));
        break;

      default:
        $this->messenger()
          ->addMessage($this->t('Saved the %label SegmentSet.', [
            '%label' => $smart_content_segment_set->label(),
          ]));
    }
    $form_state->setRedirectUrl($smart_content_segment_set->toUrl('collection'));
  }

  /**
   * Utility for storing SmartSegmentSet $entity based on parents.
   *
   * Stores the $entity based on an elements $parents(usually '#array_parents')
   * to maintain state.  This is necessary for embedded forms where multiple
   * SmartSegmentSet entities may exist.
   */
  public static function saveSegmentSetState($form_state, $parents, SegmentSet $segment_set) {
    NestedArray::setValue($form_state->getStorage(), array_merge(['smart_content'], $parents, ['segment_set']), $segment_set);
  }

  /**
   * Retrieves SmartSegmentSet $segment_set based on $parents.
   */
  public static function getSegmentSetState($form_state, $parents) {
    return NestedArray::getValue($form_state->getStorage(), array_merge(['smart_content'], $parents, ['segment_set']));
  }

  /**
   * Utility for storing SmartSegmentSet $entity based on parents.
   *
   * Stores the $entity based on an elements $parents(usually '#array_parents')
   * to maintain state.  This is necessary for embedded forms where multiple
   * SmartSegmentSet entities may exist.
   */
  public static function saveReactionSetState($form_state, $parents, Decision $reaction_set) {
    NestedArray::setValue($form_state->getStorage(), array_merge(['smart_content'], $parents, ['reaction_set']), $reaction_set);
  }

  /**
   * Retrieves SmartSegmentSet $segment_set based on $parents.
   */
  public static function getReactionSetState($form_state, $parents) {
    return NestedArray::getValue($form_state->getStorage(), array_merge(['smart_content'], $parents, ['reaction_set']));
  }

  /**
   * Utility function for attaching plugin forms.
   *
   * This function attaches forms for plugins implementing
   * Drupal\Core\Plugin\PluginFormInterface.  The plugin form is automatically
   * provided a Drupal\Core\Form\SubformState for tracking $form_state at the
   * plugin level.
   *
   * @param mixed $plugin
   *   Plugin to load form from.
   * @param array $form
   *   Parent form to embed form on.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state from parent form.
   * @param array $parents
   *   Array of keys for embedded form.
   */
  public static function pluginForm($plugin, array &$form, FormStateInterface $form_state, array $parents) {
    // If plugin implements PluginFormInterface, create SubFormState and attach.
    if ($plugin instanceof PluginFormInterface) {
      if (!$plugin_form = NestedArray::getValue($form, $parents)) {
        $plugin_form = [];
      }
      $plugin_form_state = SubformState::createForSubform($plugin_form, $form, $form_state);
      $plugin_form = $plugin->buildConfigurationForm($plugin_form, $plugin_form_state);
      $plugin_form['#tree'] = TRUE;
      // Set PluginForm within array parents.
      NestedArray::setValue($form, $parents, $plugin_form);
    }
  }

  /**
   * Utility function for validating plugin forms.
   *
   * @param mixed $plugin
   *   Plugin to load form from.
   * @param array $form
   *   Parent form to embed form on.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state from parent form.
   * @param array $parents
   *   Array of keys for embedded form.
   */
  public static function pluginFormValidate($plugin, array &$form, FormStateInterface $form_state, array $parents) {
    // If plugin implements PluginFormInterface, validate form.
    if ($plugin instanceof PluginFormInterface) {
      if (!$plugin_form = NestedArray::getValue($form, $parents)) {
        $plugin_form = [];
      }
      $plugin_form_state = SubformState::createForSubform($plugin_form, $form, $form_state);
      $plugin->validateConfigurationForm($plugin_form, $plugin_form_state);
    }
  }

  /**
   * Utility function for submitting plugin forms.
   *
   * @param mixed $plugin
   *   Plugin to load form from.
   * @param array $form
   *   Parent form to embed form on.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state from parent form.
   * @param array $parents
   *   Array of keys for embedded form.
   */
  public static function pluginFormSubmit($plugin, array &$form, FormStateInterface $form_state, array $parents) {
    // If plugin implements PluginFormInterface, submit form.
    if ($plugin instanceof PluginFormInterface) {
      if (!$plugin_form = NestedArray::getValue($form, $parents)) {
        $plugin_form = [];
      }
      $plugin_form_state = SubformState::createForSubform($plugin_form, $form, $form_state);
      $plugin->submitConfigurationForm($plugin_form, $plugin_form_state);
    }
  }

  /**
   * Utility method for generating unique segment label.
   *
   * Function is called recursively, each time incrementing the delta until
   * a unique label is created.
   *
   * @param \Drupal\smart_content\SegmentSet $segment_set
   *   The segment set.
   * @param int $delta
   *   The delta of the segment.
   *
   * @return string
   *   The unique label string.
   */
  public static function getUniqueSegmentLabel(SegmentSet $segment_set, $delta = 1) {
    $has_label = FALSE;
    $segment_label = $delta;
    foreach ($segment_set->getSegments() as $segment) {
      if ($segment->getLabel() == $segment_label) {
        $has_label = TRUE;
        break;
      }
    }
    $delta++;
    return $has_label ? static::getUniqueSegmentLabel($segment_set, $delta) : $segment_label;
  }

  /**
   * Generates a unique ID from the $form array parents.
   *
   * The form array must contain the '#array_parents' property.
   *
   * @param array $form
   *   The form array.
   * @param string|null $suffix
   *   Optional suffix.
   *
   * @return string
   *   An id from array parents.
   */
  public static function getFormParentsUniqueId(array $form, $suffix = NULL) {
    if (!isset($form['#array_parents'])) {
      throw new \InvalidArgumentException('Argument $form must have property #array_parents.');
    }
    $pieces = $form['#array_parents'];
    if ($suffix) {
      $pieces[] = $suffix;
    }
    return Html::getClass(implode('-', $pieces));
  }

}
