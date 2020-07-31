<?php

namespace Drupal\k8s\Plugin\cloud\config;

use Drupal\cloud\Plugin\cloud\CloudPluginBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\Filesystem;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Kubernetes cloud service provider plugin.
 */
class K8sCloudConfigPlugin extends CloudPluginBase implements CloudConfigPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * K8sCloudConfigPlugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity type manager.
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   The FileSystem object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager,
                              FileSystem $fileSystem) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('file_system')
    );
  }

  /**
   * Load all entities for a given entity type and bundle.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Array of Entity Interface.
   */
  public function loadConfigEntities() {
    return $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type'])->loadByProperties(['type' => [$this->pluginDefinition['entity_bundle']]]);
  }

  /**
   * Load an array of credentials.
   *
   * @param string $cloud_context
   *   Cloud Cotext string.
   *
   * @return array
   *   Array of credentials.
   */
  public function loadCredentials($cloud_context) {
    /* @var \Drupal\cloud\Entity\CloudConfig $entity */
    $entity = $this->loadConfigEntity($cloud_context);
    $credentials = [];
    if ($entity !== FALSE) {
      $credentials['master'] = $entity->get('field_api_server')->value;
      $credentials['token'] = $entity->get('field_token')->value;
    }
    return $credentials;
  }

  /**
   * Load a cloud service provider (CloudConfig) entity.
   *
   * @param string $cloud_context
   *   Cloud Cotext string.
   *
   * @return bool|mixed
   *   Entity or FALSE if there is no entity.
   */
  public function loadConfigEntity($cloud_context) {
    $entity = $this->entityTypeManager
      ->getStorage($this->pluginDefinition['entity_type'])
      ->loadByProperties(['cloud_context' => [$cloud_context]]);

    if (count($entity) === 1) {
      return array_shift($entity);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceCollectionTemplateName() {
    return 'view.k8s_node.list';
  }

  /**
   * {@inheritdoc}
   */
  public function getPricingPageRoute() {
    return '';
  }

}
