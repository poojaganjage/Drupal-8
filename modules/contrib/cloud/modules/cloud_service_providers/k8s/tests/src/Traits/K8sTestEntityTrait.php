<?php

namespace Drupal\Tests\k8s\Traits;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Entity\CloudProject;
use Drupal\k8s\Entity\K8sApiService;
use Drupal\k8s\Entity\K8sClusterRole;
use Drupal\k8s\Entity\K8sClusterRoleBinding;
use Drupal\k8s\Entity\K8sConfigMap;
use Drupal\k8s\Entity\K8sCronJob;
use Drupal\k8s\Entity\K8sNode;
use Drupal\k8s\Entity\K8sDaemonSet;
use Drupal\k8s\Entity\K8sDeployment;
use Drupal\k8s\Entity\K8sEndpoint;
use Drupal\k8s\Entity\K8sIngress;
use Drupal\k8s\Entity\K8sJob;
use Drupal\k8s\Entity\K8sLimitRange;
use Drupal\k8s\Entity\K8sNamespace;
use Drupal\k8s\Entity\K8sNetworkPolicy;
use Drupal\k8s\Entity\K8sPersistentVolume;
use Drupal\k8s\Entity\K8sPersistentVolumeClaim;
use Drupal\k8s\Entity\K8sPod;
use Drupal\k8s\Entity\K8sPriorityClass;
use Drupal\k8s\Entity\K8sReplicaSet;
use Drupal\k8s\Entity\K8sResourceQuota;
use Drupal\k8s\Entity\K8sRole;
use Drupal\k8s\Entity\K8sRoleBinding;
use Drupal\k8s\Entity\K8sSecret;
use Drupal\k8s\Entity\K8sServiceAccount;
use Drupal\k8s\Entity\K8sServiceEntity;
use Drupal\k8s\Entity\K8sStatefulSet;
use Drupal\k8s\Entity\K8sStorageClass;
use Drupal\Tests\cloud\Traits\CloudTestEntityTrait;
use Drupal\cloud\Entity\CloudServerTemplate;

/**
 * The trait creating test entity for k8s testing.
 */
trait K8sTestEntityTrait {

  use CloudTestEntityTrait;

  /**
   * Create a K8s Namespace test entity.
   *
   * @param array $namespace
   *   The namespace data.
   *
   * @return \Drupal\k8s\Entity\K8sNamespace
   *   The Namespace entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createNamespaceTestEntity(array $namespace): K8sNamespace {
    $entity = K8sNamespace::create([
      'name' => $namespace['name'],
      'cloud_context' => $this->cloudContext,
    ]);
    $entity->save();
    return $entity;
  }

  /**
   * Create a K8s Pod test entity.
   *
   * @param array $node
   *   The node data.
   *
   * @return \Drupal\k8s\Entity\K8sPod
   *   The Node entity.
   */
  protected function createNodeTestEntity(array $node): CloudContentEntityBase {
    return $this->createTestEntity(K8sNode::class, $node);
  }

  /**
   * Create a K8s Pod test entity.
   *
   * @param array $pod
   *   The pod data.
   *
   * @return \Drupal\k8s\Entity\K8sPod
   *   The Pod entity.
   */
  protected function createPodTestEntity(array $pod): CloudContentEntityBase {
    return $this->createTestEntity(K8sPod::class, $pod);
  }

  /**
   * Create a K8s Deployment test entity.
   *
   * @param array $deployment
   *   The deployment data.
   *
   * @return \Drupal\k8s\Entity\K8sDeployment
   *   The Deployment entity.
   */
  protected function createDeploymentTestEntity(array $deployment): CloudContentEntityBase {
    return $this->createTestEntity(K8sDeployment::class, $deployment);
  }

  /**
   * Create a K8s Replica Set test entity.
   *
   * @param array $replica_set
   *   The replica set data.
   *
   * @return \Drupal\k8s\Entity\K8sReplicaSet
   *   The Replica Set entity.
   */
  protected function createReplicaSetTestEntity(array $replica_set): CloudContentEntityBase {
    return $this->createTestEntity(K8sReplicaSet::class, $replica_set);
  }

  /**
   * Create a K8s Service test entity.
   *
   * @param array $service
   *   The service data.
   *
   * @return \Drupal\k8s\Entity\K8sServiceEntity
   *   The Service entity.
   */
  protected function createServiceTestEntity(array $service): CloudContentEntityBase {
    return $this->createTestEntity(K8sServiceEntity::class, $service);
  }

  /**
   * Create a K8s Cron Job test entity.
   *
   * @param array $cron_job
   *   The cron job data.
   *
   * @return \Drupal\k8s\Entity\K8sCronJob
   *   The Cron Job entity.
   */
  protected function createCronJobTestEntity(array $cron_job): CloudContentEntityBase {
    return $this->createTestEntity(K8sCronJob::class, $cron_job);
  }

