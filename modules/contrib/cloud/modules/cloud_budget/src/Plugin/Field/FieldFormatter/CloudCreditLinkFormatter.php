<?php

namespace Drupal\cloud_budget\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'credit_link_formatter' formatter.
 *
 * This formatter links user name to the entity of
 * cloud credits.
 *
 * @FieldFormatter(
 *   id = "credit_link_formatter",
 *   label = @Translation("Cloud Credit Link"),
 *   field_types = {
 *     "entity_reference",
 *   }
 * )
 */
class CloudCreditLinkFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructs a StringFormatter instance.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entity = $items->getEntity();
    $storage = $this->entityTypeManager->getStorage('cloud_credit');
    foreach ($items ?: [] as $delta => $item) {
      if (!$item->isEmpty()) {
        $label = $item->entity->label();
        $cloud_credits = $storage->loadByProperties([
          'cloud_context' => $entity->getCloudContext(),
          'user' => $item->entity->id(),
        ]);

        if (empty($cloud_credits)) {
          continue;
        }

        $cloud_credit = reset($cloud_credits);

        $elements[$delta] = [
          '#type' => 'link',
          '#url' => Url::fromRoute('entity.cloud_credit.canonical', [
            'cloud_context' => $entity->getCloudContext(),
            'cloud_credit' => $cloud_credit->id(),
          ]),
          '#title' => $label,
        ];
      }
    }
    return $elements;
  }

}
