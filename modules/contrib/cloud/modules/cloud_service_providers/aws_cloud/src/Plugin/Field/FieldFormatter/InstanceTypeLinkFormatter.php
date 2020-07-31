<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'instance_type_link_formatter' formatter.
 *
 * Links a cloud service provider name to the list of cloud server templates.
 *
 * @FieldFormatter(
 *   id = "instance_type_link_formatter",
 *   label = @Translation("Instance type link"),
 *   field_types = {
 *     "string",
 *     "uri",
 *     "list_string",
 *   }
 * )
 */
class InstanceTypeLinkFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs an EntityLinkFormatter instance.
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
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    RouteMatchInterface $route_match) {

    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );

    $this->routeMatch = $route_match;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entity = $items->getEntity();
    $cloud_context = $entity->getCloudContext();
    if (empty($cloud_context)) {
      $cloud_context = $this->routeMatch->getParameter('cloud_context');
    }

    $elements = [];
    foreach ($items ?: [] as $delta => $item) {
      if (!$item->isEmpty()) {
        $elements[$delta] = [
          '#type' => 'link',
          '#url' => Url::fromRoute(
            'aws_cloud.instance_type_prices',
            ['cloud_context' => $cloud_context],
            ['fragment' => $item->value]
          ),
          '#title' => $item->value,
        ];
      }
    }

    return $elements;
  }

}