  /**
   * Create a K8s Job test entity.
   *
   * @param array $job
   *   The job data.
   *
   * @return \Drupal\k8s\Entity\K8sJob
   *   The Job entity.
   */
  protected function createJobTestEntity(array $job): CloudContentEntityBase {
    return $this->createTestEntity(K8sJob::class, $job);
  }

  /**
   * Create a K8s Resource Quota test entity.
   *
   * @param array $resource_quota
   *   The resource quota data.
   *
   * @return \Drupal\k8s\Entity\K8sResourceQuota
   *   The Resource Quota entity.
   */
  protected function createResourceQuotaTestEntity(array $resource_quota): CloudContentEntityBase {
    return $this->createTestEntity(K8sResourceQuota::class, $resource_quota);
  }

  /**
   * Create a K8s Limit Range test entity.
   *
   * @param array $limit_range
   *   The limit range data.
   *
   * @return \Drupal\k8s\Entity\K8sLimitRange
   *   The Limit Range entity.
   */
  protected function createLimitRangeTestEntity(array $limit_range): CloudContentEntityBase {
    return $this->createTestEntity(K8sLimitRange::class, $limit_range);
  }

  /**
   * Create a K8s Secret test entity.
   *
   * @param array $secret
   *   The secret data.
   *
   * @return \Drupal\k8s\Entity\K8sSecret
   *   The secret entity.
   */
  protected function createSecretTestEntity(array $secret): CloudContentEntityBase {
    return $this->createTestEntity(K8sSecret::class, $secret);
  }

  /**
   * Create a K8s ConfigMap test entity.
   *
   * @param array $config_map
   *   The ConfigMap data.
   *
   * @return \Drupal\k8s\Entity\K8sConfigMap
   *   The ConfigMap entity.
   */
  protected function createConfigMapTestEntity(array $config_map): CloudContentEntityBase {
    return $this->createTestEntity(K8sConfigMap::class, $config_map);
  }

  /**
   * Create a K8s Network Policy test entity.
   *
   * @param array $network_policy
   *   The network policy data.
   *
   * @return \Drupal\k8s\Entity\K8sNetworkPolicy
   *   The Network Policy entity.
   */
  protected function createNetworkPolicyTestEntity(array $network_policy): CloudContentEntityBase {
    return $this->createTestEntity(K8sNetworkPolicy::class, $network_policy);
  }

  /**
   * Create a K8s Role test entity.
   *
   * @param array $role
   *   The role data.
   *
   * @return \Drupal\k8s\Entity\K8sRole
   *   The role entity.
   */
  protected function createRoleTestEntity(array $role): CloudContentEntityBase {
    return $this->createTestEntity(K8sRole::class, $role);
  }

  /**
   * Create a K8s Cluster Role test entity.
   *
   * @param array $cluster_role
   *   The cluster role data.
   *
   * @return \Drupal\k8s\Entity\K8sClusterRole
   *   The cluster role entity.
   */
  protected function createClusterRoleTestEntity(array $cluster_role): CloudContentEntityBase {
    return $this->createTestEntity(K8sClusterRole::class, $cluster_role);
  }

  /**
   * Create K8s Persistent Volumes test entity.
   *
   * @param array $persistent_volume
   *   The persistent volume data.
   *
   * @return \Drupal\k8s\Entity\K8sPersistentVolume
   *   The persistent volume entity.
   */
  protected function createPersistentVolumeTestEntity(array $persistent_volume): CloudContentEntityBase {
    return $this->createTestEntity(K8sPersistentVolume::class, $persistent_volume);
  }

  /**
   * Create a K8s Storage Class test entity.
   *
   * @param array $storage_class
   *   The storage class data.
   *
   * @return \Drupal\k8s\Entity\K8sStorageClass
   *   The storage class entity.
   */
  protected function createStorageClassTestEntity(array $storage_class): CloudContentEntityBase {
    return $this->createTestEntity(K8sStorageClass::class, $storage_class);
  }

  /**
   * Create a K8s Stateful Set test entity.
   *
   * @param array $stateful_set
   *   The stateful set data.
   *
   * @return \Drupal\k8s\Entity\K8sStatefulSet
   *   The stateful set entity.
   */
  protected function createStatefulSetTestEntity(array $stateful_set): CloudContentEntityBase {
    return $this->createTestEntity(K8sStatefulSet::class, $stateful_set);
  }

  /**
   * Create a K8s Ingress test entity.
   *
   * @param array $ingress
   *   The ingress data.
   *
   * @return \Drupal\k8s\Entity\K8sIngress
   *   The ingress entity.
   */
  protected function createIngressTestEntity(array $ingress): CloudContentEntityBase {
    return $this->createTestEntity(K8sIngress::class, $ingress);
  }

