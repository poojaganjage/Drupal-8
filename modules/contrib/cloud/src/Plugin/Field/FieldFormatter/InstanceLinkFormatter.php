<?php

namespace Drupal\cloud\Plugin\Field\FieldFormatter;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'instance_link_formatter' formatter.
 *
 * This formatter links a cloud service provider name to the list of server
 * templates.
 *
 * @FieldFormatter(
 *   id = "instance_link_formatter",
 *   label = @Translation("Instance link"),
 *   field_types = {
 *     "string",
 *     "uri",
 *   }
 * )
 */
class InstanceLinkFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

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
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $entity_type_manager);

    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
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
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entity = $items->getEntity();
    foreach ($items ?: [] as $delta => $item) {
      if (!$item->isEmpty()) {
        $this->cloudConfigPluginManager->setCloudContext($entity->getCloudContext());
        $route = $this->cloudConfigPluginManager->getInstanceCollectionTemplateName();

        // Prepend the icon if it is set.
        $image_url = $this->prepareIcon($entity);
        if (!empty($image_url)) {
          $elements[$delta][] = [
            '#type' => 'link',
            '#title' => [
              '#type' => 'markup',
              // Manually building the image url.  If using the image_style
              // theme function, it adds a white space that shows up as
              // underline on hover.
              '#markup' => '<img src=' . $image_url . ' class="image-style-icon-32x32">',
              '#attached' => [
                'library' => [
                  'cloud/cloud_icon',
                ],
              ],
            ],
            '#url' => Url::fromRoute($route, ['cloud_context' => $entity->getCloudContext()]),
          ];
        }

        $elements[$delta][] = [
          '#type' => 'link',
          '#url' => Url::fromRoute($route, ['cloud_context' => $entity->getCloudContext()]),
          '#title' => $item->value,
        ];
      }
    }
    return $elements;
  }

  /**
   * Prepare the cloud config icon.
   *
   * @param \Drupal\cloud\Entity\CloudConfig $entity
   *   Cloud config entity.
   *
   * @return string
   *   Generated image url or empty if not found.
   */
  private function prepareIcon(CloudConfig $entity) {
    $image_url = '';
    $fid = $entity->getIconFid();

    // Generate icon url only have file_target has been retrieved.
    if (!empty($fid)) {
      $image = File::load($fid);
      try {
        $image_url = ImageStyle::load('icon_32x32')->buildUrl($image->uri->value);
      }
      catch (\Exception $e) {
        $this->handleException($e);
      }
    }
    return $image_url;
  }

}
