<?php

namespace Drupal\k8s\Plugin\cloud_cost_storage;

use Drupal\cloud_budget\Plugin\cloud_cost_storage\CloudCostStorageBase;
use Drupal\Component\Serialization\Yaml;

/**
 * Defines the cost calculator for k8s.
 *
 * @CloudCostStorage(
 *   id = "K8s_cloud_cost_storage"
 * )
 */
class K8sCloudCostStoragePlugin extends CloudCostStorageBase {

  /**
   * Variable to compare with the time difference.
   *
   * @var int
   */
  public $period = 60 * 60;

  /**
   * {@inheritdoc}
   */
  public function updateResourceStorageEntity() {
    // Set queue.
    $queue = \Drupal::queue('k8s_update_cost_storage_queue');
    $entities = \Drupal::service('plugin.manager.cloud_config_plugin')->loadConfigEntities('k8s');
    if (empty($entities)) {
      return;
    }

    // Get target entities to be updated.
    foreach ($entities ?: [] as $k8s_cluster) {
      $cloud_context = $k8s_cluster->getCloudContext();
      $namespace_entities = $this->entityTypeManager
        ->getStorage('k8s_namespace')
        ->loadByProperties([
          'cloud_context' => $cloud_context,
        ]);
      if (empty($namespace_entities)) {
        return;
      }

      foreach ($namespace_entities ?: [] as $namespace) {
        // Create params.
        $params = [
          'label' => $namespace->getName(),
          'cloud_context' => $cloud_context,
        ];
        // Enqueue.
        $queue->createItem([
          'export_function_name' => 'update_resource_storage_entity',
          'params' => $params,
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateCostStorageEntity() {
    // Get target entities to be updated.
    $project_entities = $this->entityTypeManager
      ->getStorage('cloud_project')
      ->loadByProperties([]);

    if (empty($project_entities)) {
      return;
    }

    foreach ($project_entities ?: [] as $project_entity) {
      $label = $project_entity->label();
      $k8s_clusters = $project_entity->get('field_k8s_clusters');

      // Get latest entity.
      $cost_entities = $this->entityTypeManager->getStorage('cloud_cost_storage')
        ->loadByProperties([
          'cloud_context' => 'k8s',
          'payer' => $label,
        ]);
      $cost_entity = array_pop($cost_entities);

      // To check time difference between current and latest update time
      // in order to decide whether to update or not.
      $latest_time = time() - (60 * 60 * 24);
      if (!empty($cost_entity)) {
        $latest_time = $cost_entity->getRefreshed();
        $time_diff = time() - $latest_time;

        // If previous update is within the specific range,
        // update process will be skipped.
        if ($time_diff < $this->period) {
          continue;
        }
      }

      $result[$label] = [];
      foreach ($k8s_clusters ?: [] as $k8s_cluster) {
        $cloud_context = $k8s_cluster->value;
        $current_time = (int) (time() / $this->period) * $this->period;
        $ids = $this->entityTypeManager
          ->getStorage('cloud_resource_storage')
          ->getQuery()
          ->condition('cloud_context', $cloud_context)
          ->condition('payer', $label)
          ->condition('refreshed', [$latest_time, $current_time], 'BETWEEN')
          ->execute();
        $resource_entities = $this->entityTypeManager->getStorage('cloud_resource_storage')
          ->loadMultiple($ids);

        // If no resource entity, it tries to catch the resource info from k8s.
        if (empty($resource_entities)) {
          $params = [
            'label' => $label,
            'cloud_context' => $cloud_context,
          ];
          update_resource_storage_entity($params);
          continue;
        }
        // Count cost in specific time range from all k8s clusters.
        foreach ($resource_entities ?: [] as $resource_entity) {
          $time_range = $resource_entity->getRefreshed();
          $result[$label][$time_range]['total_cost'] += $resource_entity->get('cost')->value;
          $result[$label][$time_range]['cnt'] += 1;
          $resources = Yaml::decode($resource_entity->getResources());
          $result[$label][$time_range]['resources']['cpu_usage'] += $resources['cpu_usage'];
          $result[$label][$time_range]['resources']['memory_usage'] += $resources['memory_usage'];
          $result[$label][$time_range]['resources']['pod_usage'] += $resources['pod_usage'];
        }
      }

      // Update cost entity.
      foreach ($result[$label] ?: [] as $key => $result) {
        $avg_cost = 0;
        $cpu_usage_avg = 0;
        $memory_usage_avg = 0;
        $pod_usage_avg = 0;
        if ($result['total_cost'] !== 0) {
          $avg_cost = (float) ($result['total_cost'] / $result['cnt']);
        }
        if ($result['resources']['cpu_usage'] !== 0) {
          $cpu_usage_avg = (float) ($result['resources']['cpu_usage'] / $result['cnt']);
        }
        if ($result['resources']['memory_usage'] !== 0) {
          $memory_usage_avg = (float) ($result['resources']['memory_usage'] / $result['cnt']);
        }
        if ($result['resources']['pod_usage'] !== 0) {
          $pod_usage_avg = (float) ($result['resources']['pod_usage'] / $result['cnt']);
        }
        $resources = [
          'cpu_usage_avg' => $cpu_usage_avg,
          'memory_usage_avg' => $memory_usage_avg,
          'pod_usage_avg' => $pod_usage_avg,
        ];
        $this->save('cloud_cost_storage', 'k8s', $label, $avg_cost, Yaml::encode($resources), $key);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteResourceStorageEntity() {
    $config = \Drupal::config('k8s.settings');
    $month = $config->get('k8s_keep_resource_storage_time_range');
    $from_time = 0;
    $to_time = time() - (60 * 60 * 24 * $month);
    $this->deleteTimeBased('cloud_resource_storage', $from_time, $to_time);
  }

}
