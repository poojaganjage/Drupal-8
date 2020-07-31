<?php

namespace Drupal\cloud\Plugin\Field\FieldFormatter;

use Drupal\cloud\Service\AnsiStringRendererInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'ansi_string_formatter' formatter.
 *
 * This formatter use PRE tag to surround content.
 *
 * @FieldFormatter(
 *   id = "ansi_string_formatter",
 *   label = @Translation("ANSI tag string formatter"),
 *   field_types = {
 *     "string_long",
 *   }
 * )
 */
class AnsiStringFormatter extends FormatterBase {

  /**
   * The ANSI string renderer service.
   *
   * @var \Drupal\cloud\Service\AnsiStringRendererInterface
   */
  private $ansiStringRenderer;

  /**
   * Constructs an AnsiStringFormatter instance.
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
   * @param \Drupal\cloud\Service\AnsiStringRendererInterface $ansi_string_renderer
   *   The entity link render service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    AnsiStringRendererInterface $ansi_string_renderer) {

    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings);

    $this->ansiStringRenderer = $ansi_string_renderer;
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
      $container->get('cloud.ansi_string_renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items ?: [] as $delta => $item) {
      $elements[$delta] = $this->ansiStringRenderer->render($item->value);
    }

    return $elements;
  }

}
