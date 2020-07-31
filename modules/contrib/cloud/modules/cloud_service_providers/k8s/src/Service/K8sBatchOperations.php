<?php

namespace Drupal\k8s\Service;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\k8s\Entity\K8sClusterRole;
use Drupal\k8s\Entity\K8sConfigMap;
use Drupal\k8s\Entity\K8sCronJob;
use Drupal\k8s\Entity\K8sDaemonSet;
use Drupal\k8s\Entity\K8sDeployment;
use Drupal\k8s\Entity\K8sEndpoint;
use Drupal\k8s\Entity\K8sEntityBase;
use Drupal\k8s\Entity\K8sEvent;
use Drupal\k8s\Entity\K8sIngress;
use Drupal\k8s\Entity\K8sJob;
use Drupal\k8s\Entity\K8sLimitRange;
use Drupal\k8s\Entity\K8sNamespace;
use Drupal\k8s\Entity\K8sNetworkPolicy;
use Drupal\k8s\Entity\K8sNode;
use Drupal\k8s\Entity\K8sPersistentVolume;
use Drupal\k8s\Entity\K8sPod;
use Drupal\k8s\Entity\K8sPriorityClass;
use Drupal\k8s\Entity\K8sReplicaSet;
use Drupal\k8s\Entity\K8sResourceQuota;
use Drupal\k8s\Entity\K8sRole;
use Drupal\k8s\Entity\K8sSecret;
use Drupal\k8s\Entity\K8sServiceEntity;
use Drupal\k8s\Entity\K8sStatefulSet;
use Drupal\k8s\Entity\K8sStorageClass;
use Drupal\k8s\Entity\K8sPersistentVolumeClaim;
use Drupal\k8s\Entity\K8sClusterRoleBinding;
use Drupal\k8s\Entity\K8sApiService;
use Drupal\k8s\Entity\K8sRoleBinding;
use Drupal\k8s\Entity\K8sServiceAccount;

/**
 * Entity update methods for Batch API processing.
 */
class K8sBatchOperations {

  use StringTranslationTrait;

  /**
   * The finish callback function.
   *
   * Deletes stale entities from the database.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $stale
   *   The stale entities to delete.
   * @param bool $clear
   *   TRUE to clear entities, FALSE keep them.
   */
  public static function finished($entity_type, array $stale, $clear = TRUE) {
    $entity_type_manager = \Drupal::entityTypeManager();
    if (count($stale) && $clear === TRUE) {
      $entity_type_manager->getStorage($entity_type)->delete($stale);
    }
  }

