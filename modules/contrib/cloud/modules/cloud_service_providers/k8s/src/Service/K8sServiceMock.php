<?php

namespace Drupal\k8s\Service;

use Drupal\Core\Form\ConfigFormBaseTrait;

/**
 * K8sServiceMock service interacts with the K8s API.
 */
class K8sServiceMock extends K8sService {

  use ConfigFormBaseTrait;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['k8s.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getNodes(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getNodes'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getNamespaces(array $params = []) : array {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getNamespaces'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createNamespace(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createNamespace'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateNamespace(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateNamespace'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteNamespace(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteNamespace'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPods(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getPods'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createPod($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createPod'];
  }

  /**
   * {@inheritdoc}
   */
  public function updatePod($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updatePod'];
  }

  /**
   * {@inheritdoc}
   */
  public function deletePod($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deletePod'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPodLogs($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getPodLogs'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDeployments(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getDeployments'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createDeployment($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createDeployment'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateDeployment($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateDeployment'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDeployment($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteDeployment'];
  }

  /**
   * {@inheritdoc}
   */
  public function getReplicaSets(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getReplicaSets'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createReplicaSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createReplicaSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateReplicaSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateReplicaSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteReplicaSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteReplicaSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function getServices(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getServices'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createService($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createService'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateService($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateService'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteService($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteService'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCronJobs(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getCronJobs'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createCronJob($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createCronJob'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateCronJob($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateCronJob'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteCronJob($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteCronJob'];
  }

  /**
   * {@inheritdoc}
   */
  public function getJobs(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getJobs'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createJob($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createJob'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateJob($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateJob'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteJob($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteJob'];
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceQuotas(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getResourceQuotas'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createResourceQuota($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createResourceQuota'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateResourceQuota($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateResourceQuota'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteResourceQuota($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteResourceQuota'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLimitRanges(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getLimitRanges'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createLimitRange($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createLimitRange'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateLimitRange($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateLimitRange'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLimitRange($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteLimitRange'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSecrets(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getSecrets'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createSecret($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createSecret'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateSecret($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateSecret'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSecret($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteSecret'];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigMaps(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getConfigMaps'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigMap($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createConfigMap'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateConfigMap($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateConfigMap'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteConfigMap($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteConfigMap'];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetricsPods(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getMetricsPods'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetricsNodes(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getMetricsNodes'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getNetworkPolicies(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getNetworkPolicies'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createNetworkPolicy($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createNetworkPolicy'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateNetworkPolicy($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateNetworkPolicy'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteNetworkPolicy($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteNetworkPolicy'];
  }

  /**
   * {@inheritdoc}
   */
  public function getRoles(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getRoles'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createRole($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createRole'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateRole($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateRole'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRole($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteRole'];
  }

  /**
   * {@inheritdoc}
   */
  public function getClusterRoles(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getClusterRoles'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createClusterRole(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createClusterRole'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRole(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateClusterRole'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteClusterRole(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteClusterRole'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistentVolumes(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getPersistentVolumes'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createPersistentVolume(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createPersistentVolume'];
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolume(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updatePersistentVolume'];
  }

  /**
   * {@inheritdoc}
   */
  public function deletePersistentVolume(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deletePersistentVolume'];
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageClasses(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getStorageClasses'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createStorageClass(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createStorageClass'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateStorageClass(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateStorageClass'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteStorageClass(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteStorageClass'];
  }

  /**
   * {@inheritdoc}
   */
  public function getStatefulSets(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getStatefulSets'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createStatefulSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createStatefulSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateStatefulSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateStatefulSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteStatefulSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteStatefulSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function getIngresses(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getIngresses'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createIngress($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createIngress'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateIngress($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateIngress'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIngress($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteIngress'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDaemonSets(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getDaemonSets'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createDaemonSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createDaemonSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateDaemonSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateDaemonSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDaemonSet($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteDaemonSet'];
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoints(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getEndpoints'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createEndpoint($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createEndpoint'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateEndpoint($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateEndpoint'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteEndpoint($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteEndpoint'];
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getEvents'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistentVolumeClaims(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getPersistentVolumeClaims'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createPersistentVolumeClaim($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createPersistentVolumeClaim'];
  }

  /**
   * {@inheritdoc}
   */
  public function updatePersistentVolumeClaim($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updatePersistentVolumeClaim'];
  }

  /**
   * {@inheritdoc}
   */
  public function deletePersistentVolumeClaim($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deletePersistentVolumeClaim'];
  }

  /**
   * {@inheritdoc}
   */
  public function getClusterRoleBindings(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getClusterRoleBindings'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createClusterRoleBinding(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createClusterRoleBinding'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateClusterRoleBinding(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateClusterRoleBinding'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteClusterRoleBinding(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteClusterRoleBinding'];
  }

  /**
   * {@inheritdoc}
   */
  public function getApiServices(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getApiServices'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createApiService(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createApiService'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateApiService(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateApiService'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteApiService(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteApiService'];
  }

  /**
   * {@inheritdoc}
   */
  public function getRoleBindings(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getRoleBindings'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createRoleBinding($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createRoleBinding'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateRoleBinding($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateRoleBinding'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRoleBinding($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteRoleBinding'];
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceAccounts(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getServiceAccounts'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createServiceAccount($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createServiceAccount'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateServiceAccount($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updateServiceAccount'];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteServiceAccount($namespace, array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deleteServiceAccount'];
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeResourceUsage($cloud_context): array {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getNodeResourceUsage'];
  }

  /**
   * {@inheritdoc}
   */
  public function getNamespaceResourceUsage($cloud_context, $namespace): array {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getNamespaceResourceUsage'];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateCostPerNamespace($cloud_context, $namespace): array {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['calculateCostPerNamespace'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPriorityClasses(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['getPriorityClasses'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function createPriorityClass(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['createPriorityClass'];
  }

  /**
   * {@inheritdoc}
   */
  public function updatePriorityClass(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['updatePriorityClass'];
  }

  /**
   * {@inheritdoc}
   */
  public function deletePriorityClass(array $params = []) {
    return json_decode($this->config('k8s.settings')->get('k8s_mock_data'), TRUE)['deletePriorityClass'];
  }

}