  /**
   * Create a K8s Daemon Set test entity.
   *
   * @param array $daemon_set
   *   The daemon set data.
   *
   * @return \Drupal\k8s\Entity\K8sDaemonSet
   *   The daemon set entity.
   */
  protected function createDaemonSetTestEntity(array $daemon_set): CloudContentEntityBase {
    return $this->createTestEntity(K8sDaemonSet::class, $daemon_set);
  }

  /**
   * Create a K8s Endpoint test entity.
   *
   * @param array $endpoint
   *   The endpoint data.
   *
   * @return \Drupal\k8s\Entity\K8sEndpoint
   *   The endpoint entity.
   */
  protected function createEndpointTestEntity(array $endpoint): CloudContentEntityBase {
    return $this->createTestEntity(K8sEndpoint::class, $endpoint);
  }

  /**
   * Create a K8s Service Account test entity.
   *
   * @param array $service_account
   *   The service account data.
   *
   * @return \Drupal\k8s\Entity\K8sServiceAccount
   *   The service account entity.
   */
  protected function createServiceAccountTestEntity(array $service_account): CloudContentEntityBase {
    return $this->createTestEntity(K8sServiceAccount::class, $service_account);
  }

  /**
   * Create a K8s Persistent Volume Claim test entity.
   *
   * @param array $persistent_volume_claim
   *   The persistent volume claim data.
   *
   * @return \Drupal\k8s\Entity\K8sPersistentVolumeClaim
   *   The persistent volume claim entity.
   */
  protected function createPersistentVolumeClaimTestEntity(array $persistent_volume_claim): CloudContentEntityBase {
    return $this->createTestEntity(K8sPersistentVolumeClaim::class, $persistent_volume_claim);
  }

  /**
   * Create a K8s Cluster Role Binding test entity.
   *
   * @param array $cluster_role_binding
   *   The cluster role binding data.
   *
   * @return \Drupal\k8s\Entity\K8sClusterRoleBinding
   *   The cluster role binding entity.
   */
  protected function createClusterRoleBindingTestEntity(array $cluster_role_binding): CloudContentEntityBase {
    return $this->createTestEntity(K8sClusterRoleBinding::class, $cluster_role_binding);
  }

  /**
   * Create a K8s API Service test entity.
   *
   * @param array $api_service
   *   The API service data.
   *
   * @return \Drupal\k8s\Entity\K8sApiService
   *   The API service entity.
   */
  protected function createApiServiceTestEntity(array $api_service): CloudContentEntityBase {
    return $this->createTestEntity(K8sApiService::class, $api_service);
  }

  /**
   * Create a K8s Role Binding test entity.
   *
   * @param array $role_binding
   *   The role binding data.
   *
   * @return \Drupal\k8s\Entity\K8sRoleBinding
   *   The role binding entity.
   */
  protected function createRoleBindingTestEntity(array $role_binding): CloudContentEntityBase {
    return $this->createTestEntity(K8sRoleBinding::class, $role_binding);
  }

  /**
   * Create a K8s Priority Class test entity.
   *
   * @param array $priority_class
   *   The Priority Class data.
   *
   * @return \Drupal\k8s\Entity\K8sPriorityClass
   *   The Priority Class entity.
   */
  protected function createPriorityClassTestEntity(array $priority_class): CloudContentEntityBase {
    return $this->createTestEntity(K8sPriorityClass::class, $priority_class);
  }

  /**
   * Create a K8s Server Template test entity.
   *
   * @param array $server_template
   *   The server template data.
   *
   * @return \Drupal\cloud\Entity\CloudServerTemplate
   *   The server template entity.
   */
  protected function createServerTemplateTestEntity(array $server_template): CloudContentEntityBase {
    $server_template['type'] = 'k8s';
    return parent::createTestEntity(CloudServerTemplate::class, $server_template);
  }

  /**
   * Create a K8s project test entity.
   *
   * @param array $project
   *   The project data.
   *
   * @return \Drupal\cloud\Entity\CloudProject
   *   The project entity.
   */
  protected function createProjectTestEntity(array $project): CloudContentEntityBase {
    $project['type'] = 'k8s';
    return parent::createTestEntity(CloudProject::class, $project);
  }

  /**
   * Helper function to create a K8s test entity.
   *
   * @param string $class_name
   *   The class name.
   * @param array $form_data
   *   The form data.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The entity.
   */
  protected function createTestEntity($class_name, array $form_data): CloudContentEntityBase {

    $params = [
      'cloud_context' => $this->cloudContext,
      'name' => $form_data['name'],
    ];

    if (array_key_exists('namespace', $form_data)
    && !empty($form_data['namespace'])) {
      $params['namespace'] = $form_data['namespace'];
    }

    return parent::createTestEntity($class_name, $params);
  }

}
