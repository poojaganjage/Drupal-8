<?php

namespace Drupal\cloud\Plugin\Field\FieldFormatter;

use Drupal\cloud\Service\EntityLinkRendererInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity_link' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_link",
 *   label = @Translation("Entity link"),
 *   field_types = {
 *     "string",
 *     "list_string",
 *   }
 * )
 */
class EntityLinkFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity link renderer service.
   *
   * @var \Drupal\cloud\Service\EntityLinkRendererInterface
   */
  private $entityLinkRenderer;

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
   * @param \Drupal\cloud\Service\EntityLinkRendererInterface $entity_link_renderer
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
    EntityLinkRendererInterface $entity_link_renderer) {

    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings);

    $this->entityLinkRenderer = $entity_link_renderer;
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
      $container->get('entity.link_renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the entity link.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'target_type' => '',
      'field_name' => '',
      'comma_separated' => FALSE,
      'html_generator_class' => '',
      'extra_route_parameter' => '',
      'extra_route_parameter_entity_method' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['target_type'] = [
      '#title' => $this->t('Target type'),
      '#type' => 'textfield',
      '#size' => 60,
      '#maxlength' => 128,
      '#default_value' => $this->getSetting('target_type'),
    ];

    $element['field_name'] = [
      '#title' => $this->t('Field name'),
      '#type' => 'textfield',
      '#size' => 60,
      '#maxlength' => 128,
      '#default_value' => $this->getSetting('field_name'),
    ];

    $element['comma_separated'] = [
      '#title' => $this->t('Separated with comma'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('comma_separated'),
    ];

    $element['html_generator_class'] = [
      '#title' => $this->t('HTML generator class'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('html_generator_class'),
    ];

    $element['extra_route_parameter'] = [
      '#title' => $this->t('Extra route parameter'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('extra_route_parameter'),
    ];

    $element['extra_route_parameter_entity_method'] = [
      '#title' => $this->t('The entity method for extra route parameter'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('extra_route_parameter_entity_method'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $cloud_context = '';
    $entity = $items->getEntity();
    if (!empty($entity) && method_exists($entity, 'getCloudContext')) {
      $cloud_context = $entity->getCloudContext();
    }

    $element = [];
    $target_type = $this->getSetting('target_type');
    $field_name = $this->getSetting('field_name');
    $comma_separated = $this->getSetting('comma_separated');
    $html_generator_class = $this->getSetting('html_generator_class');
    $extra_route_parameter = $this->getSetting('extra_route_parameter');
    $extra_route_parameter_entity_method = $this->getSetting('extra_route_parameter_entity_method');

    foreach ($items ?: [] as $delta => $item) {
      $value = $item->value;
      if ($comma_separated) {
        $value = array_map(static function ($item) {
          return trim($item);
        }, explode(',', $value));
      }

      $element[$delta] = $this->entityLinkRenderer
        ->renderViewElement(
          $value,
          $target_type,
          $field_name,
          [],
          '',
          $html_generator_class,
          $cloud_context,
          $extra_route_parameter,
          $extra_route_parameter_entity_method
        );
    }

    return $element;
  }

}
