<?php

namespace Drupal\cloud\Service;

/**
 * Class CloudService.
 */
interface CloudServiceInterface {

  /**
   * Create cloud context according to name.
   *
   * @param string $name
   *   The name of the cloud service provider (CloudConfig) entity.
   *
   * @return string
   *   The cloud context
   */
  public function generateCloudContext($name): string;

  /**
   * Check if cloud_context exists regardless of the cloud service provider.
   *
   * @param string $name
   *   Name passed from Cloud Config add form.
   *
   * @return bool
   *   TRUE or FALSE if the cloud_context exists.
   */
  public function cloudContextExists($name) : bool;

  /**
   * Function to reorder form values.
   *
   * @param array $form
   *   Form elements.
   * @param array $fieldset_defs
   *   Field set definitions.
   */
  public function reorderForm(array &$form, array $fieldset_defs): void;

  /**
   * Helper function to install or update configuration for a set of yml files.
   *
   * @param array $files
   *   An array of yml file names.
   * @param string $module_name
   *   Module where the files are found.
   */
  public function updateYmlDefinitions(array $files, $module_name = 'cloud'): void;

  /**
   * Update plural and singular labels in entity annotations.
   *
   * @param string $module
   *   The entities the module provides.
   */
  public function updateEntityPluralLabels($module): void;

  /**
   * Helper function to rename permissions.
   *
   * @param array $permission_map
   *   A array map of ['current_permission_name' => 'new_permission_name'].
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updatePermissions(array $permission_map): void;

  /**
   * Delete views by IDs.
   *
   * @param array $ids
   *   An array of view IDs.
   */
  public function deleteViews(array $ids): void;

  /**
   * Helper function to add default icon.
   *
   * @param string $module
   *   The module name.
   */
  public function addDefaultIcon($module): void;

  /**
   * Helper function to delete default icon.
   *
   * @param string $module
   *   The module name.
   */
  public function deleteDefaultIcon($module): void;

  /**
   * Helper function to install location-related fields.
   *
   * @param string $bundle
   *   The CloudConfig bundle name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   */
  public function installLocationFields($bundle): void;

  /**
   * Helper function to uninstall location-related fields.
   *
   * @param string $module_name
   *   The module name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   * @param string $bundle
   *   The CloudConfig bundle name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   */
  public function uninstallLocationFields($module_name, $bundle = NULL): void;

  /**
   * Helper function to uninstall a submodule for cloud service provider.
   *
   * @param string $cloud_config
   *   The CloudConfig bundle name (e.g. 'aws_cloud', 'k8s' or 'openstack').
   */
  public function uninstallServiceProvider($cloud_config): void;

  /**
   * Initialize the geocoder provider.
   */
  public function initGeocoder(): void;

}
