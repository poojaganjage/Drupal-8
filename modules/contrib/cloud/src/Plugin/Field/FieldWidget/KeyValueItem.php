<?php

namespace Drupal\cloud\Plugin\Field\FieldWidget;

use Drupal\cloud\Plugin\Field\Util\ReservedKeyCheckerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ClassResolver;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'key_value_item' widget.
 *
 * @FieldWidget(
 *   id = "key_value_item",
 *   label = @Translation("Key value"),
 *   field_types = {
 *     "key_value"
 *   }
 * )
 */
class KeyValueItem extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolver
   */
  private $classResolver;

  /**
   * Constructs a KeyValueItem instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\DependencyInjection\ClassResolver $class_resolver
   *   The class resolver service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    ClassResolver $class_resolver) {

    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings);

    $this->classResolver = $class_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('class_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'reserved_key_checker_class' => '',
      'value_converter_class' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['reserved_key_checker_class'] = [
      '#title' => $this->t('Reserved key checker class'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('reserved_key_checker_class'),
    ];

    $element['value_converter_class'] = [
      '#title' => $this->t('Value converter class'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('value_converter_class'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $key = $items[$delta]->item_key ?? NULL;
    $value = $items[$delta]->item_value ?? NULL;

    // Check if the key is reserved word or not.
    $reserved = FALSE;
    $reserved_key_checker_class = $this->getSetting('reserved_key_checker_class');
    if (!empty($reserved_key_checker_class)) {
      $key_checker = $this->classResolver->getInstanceFromDefinition($reserved_key_checker_class);
      if ($key_checker && $key_checker instanceof ReservedKeyCheckerInterface) {
        $reserved = $key_checker->isReservedWord($key);
      }
    }

    // Convert value.
    $value_converter_class = $this->getSetting('value_converter_class');
    if (!empty($value_converter_class)) {
      $value_converter = $this->classResolver->getInstanceFromDefinition($value_converter_class);
      if ($value_converter && $value_converter instanceof ValueConverterInterface) {
        $value = $value_converter->convert($key, $value);
      }
    }

    $element['item_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#size' => 60,
      '#default_value' => $key,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
      '#disabled' => $reserved,
    ];

    $element['item_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#size' => 60,
      '#default_value' => $value,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
      '#disabled' => $reserved,
    ];

    return $element;
  }

}
