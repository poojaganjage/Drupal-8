<?php

namespace Drupal\k8s\Service\K8sClientExtension;

use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sApiServiceRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sClusterRoleBindingRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sClusterRoleRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sDaemonSetRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sLimitRangeRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sMetricsNodeRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sMetricsPodRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sPriorityClassRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sQuotaRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sReplicaSetRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sRoleBindingRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sRoleRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sServiceAccountRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sStatefulSetRepository;
use Drupal\k8s\Service\K8sClientExtension\Repositories\K8sStorageClassRepository;
use Maclof\Kubernetes\RepositoryRegistry;

/**
 * K8s repository registry.
 */
class K8sRepositoryRegistry extends RepositoryRegistry {

  /**
   * The constructor.
   */
  public function __construct() {
    parent::__construct();

    // Change the repository of quotas.
    $this->map['quotas'] = K8sQuotaRepository::class;

    // Change the repository of limit ranges.
    $this->map['limitRanges'] = K8sLimitRangeRepository::class;

    // Add the repository of metrics pods.
    $this->map['metricsPods'] = K8sMetricsPodRepository::class;

    // Add the repository of metrics nodes.
    $this->map['metricsNodes'] = K8sMetricsNodeRepository::class;

    // Add the repository of roles.
    $this->map['roles'] = K8sRoleRepository::class;

    // Add the repository of role bindings.
    $this->map['roleBindings'] = K8sRoleBindingRepository::class;

    // Add the repository of cluster roles.
    $this->map['clusterRoles'] = K8sClusterRoleRepository::class;

    // Add the repository of cluster role bindings.
    $this->map['clusterRoleBindings'] = K8sClusterRoleBindingRepository::class;

    // Add the repository of storage classes.
    $this->map['storageClasses'] = K8sStorageClassRepository::class;

    // Add the repository of stateful sets.
    $this->map['statefulSets'] = K8sStatefulSetRepository::class;

    // Add the repository of api services.
    $this->map['apiServices'] = K8sApiServiceRepository::class;

    // Add the repository of service accounts.
    $this->map['serviceAccounts'] = K8sServiceAccountRepository::class;

    // Add the repository of replica sets.
    $this->map['replicaSets'] = K8sReplicaSetRepository::class;

    // Add the repository of daemon sets.
    $this->map['daemonSets'] = K8sDaemonSetRepository::class;

    // Add the repository of priority classes.
    $this->map['priorityClasses'] = K8sPriorityClassRepository::class;
  }

}
