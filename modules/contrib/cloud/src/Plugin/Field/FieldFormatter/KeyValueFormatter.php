<?php

namespace Drupal\cloud\Plugin\Field\FieldFormatter;

use Drupal\cloud\Plugin\Field\Util\ValueConverterInterface;
use Drupal\Core\DependencyInjection\ClassResolver;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'key_value_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "key_value_formatter",
 *   label = @Translation("Key value formatter"),
 *   field_types = {
 *     "key_value"
 *   }
 * )
 */
class KeyValueFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

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
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
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
    $label,
    $view_mode,
    array $third_party_settings,
    ClassResolver $class_resolver) {

    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('class_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'value_converter_class' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $rows = [];

    // Get value converter.
    $value_converter = NULL;
    $value_converter_class = $this->getSetting('value_converter_class');
    if (!empty($value_converter_class)) {
      $value_converter = $this->classResolver->getInstanceFromDefinition($value_converter_class);
    }

    foreach ($items ?: [] as $item) {
      /* @var \Drupal\cloud\Plugin\Field\FieldType\KeyValue $item */
      if (!$item->isEmpty()) {
        $value = $item->item_value;
        if ($value_converter && $value_converter instanceof ValueConverterInterface) {
          $value = $value_converter->convert($item->item_key, $value);
        }

        $rows[] = [
          $item->item_key,
          $value,
        ];
      }
    }

    if (count($rows)) {
      $elements[0] = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Key'),
          $this->t('Value'),
        ],
        '#rows' => $rows,
      ];
    }

    return $elements;
  }

}
