<?php

namespace Drupal\aws_cloud\Plugin\cloud\config;

use Drupal\cloud\Plugin\cloud\CloudPluginBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\Filesystem;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AWS Cloud cloud service provider (CloudConfig) plugin.
 */
class AwsCloudConfigPlugin extends CloudPluginBase implements CloudConfigPluginInterface, ContainerFactoryPluginInterface {

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
   * AwsCloudConfigPlugin constructor.
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
    return $this->entityTypeManager
      ->getStorage($this->pluginDefinition['entity_type'])
      ->loadByProperties([
        'type' => [$this->pluginDefinition['entity_bundle']],
      ]);
  }

  /**
   * Load an array of credentials.
   *
   * @param string $cloud_context
   *   Cloud Context string.
   *
   * @return array
   *   Array of credentials.
   */
  public function loadCredentials($cloud_context) {
    /* @var \Drupal\cloud\Entity\CloudConfig $entity */
    $entity = $this->loadConfigEntity($cloud_context);
    $credentials = [];

    if (empty($entity)) {
      return $credentials;
    }

    // Check if using an instance profile or credentials.
    $credentials['use_instance_profile'] = $entity->get('field_use_instance_profile')->value ?? FALSE;
    $credentials['use_assume_role'] = $entity->get('field_use_assume_role')->value ?? FALSE;
    $credentials['use_switch_role'] = $entity->get('field_use_switch_role')->value ?? FALSE;
    $credentials['role_arn'] = '';
    $credentials['switch_role_arn'] = '';

    // Setup Assume Role configurations.
    if (!empty($credentials['use_assume_role'])) {
      $credentials['role_arn'] = sprintf('arn:aws:iam::%s:role/%s', trim($entity->get('field_account_id')->value), trim($entity->get('field_iam_role')->value));

      // Setup Switch Role configurations.
      if (!empty($credentials['use_switch_role'])) {
        $credentials['switch_role_arn'] = sprintf('arn:aws:iam::%s:role/%s', trim($entity->get('field_switch_role_account_id')->value), trim($entity->get('field_switch_role_iam_role')->value));
      }
    }

    $credentials['ini_file'] = $this->fileSystem->realpath(aws_cloud_ini_file_path($entity->get('cloud_context')->value));
    $credentials['region'] = $entity->get('field_region')->value;
    $credentials['version'] = $entity->get('field_api_version')->value;
    $credentials['endpoint'] = $entity->get('field_api_endpoint_uri')->value;

    return $credentials;
  }

  /**
   * Load a cloud service provider (CloudConfig) entity.
   *
   * @param string $cloud_context
   *   Cloud Context string.
   *
   * @return bool|mixed
   *   Entity or FALSE if there is no entity.
   */
  public function loadConfigEntity($cloud_context) {
    $entity = $this->entityTypeManager
      ->getStorage($this->pluginDefinition['entity_type'])
      ->loadByProperties([
        'cloud_context' => [$cloud_context],
      ]);

    if (count($entity) > 0) {
      return array_shift($entity);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceCollectionTemplateName() {
    return 'view.aws_cloud_instance.list';
  }

  /**
   * {@inheritdoc}
   */
  public function getPricingPageRoute() {
    return 'aws_cloud.instance_type_prices';
  }

}
