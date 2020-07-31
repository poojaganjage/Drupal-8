<?php

namespace Drupal\k8s\Service;

/**
 * Interface K8sServiceInterface.
 */
interface K8sServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Get pods.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getPods(array $params = []);

  /**
   * Update the Pods.
   *
   * Delete old Pod entities, query the api for updated entities and store
   * them as Pod entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updatePods(array $params = [], $clear = TRUE);

  /**
   * Update the Pods without batch.
   *
   * Delete old Pod entities, query the api for updated entities and store
   * them as Pod entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updatePodsWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Create k8s pod.
   *
   * @param string $namespace
   *   The namespace of the pod.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createPod($namespace, array $params = []);

  /**
   * Update k8s pod.
   *
   * @param string $namespace
   *   The namespace of the pod.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updatePod($namespace, array $params = []);

  /**
   * Delete k8s pod.
   *
   * @param string $namespace
   *   The namespace of the pod.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deletePod($namespace, array $params = []);

  /**
   * Get k8s pod logs.
   *
   * @param string $namespace
   *   The namespace of the pod.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getPodLogs($namespace, array $params = []);

  /**
   * Get k8s nodes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getNodes(array $params = []);

  /**
   * Get k8s namespaces.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getNamespaces(array $params = []);

  /**
   * Create k8s namespace.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createNamespace(array $params = []);

  /**
   * Update k8s namespace.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateNamespace(array $params = []);

  /**
   * Delete k8s namespace.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteNamespace(array $params = []);

  /**
   * Get metrics pods.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getMetricsPods(array $params = []);

  /**
   * Get metrics nodes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getMetricsNodes(array $params = []);

  /**
   * Update the Nodes.
   *
   * Delete old Node entities, query the api for updated entities and store
   * them as Node entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateNodes(array $params = [], $clear = TRUE);

  /**
   * Update the Nodes without batch.
   *
   * Delete old Node entities, query the api for updated entities and store
   * them as Node entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateNodesWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Update the Namespaces.
   *
   * Delete old Namespace entities, query the api for updated entities and store
   * them as Namespace entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateNamespaces(array $params = [], $clear = TRUE);

  /**
   * Update the Namespaces without batch.
   *
   * Delete old Namespace entities, query the api for updated entities and store
   * them as Namespace entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateNamespacesWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Get deployments.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getDeployments(array $params = []);

  /**
   * Update the deployments.
   *
   * Delete old deployment entities, query the api for updated entities
   * and store them as deployment entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateDeployments(array $params = [], $clear = TRUE);

  /**
   * Create k8s deployment.
   *
   * @param string $namespace
   *   The namespace of the deployment.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createDeployment($namespace, array $params = []);

  /**
   * Update k8s deployment.
   *
   * @param string $namespace
   *   The namespace of the deployment.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateDeployment($namespace, array $params = []);

  /**
   * Delete k8s deployment.
   *
   * @param string $namespace
   *   The namespace of the deployment.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteDeployment($namespace, array $params = []);

  /**
   * Get replica sets.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getReplicaSets(array $params = []);

  /**
   * Create the replica sets.
   *
   * @param string $namespace
   *   The namespace of the replica set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createReplicaSet($namespace, array $params = []);

  /**
   * Update k8s replica set.
   *
   * @param string $namespace
   *   The namespace of the replica set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateReplicaSet($namespace, array $params = []);

  /**
   * Delete k8s replica set.
   *
   * @param string $namespace
   *   The namespace of the replica set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteReplicaSet($namespace, array $params = []);

  /**
   * Update the network policies.
   *
   * Delete old network policy entities, query the api for updated entities
   * and store them as network policy entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateNetworkPolicies(array $params = [], $clear = TRUE);

  /**
   * Get network policies.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getNetworkPolicies(array $params = []);

  /**
   * Create k8s network policy.
   *
   * @param string $namespace
   *   The namespace of the network policy.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createNetworkPolicy($namespace, array $params = []);

  /**
   * Update k8s network policy.
   *
   * @param string $namespace
   *   The namespace of the deployment.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateNetworkPolicy($namespace, array $params = []);

  /**
   * Delete k8s network policy.
   *
   * @param string $namespace
   *   The namespace of the deployment.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteNetworkPolicy($namespace, array $params = []);

  /**
   * Get persistent volumes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getPersistentVolumes(array $params = []);

  /**
   * Update the persistent volumes.
   *
   * Delete old persistent volume entities, query the api for updated entities
   * and store them as persistent volume entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updatePersistentVolumes(array $params = [], $clear = TRUE);

  /**
   * Update the persistent volumes without batch.
   *
   * Delete old persistent volume entities, query the api for updated entities
   * and store them as persistent volume entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateVolumeWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Create k8s persistent volume.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createPersistentVolume(array $params = []);

  /**
   * Update k8s persistent volume.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updatePersistentVolume(array $params = []);

  /**
   * Delete k8s persistent volume.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deletePersistentVolume(array $params = []);

  /**
   * Get cluster roles.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getClusterRoles(array $params = []);

  /**
   * Update the cluster roles.
   *
   * Delete old cluster role entities, query the api for updated entities
   * and store them as cluster role entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateClusterRoles(array $params = [], $clear = TRUE);

  /**
   * Create k8s cluster role.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createClusterRole(array $params = []);

  /**
   * Update k8s cluster role.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateClusterRole(array $params = []);

  /**
   * Delete k8s cluster role.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteClusterRole(array $params = []);

  /**
   * Get cluster roles binding.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getClusterRoleBindings(array $params = []);

  /**
   * Update the cluster roles binding.
   *
   * Delete old cluster role binding entities, query the API for updated
   *  entities and store them as cluster role binding entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateClusterRoleBindings(array $params = [], $clear = TRUE);

  /**
   * Create k8s cluster role binding.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createClusterRoleBinding(array $params = []);

  /**
   * Update k8s cluster role binding.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateClusterRoleBinding(array $params = []);

  /**
   * Delete k8s cluster role binding.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteClusterRoleBinding(array $params = []);

  /**
   * Get storage classes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getStorageClasses(array $params = []);

  /**
   * Update the storage classes.
   *
   * Delete old storage class entities, query the api for updated entities
   * and store them as storage class entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateStorageClasses(array $params = [], $clear = TRUE);

  /**
   * Create k8s storage class.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createStorageClass(array $params = []);

  /**
   * Update k8s storage class.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateStorageClass(array $params = []);

  /**
   * Delete k8s storage class.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteStorageClass(array $params = []);

  /**
   * Get stateful sets.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getStatefulSets(array $params = []);

  /**
   * Update the stateful sets.
   *
   * Delete old stateful set entities, query the api for updated entities
   * and store them as stateful set entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateStatefulSets(array $params = [], $clear = TRUE);

  /**
   * Create k8s stateful set.
   *
   * @param string $namespace
   *   The namespace of the stateful set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createStatefulSet($namespace, array $params = []);

  /**
   * Update k8s stateful set.
   *
   * @param string $namespace
   *   The namespace of the stateful set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateStatefulSet($namespace, array $params = []);

  /**
   * Delete k8s stateful sets.
   *
   * @param string $namespace
   *   The namespace of the stateful set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteStatefulSet($namespace, array $params = []);

  /**
   * Get ingresses.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getIngresses(array $params = []);

  /**
   * Update the ingresses.
   *
   * Delete old ingress entities, query the api for updated entities
   * and store them as ingress entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateIngresses(array $params = [], $clear = TRUE);

  /**
   * Create k8s ingresses.
   *
   * @param string $namespace
   *   The namespace of the ingress.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createIngress($namespace, array $params = []);

  /**
   * Update k8s ingress.
   *
   * @param string $namespace
   *   The namespace of the ingress.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateIngress($namespace, array $params = []);

  /**
   * Delete k8s ingresses.
   *
   * @param string $namespace
   *   The namespace of the ingress.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteIngress($namespace, array $params = []);

  /**
   * Get daemon sets.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getDaemonSets(array $params = []);

  /**
   * Update the daemon sets.
   *
   * Delete old daemon set entities, query the api for updated entities
   * and store them as daemon set entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateDaemonSets(array $params = [], $clear = TRUE);

  /**
   * Create k8s daemon set.
   *
   * @param string $namespace
   *   The namespace of the daemon set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createDaemonSet($namespace, array $params = []);

  /**
   * Update k8s daemon set.
   *
   * @param string $namespace
   *   The namespace of the daemon set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateDaemonSet($namespace, array $params = []);

  /**
   * Delete k8s daemon sets.
   *
   * @param string $namespace
   *   The namespace of the daemon set.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteDaemonSet($namespace, array $params = []);

  /**
   * Get endpoints.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getEndpoints(array $params = []);

  /**
   * Update the endpoints.
   *
   * Delete old endpoint entities, query the api for updated entities
   * and store them as endpoint entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateEndpoints(array $params = [], $clear = TRUE);

  /**
   * Create k8s endpoints.
   *
   * @param string $namespace
   *   The namespace of the endpoint.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createEndpoint($namespace, array $params = []);

  /**
   * Update k8s endpoint.
   *
   * @param string $namespace
   *   The namespace of the endpoint.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateEndpoint($namespace, array $params = []);

  /**
   * Delete k8s endpoints.
   *
   * @param string $namespace
   *   The namespace of the endpoint.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteEndpoint($namespace, array $params = []);

  /**
   * Get k8s events.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getEvents(array $params = []);

  /**
   * Update the Events.
   *
   * Delete old Event entities, query the api for updated entities and store
   * them as Event entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateEvents(array $params = [], $clear = TRUE);

  /**
   * Get persistent volume claims.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getPersistentVolumeClaims(array $params = []);

  /**
   * Update the persistent volume claims.
   *
   * Delete old persistent volume claim entities,
   * query the api for updated entities and store them as
   * persistent volume claim entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updatePersistentVolumeClaims(array $params = [], $clear = TRUE);

  /**
   * Create k8s persistent volume claim.
   *
   * @param string $namespace
   *   The namespace of the persistent volume claim.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createPersistentVolumeClaim($namespace, array $params = []);

  /**
   * Update k8s persistent volume claim.
   *
   * @param string $namespace
   *   The namespace of the persistent volume claim.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updatePersistentVolumeClaim($namespace, array $params = []);

  /**
   * Delete k8s persistent volume claims.
   *
   * @param string $namespace
   *   The namespace of the persistent volume claim.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deletePersistentVolumeClaim($namespace, array $params = []);

  /**
   * Get roles binding.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getRoleBindings(array $params = []);

  /**
   * Update the roles binding.
   *
   * Delete old role binding entities, query the api for updated entities
   * and store them as role binding entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateRoleBindings(array $params = [], $clear = TRUE);

  /**
   * Create k8s role binding.
   *
   * @param string $namespace
   *   The namespace of the api service.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createRoleBinding($namespace, array $params = []);

  /**
   * Update k8s role binding.
   *
   * @param string $namespace
   *   The namespace of the Role Binding.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateRoleBinding($namespace, array $params = []);

  /**
   * Delete k8s role binding.
   *
   * @param string $namespace
   *   The namespace of the Role Binding.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteRoleBinding($namespace, array $params = []);

  /**
   * Get API services.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getApiServices(array $params = []);

  /**
   * Update the API services.
   *
   * Delete old API service entities, query the API for updated entities
   * and store them as API service entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateApiServices(array $params = [], $clear = TRUE);

  /**
   * Create k8s API service.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createApiService(array $params = []);

  /**
   * Update k8s API service.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateApiService(array $params = []);

  /**
   * Delete k8s API service.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteApiService(array $params = []);

  /**
   * Get service accounts.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getServiceAccounts(array $params = []);

  /**
   * Update the service accounts.
   *
   * Delete old service account entities, query the api for updated entities
   * and store them as service account entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateServiceAccounts(array $params = [], $clear = TRUE);

  /**
   * Create k8s service accounts.
   *
   * @param string $namespace
   *   The namespace of the service account.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createServiceAccount($namespace, array $params = []);

  /**
   * Update k8s service account.
   *
   * @param string $namespace
   *   The namespace of the service account.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updateServiceAccount($namespace, array $params = []);

  /**
   * Delete k8s service accounts.
   *
   * @param string $namespace
   *   The namespace of the service account.
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deleteServiceAccount($namespace, array $params = []);

  /**
   * Get priority classes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function getPriorityClasses(array $params = []);

  /**
   * Create k8s priority classes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function createPriorityClass(array $params = []);

  /**
   * Update k8s priority class.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function updatePriorityClass(array $params = []);

  /**
   * Delete k8s priority classes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function deletePriorityClass(array $params = []);

  /**
   * Delete k8s resources and entities.
   *
   * @param array $entities
   *   The array entities to be deleted.
   */
  public function deleteResourcesWithEntities(array $entities);

  /**
   * Get link for the metrics server.
   *
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return string
   *   The link for the metrics server.
   */
  public function getMetricsServerLink($cloud_context = NULL);

  /**
   * Run the time scheduler. Change resource quota params based on the schedule.
   */
  public function runTimeScheduler();

  /**
   * Change the parameters for resource quota.
   *
   * @param string $label
   *   Name of namespace in kubernetes cluster.
   * @param array $config_entities
   *   The list of cloud context.
   * @param array $param
   *   Parameter set on resource quota.
   *
   * @return string
   *   Contain return value after calling k8s API.
   */
  public function changeResourceQuota($label, array $config_entities, array $param);

  /**
   * Determine whether the time is within specific time range or not.
   *
   * @param string $startup_time
   *   The start of specific time.
   * @param string $stop_time
   *   The end of specific time.
   * @param int $time
   *   The time to be validated with unix epoch time.
   *
   * @return bool
   *   TRUE means that input time is in specific time range
   *   defined by startup and stop time.
   */
  public function validateScheduledTime($startup_time, $stop_time, $time = NULL);

  /**
   * Create/update resource with entity.
   *
   * @param string $type
   *   Entity type.
   * @param string $cloud_context
   *   Cloud context.
   * @param string $label
   *   The label.
   * @param array $params
   *   Parameter for type.
   */
  public function updateResourceWithEntity($type, $cloud_context, $label, array $params);

  /**
   * Export node metrics to log.
   *
   * @param array $metrics_nodes
   *   The metrics of nodes.
   */
  public function exportNodeMetrics(array $metrics_nodes);

  /**
   * Export pod metrics to log.
   *
   * @param array $metrics_pods
   *   The metrics of nodes.
   */
  public function exportPodMetrics(array $metrics_pods);

  /**
   * Create queue items for update resources queue.
   */
  public function createResourceQueueItems();

  /**
   * Create queue items for update cost storage queue.
   */
  public function createCostStorageQueueItems();

  /**
   * Format costs.
   *
   * @param int $costs
   *   The costs.
   * @param int $total_costs
   *   The total costs.
   */
  public function formatCosts($costs, $total_costs);

  /**
   * Format cpu usage.
   *
   * @param float $cpu_usage
   *   The cpu usage.
   * @param float $cpu_capacity
   *   The cpu capacity.
   */
  public function formatCpuUsage($cpu_usage, $cpu_capacity);

  /**
   * Format memory usage.
   *
   * @param int $memory_usage
   *   The memory usage.
   * @param int $memory_capacity
   *   The memory capacity.
   */
  public function formatMemoryUsage($memory_usage, $memory_capacity);

  /**
   * Format pod usage.
   *
   * @param int $pod_usage
   *   The pod usage.
   * @param int $pod_capacity
   *   The pod capacity.
   */
  public function formatPodUsage($pod_usage, $pod_capacity);

  /**
   * Get total costs of nodes.
   *
   * @param array $nodes
   *   The k8s_node entities.
   */
  public function getTotalCosts(array $nodes);

  /**
   * Get amount of resource usage per a namespace.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param string $namespace
   *   The namespace of kubernetes.
   */
  public function getNamespaceResourceUsage($cloud_context, $namespace);

  /**
   * Get amount of resource usage per a namespace.
   *
   * @param string $cloud_context
   *   The cloud context.
   */
  public function getNodeResourceUsage($cloud_context);

  /**
   * Get amount of resource usage per a namespace.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param string $label
   *   The label.
   */
  public function calculateCostPerNamespace($cloud_context, $label);

}