  /**
   * Update or create a k8s node entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $node
   *   The node array.
   * @param array $extra_data
   *   The extra data.
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *   Thrown when unable to get metrics nodes.
   */
  public static function updateNode($cloud_context, array $node, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $node['metadata']['name'];
    $entity_id = $k8s_service->getEntityId('k8s_node', 'name', $name);

    $status = '';
    $last_condition = end($node['status']['conditions']);
    if (!empty($last_condition)) {
      $status = $last_condition['type'];
    }

    if (!empty($entity_id)) {
      $entity = K8sNode::load($entity_id);
    }
    else {
      $entity = K8sNode::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($node, $timestamp),
        'changed' => self::getCreationTimestamp($node, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    $entity->setStatus($status);

    // Labels.
    self::setKeyValueTypeFieldValue($entity, 'labels', $node['metadata']['labels']);

    // Annotations.
    self::setKeyValueTypeFieldValue($entity, 'annotations', $node['metadata']['annotations']);

    // Addresses.
    $map = [];
    foreach ($node['status']['addresses'] as $address) {
      $map[$address['type']] = $address['address'];
    }
    self::setKeyValueTypeFieldValue($entity, 'addresses', $map);

    // Metrics.
    // Capacity.
    $entity->setCpuCapacity($node['status']['capacity']['cpu']);
    $entity->setMemoryCapacity(k8s_convert_memory_to_integer($node['status']['capacity']['memory']));
    $entity->setPodsCapacity($node['status']['capacity']['pods']);

    // Request and limit.
    $pods = $k8s_service->getPods(['spec.nodeName' => $name]);
    $cpu_limit = 0;
    $cpu_request = 0;
    $memory_limit = 0;
    $memory_request = 0;
    $pods_allocation = 0;
    foreach ($pods ?: [] as $pod) {
      if (is_object($pod)) {
        $pod = $pod->toArray();
      }

      if (!isset($pod['spec']['containers'])) {
        continue;
      }

      // Skip if the status is Succeeded or Failed.
      if ($pod['status']['phase'] === 'Succeeded' || $pod['status']['phase'] === 'Failed') {
        continue;
      }

      $pods_allocation++;

      foreach ($pod['spec']['containers'] ?: [] as $container) {
        if (isset($container['resources']['requests'])) {
          $requests = $container['resources']['requests'];
          if (isset($requests['cpu'])) {
            $cpu_request += k8s_convert_cpu_to_float($requests['cpu']);
          }

          if (isset($requests['memory'])) {
            $memory_request += k8s_convert_memory_to_integer($requests['memory']);
          }
        }

        if (isset($container['resources']['limits'])) {
          $limits = $container['resources']['limits'];
          if (isset($limits['cpu'])) {
            $cpu_limit += k8s_convert_cpu_to_float($limits['cpu']);
          }

          if (isset($limits['memory'])) {
            $memory_limit += k8s_convert_memory_to_integer($limits['memory']);
          }
        }
      }
    }

    $entity->setCpuRequest($cpu_request);
    $entity->setCpuLimit($cpu_limit);
    $entity->setMemoryRequest($memory_request);
    $entity->setMemoryLimit($memory_limit);

    $metrics_nodes = [];
    try {
      $metrics_nodes = $k8s_service->getMetricsNodes(['metadata.name' => $name]);
    }
    catch (K8sServiceException $e) {
      \Drupal::messenger()->addWarning(t('Unable to retrieve CPU and Memory usage of nodes. Please install @$metrics_server_link to K8s.', [
        '@$metrics_server_link' => $k8s_service->getMetricsServerLink($cloud_context),
      ]));
    }

    if (!empty($metrics_nodes)) {
      $k8s_service->exportNodeMetrics($metrics_nodes);

      if (is_object($metrics_nodes[0])) {
        $metrics_nodes = $metrics_nodes[0]->toArray();
      }

      if (isset($metrics_nodes['usage']['cpu'])) {
        $entity->setCpuUsage(k8s_convert_cpu_to_float($metrics_nodes['usage']['cpu']));
      }

      if (isset($metrics_nodes['usage']['memory'])) {
        $entity->setMemoryUsage(k8s_convert_memory_to_integer($metrics_nodes['usage']['memory']));
      }
    }

    // Pods allocated.
    $entity->setPodsAllocation($pods_allocation);

    $entity->setPodCidr($node['spec']['podCIDR'] ?? '');
    $entity->setProviderId($node['spec']['providerID']);
    $entity->setUnschedulable(isset($node['spec']['unschedulable']) ?: FALSE);
    $entity->setMachineId($node['status']['nodeInfo']['machineID']);
    $entity->setSystemUuid($node['status']['nodeInfo']['systemUUID']);
    $entity->setBootId($node['status']['nodeInfo']['bootID']);
    $entity->setKernelVersion($node['status']['nodeInfo']['kernelVersion']);
    $entity->setOsImage($node['status']['nodeInfo']['osImage']);
    $entity->setContainerRuntimeVersion($node['status']['nodeInfo']['containerRuntimeVersion']);
    $entity->setKubeletVersion($node['status']['nodeInfo']['kubeletVersion']);
    $entity->setKubeProxyVersion($node['status']['nodeInfo']['kubeProxyVersion']);
    $entity->setOperatingSystem($node['status']['nodeInfo']['operatingSystem']);
    $entity->setArchitecture($node['status']['nodeInfo']['architecture']);
    $entity->setDetail(Yaml::encode($node));

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s namespace entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $namespace
   *   The namespace array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateNamespace($cloud_context, array $namespace, array $extra_data) {

    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $namespace['metadata']['name'];
    $entity_id = $k8s_service->getEntityId('k8s_namespace', 'name', $name);

    $status = $namespace['status']['phase'];

    if (!empty($entity_id)) {
      $entity = K8sNamespace::load($entity_id);
    }
    else {
      $entity = K8sNamespace::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($namespace, $timestamp),
        'changed' => self::getCreationTimestamp($namespace, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    $entity->setStatus($status);

    // Labels.
    $labels = [];
    if (!empty($namespace['metadata']['labels'])) {
      $labels = $namespace['metadata']['labels'];
    }
    self::setKeyValueTypeFieldValue($entity, 'labels', $labels);

    // Annotations.
    $annotations = [];
    if (!empty($namespace['metadata']['annotations'])) {
      $annotations = $namespace['metadata']['annotations'];
    }
    self::setKeyValueTypeFieldValue($entity, 'annotations', $annotations);

    $entity->setDetail(Yaml::encode($namespace));
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s pod entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $pod
   *   The pod array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updatePod($cloud_context, array $pod, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);
    $metrics = $extra_data;

    $timestamp = time();
    $name = $pod['metadata']['name'] ?? '';
    $namespace = $pod['metadata']['namespace'] ?? '';
    $entity_id = $k8s_service->getEntityId(
      'k8s_pod',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sPod::load($entity_id);
    }
    else {
      $entity = K8sPod::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($pod, $timestamp),
        'changed' => self::getCreationTimestamp($pod, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Owner ID.
    $uid = NULL;
    if (isset($pod['metadata']['annotations'])
      && isset($pod['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID])) {
      $uid = $pod['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID];
    }
    $entity->setOwnerById($uid);

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $pod['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $pod['metadata']['annotations'] ?? []
    );

    // Containers.
    $containers = [];
    foreach ($pod['spec']['containers'] ?: [] as $container_data) {
      $containers[] = Yaml::encode($container_data);
    }
    $entity->setContainers($containers);

    // Restarts.
    if (!empty($pod['status']['containerStatuses'])) {
      $entity->setRestarts($pod['status']['containerStatuses'][0]['restartCount']);
    }

    // Metrics.
    $cpu_request = 0;
    $cpu_limit = 0;
    $memory_limit = 0;
    $memory_request = 0;
    foreach ($pod['spec']['containers'] ?: [] as $container) {
      if (isset($container['resources']['requests'])) {
        $requests = $container['resources']['requests'];
        if (isset($requests['cpu'])) {
          $cpu_request += k8s_convert_cpu_to_float($requests['cpu']);
        }

        if (isset($requests['memory'])) {
          $memory_request += k8s_convert_memory_to_integer($requests['memory']);
        }
      }

      if (isset($container['resources']['limits'])) {
        $limits = $container['resources']['limits'];
        if (isset($limits['cpu'])) {
          $cpu_limit += k8s_convert_cpu_to_float($limits['cpu']);
        }

        if (isset($limits['memory'])) {
          $memory_limit += k8s_convert_memory_to_integer($limits['memory']);
        }
      }
    }

    $entity->setCpuRequest($cpu_request);
    $entity->setCpuLimit($cpu_limit);
    $entity->setMemoryRequest($memory_request);
    $entity->setMemoryLimit($memory_limit);

    $pod_status = $pod['status']['phase'] ?? '';

    if ($pod_status !== 'Succeeded' && $pod_status !== 'Failed') {
      $cpu_usage = 0;
      $memory_usage = 0;
      if (!empty($metrics["$namespace.$name"])) {
        $metrics_pod = $metrics["$namespace.$name"];
        $k8s_service->exportPodMetrics([$metrics_pod]);

        foreach ($metrics_pod['containers'] ?: [] as $container) {
          if (isset($container['usage']['cpu'])) {
            $cpu_usage += k8s_convert_cpu_to_float($container['usage']['cpu']);
          }

          if (isset($container['usage']['memory'])) {
            $memory_usage += k8s_convert_memory_to_integer($container['usage']['memory']);
          }
        }

        $entity->setCpuUsage($cpu_usage);
        $entity->setMemoryUsage($memory_usage);
      }
    }

    // Detail.
    $entity->setDetail(Yaml::encode($pod));

    $entity->setNamespace($namespace);
    $entity->setStatus($pod_status);
    $entity->setQosClass($pod['status']['qosClass'] ?? '');
    $entity->setNodeName($pod['spec']['nodeName'] ?? '');
    $entity->setPodIp($pod['status']['podIP'] ?? '');
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s deployment entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $deployment
   *   The deployment array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateDeployment($cloud_context, array $deployment, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $deployment['metadata']['name'] ?? '';
    $namespace = $deployment['metadata']['namespace'] ?? '';
    $entity_id = $k8s_service->getEntityId(
      'k8s_deployment',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sDeployment::load($entity_id);
    }
    else {
      $entity = K8sDeployment::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($deployment, $timestamp),
        'changed' => self::getCreationTimestamp($deployment, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Owner ID.
    $uid = NULL;
    if (isset($deployment['metadata']['annotations'])
      && isset($deployment['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID])) {
      $uid = $deployment['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID];
    }
    $entity->setOwnerById($uid);

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $deployment['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $deployment['metadata']['annotations'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($deployment));

    $entity->setNamespace($namespace);
    $entity->setStrategy($deployment['spec']['strategy']['type'] ?? '');
    $entity->setMinReadySeconds($deployment['spec']['minReadySeconds'] ?? 0);
    $entity->setRevisionHistoryLimit($deployment['spec']['revisionHistoryLimit'] ?? '');
    $entity->setAvailableReplicas($deployment['status']['availableReplicas'] ?? 0);
    $entity->setCollisionCount($deployment['status']['collisionCount'] ?? 0);
    $entity->setObservedGeneration($deployment['status']['observedGeneration'] ?? 0);
    $entity->setReadyReplicas($deployment['status']['readyReplicas'] ?? 0);
    $entity->setReplicas($deployment['status']['replicas'] ?? 0);
    $entity->setUnavailableReplicas($deployment['status']['unavailableReplicas'] ?? 0);
    $entity->setUpdatedReplicas($deployment['status']['updatedReplicas'] ?? 0);

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s replica set entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $replica_set
   *   The replica set array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateReplicaSet($cloud_context, array $replica_set, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $replica_set['metadata']['name'];
    $namespace = $replica_set['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_replica_set',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sReplicaSet::load($entity_id);
    }
    else {
      $entity = K8sReplicaSet::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($replica_set, $timestamp),
        'changed' => self::getCreationTimestamp($replica_set, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $replica_set['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $replica_set['metadata']['annotations'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($replica_set));
    $entity->setNamespace($replica_set['metadata']['namespace']);
    $entity->setReplicas($replica_set['spec']['replicas'] ?? 0);
    $entity->setAvailableReplicas($replica_set['status']['availableReplicas'] ?? 0);
    $entity->setFullyLabeledReplicas($replica_set['status']['fullyLabeledReplicas'] ?? 0);
    $entity->setReadyReplicas($replica_set['status']['readyReplicas'] ?? 0);
    $entity->setObservedGeneration($replica_set['status']['observedGeneration'] ?? 0);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s service entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $service
   *   The service array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateService($cloud_context, array $service, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $service['metadata']['name'];
    $namespace = $service['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_service',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sServiceEntity::load($entity_id);
    }
    else {
      $entity = K8sServiceEntity::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($service, $timestamp),
        'changed' => self::getCreationTimestamp($service, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $service['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $service['metadata']['annotations'] ?? []
    );

    // Selector.
    self::setKeyValueTypeFieldValue(
      $entity,
      'selector',
      $service['spec']['selector'] ?? []
    );

    $namespace = $service['metadata']['namespace'];

    // Internal endpoints.
    $internal_endpoints = [];
    foreach ($service['spec']['ports'] ?: [] as $port) {
      $internal_endpoints[] = sprintf(
        '%s.%s:%s %s',
        $name,
        $namespace,
        $port['port'],
        $port['protocol']
      );

      if (isset($port['nodePort'])) {
        $internal_endpoints[] = sprintf(
          '%s.%s:%s %s',
          $name,
          $namespace,
          $port['nodePort'],
          $port['protocol']
        );
      }
    }
    $entity->setInternalEndpoints($internal_endpoints);

    // External endpoints.
    if ($service['spec']['type'] === 'LoadBalancer') {
      $external_endpoints = [];

      if (isset($service['status']['loadBalancer']['ingress'])) {
        foreach ($service['status']['loadBalancer']['ingress'] ?: [] as $lb) {
          foreach ($service['spec']['ports'] ?: [] as $port) {
            $external_endpoints[] = sprintf(
              '%s:%s',
              $lb['hostname'],
              $port['port']
            );
          }
        }
      }

      $entity->setExternalEndpoints($external_endpoints);
    }

    // Detail.
    $entity->setDetail(Yaml::encode($service));

    $entity->setNamespace($namespace);
    $entity->setType($service['spec']['type']);
    $entity->setSessionAffinity($service['spec']['sessionAffinity']);
    $entity->setClusterIp($service['spec']['clusterIP']);

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s cron job entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $cron_job
   *   The cron job array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateCronJob($cloud_context, array $cron_job, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $cron_job['metadata']['name'];
    $namespace = $cron_job['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_cron_job',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sCronJob::load($entity_id);
    }
    else {
      $entity = K8sCronJob::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($cron_job, $timestamp),
        'changed' => self::getCreationTimestamp($cron_job, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $cron_job['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $cron_job['metadata']['annotations'] ?? []
    );

    $namespace = $cron_job['metadata']['namespace'];

    // Active.
    if (isset($cron_job['status']['active'])) {
      $entity->setActive(count($cron_job['status']['active']));
    }
    else {
      $entity->setActive(0);
    }

    // Detail.
    $entity->setDetail(Yaml::encode($cron_job));

    $entity->setNamespace($namespace);
    $entity->setSchedule($cron_job['spec']['schedule']);
    $entity->setSuspend($cron_job['spec']['suspend']);

    if (!empty($cron_job['status']['lastScheduleTime'])) {
      $entity->setLastScheduleTime(strtotime($cron_job['status']['lastScheduleTime']));
    }
    $entity->setConcurrencyPolicy($cron_job['spec']['concurrencyPolicy']);
    if (isset($cron_job['spec']['startingDeadlineSeconds'])) {
      $entity->setStartingDeadlineSeconds($cron_job['spec']['startingDeadlineSeconds']);
    }

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s job entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $job
   *   The job array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateJob($cloud_context, array $job, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $job['metadata']['name'];
    $namespace = $job['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_job',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sJob::load($entity_id);
    }
    else {
      $entity = K8sJob::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($job, $timestamp),
        'changed' => self::getCreationTimestamp($job, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $job['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $job['metadata']['annotations'] ?? []
    );

    $namespace = $job['metadata']['namespace'];

    // Image.
    if (!empty($job['spec']['template']['spec']['containers'])) {
      $entity->setImage($job['spec']['template']['spec']['containers'][0]['image']);
    }

    // Detail.
    $entity->setDetail(Yaml::encode($job));

    $entity->setNamespace($namespace);
    $entity->setCompletions($job['spec']['completions']);
    $entity->setParallelism($job['spec']['parallelism']);

    // Active.
    if (isset($job['status']['active'])) {
      $entity->setActive($job['status']['active']);
    }
    else {
      $entity->setActive(0);
    }

    // Succeeded.
    if (isset($job['status']['succeeded'])) {
      $entity->setSucceeded($job['status']['succeeded']);
    }
    else {
      $entity->setSucceeded(0);
    }

    // Failed.
    if (isset($job['status']['failed'])) {
      $entity->setFailed($job['status']['failed']);
    }
    else {
      $entity->setFailed(0);
    }

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s resource quota entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $resource_quota
   *   The resource quota array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateResourceQuota($cloud_context, array $resource_quota, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $resource_quota['metadata']['name'];
    $namespace = $resource_quota['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_resource_quota',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sResourceQuota::load($entity_id);
    }
    else {
      $entity = K8sResourceQuota::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($resource_quota, $timestamp),
        'changed' => self::getCreationTimestamp($resource_quota, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $resource_quota['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $resource_quota['metadata']['annotations'] ?? []
    );

    // Status hard.
    self::setKeyValueTypeFieldValue(
      $entity,
      'status_hard',
      $resource_quota['status']['hard'] ?? []
    );

    // Status used.
    self::setKeyValueTypeFieldValue(
      $entity,
      'status_used',
      $resource_quota['status']['used'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($resource_quota));

    $namespace = $resource_quota['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s limit range entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $limit_range
   *   The limit range array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateLimitRange($cloud_context, array $limit_range, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $limit_range['metadata']['name'];
    $namespace = $limit_range['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_limit_range',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sLimitRange::load($entity_id);
    }
    else {
      $entity = K8sLimitRange::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($limit_range, $timestamp),
        'changed' => self::getCreationTimestamp($limit_range, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $limit_range['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $limit_range['metadata']['annotations'] ?? []
    );

    // Limits.
    $resources = [
      'cpu',
      'memory',
      'storage',
    ];
    $fields = [
      'max',
      'min',
      'default',
      'default_request',
      'max_limit_request_ratio',
    ];
    $limits = [];
    foreach ($limit_range['spec']['limits'] ?: [] as $limit_data) {
      $limit = [];
      $limit['limit_type'] = $limit_data['type'];
      foreach ($resources ?: [] as $resource) {
        $limit['resource'] = $resource;
        $has_limit_data = FALSE;
        foreach ($fields ?: [] as $field) {
          $field_camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field))));

          if (empty($limit_data[$field_camel]) || empty($limit_data[$field_camel][$resource])) {
            continue;
          }

          $has_limit_data = TRUE;
          $limit[$field] = $limit_data[$field_camel][$resource];
        }

        if ($has_limit_data) {
          $limits[] = $limit;
        }
      }
    }

    $entity->set('limits', $limits);

    // Detail.
    $entity->setDetail(Yaml::encode($limit_range));

    $namespace = $limit_range['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s secret entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $secret
   *   The secret array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateSecret($cloud_context, array $secret, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $secret['metadata']['name'];
    $namespace = $secret['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_secret',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sSecret::load($entity_id);
    }
    else {
      $entity = K8sSecret::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($secret, $timestamp),
        'changed' => self::getCreationTimestamp($secret, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $secret['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $secret['metadata']['annotations'] ?? []
    );

    // Data.
    self::setKeyValueTypeFieldValue(
      $entity,
      'data',
      $secret['data'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($secret));

    $namespace = $secret['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setSecretType($secret['type']);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a K8s ConfigMap entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $config_map
   *   The ConfigMap array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateConfigMap($cloud_context, array $config_map, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $config_map['metadata']['name'];
    $namespace = $config_map['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_config_map',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sConfigMap::load($entity_id);
    }
    else {
      $entity = K8sConfigMap::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($config_map, $timestamp),
        'changed' => self::getCreationTimestamp($config_map, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $config_map['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $config_map['metadata']['annotations'] ?? []
    );

    // Data.
    self::setKeyValueTypeFieldValue(
      $entity,
      'data',
      $config_map['data'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($config_map));

    $namespace = $config_map['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s network policy entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $network_policy
   *   The network policy array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateNetworkPolicy($cloud_context, array $network_policy, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $network_policy['metadata']['name'];
    $namespace = $network_policy['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_network_policy',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sNetworkPolicy::load($entity_id);
    }
    else {
      $entity = K8sNetworkPolicy::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($network_policy, $timestamp),
        'changed' => self::getCreationTimestamp($network_policy, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $network_policy['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $network_policy['metadata']['annotations'] ?? []
    );

    // Network Polices.
    self::setKeyValueTypeFieldValue(
      $entity,
      'egress',
      $network_policy['spec']['egress'] ?? []
    );

    self::setKeyValueTypeFieldValue(
      $entity,
      'ingress',
      $network_policy['spec']['pod_selector'] ?? []
    );

    self::setKeyValueTypeFieldValue(
      $entity,
      'policy_types',
      $network_policy['spec']['policy_types'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($network_policy));

    $namespace = $network_policy['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s role entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $role
   *   The role array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateRole($cloud_context, array $role, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $role['metadata']['name'];
    $namespace = $role['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_role',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sRole::load($entity_id);
    }
    else {
      $entity = K8sRole::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($role, $timestamp),
        'changed' => self::getCreationTimestamp($role, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $role['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $role['metadata']['annotations'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($role));

    // Rules.
    $fields = [
      'resources',
      'resource_names',
      'api_groups',
      'verbs',
    ];
    $rules = [];
    foreach ($role['rules'] ?: [] as $rule_data) {
      $rule = [];
      foreach ($fields ?: [] as $field) {
        $field_camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field))));
        if (empty($rule_data[$field_camel])) {
          continue;
        }
        $rule[$field] = implode(', ', $rule_data[$field_camel]);
      }
      $rules[] = $rule;
    }

    $entity->set('rules', $rules);

    $namespace = $role['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s cluster role entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $cluster_role
   *   The cluster role array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateClusterRole($cloud_context, array $cluster_role, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $cluster_role['metadata']['name'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_cluster_role',
      'name',
      $name
    );

    if (!empty($entity_id)) {
      $entity = K8sClusterRole::load($entity_id);
    }
    else {
      $entity = K8sClusterRole::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($cluster_role, $timestamp),
        'changed' => self::getCreationTimestamp($cluster_role, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $cluster_role['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $cluster_role['metadata']['annotations'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($cluster_role));

    // Rules.
    $fields = [
      'resources',
      'resource_names',
      'api_groups',
      'verbs',
    ];
    $rules = [];
    foreach ($cluster_role['rules'] ?: [] as $rule_data) {
      $rule = [];
      foreach ($fields ?: [] as $field) {
        $field_camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field))));
        if (empty($rule_data[$field_camel])) {
          continue;
        }
        $rule[$field] = implode(', ', $rule_data[$field_camel]);
      }
      $rules[] = $rule;
    }

    $entity->set('rules', $rules);

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s storage class entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $storage_class
   *   The storage class array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateStorageClass($cloud_context, array $storage_class, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $storage_class['metadata']['name'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_storage_class',
      'name',
      $name
    );

    if (!empty($entity_id)) {
      $entity = K8sStorageClass::load($entity_id);
    }
    else {
      $entity = K8sStorageClass::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($storage_class, $timestamp),
        'changed' => self::getCreationTimestamp($storage_class, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $storage_class['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $storage_class['metadata']['annotations'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($storage_class));

    self::setKeyValueTypeFieldValue(
      $entity,
      'parameters',
      $storage_class['parameters'] ?? []
    );

    $entity->setProvisioner($storage_class['provisioner'] ?? '');
    $entity->setReclaimPolicy($storage_class['reclaimPolicy'] ?? '');
    $entity->setVolumeBindingMode($storage_class['volumeBindingMode'] ?? '');

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s stateful set entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $stateful_set
   *   The stateful set array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateStatefulSet($cloud_context, array $stateful_set, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $stateful_set['metadata']['name'] ?? '';
    $namespace = $stateful_set['metadata']['namespace'] ?? '';
    $entity_id = $k8s_service->getEntityId(
      'k8s_stateful_set',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sStatefulSet::load($entity_id);
    }
    else {
      $entity = K8sStatefulSet::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($stateful_set, $timestamp),
        'changed' => self::getCreationTimestamp($stateful_set, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Owner ID.
    $uid = NULL;
    if (isset($stateful_set['metadata']['annotations'])
      && isset($stateful_set['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID])) {
      $uid = $stateful_set['metadata']['annotations'][K8sEntityBase::ANNOTATION_CREATED_BY_UID];
    }
    $entity->setOwnerById($uid);

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $stateful_set['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $stateful_set['metadata']['annotations'] ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($stateful_set));

    $entity->setNamespace($namespace);
    $entity->setUpdateStrategy($stateful_set['spec']['updateStrategy']['type'] ?? '');
    $entity->setServiceName($stateful_set['spec']['serviceName'] ?? '');
    $entity->setPodManagementPolicy($stateful_set['spec']['podManagementPolicy'] ?? '');
    $entity->setRevisionHistoryLimit($stateful_set['spec']['revisionHistoryLimit'] ?? '');
    $entity->setObservedGeneration($stateful_set['status']['observedGeneration'] ?? 0);
    $entity->setReplicas($stateful_set['status']['replicas'] ?? 0);
    $entity->setReadyReplicas($stateful_set['status']['readyReplicas'] ?? 0);
    $entity->setCurrentReplicas($stateful_set['status']['currentReplicas'] ?? 0);
    $entity->setUpdatedReplicas($stateful_set['status']['updatedReplicas'] ?? 0);
    $entity->setCurrentRevision($stateful_set['status']['currentRevision'] ?? '');
    $entity->setUpdateRevision($stateful_set['status']['updateRevision'] ?? '');
    $entity->setCollisionCount($stateful_set['status']['collisionCount'] ?? 0);

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s persistent volume entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $persistent_volume
   *   The persistent volume array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updatePersistentVolume($cloud_context, array $persistent_volume, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $persistent_volume['metadata']['name'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_persistent_volume',
      'name',
      $name
    );

    if (!empty($entity_id)) {
      $entity = K8sPersistentVolume::load($entity_id);
    }
    else {
      $entity = K8sPersistentVolume::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($persistent_volume, $timestamp),
        'changed' => self::getCreationTimestamp($persistent_volume, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $persistent_volume['metadata']['labels'] ?? []
    );
    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $persistent_volume['metadata']['annotations'] ?? []
    );

    $claim = NULL;
    if (isset($persistent_volume['spec']['claimRef'])) {
      $claim_namespace = $persistent_volume['spec']['claimRef']['namespace'] ?? '';
      $claim_name = $persistent_volume['spec']['claimRef']['name'] ?? '';
      $claim = $claim_namespace . '/' . $claim_name;
    }

    // Persistent Volume.
    $entity->setCapacity($persistent_volume['spec']['capacity']['storage'] ?? '');

    $entity->setAccessModes(implode(', ', $persistent_volume['spec']['accessModes']) ?? '');

    $entity->setReclaimPolicy($persistent_volume['spec']['persistentVolumeReclaimPolicy'] ?? '');
    $entity->setStorageClassName($persistent_volume['spec']['storageClassName'] ?? '');
    $entity->setClaimRef($claim);
    $entity->setPhase($persistent_volume['status']['phase'] ?? '');
    $entity->setReason($persistent_volume['status']['reason'] ?? '-');

    // Detail.
    $entity->setDetail(Yaml::encode($persistent_volume));

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s ingress entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $ingress
   *   The Ingress array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateIngress($cloud_context, array $ingress, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $ingress['metadata']['name'];
    $namespace = $ingress['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_ingress',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sIngress::load($entity_id);
    }
    else {
      $entity = K8sIngress::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($ingress, $timestamp),
        'changed' => self::getCreationTimestamp($ingress, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $ingress['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $ingress['metadata']['annotations'] ?? []
    );

    // Ingress.
    self::setKeyValueTypeFieldValue(
      $entity,
      'backend',
      $ingress['spec']['backend'] ?? []
    );

    // TLS.
    $ingress_tls = $ingress['spec']['tls'] ?? [];
    $tls = [];
    foreach ($ingress_tls ?: [] as $tls_data) {
      foreach ($tls_data['hosts'] ?: [] as $tls_host) {
        $tls['hosts'] = $tls_host;
      }
      $tls['secretName'] = $tls_data['secretName'];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'tls',
      $tls ?? []
    );

    // Rules.
    $ingress_rules = $ingress['spec']['rules'] ?? [];
    $rule = [];
    foreach ($ingress_rules ?: [] as $rule_data) {
      $rule['host'] = $rule_data['host'];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'rules',
      $rule ?? []
    );

    // Load Balancer.
    $ingress_load_balancer = $ingress['status']['loadBalancer']['ingress'] ?? [];
    $load_balancer = [];
    foreach ($ingress_load_balancer ?: [] as $load_balancer_data) {
      $load_balancer['ip'] = $load_balancer_data['ip'];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'load_balancer',
      $load_balancer ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($ingress));

    $namespace = $ingress['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s daemon set entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $daemon_set
   *   The daemon set array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateDaemonSet($cloud_context, array $daemon_set, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $daemon_set['metadata']['name'] ?? '';
    $namespace = $daemon_set['metadata']['namespace'] ?? '';
    $entity_id = $k8s_service->getEntityId(
      'k8s_daemon_set',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sDaemonSet::load($entity_id);
    }
    else {
      $entity = K8sDaemonSet::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($daemon_set, $timestamp),
        'changed' => self::getCreationTimestamp($daemon_set, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $daemon_set['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $daemon_set['metadata']['annotations'] ?? []
    );

    // Metrics.
    $cpu_request = 0;
    $cpu_limit = 0;
    $memory_limit = 0;
    $memory_request = 0;
    foreach ($daemon_set['spec']['template']['spec']['containers'] ?: [] as $container) {
      if (isset($container['resources']['requests'])) {
        $requests = $container['resources']['requests'];
        if (isset($requests['cpu'])) {
          $cpu_request += k8s_convert_cpu_to_float($requests['cpu']);
        }

        if (isset($requests['memory'])) {
          $memory_request += k8s_convert_memory_to_integer($requests['memory']);
        }
      }

      if (isset($container['resources']['limits'])) {
        $limits = $container['resources']['limits'];
        if (isset($limits['cpu'])) {
          $cpu_limit += k8s_convert_cpu_to_float($limits['cpu']);
        }

        if (isset($limits['memory'])) {
          $memory_limit += k8s_convert_memory_to_integer($limits['memory']);
        }
      }
    }

    $entity->setCpuRequest($cpu_request);
    $entity->setCpuLimit($cpu_limit);
    $entity->setMemoryRequest($memory_request);
    $entity->setMemoryLimit($memory_limit);

    // Detail.
    $entity->setDetail(Yaml::encode($daemon_set));

    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s endpoint entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $endpoint
   *   The Endpoint array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateEndpoint($cloud_context, array $endpoint, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $endpoint['metadata']['name'];
    $namespace = $endpoint['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_endpoint',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sEndpoint::load($entity_id);
    }
    else {
      $entity = K8sEndpoint::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($endpoint, $timestamp),
        'changed' => self::getCreationTimestamp($endpoint, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $endpoint['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $endpoint['metadata']['annotations'] ?? []
    );

    $endpoint_subsets = $endpoint['subsets'] ?? [];

    // Addresses.
    $address = [];
    foreach ($endpoint_subsets ?: [] as $subset_data) {
      $endpoint_addresses = $subset_data['addresses'] ?? [];
      foreach ($endpoint_addresses ?: [] as $key => $address_data) {
        $address['ip'] = $address_data['ip'];
        if (isset($address_data['hostname'])) {
          $address['hostname'] = $address_data['hostname'];
        }
        if (isset($address_data['nodeName'])) {
          $address['nodeName'] = $address_data['nodeName'];
        }
        if (isset($address_data['targetRef'])) {
          $address['podName'] = $address_data['targetRef']['name'];
        }
      }
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'addresses',
      $address ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($endpoint));

    $namespace = $endpoint['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setNodeName($address['nodeName'] ?? '');
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s event entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $event
   *   The event array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateEvent($cloud_context, array $event, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $event['metadata']['name'];
    $entity_id = $k8s_service->getEntityId('k8s_event', 'name', $name);

    if (!empty($entity_id)) {
      $entity = K8sEvent::load($entity_id);
    }
    else {
      $entity = K8sEvent::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($event, $timestamp),
        'changed' => self::getCreationTimestamp($event, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue($entity, 'labels', $event['metadata']['labels'] ?? []);

    // Annotations.
    self::setKeyValueTypeFieldValue($entity, 'annotations', $event['metadata']['annotations'] ?? []);

    // Event Attributes.
    $entity->setType($event['type'] ?? '');
    $entity->setReason($event['reason'] ?? '');
    $entity->setObjectKind($event['involvedObject']['kind'] ?? '');
    $entity->setObjectName($event['involvedObject']['name'] ?? '');
    $entity->setMessage($event['message'] ?? '');
    // $entity->setTimeStamp($event['lastTimestamp'] ?? '');  .
    if (!empty($event['lastTimestamp'])) {
      $entity->setTimeStamp(strtotime($event['lastTimestamp']));
    }

    // Detail.
    $entity->setDetail(Yaml::encode($event));
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s persistent volume claim entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $persistent_volume_claim
   *   The persistent volume claim array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updatePersistentVolumeClaim($cloud_context, array $persistent_volume_claim, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $persistent_volume_claim['metadata']['name'] ?? '';
    $namespace = $persistent_volume_claim['metadata']['namespace'] ?? '';
    $entity_id = $k8s_service->getEntityId(
      'k8s_persistent_volume_claim',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sPersistentVolumeClaim::load($entity_id);
    }
    else {
      $entity = K8sPersistentVolumeClaim::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($persistent_volume_claim, $timestamp),
        'changed' => self::getCreationTimestamp($persistent_volume_claim, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $persistent_volume_claim['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $persistent_volume_claim['metadata']['annotations'] ?? []
    );

    // Attributes.
    $entity->setPhase($persistent_volume_claim['status']['phase'] ?? '');
    $entity->setVolumeName($persistent_volume_claim['spec']['volumeName'] ?? '');
    $entity->setCapacity($persistent_volume_claim['status']['capacity']['storage'] ?? '');
    $entity->setRequest($persistent_volume_claim['spec']['resources']['requests']['storage'] ?? '');
    $entity->setAccessMode($persistent_volume_claim['spec']['accessModes'] ?? '');
    $entity->setStorageClass($persistent_volume_claim['spec']['storageClassName'] ?? '');

    // Detail.
    $entity->setDetail(Yaml::encode($persistent_volume_claim));
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s cluster role binding entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $cluster_role_binding
   *   The cluster role binding array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateClusterRoleBinding($cloud_context, array $cluster_role_binding, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $cluster_role_binding['metadata']['name'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_cluster_role_binding',
      'name',
      $name
    );

    if (!empty($entity_id)) {
      $entity = K8sClusterRoleBinding::load($entity_id);
    }
    else {
      $entity = K8sClusterRoleBinding::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($cluster_role_binding, $timestamp),
        'changed' => self::getCreationTimestamp($cluster_role_binding, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $cluster_role_binding['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $cluster_role_binding['metadata']['annotations'] ?? []
    );

    $cluster_role_subjects = $cluster_role_binding['subjects'] ?? [];

    // Subjects.
    $subjects = [];
    foreach ($cluster_role_subjects ?: [] as $subject_data) {
      $subjects['name'] = $subject_data['name'] ?? [];
      $subjects['namespace'] = $subject_data['namespace'] ?? [];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'subjects',
      $subjects ?? []
    );

    // Role.
    $role_name = NULL;
    if (isset($cluster_role_binding['roleRef'])) {
      $role_name = $cluster_role_binding['roleRef']['name'] ?? '';
    }

    $entity->setRoleRef($role_name);

    // Detail.
    $entity->setDetail(Yaml::encode($cluster_role_binding));

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s role binding entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $role_binding
   *   The role binding array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateRoleBinding($cloud_context, array $role_binding, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = !empty($role_binding['metadata']['name']) ? $role_binding['metadata']['name'] : '';
    $namespace = !empty($role_binding['metadata']['namespace']) ? $role_binding['metadata']['namespace'] : '';
    $entity_id = $k8s_service->getEntityId(
      'k8s_role_binding',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sRoleBinding::load($entity_id);
    }
    else {
      $entity = K8sRoleBinding::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($role_binding, $timestamp),
        'changed' => self::getCreationTimestamp($role_binding, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $role_binding['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $role_binding['metadata']['annotations'] ?? []
    );

    $role_subjects = $role_binding['subjects'] ?? [];

    // Subjects.
    $subjects = [];
    foreach ($role_subjects ?: [] as $subject_data) {
      $subjects['name'] = $subject_data['name'] ?? [];
      $subjects['namespace'] = $subject_data['namespace'] ?? [];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'subjects',
      $subjects ?? []
    );

    // Role.
    $role_name = NULL;
    if (isset($role_binding['roleRef'])) {
      $role_name = $role_binding['roleRef']['name'] ?? '';
    }

    $entity->setRole($role_name);

    // Detail.
    $entity->setDetail(Yaml::encode($role_binding));

    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Set key_value type field value.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   * @param string $field_name
   *   The field name.
   * @param array $value_map
   *   The value of map type.
   */
  private static function setKeyValueTypeFieldValue(EntityInterface $entity, $field_name, array $value_map) {
    $key_values = [];
    if (!isset($value_map)) {
      $value_map = [];
    }
    foreach ($value_map ?: [] as $key => $value) {
      $key_values[] = ['item_key' => $key, 'item_value' => $value ?: ''];
    }

    usort($key_values, static function ($a, $b) {
      return strcmp($a['item_key'], $b['item_key']);
    });

    $entity->set($field_name, $key_values);
  }

  /**
   * Get creation timestamp.
   *
   * @param array $data
   *   The data.
   * @param int $default_timestamp
   *   The default timestamp.
   *
   * @return int
   *   The creation timestamp.
   */
  private static function getCreationTimestamp(array $data, $default_timestamp) {
    if (empty($data['metadata']['creationTimestamp'])) {
      return $default_timestamp;
    }

    return strtotime($data['metadata']['creationTimestamp']);
  }

  /**
   * Update or create a k8s API service entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $api_service
   *   The API service array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateApiService($cloud_context, array $api_service, array $extra_data) {

    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);
    $timestamp = time();
    $name = $api_service['metadata']['name'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_api_service',
      'name',
      $name
    );

    if (!empty($entity_id)) {
      $entity = K8sApiService::load($entity_id);
    }
    else {
      $entity = K8sApiService::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($api_service, $timestamp),
        'changed' => self::getCreationTimestamp($api_service, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      $api_service['metadata']['labels'] ?? []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      $api_service['metadata']['annotations'] ?? []
    );

    // Service.
    $services = $api_service['spec']['service'] ?? [];
    $service = [];
    $service['namespace'] = $services['namespace'] ?? '';
    $service['name'] = $services['name'] ?? '';

    self::setKeyValueTypeFieldValue(
      $entity,
      'service',
      !empty($service) ? $service : []
    );

    // Conditions.
    $api_condition = $api_service['status']['conditions'] ?? [];
    $rule = [];
    foreach ($api_condition ?: [] as $api_data) {
      $api['status'] = $api_data['status'];
      $api['type'] = $api_data['type'];
      $api['reason'] = $api_data['reason'];
      $api['message'] = $api_data['message'];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'conditions',
      $api ?? []
    );

    // API Service.
    $entity->setGroupPriorityMinimum($api_service['spec']['groupPriorityMinimum'] ?? 0);
    $entity->setVersionPriority($api_service['spec']['versionPriority'] ?? 0);
    $entity->setGroup($api_service['spec']['group'] ?? '');
    $entity->setInsecureSkipTlsVerify($api_service['spec']['insecureSkipTLSVerify'] ?? '');
    $entity->setVersion($api_service['spec']['version'] ?? '');

    // Detail.
    $entity->setDetail(Yaml::encode($api_service));

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s Service account entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $service_account
   *   The Service Account array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updateServiceAccount($cloud_context, array $service_account, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $service_account['metadata']['name'];
    $namespace = $service_account['metadata']['namespace'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_service_account',
      'name',
      $name,
      ['namespace' => $namespace]
    );

    if (!empty($entity_id)) {
      $entity = K8sServiceAccount::load($entity_id);
    }
    else {
      $entity = K8sServiceAccount::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($service_account, $timestamp),
        'changed' => self::getCreationTimestamp($service_account, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      isset($service_account['metadata']['labels']) ? $service_account['metadata']['labels'] : []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      isset($service_account['metadata']['annotations']) ? $service_account['metadata']['annotations'] : []
    );

    // Secrets.
    $service_account_secrets = $service_account['secrets'] ?? [];
    $secrets = [];
    foreach ($service_account_secrets ?: [] as $secret_data) {
      $secrets['name'] = $secret_data['name'];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'secrets',
      $secrets ?? []
    );

    // ImagePullSecrets.
    $service_account_image_pull_secrets = $service_account['imagePullSecrets'] ?? [];
    $image_pull_secrets = [];
    foreach ($service_account_image_pull_secrets ?: [] as $image_pull_secrets_data) {
      $image_pull_secrets['name'] = $image_pull_secrets_data['name'];
    }

    self::setKeyValueTypeFieldValue(
      $entity,
      'image_pull_secrets',
      $image_pull_secrets ?? []
    );

    // Detail.
    $entity->setDetail(Yaml::encode($service_account));

    $namespace = $service_account['metadata']['namespace'];
    $entity->setNamespace($namespace);
    $entity->setRefreshed($timestamp);
    $entity->save();
  }

  /**
   * Update or create a k8s Priority Class entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $priority_class
   *   The Priority Class array.
   * @param array $extra_data
   *   The extra data.
   */
  public static function updatePriorityClass($cloud_context, array $priority_class, array $extra_data) {
    $k8s_service = \Drupal::service('k8s');
    $k8s_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $priority_class['metadata']['name'];
    $entity_id = $k8s_service->getEntityId(
      'k8s_priority_class',
      'name',
      $name
    );

    if (!empty($entity_id)) {
      $entity = K8sPriorityClass::load($entity_id);
    }
    else {
      $entity = K8sPriorityClass::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($priority_class, $timestamp),
        'changed' => self::getCreationTimestamp($priority_class, $timestamp),
        'refreshed' => $timestamp,
      ]);
    }

    // Labels.
    self::setKeyValueTypeFieldValue(
      $entity,
      'labels',
      isset($priority_class['metadata']['labels']) ? $priority_class['metadata']['labels'] : []
    );

    // Annotations.
    self::setKeyValueTypeFieldValue(
      $entity,
      'annotations',
      isset($priority_class['metadata']['annotations']) ? $priority_class['metadata']['annotations'] : []
    );

    $entity->setValue($priority_class['value'] ?? 0);
    $entity->setGlobalDefault($priority_class['globalDefault'] ?? FALSE);
    $entity->setDescription($priority_class['description'] ?? '');

    // Detail.
    $entity->setDetail(Yaml::encode($priority_class));

    $entity->setRefreshed($timestamp);
    $entity->save();
  }

}
