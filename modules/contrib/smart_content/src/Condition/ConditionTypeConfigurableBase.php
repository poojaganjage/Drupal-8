<?php

namespace Drupal\smart_content\Condition;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ObjectWithPluginCollectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\smart_content\Condition\Group\ConditionGroupManager;
use Drupal\smart_content\Condition\Type\ConditionTypeManager;
use Drupal\smart_content\Condition\Type\ConditionTypePluginCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class for conditions using the type plugin.
 *
 * @package Drupal\smart_content\Condition
 */
abstract class ConditionTypeConfigurableBase extends ConditionBase implements PluginFormInterface, ObjectWithPluginCollectionInterface {

  /**
   * The condition type plugin manager.
   *
   * @var \Drupal\smart_content\Condition\Type\ConditionTypeManager
   */
  protected $conditionTypeManager;

  /**
   * The plugin collection to lazy load the condition type plugin.
   *
   * @var \Drupal\smart_content\Condition\Type\ConditionTypeInterface
   */
  protected $conditionTypeCollection;

  /**
   * Constructs a ConditionBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\smart_content\Condition\Group\ConditionGroupManager $conditionGroupManager
   *   The condition group plugin manager.
   * @param \Drupal\smart_content\Condition\Type\ConditionTypeManager $conditionTypeManager
   *   The condition type plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConditionGroupManager $conditionGroupManager, ConditionTypeManager $conditionTypeManager) {
    $this->conditionTypeManager = $conditionTypeManager;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $conditionGroupManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.smart_content.condition_group'),
      $container->get('plugin.manager.smart_content.condition_type')
    );
  }

  /**
   * Helper function to return condition type.
   *
   * @return \Drupal\smart_content\Condition\Type\ConditionTypeInterface|object
   *   The condition type.
   */
  public function getConditionType() {
    return $this->getConditionTypePluginCollection()
      ->get($this->getPluginDefinition()['type']);
  }

  /**
   * Gets the plugin collections used by this object.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection[]
   *   An array of plugin collections, keyed by the property name they use to
   *   store their configuration.
   */
  public function getPluginCollections() {
    return [
      'condition_type_settings' => $this->getConditionTypePluginCollection(),
    ];
  }

  /**
   * Encapsulates the creation of the conditions's LazyPluginCollection.
   *
   * @return \Drupal\smart_content\Condition\Type\ConditionTypePluginCollection
   *   The condition's type plugin collection.
   */
  protected function getConditionTypePluginCollection() {
    $plugin_type_definition = $this->getPluginDefinition()['type'];
    if (!$this->conditionTypeCollection) {
      $configuration = [];
      if (!empty($this->configuration['condition_type_settings'])) {
        $configuration = $this->configuration['condition_type_settings'];
      }
      $this->conditionTypeCollection = new ConditionTypePluginCollection($this->conditionTypeManager, $plugin_type_definition, (array) $configuration, $this);
    }
    return $this->conditionTypeCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = ConditionBase::attachNegateElement($form, $this->configuration);
    $form['#attributes']['class'] = ['condition'];
    $definition = $this->getPluginDefinition();
    $label = $definition['label'];
    if ($group_label = $this->getGroupLabel()) {
      $label .= '(' . $group_label . ')';
    }
    $form['label'] = [
      '#type' => 'container',
      '#markup' => $label,
      '#attributes' => ['class' => ['condition-label']],
    ];
    return $this->getConditionType()
      ->buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->getConditionType()->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setNegated($form_state->getValue('negate'));
    $this->getConditionType()->submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return $this->getConditionType()->getLibraries();
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedSettings() {
    $settings = parent::getAttachedSettings();
    // Add the field 'settings' from the ConditionType Plugin.
    // @todo: do we need getFieldAttachedSettings() ?
    $settings['field']['settings'] = $this->getConditionType()
      ->getAttachedSettings();
    // Get the 'settings' from the ConditionType Plugin.
    $settings['settings'] = $this->getConditionType()->getAttachedSettings();
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'condition_type_settings' => $this->getConditionType()
        ->defaultConfiguration(),
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $configuration = parent::getConfiguration();
    foreach ($this->getPluginCollections() as $plugin_config_key => $plugin_collection) {
      $configuration[$plugin_config_key] = $plugin_collection->getConfiguration();
    }
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeId() {
    return 'type:' . $this->getConditionType()->getPluginId();
  }

}
