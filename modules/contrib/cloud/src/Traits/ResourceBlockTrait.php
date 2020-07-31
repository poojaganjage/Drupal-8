<?php

namespace Drupal\cloud\Traits;

use Drupal\Core\Link;

/**
 * The trait with common functions for Resource Blocks.
 */
trait ResourceBlockTrait {

  use CloudContentEntityTrait;

  /**
   * Get a count of all aws resources.
   *
   * @param string $resource_name
   *   The resource name.
   * @param string $permission
   *   The permission.
   * @param array $params
   *   The params.
   *
   * @return int|void
   *   Entity count.
   */
  protected function getResourceCount($resource_name, $permission, array $params) {
    if (!$this->currentUser->hasPermission($permission)) {
      $params['uid'] = $this->currentUser->id();
    }
    $entities = $this->runEntityQuery($resource_name, $params);
    return count($entities);
  }

  /**
   * Execute an entity query.
   *
   * @param string $entity_name
   *   The entity name.
   * @param array $params
   *   Array of parameters.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of loaded entities.
   */
  protected function runEntityQuery($entity_name, array $params) {
    $cloud_context = $this->configuration['cloud_context'];
    $cloud_project = $this->routeMatch->getParameter('cloud_project');
    if (empty($cloud_project) && !empty($cloud_context)) {
      $params['cloud_context'] = $cloud_context;
    }
    return $this->entityTypeManager->getStorage($entity_name)
      ->loadByProperties($params);
  }

  /**
   * Generate AWS resource link.
   *
   * @param string $resource_type
   *   The resource type.
   * @param string $permission
   *   The getResourceCount permission.
   * @param array $params
   *   The getResourceCount params.
   *
   * @return \Drupal\Core\Link
   *   The AWS resource link.
   */
  protected function getResourceLink($resource_type, $permission, array $params = []) {
    // Fetch the labels.
    $labels = $this->getDisplayLabels($resource_type);
    $cloud_context = $this->configuration['cloud_context'];

    if (!empty($cloud_context)) {
      $route_name = "view.${resource_type}.list";
      $params = [
        'cloud_context' => $cloud_context,
      ];
    }
    else {
      $route_name = "view.${resource_type}.all";
    }

    return Link::createFromRoute(
      $this->formatPlural(
        $this->getResourceCount($resource_type, $permission, $params),
        '1 @label',
        '@count @plural_label',
        [
          '@label' => $labels['singular'] ?? $resource_type,
          '@plural_label' => $labels['plural'] ?? $resource_type,
        ]
      ),
      $route_name,
      $params
    );
  }

  /**
   * Load the cloud configs as an array for use in a select dropdown.
   *
   * @param string $default_text
   *   The default dropdown option.
   * @param string $entity_type
   *   The entity type to load.
   *
   * @return array
   *   An array of cloud configs.
   */
  protected function getCloudConfigs($default_text, $entity_type) {
    $cloud_configs = ['' => $default_text];
    $configs = $this->cloudConfigPluginManager->loadConfigEntities($entity_type);
    foreach ($configs ?: [] as $config) {
      $cloud_configs[$config->getCloudContext()] = $config->getName();
    }
    return $cloud_configs;
  }

  /**
   * Build a resource table row.
   *
   * @param array $resources
   *   Entity array to build the row.
   *
   * @return array
   *   Fully built rows.
   */
  protected function buildResourceTableRows(array $resources) {
    $rows = [];
    $data = [];
    $i = 0;
    $index = 0;

    foreach ($resources ?: [] as $key => $values) {
      $data[] = $key === 'instance_type_prices'
        ? $this->getInstanceTypePricingLink($key, $values[0])
        : $this->getResourceLink($key, $values[0], $values[1]);

      // Skip if $data doesn't have any value.
      if (empty($data[count($data) - 1])) {
        continue;
      }

      $rows[$index] = [
        'no_striping' => TRUE,
        'data' => $data,
      ];
      if ($i++ % 2) {
        $index++;
        $data = [];
      }
    }
    return $rows;
  }

}
