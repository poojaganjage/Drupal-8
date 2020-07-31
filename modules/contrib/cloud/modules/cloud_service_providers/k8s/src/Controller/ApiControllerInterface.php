<?php

namespace Drupal\k8s\Controller;

use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\k8s\Entity\K8sNode;
use Drupal\k8s\Entity\K8sPod;

/**
 * {@inheritdoc}
 */
interface ApiControllerInterface {

  /**
   * Update all resources.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateAllResources($cloud_context);

  /**
   * Update all nodes.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateNodeList($cloud_context);

  /**
   * Update all namespaces.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateNamespaceList($cloud_context);

  /**
   * Update all pods.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updatePodList($cloud_context);

  /**
   * Update all Network Policies.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateNetworkPolicyList($cloud_context);

  /**
   * Update all deployments.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateDeploymentList($cloud_context);

  /**
   * Update all replica sets.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateReplicaSetList($cloud_context);

  /**
   * Update all services.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateServiceList($cloud_context);

  /**
   * Update all cron jobs.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateCronJobList($cloud_context);

  /**
   * Update all jobs.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateJobList($cloud_context);

  /**
   * Update all resource quotas.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateResourceQuotaList($cloud_context);

  /**
   * Update all limit ranges.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateLimitRangeList($cloud_context);

  /**
   * Update all secrets.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateSecretList($cloud_context);

  /**
   * Update all ConfigMaps.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateConfigMapList($cloud_context);

  /**
   * Update all roles.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateRoleList($cloud_context);

  /**
   * Update all cluster roles.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateClusterRoleList($cloud_context);

  /**
   * Update all persistent volume.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updatePersistentVolumeList($cloud_context);

  /**
   * Update all storage classes.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateStorageClassList($cloud_context);

  /**
   * Update all stateful sets.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateStatefulSetsList($cloud_context);

  /**
   * Update all ingresses.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateIngressList($cloud_context);

  /**
   * Update all daemon sets.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateDaemonSetList($cloud_context);

  /**
   * Update all endpoints.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateEndpointList($cloud_context);

  /**
   * Update all events.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateEventList($cloud_context);

  /**
   * Update all persistent volume claim.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updatePersistentVolumeClaimList($cloud_context);

  /**
   * Update all cluster roles binding.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateClusterRoleBindingList($cloud_context);

  /**
   * Update all role binding.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateRoleBindingsList($cloud_context);

  /**
   * Update all service accounts.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateServiceAccountsList($cloud_context);

  /**
   * Get node metrics.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\k8s\Entity\K8sNode $node
   *   The K8s node entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getNodeMetrics($cloud_context, K8sNode $node);

  /**
   * Get pod metrics.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param \Drupal\k8s\Entity\K8sPod $pod
   *   The node name.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getPodMetrics($cloud_context, K8sPod $pod);

  /**
   * Get pods allocation data for a Pods allocation chart.
   *
   * @param string $cloud_context
   *   A cloud context string.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of a Node heatmap incl. Node name, Pods capacity and
   *   Allocations.
   */
  public function getNodeAllocatedResourcesList($cloud_context);

  /**
   * Get pods allocation data for a Pods allocation chart.
   *
   * @param string $cloud_context
   *   A cloud context string.
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The cloud project.
   * @param \Drupal\k8s\Entity\K8sNode $node
   *   The K8s node entity.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of a Node heatmap incl. Node name, Pods capacity and
   *   Allocations.
   */
  public function getNodeAllocatedResources($cloud_context, CloudProjectInterface $cloud_project = NULL, K8sNode $node);

  /**
   * Update all API service.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updateApiServiceList($cloud_context);

  /**
   * Update all priority classes.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function updatePriorityClassesList($cloud_context);

}
