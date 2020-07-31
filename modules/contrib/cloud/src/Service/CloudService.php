<?php

namespace Drupal\cloud\Service;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\geocoder\Entity\GeocoderProvider;
use Drupal\user\Entity\Role;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CloudService.
 */
class CloudService extends CloudServiceBase implements CloudServiceInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $entityDefinitionUpdateManager;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * Constructs a new K8sService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $entity_definition_update_manager
   *   The entity definition update manager.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed configuration manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request Object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              EntityDefinitionUpdateManagerInterface $entity_definition_update_manager,
                              ConfigManagerInterface $config_manager,
                              ConfigFactoryInterface $config_factory,
                              TypedConfigManagerInterface $typed_config,
                              FileSystemInterface $file_system,
                              ModuleHandlerInterface $module_handler,
                              RequestStack $request_stack) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    // Setup the entity type manager for querying entities.
    $this->entityTypeManager = $entity_type_manager;

    // Setup the entity definition update manager.
    $this->entityDefinitionUpdateManager = $entity_definition_update_manager;

    // Setup the config manager.
    $this->configManager = $config_manager;

    // Setup the configuration factory.
    $this->configFactory = $config_factory;

    $this->typedConfigManager = $typed_config;

    // Setup the file system.
    $this->fileSystem = $file_system;

    // Setup the module handler.
    $this->moduleHandler = $module_handler;

    // Setup the request.
    $this->request = $request_stack->getCurrentRequest();;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.definition_update_manager'),
      $container->get('config.manager'),
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('file_system'),
      $container->get('module_handler'),
      $container->get('request_stack')
    );
  }

  /**
   * Create cloud context according to name.
   *
   * @param string $name
   *   The name of the cloud service provider (CloudConfig) entity.
   *
   * @return string
   *   The cloud context
   */
  public function generateCloudContext($name): string {

    // Convert ' ' or '-' to '_'.
    $cloud_context = preg_replace('/[ \-]/', '_', strtolower($name));

    // Remove special characters.
    $cloud_context = preg_replace('/[^a-z0-9_]/', '', $cloud_context);

    return $cloud_context;
  }

  /**
   * Check if cloud_context exists regardless of the cloud service provider.
   *
   * @param string $name
   *   Name passed from Cloud Config add form.
   *
   * @return bool
   *   TRUE or FALSE if the cloud_context exists.
   */
  public function cloudContextExists($name) : bool {
    $exists = FALSE;
    $cloud_context = $this->generateCloudContext($name);
    try {
      $entities = $this->entityTypeManager->getStorage('cloud_config')
        ->loadByProperties([
          'cloud_context' => $cloud_context,
        ]);
      if (!empty($entities)) {
        $exists = TRUE;
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
    return $exists;
  }

  /**
   * Function to reorder form values.
   *
   * @param array $form
   *   Form elements.
   * @param array $fieldset_defs
   *   Field set definitions.
   */
  public function reorderForm(array &$form, array $fieldset_defs): void {
    $weight = 0;
    foreach ($fieldset_defs ?: [] as $fieldset_def) {
      $fieldset_name = $fieldset_def['name'];
      $form[$fieldset_name] = [
        '#type' => 'details',
        '#title' => $fieldset_def['title'],
        '#weight' => $weight++,
        '#open' => $fieldset_def['open'],
      ];

      foreach ($fieldset_def['fields'] ?: [] as $field_name) {
        if (!isset($form[$field_name])) {
          continue;
        }

        $form[$fieldset_name][$field_name] = $form[$field_name];
        $form[$fieldset_name][$field_name]['#weight'] = $weight++;
        unset($form[$field_name]);
      }

      // Support second level fieldset.
      if (isset($fieldset_def['subfieldsets'])) {
        foreach ($fieldset_def['subfieldsets'] ?: [] as $subfieldset_def) {
          $subfieldset_name = $subfieldset_def['name'];
          $form[$fieldset_name][$subfieldset_name] = [
            '#type' => 'details',
            '#title' => $subfieldset_def['title'],
            '#weight' => $weight++,
            '#open' => $subfieldset_def['open'],
          ];
          foreach ($subfieldset_def['fields'] ?: [] as $subfield) {
            if (!isset($form[$subfield])) {
              continue;
            }
            $form[$fieldset_name][$subfieldset_name][$subfield] = $form[$subfield];
            $form[$fieldset_name][$subfieldset_name][$subfield]['#weight'] = $weight++;
            unset($form[$subfield]);
          }

          // Third level field set.
          if (isset($subfieldset_def['subfieldsets'])) {
            foreach ($subfieldset_def['subfieldsets'] ?: [] as $third_fieldset_def) {
              $third_fieldset_name = $third_fieldset_def['name'];
              $form[$fieldset_name][$subfieldset_name][$third_fieldset_name] = [
                '#type' => 'details',
                '#title' => $third_fieldset_def['title'],
                '#weight' => $weight++,
                '#open' => $third_fieldset_def['open'],
              ];
              foreach ($third_fieldset_def['fields'] ?: [] as $third_field) {
                if (!isset($form[$third_field])) {
                  continue;
                }
                $form[$fieldset_name][$subfieldset_name][$third_fieldset_name][$third_field] = $form[$third_field];
                $form[$fieldset_name][$subfieldset_name][$third_fieldset_name][$third_field]['#weight'] = $weight++;
                unset($form[$third_field]);
              }
            }
          }
        }
      }
    }
  }

  /**
   * Helper function to install or update configuration for a set of yml files.
   *
   * @param array $files
   *   An array of yml file names.
   * @param string $module_name
   *   Module where the files are found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function updateYmlDefinitions(array $files, $module_name = 'cloud'): void {
    $config_path = realpath(drupal_get_path('module', $module_name)) . '/config/install';

    foreach ($files ?: [] as $file) {
      $filename = $config_path . '/' . $file;
      $file = file_get_contents($filename);
      if (!$file) {
        continue;
      }
      $value = Yaml::decode($file);
      $type = $this->configManager->getEntityTypeIdByName(basename($filename));
      $definition = $this->entityTypeManager->getDefinition($type);
      $id_key = $definition->getKey('id');
      $id = $value[$id_key];
      $entity_storage = $this->entityTypeManager->getStorage($type);
      $entity = $entity_storage->load($id);
      if ($entity) {
        $entity = $entity_storage->updateFromStorageRecord($entity, $value);
        $entity->save();
      }
      else {
        $entity = $entity_storage->createFromStorageRecord($value);
        $entity->save();
      }
    }
  }

  /**
   * Update plural and singular labels in entity annotations.
   *
   * @param string $module
   *   The entities the module provides.
   */
  public function updateEntityPluralLabels($module): void {
    $types = $this->entityDefinitionUpdateManager->getEntityTypes();
    /* @var \Drupal\Core\Entity\EntityTypeInterface $type */
    foreach ($types ?: [] as $type) {
      $provider = $type->getProvider();
      if ($provider === $module) {
        $prefix = "${provider}_";
        $label = substr($type->id(), strlen($prefix));
        $label = str_replace('_', ' ', $label);
        $plural_label = "{$label}s";
        if (preg_match('/(.*)[sS]$/', $label)) {
          $plural_label = "{$label}es";
        }
        elseif (preg_match('/(.*)[yY]$/', $label)) {
          $plural_label = substr($label, 0, -1) . 'ies';
        }
        $type->set('label_collection', ucwords($plural_label));
        $type->set('label_singular', ucwords($label));
        $type->set('label_plural', ucwords($plural_label));
        $type->set('id_plural', $prefix . $plural_label);
        $this->entityDefinitionUpdateManager->updateEntityType($type);
      }
    }
    $this->typedConfigManager->clearCachedDefinitions();
    drupal_flush_all_caches();
  }

  /**
   * Helper function to rename permissions.
   *
   * @param array $permission_map
   *   A array map of ['current_permission_name' => 'new_permission_name'].
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updatePermissions(array $permission_map): void {

    $roles = Role::loadMultiple();

    foreach ($roles ?: [] as $role) {
      $permissions = $role->getPermissions();
      foreach ($permissions ?: [] as $permission) {
        if (array_key_exists($permission, $permission_map)) {
          $role->revokePermission($permission);
          $role->grantPermission($permission_map[$permission]);
          $role->save();
        }
      }
    }
  }

  /**
   * Delete views by IDs.
   *
   * @param array $ids
   *   An array of view IDs.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteViews(array $ids): void {

    foreach ($ids ?: [] as $id) {
      $view_storage = $this->entityTypeManager->getStorage('view')
        ->load($id);
      if ($view_storage) {
        $view_storage->delete();
      }
    }
  }

  /**
   * Helper function to add default icon.
   *
   * @param string $module
   *   The module name.
   */
  public function addDefaultIcon($module): void {

    try {
      // Add default icon.
      $icon = drupal_get_path('module', $module) . "/images/${module}.png";
      $handle = fopen($icon, 'rb');
      $icon_content = fread($handle, filesize($icon));
      fclose($handle);

      $destination = 'public://images/cloud/icons';
      $this->fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY);

      $file = file_save_data($icon_content, "public://images/cloud/icons/${module}.png");
      if (!empty($file)) {
        $file->setPermanent();
        $file->save();
        $config = $this->configFactory->getEditable("${module}.settings");
        $config->set("${module}_cloud_config_icon", $file->id());
        $config->save();
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * Helper function to delete default icon.
   *
   * @param string $module
   *   The module name.
   */
  public function deleteDefaultIcon($module): void {

    try {
      // Delete default icon.
      $config = $this->configFactory->getEditable("${module}.settings");
      $fid = !empty($config) ? $config->get("${module}_cloud_config_icon") : NULL;

      // Delete file from disk and from database.
      if (!empty($fid)) {
        $storage = $this->entityTypeManager->getStorage('file');
        $files = !empty($storage) ? $storage->loadMultiple([$fid]) : [];
        $storage->delete($files);
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * Helper function to install location-related fields.
   *
   * @param string $bundle
   *   The CloudConfig bundle name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   */
  public function installLocationFields($bundle): void {

    $location_fields = [
      'field_location_country',
      'field_location_city',
      'field_location_latitude',
      'field_location_longitude',
    ];

    $config_path = realpath(drupal_get_path('module', 'cloud')) . '/config/install';
    $cloud_source = new FileStorage($config_path);

    $config_path = realpath(drupal_get_path('module', $bundle)) . '/config/install';
    $bundle_source = new FileStorage($config_path);

    try {
      foreach ($location_fields ?: [] as $location_field) {

        $field_storage = FieldStorageConfig::loadByName('cloud_config', $location_field);
        if (!empty($field_storage)) {
          continue;
        }

        // Obtain the storage manager for field_storage_config entity type, then
        // create a new field from the yaml configuration and save.
        $this->entityTypeManager->getStorage('field_storage_config')
          ->create($cloud_source->read("field.storage.cloud_config.${location_field}"))
          ->save();

        // Obtain the storage manager for field_config entity type, then
        // create a new field from the yaml configuration and save.
        $this->entityTypeManager->getStorage('field_config')
          ->create($bundle_source->read("field.field.cloud_config.${bundle}.${location_field}"))
          ->save();
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * Helper function to uninstall location-related fields.
   *
   * @param string $module_name
   *   The module name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   * @param string $bundle
   *   The CloudConfig bundle name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   */
  public function uninstallLocationFields($module_name, $bundle = NULL): void {

    $bundle = $bundle ?? $module_name;

    $fields = [
      "field.field.cloud_config.${bundle}.field_location_country",
      "field.field.cloud_config.${bundle}.field_location_city",
      "field.field.cloud_config.${bundle}.field_location_latitude",
      "field.field.cloud_config.${bundle}.field_location_longitude",
    ];

    // Delete field in bundles.
    foreach ($fields ?: [] as $field) {
      $this->configFactory->getEditable($field)->delete();
    }
  }

  /**
   * Helper function to uninstall a submodule for cloud service provider.
   *
   * @param string $cloud_config
   *   The CloudConfig bundle name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   */
  public function uninstallServiceProvider($cloud_config): void {

    try {
      // Delete all aws_cloud cloud_config entities.
      $entities = $this->entityTypeManager
        ->getStorage('cloud_config')
        ->loadByProperties([
          'type' => $cloud_config,
        ]);

      if (!empty($entities)) {
        $this->entityTypeManager->getStorage('cloud_config')->delete($entities);
      }

      // Delete the aws_cloud entity type.
      $entity_type = $this->entityTypeManager->getStorage('cloud_config_type')
        ->load($cloud_config);

      if (!empty($entity_type)) {
        $entity_type->delete();
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }

    // Rebuild caches.
    drupal_flush_all_caches();
  }

  /**
   * Initialize the geocoder provider.
   */
  public function initGeocoder(): void {
    if (!$this->moduleHandler->moduleExists('geocoder')) {
      return;
    }
    $plugin_manager = \Drupal::service('plugin.manager.geocoder.provider');
    $plugins = $plugin_manager->getDefinitions();

    if (empty($plugins)) {
      return;
    }

    $config = $this->configFactory->getEditable('cloud.settings');
    $provider_id = $config->get('cloud_location_geocoder_plugin');

    if (empty($provider_id) || !isset($plugins[$provider_id])) {
      $config_path = realpath(drupal_get_path('module', 'cloud')) . '/config/install';
      $filename = $config_path . '/cloud.settings.yml';
      $file = file_get_contents($filename);
      if ($file) {
        $values = Yaml::decode($file);
        if (!empty($values) && is_array($values) && isset($values['cloud_location_geocoder_plugin']) && isset($plugins[$values['cloud_location_geocoder_plugin']])) {
          $provider_id = $values['cloud_location_geocoder_plugin'];
          $config->set('cloud_location_geocoder_plugin', $provider_id);
          $config->save();
        }
        else {
          return;
        }
      }
    }
    $storage = $this->entityTypeManager->getStorage('geocoder_provider');
    $entity = $storage->load($provider_id);
    if (!empty($entity)) {
      return;
    }
    $entity = GeocoderProvider::create([
      'id' => $provider_id,
      'label' => $plugins[$provider_id]['name'],
      'plugin' => $provider_id,
    ]);

    $geocoder_config = $this->configFactory->getEditable('geocoder.settings');
    $configuration = $geocoder_config->get('plugins_options') ? $geocoder_config->get('plugins_options')[$provider_id] : [];
    $host = $this->request->getHost();
    $configuration['userAgent'] = $configuration['referer'] = $host;

    $entity->set('configuration', $configuration);
    $entity->save();
  }

}
