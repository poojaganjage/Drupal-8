<?php

namespace Drupal\Tests\k8s\Traits;

use Drupal\Component\Utility\Random;

/**
 * The trait creating mock data for k8s testing.
 */
trait K8sTestMockTrait {

  /**
   * Update getNodes in mock data.
   *
   * @param array $data
   *   The mock data.
   *
   * @throws \Exception
   */
  protected function updateNodesMockData(array $data): void {
    $mock_data = $this->getMockDataFromConfig();
    $random = new Random();

    $nodes = [];
    foreach ($data ?: [] as $node_data) {
      $nodes[] = [
        'metadata' => [
          'name' => $node_data['name'],
          'creationTimestamp' => date('Y/m/d H:i:s'),
          'labels' => [
            'label1' => 'label-' . $random->string(8, TRUE),
          ],
          'annotations' => [
            'annotation1' => 'annotation-' . $random->string(8, TRUE),
          ],
        ],
        'spec' => [
          'podCIDR' => $random->name(8, TRUE),
          'providerID' => $random->name(8, TRUE),
          'unschedulable' => FALSE,
        ],
        'status' => [
          'capacity' => [
            'cpu' => 2,
            'memory' => '2Gi',
            'pods' => 110,
          ],
          'conditions' => [
            [
              'type' => 'Ready',
            ],
          ],
          'addresses' => [
            [
              'type' => 'type-' . $random->name(8, TRUE),
              'address' => implode(
                '.', [
                  random_int(0, 254),
                  random_int(0, 255),
                  random_int(0, 255),
                  random_int(1, 255),
                ]
              ),
            ],
          ],
          'nodeInfo' => [
            'machineID' => 'machine-' . $random->name(16, TRUE),
            'systemUUID' => 'system-' . $random->name(16, TRUE),
            'bootID' => 'boot-' . $random->name(16, TRUE),
            'kernelVersion' => 'kernel-' . $random->name(8, TRUE),
            'osImage' => 'os-' . $random->name(8, TRUE),
            'containerRuntimeVersion' => 'version-' . $random->name(8, TRUE),
            'kubeletVersion' => 'kubelet-' . $random->name(8, TRUE),
            'kubeProxyVersion' => 'kubeProxy-' . $random->name(8, TRUE),
            'operatingSystem' => 'linux',
            'architecture' => 'arc-' . $random->name(8, TRUE),
          ],
        ],
      ];
    }
    $mock_data['getNodes'] = $nodes;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createNamespace in mock data.
   *
   * @param array $namespace
   *   The namespace mock data.
   */
  protected function addNamespaceMockData(array $namespace): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_namespace = [
      'metadata' => [
        'name' => $namespace['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];

    if (!empty($namespace['metadata']['annotations']['startup_time'])
    && !empty($namespace['metadata']['annotations']['stop_time'])) {
      $create_namespace['metadata']['annotations']['startup_time'] = $namespace['metadata']['annotations']['startup_time'];
      $create_namespace['metadata']['annotations']['stop_time'] = $namespace['metadata']['annotations']['stop_time'];
    }

    if (!empty($namespace['metadata']['annotations']['request_cpu'])) {
      $create_namespace['metadata']['annotations']['request_cpu'] = $namespace['metadata']['annotations']['request_cpu'];
    }
    if (!empty($namespace['metadata']['annotations']['request_memory'])) {
      $create_namespace['metadata']['annotations']['request_memory'] = $namespace['metadata']['annotations']['request_memory'];
    }
    if (!empty($namespace['metadata']['annotations']['pod_count'])) {
      $create_namespace['metadata']['annotations']['pod_count'] = $namespace['metadata']['annotations']['pod_count'];
    }

    $mock_data['createNamespace'] = $create_namespace;

    $get_namespaces = [];
    $get_namespaces[] = $create_namespace;
    $mock_data['getNamespaces'] = $get_namespaces;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update getNamespace in mock data.
   *
   * @param array $namespace
   *   The namespace mock data.
   */
  protected function getNamespaceMockData(array $namespace): void {
    $get_namespaces[] = [
      'metadata' => [
        'name' => $namespace['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateNamespace in mock data.
   *
   * @param array $namespace
   *   The namespace mock data.
   */
  protected function updateNamespaceMockData(array $namespace): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_namespace = [
      'metadata' => [
        'name' => $namespace['name'],
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];
    $mock_data['updateNamespace'] = $update_namespace;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteNamespace in mock data.
   *
   * @param array $namespace
   *   The namespace mock data.
   */
  protected function deleteNamespaceMockData(array $namespace): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_namespace = [
      'metadata' => [
        'name' => $namespace['name'],
      ],
    ];
    $mock_data['deleteNamespace'] = $delete_namespace;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deletePod in mock data.
   *
   * @param array $pod
   *   The pod mock data.
   */
  protected function deletePodMockData(array $pod): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_pod = [
      'metadata' => [
        'name' => $pod['name'],
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];
    $mock_data['deletePod'] = $delete_pod;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createPod in mock data.
   *
   * @param array $pod
   *   The pod mock data.
   */
  protected function addPodMockData(array $pod): void {
    $mock_data = $this->getMockDataFromConfig();

    $random = new Random();
    $create_pod = [
      'metadata' => [
        'name' => $pod['name'],
        'namespace' => $pod['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];
    $mock_data['createPod'] = $create_pod;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $pod['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_pods = [];
    $get_pods[] = $create_pod;
    $get_pods[0]['spec'] = [
      'containers' => [],
      'nodeName' => 'node-name' . $random->name(8, TRUE),
    ];
    $get_pods[0]['status'] = [
      'containerStatuses' => [],
      'phase' => 'Active',
      'qosClass' => 'BestEffort',
      'podIP' => '10.0.0.1',
    ];
    $mock_data['getPods'] = $get_pods;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updatePod in mock data.
   *
   * @param array $pod
   *   The pod mock data.
   */
  protected function updatePodMockData(array $pod): void {
    $mock_data = $this->getMockDataFromConfig();

    $random = new Random();
    $update_pod = [
      'metadata' => [
        'name' => $pod['name'],
        'namespace' => $pod['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];
    $mock_data['updatePod'] = $update_pod;

    $get_pods = [];
    $get_pods[] = $update_pod;
    $get_pods[0]['spec'] = [
      'containers' => [],
      'nodeName' => 'node-name' . $random->name(8, TRUE),
    ];
    $get_pods[0]['status'] = [
      'containerStatuses' => [],
      'phase' => 'Active',
      'qosClass' => 'BestEffort',
      'podIP' => '10.0.0.1',
    ];
    $mock_data['getPods'] = $get_pods;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update getPodLogs in mock data.
   *
   * @param string $logs
   *   The pod mock data.
   */
  protected function getPodLogsMockData($logs): void {
    $mock_data = $this->getMockDataFromConfig();

    $mock_data['getPodLogs'] = $logs;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update getMetricsPod in mock data.
   *
   * @param string $metrics
   *   The pod mock data.
   */
  protected function getMetricsPodMockData($metrics): void {
    $mock_data = $this->getMockDataFromConfig();
    $mock_data['getMetricsPods'] = $metrics;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createDeployment in mock data.
   *
   * @param array $deployment
   *   The pod mock data.
   */
  protected function addDeploymentMockData(array $deployment): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_deployment = [
      'metadata' => [
        'name' => $deployment['name'],
        'namespace' => $deployment['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];
    $mock_data['createDeployment'] = $create_deployment;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $deployment['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_deployments = [];
    $get_deployments[] = $create_deployment;
    $get_deployments[0]['spec'] = [
      'strategy' => ['type' => 'rollingUpdate'],
      'revisionHistoryLimit' => 10,
    ];
    $get_deployments[0]['status'] = [
      'availableReplicas' => 3,
      'collisionCount' => 1,
      'observedGeneration' => 5,
      'readyReplicas' => 2,
      'replicas' => 2,
      'unavailableReplicas' => 1,
      'updatedReplicas' => 2,
    ];
    $mock_data['getDeployments'] = $get_deployments;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateDeployment in mock data.
   *
   * @param array $deployment
   *   The deployment mock data.
   */
  protected function updateDeploymentMockData(array $deployment): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_deployment = [
      'metadata' => [
        'name' => $deployment['name'],
        'namespace' => $deployment['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['updateDeployment'] = $update_deployment;

    $get_deployments = [];
    $get_deployments[] = $update_deployment;
    $get_deployments[0]['spec'] = [
      'strategy' => ['type' => 'rollingUpdate'],
      'revisionHistoryLimit' => 10,
    ];
    $get_deployments[0]['status'] = [
      'availableReplicas' => 3,
      'collisionCount' => 1,
      'observedGeneration' => 5,
      'readyReplicas' => 2,
      'replicas' => 2,
      'unavailableReplicas' => 1,
      'updatedReplicas' => 2,
    ];
    $mock_data['getDeployments'] = $get_deployments;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteDeployment in mock data.
   *
   * @param array $deployment
   *   The deployment mock data.
   */
  protected function deleteDeploymentMockData(array $deployment): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_deployment = [
      'metadata' => [
        'name' => $deployment['name'],
      ],
    ];
    $mock_data['deleteDeployment'] = $delete_deployment;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createReplicaSet in mock data.
   *
   * @param array $replica_set
   *   The pod mock data.
   */
  protected function addReplicaSetMockData(array $replica_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_replica_set = [
      'metadata' => [
        'name' => $replica_set['name'],
        'namespace' => $replica_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];
    $mock_data['createReplicaSet'] = $create_replica_set;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $replica_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_replica_sets = [];
    $get_replica_sets[] = $create_replica_set;
    $get_replica_sets[0]['spec'] = [
      'replicas' => 10,
    ];
    $get_replica_sets[0]['status'] = [
      'availableReplicas' => 5,
      'observedGeneration' => 2,
      'readyReplicas' => 2,
      'fullyLabeledReplicas' => 1,
    ];
    $mock_data['getReplicaSets'] = $get_replica_sets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateReplicaSet in mock data.
   *
   * @param array $replica_set
   *   The replica set mock data.
   */
  protected function updateReplicaSetMockData(array $replica_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_replica_set = [
      'metadata' => [
        'name' => $replica_set['name'],
        'namespace' => $replica_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['updateReplicaSet'] = $update_replica_set;

    $get_replica_sets = [];
    $get_replica_sets[] = $update_replica_set;

    $get_replica_sets[0]['spec'] = [
      'replicas' => 15,
    ];
    $get_replica_sets[0]['status'] = [
      'availableReplicas' => 5,
      'observedGeneration' => 3,
      'readyReplicas' => 2,
      'fullyLabeledReplicas' => 2,
    ];
    $mock_data['getReplicaSets'] = $get_replica_sets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteReplicaSet in mock data.
   *
   * @param array $replica_set
   *   The replica set mock data.
   */
  protected function deleteReplicaSetMockData(array $replica_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_replica_set = [
      'metadata' => [
        'name' => $replica_set['name'],
      ],
    ];
    $mock_data['deleteReplicaSet'] = $delete_replica_set;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createService in mock data.
   *
   * @param array $service
   *   The pod mock data.
   */
  protected function addServiceMockData(array $service): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_service = [
      'metadata' => [
        'name' => $service['name'],
        'namespace' => $service['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'ports' => [],
        'selector' => [],
        'clusterIP' => '10.10.0.1',
        'type' => 'ClusterIP',
        'sessionAffinity' => 'None',
      ],
    ];
    $mock_data['createService'] = $create_service;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $service['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_services = [];
    $get_services[] = $create_service;
    $mock_data['getServices'] = $get_services;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateService in mock data.
   *
   * @param array $service
   *   The service mock data.
   */
  protected function updateServiceMockData(array $service): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_service = [
      'metadata' => [
        'name' => $service['name'],
        'namespace' => $service['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'ports' => [],
        'selector' => [],
        'clusterIP' => '10.10.0.1',
        'type' => 'ClusterIP',
        'sessionAffinity' => 'None',
      ],
    ];
    $mock_data['updateService'] = $update_service;

    $get_services = [];
    $get_services[] = $update_service;
    $mock_data['getServices'] = $get_services;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteService in mock data.
   *
   * @param array $service
   *   The service mock data.
   */
  protected function deleteServiceMockData(array $service): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_service = [
      'metadata' => [
        'name' => $service['name'],
      ],
    ];
    $mock_data['deleteService'] = $delete_service;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createCronJob in mock data.
   *
   * @param array $cron_job
   *   The pod mock data.
   */
  protected function addCronJobMockData(array $cron_job): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_cron_job = [
      'metadata' => [
        'name' => $cron_job['name'],
        'namespace' => $cron_job['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'schedule' => '* * */1 * *',
        'suspend' => FALSE,
        'concurrencyPolicy' => 'Allow',
        'startingDeadlineSeconds' => 10,
      ],
      'status' => [
        'lastScheduleTime' => date('Y/m/d H:i:s'),
        'active' => [],
      ],
    ];
    $mock_data['createCronJob'] = $create_cron_job;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $cron_job['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_cron_jobs = [];
    $get_cron_jobs[] = $create_cron_job;
    $mock_data['getCronJobs'] = $get_cron_jobs;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateCronJob in mock data.
   *
   * @param array $cron_job
   *   The cron job mock data.
   */
  protected function updateCronJobMockData(array $cron_job): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_cron_job = [
      'metadata' => [
        'name' => $cron_job['name'],
        'namespace' => $cron_job['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'schedule' => '* * */1 * *',
        'suspend' => FALSE,
        'concurrencyPolicy' => 'Allow',
        'startingDeadlineSeconds' => 10,
      ],
      'status' => [
        'lastScheduleTime' => date('Y/m/d H:i:s'),
        'active' => [],
      ],
    ];
    $mock_data['updateCronJob'] = $update_cron_job;

    $get_cron_jobs = [];
    $get_cron_jobs[] = $update_cron_job;
    $mock_data['getCronJobs'] = $get_cron_jobs;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteCronJob in mock data.
   *
   * @param array $cron_job
   *   The cron job mock data.
   */
  protected function deleteCronJobMockData(array $cron_job): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_cron_job = [
      'metadata' => [
        'name' => $cron_job['name'],
      ],
    ];
    $mock_data['deleteCronJob'] = $delete_cron_job;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createJob in mock data.
   *
   * @param array $job
   *   The pod mock data.
   */
  protected function addJobMockData(array $job): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_job = [
      'metadata' => [
        'name' => $job['name'],
        'namespace' => $job['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'completions' => 1,
        'parallelism' => 1,
        'template' => [
          'spec' => [
            'containers' => [
              [
                'image' => 'perl',
              ],
            ],
          ],
        ],
      ],
      'status' => [
        'active' => 0,
        'succeeded' => 1,
        'failed' => 0,
      ],
    ];
    $mock_data['createJob'] = $create_job;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $job['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_jobs = [];
    $get_jobs[] = $create_job;
    $mock_data['getJobs'] = $get_jobs;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateJob in mock data.
   *
   * @param array $job
   *   The job mock data.
   */
  protected function updateJobMockData(array $job): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_job = [
      'metadata' => [
        'name' => $job['name'],
        'namespace' => $job['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'completions' => 1,
        'parallelism' => 1,
        'template' => [
          'spec' => [
            'containers' => [
              [
                'image' => 'perl',
              ],
            ],
          ],
        ],
      ],
      'status' => [
        'active' => 0,
        'succeeded' => 1,
        'failed' => 0,
      ],
    ];
    $mock_data['updateJob'] = $update_job;

    $get_jobs = [];
    $get_jobs[] = $update_job;
    $mock_data['getJobs'] = $get_jobs;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteJob in mock data.
   *
   * @param array $job
   *   The job mock data.
   */
  protected function deleteJobMockData(array $job): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_job = [
      'metadata' => [
        'name' => $job['name'],
      ],
    ];
    $mock_data['deleteJob'] = $delete_job;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createResourceQuota in mock data.
   *
   * @param array $resource_quota
   *   The pod mock data.
   */
  protected function addResourceQuotaMockData(array $resource_quota): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_resource_quota = empty($resource_quota)
      ? []
      : [
        'metadata' => [
          'name' => $resource_quota['name'],
          'namespace' => $resource_quota['post_data']['namespace'],
          'creationTimestamp' => date('Y/m/d H:i:s'),
        ],
        'status' => [
          'hard' => [
            'cpu' => $resource_quota['spec']['hard']['cpu'],
            'memory' => $resource_quota['spec']['hard']['memory'],
            'pods' => $resource_quota['spec']['hard']['pods'],
          ],
          'used' => [
            'cpu' => '0m',
            'memory' => '0Mi',
            'pods' => '0',
          ],
        ],
      ];
    $mock_data['createResourceQuota'] = $create_resource_quota;
    $mock_data['getResourceQuotas'][] = $create_resource_quota;

    $get_namespace = empty($resource_quota)
      ? []
      : [
        'metadata' => [
          'name' => $resource_quota['post_data']['namespace'],
          'creationTimestamp' => date('Y/m/d H:i:s'),
        ],
        'status' => [
          'phase' => 'Active',
        ],
      ];
    $mock_data['getNamespaces'][] = $get_namespace;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateResourceQuota in mock data.
   *
   * @param array $resource_quota
   *   The resource quota mock data.
   */
  protected function updateResourceQuotaMockData(array $resource_quota): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_resource_quota = [
      'metadata' => [
        'name' => $resource_quota['name'],
        'namespace' => $resource_quota['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'completions' => 1,
        'parallelism' => 1,
        'template' => [
          'spec' => [
            'containers' => [
              [
                'image' => 'perl',
              ],
            ],
          ],
        ],
      ],
      'status' => [
        'active' => 0,
        'succeeded' => 1,
        'failed' => 0,
        'hard' => [
          'cpu' => $resource_quota['spec']['hard']['cpu'],
          'memory' => $resource_quota['spec']['hard']['memory'],
          'pods' => $resource_quota['spec']['hard']['pods'],
        ],
        'used' => [
          'cpu' => '0m',
          'memory' => '0Mi',
          'pods' => '0',
        ],
      ],
    ];
    $mock_data['updateResourceQuota'] = $update_resource_quota;

    $get_resource_quotas = [];
    $get_resource_quotas[] = $update_resource_quota;
    $mock_data['getResourceQuotas'] = $get_resource_quotas;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteResourceQuota in mock data.
   *
   * @param array $resource_quota
   *   The resource quota mock data.
   */
  protected function deleteResourceQuotaMockData(array $resource_quota): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_resource_quota = [
      'metadata' => [
        'name' => $resource_quota['name'],
      ],
    ];
    $mock_data['deleteResourceQuota'] = $delete_resource_quota;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createLimitRange in mock data.
   *
   * @param array $limit_range
   *   The limit range mock data.
   */
  protected function addLimitRangeMockData(array $limit_range): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_limit_range = [
      'metadata' => [
        'name' => $limit_range['name'],
        'namespace' => $limit_range['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'limits' => [
          [
            'type' => 'Pod',
            'maxLimitRequestRatio' => [
              'memory' => '3',
            ],
          ],
        ],
      ],
    ];
    $mock_data['createLimitRange'] = $create_limit_range;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $limit_range['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_limit_ranges = [];
    $get_limit_ranges[] = $create_limit_range;
    $mock_data['getLimitRanges'] = $get_limit_ranges;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateLimitRange in mock data.
   *
   * @param array $limit_range
   *   The limit range mock data.
   */
  protected function updateLimitRangeMockData(array $limit_range): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_limit_range = [
      'metadata' => [
        'name' => $limit_range['name'],
        'namespace' => $limit_range['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'limits' => [
          [
            'type' => 'Pod',
            'maxLimitRequestRatio' => [
              'memory' => '3',
            ],
          ],
        ],
      ],
    ];
    $mock_data['updateLimitRange'] = $update_limit_range;

    $get_limit_ranges = [];
    $get_limit_ranges[] = $update_limit_range;
    $mock_data['getLimitRanges'] = $get_limit_ranges;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteLimitRange in mock data.
   *
   * @param array $limit_range
   *   The limit range mock data.
   */
  protected function deleteLimitRangeMockData(array $limit_range): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_limit_range = [
      'metadata' => [
        'name' => $limit_range['name'],
      ],
    ];
    $mock_data['deleteLimitRange'] = $delete_limit_range;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createSecret in mock data.
   *
   * @param array $secret
   *   The secret mock data.
   */
  protected function addSecretMockData(array $secret): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_secret = [
      'metadata' => [
        'name' => $secret['name'],
        'namespace' => $secret['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'data' => [
        'username' => 'YWRtaW4=',
        'password' => 'MWYyZDFlMmU2N2Rm',
      ],
      'type' => 'Opaque',
    ];
    $mock_data['createSecret'] = $create_secret;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $secret['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_secrets = [];
    $get_secrets[] = $create_secret;
    $mock_data['getSecrets'] = $get_secrets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateSecret in mock data.
   *
   * @param array $secret
   *   The secret mock data.
   */
  protected function updateSecretMockData(array $secret): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_secret = [
      'metadata' => [
        'name' => $secret['name'],
        'namespace' => $secret['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'data' => [
        'username' => 'YWRtaW4=',
        'password' => 'MWYyZDFlMmU2N2Rm',
      ],
      'type' => 'Opaque',
    ];
    $mock_data['updateSecret'] = $update_secret;

    $get_secrets = [];
    $get_secrets[] = $update_secret;
    $mock_data['getSecrets'] = $get_secrets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteSecret in mock data.
   *
   * @param array $secret
   *   The secret mock data.
   */
  protected function deleteSecretMockData(array $secret): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_secret = [
      'metadata' => [
        'name' => $secret['name'],
      ],
    ];
    $mock_data['deleteSecret'] = $delete_secret;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createConfigMap in mock data.
   *
   * @param array $config_map
   *   The ConfigMap mock data.
   */
  protected function addConfigMapMockData(array $config_map): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_config_map = [
      'metadata' => [
        'name' => $config_map['name'],
        'namespace' => $config_map['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'data' => [
        'property1' => 'hello',
        'property2' => 'world',
      ],
    ];
    $mock_data['createConfigMap'] = $create_config_map;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $config_map['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_config_maps = [];
    $get_config_maps[] = $create_config_map;
    $mock_data['getConfigMaps'] = $get_config_maps;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateConfigMap in mock data.
   *
   * @param array $config_map
   *   The ConfigMap mock data.
   */
  protected function updateConfigMapMockData(array $config_map): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_config_map = [
      'metadata' => [
        'name' => $config_map['name'],
        'namespace' => $config_map['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'data' => [
        'property1' => 'hello',
        'property2' => 'world',
      ],
      'type' => 'Opaque',
    ];
    $mock_data['updateConfigMap'] = $update_config_map;

    $get_config_maps = [];
    $get_config_maps[] = $update_config_map;
    $mock_data['getConfigMaps'] = $get_config_maps;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteConfigMap in mock data.
   *
   * @param array $config_map
   *   The ConfigMap mock data.
   */
  protected function deleteConfigMapMockData(array $config_map): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_config_map = [
      'metadata' => [
        'name' => $config_map['name'],
      ],
    ];
    $mock_data['deleteConfigMap'] = $delete_config_map;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createNetworkPolicy in mock data.
   *
   * @param array $network_policy
   *   The network policy mock data.
   */
  protected function addNetworkPolicyMockData(array $network_policy): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_network_policy = [
      'metadata' => [
        'name' => $network_policy['name'],
        'namespace' => $network_policy['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'egress' => NULL,
        'ingress' => NULL,
        'pod_selector' => NULL,
        'policy_types' => NULL,
      ],
    ];
    $mock_data['createNetworkPolicy'] = $create_network_policy;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $network_policy['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_network_policies = [];
    $get_network_policies[] = $create_network_policy;
    $mock_data['getNetworkPolicies'] = $get_network_policies;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateNetworkPolicy in mock data.
   *
   * @param array $network_policy
   *   The network policy mock data.
   */
  protected function updateNetworkPolicyMockData(array $network_policy): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_network_policy = [
      'metadata' => [
        'name' => $network_policy['name'],
        'namespace' => $network_policy['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'egress' => NULL,
        'ingress' => NULL,
        'pod_selector' => NULL,
        'policy_types' => NULL,
      ],
    ];
    $mock_data['updateNetworkPolicy'] = $update_network_policy;

    $get_network_policies = [];
    $get_network_policies[] = $update_network_policy;
    $mock_data['getNetworkPolicies'] = $get_network_policies;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteNetworkPolicy in mock data.
   *
   * @param array $network_policy
   *   The network policy mock data.
   */
  protected function deleteNetworkPolicyMockData(array $network_policy): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_network_policy = [
      'metadata' => [
        'name' => $network_policy['name'],
      ],
    ];
    $mock_data['deleteNetworkPolicy'] = $delete_network_policy;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createRole in mock data.
   *
   * @param array $role
   *   The role mock data.
   */
  protected function addRoleMockData(array $role): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_role = [
      'metadata' => [
        'name' => $role['name'],
        'namespace' => $role['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'rules' => [
        [
          'verbs' => ['get', 'list'],
          'apiGroups' => ['apps', 'extensions'],
          'resources' => ['deployments'],
        ],
      ],
    ];
    $mock_data['createRole'] = $create_role;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $role['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_roles = [];
    $get_roles[] = $create_role;
    $mock_data['getRoles'] = $get_roles;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateRole in mock data.
   *
   * @param array $role
   *   The role mock data.
   */
  protected function updateRoleMockData(array $role): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_role = [
      'metadata' => [
        'name' => $role['name'],
        'namespace' => $role['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'rules' => [
        [
          'verbs' => ['get', 'list'],
          'apiGroups' => ['apps', 'extensions'],
          'resources' => ['deployments'],
        ],
      ],
    ];
    $mock_data['updateRole'] = $update_role;

    $get_roles = [];
    $get_roles[] = $update_role;
    $mock_data['getRoles'] = $get_roles;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deletRole in mock data.
   *
   * @param array $role
   *   The role mock data.
   */
  protected function deleteRoleMockData(array $role): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_role = [
      'metadata' => [
        'name' => $role['name'],
      ],
    ];
    $mock_data['deleteRole'] = $delete_role;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createClusterRole in mock data.
   *
   * @param array $cluster_role
   *   The cluster role mock data.
   */
  protected function addClusterRoleMockData(array $cluster_role): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_cluster_role = [
      'metadata' => [
        'name' => $cluster_role['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'rules' => [
        [
          'verbs' => ['get', 'list'],
          'apiGroups' => ['apps', 'extensions'],
          'resources' => ['deployments'],
        ],
      ],
    ];
    $mock_data['createClusterRole'] = $create_cluster_role;

    $get_cluster_roles = [];
    $get_cluster_roles[] = $create_cluster_role;
    $mock_data['getClusterRoles'] = $get_cluster_roles;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateClusterRole in mock data.
   *
   * @param array $cluster_role
   *   The cluster role mock data.
   */
  protected function updateClusterRoleMockData(array $cluster_role): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_cluster_role = [
      'metadata' => [
        'name' => $cluster_role['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'rules' => [
        [
          'verbs' => ['get', 'list'],
          'apiGroups' => ['apps', 'extensions'],
          'resources' => ['deployments'],
        ],
      ],
    ];
    $mock_data['updateClusterRole'] = $update_cluster_role;

    $get_cluster_roles = [];
    $get_cluster_roles[] = $update_cluster_role;
    $mock_data['getClusterRoles'] = $get_cluster_roles;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteClusterRole in mock data.
   *
   * @param array $cluster_role
   *   The cluster role mock data.
   */
  protected function deleteClusterRoleMockData(array $cluster_role): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_cluster_role = [
      'metadata' => [
        'name' => $cluster_role['name'],
      ],
    ];
    $mock_data['deleteClusterRole'] = $delete_cluster_role;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createPersistentVolume in mock data.
   *
   * @param array $persistent_volume
   *   The persistent volume mock data.
   */
  protected function addPersistentVolumeMockData(array $persistent_volume): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_persistent_volume = [
      'metadata' => [
        'name' => $persistent_volume['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'capacity' => [
          'storage' => '10Gi',
        ],
        'accessModes' => [
          'ReadWriteOnce',
        ],
        'persistentVolumeReclaimPolicy' => 'Recycle',
        'storageClassName' => 'slow',
      ],
    ];
    $mock_data['createPersistentVolume'] = $create_persistent_volume;

    $get_persistent_volume = [];
    $get_persistent_volume[] = $create_persistent_volume;
    $mock_data['getPersistentVolumes'] = $get_persistent_volume;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updatePersistentVolume in mock data.
   *
   * @param array $persistent_volume
   *   The persistent volume mock data.
   */
  protected function updatePersistentVolumeMockData(array $persistent_volume): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_persistent_volume = [
      'metadata' => [
        'name' => $persistent_volume['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'capacity' => '10Gi',
        'access_modes' => 'ReadWriteOnce',
        'reclaim_policy' => 'Recycle',
        'storage_class_name' => 'slow',
      ],
    ];
    $mock_data['updatePersistentVolume'] = $update_persistent_volume;

    $get_persistent_volume = [];
    $get_persistent_volume[] = $update_persistent_volume;
    $mock_data['getPersistentVolume'] = $get_persistent_volume;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deletePersistentVolume in mock data.
   *
   * @param array $persistent_volume
   *   The persistent volume mock data.
   */
  protected function deletePersistentVolumeMockData(array $persistent_volume): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_persistent_volume = [
      'metadata' => [
        'name' => $persistent_volume['name'],
      ],
    ];
    $mock_data['deletePersistentVolume'] = $delete_persistent_volume;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createStorageClass in mock data.
   *
   * @param array $storage_class
   *   The storage class mock data.
   */
  protected function addStorageClassMockData(array $storage_class): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_storage_class = [
      'metadata' => [
        'name' => $storage_class['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['createStorageClass'] = $create_storage_class;

    $get_storage_classes = [];
    $get_storage_classes[] = $create_storage_class;
    $mock_data['getStorageClasses'] = $get_storage_classes;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateStorageClass in mock data.
   *
   * @param array $storage_class
   *   The storage class mock data.
   */
  protected function updateStorageClassMockData(array $storage_class): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_storage_class = [
      'metadata' => [
        'name' => $storage_class['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['updateStorageClass'] = $update_storage_class;

    $get_storage_classes = [];
    $get_storage_classes[] = $update_storage_class;
    $mock_data['getStorageClasses'] = $get_storage_classes;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteStorageClass in mock data.
   *
   * @param array $storage_class
   *   The storage class mock data.
   */
  protected function deleteStorageClassMockData(array $storage_class): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_storage_class = [
      'metadata' => [
        'name' => $storage_class['name'],
      ],
    ];
    $mock_data['deleteStorageClass'] = $delete_storage_class;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createStatefulSet in mock data.
   *
   * @param array $stateful_set
   *   The stateful set mock data.
   */
  protected function addStatefulSetMockData(array $stateful_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_stateful_set = [
      'metadata' => [
        'name' => $stateful_set['name'],
        'namespace' => $stateful_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'status' => [
        'phase' => 'Active',
      ],
    ];
    $mock_data['createStatefulSet'] = $create_stateful_set;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $stateful_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_stateful_sets = [];
    $get_stateful_sets[] = $create_stateful_set;
    $get_stateful_sets[0]['spec'] = [
      'updateStrategy' => ['type' => 'rollingUpdate'],
      'revisionHistoryLimit' => 10,
    ];
    $get_stateful_sets[0]['status'] = [
      'observedGeneration' => 1,
      'replicas' => 2,
      'readyReplicas' => 2,
      'currentReplicas' => 2,
      'updatedReplicas' => 2,
      'currentRevision' => 'web-b46f789c4',
      'updateRevision' => 'web-b46f789c4',
      'collisionCount' => 1,
    ];
    $mock_data['getStatefulSets'] = $get_stateful_sets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateStatefulSet in mock data.
   *
   * @param array $stateful_set
   *   The stateful set mock data.
   */
  protected function updateStatefulSetMockData(array $stateful_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_stateful_set = [
      'metadata' => [
        'name' => $stateful_set['name'],
        'namespace' => $stateful_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['updateStatefulSet'] = $update_stateful_set;

    $get_stateful_sets = [];
    $get_stateful_sets[] = $update_stateful_set;
    $get_stateful_sets[0]['spec'] = [
      'updateStrategy' => ['type' => 'rollingUpdate'],
      'revisionHistoryLimit' => 10,
    ];
    $get_stateful_sets[0]['status'] = [
      'observedGeneration' => 1,
      'replicas' => 2,
      'readyReplicas' => 2,
      'currentReplicas' => 2,
      'updatedReplicas' => 2,
      'currentRevision' => 'web-b46f789c4',
      'updateRevision' => 'web-b46f789c4',
      'collisionCount' => 1,
    ];
    $mock_data['getStatefulSets'] = $get_stateful_sets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteStatefulSet in mock data.
   *
   * @param array $stateful_set
   *   The stateful set mock data.
   */
  protected function deleteStatefulSetMockData(array $stateful_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_stateful_set = [
      'metadata' => [
        'name' => $stateful_set['name'],
      ],
    ];
    $mock_data['deleteStatefulSet'] = $delete_stateful_set;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createIngress in mock data.
   *
   * @param array $ingress
   *   The ingress mock data.
   */
  protected function addIngressMockData(array $ingress): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_ingress = [
      'metadata' => [
        'name' => $ingress['name'],
        'namespace' => $ingress['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'rules' => [
          [
            'host' => 'sslexample.foo.com',
          ],
        ],
      ],
    ];
    $mock_data['createIngress'] = $create_ingress;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $ingress['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_ingresses = [];
    $get_ingresses[] = $create_ingress;
    $mock_data['getIngresses'] = $get_ingresses;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateIngress in mock data.
   *
   * @param array $ingress
   *   The ingress mock data.
   */
  protected function updateIngressMockData(array $ingress): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_ingress = [
      'metadata' => [
        'name' => $ingress['name'],
        'namespace' => $ingress['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'tls' => [
          [
            'hosts' => [
              'sslexample.foo.com',
            ],
            'secretName' => 'testsecret-tls',
          ],
        ],
        'rules' => [
          [
            'host' => 'sslexample.foo.com',
          ],
        ],
      ],
    ];
    $mock_data['updateIngress'] = $update_ingress;

    $get_ingresses = [];
    $get_ingresses[] = $update_ingress;
    $mock_data['getIngresses'] = $get_ingresses;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteIngress in mock data.
   *
   * @param array $ingress
   *   The ingress mock data.
   */
  protected function deleteIngressMockData(array $ingress): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_ingress = [
      'metadata' => [
        'name' => $ingress['name'],
      ],
    ];
    $mock_data['deleteIngress'] = $delete_ingress;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createDaemonSet in mock data.
   *
   * @param array $daemon_set
   *   The daemon set mock data.
   */
  protected function addDaemonSetMockData(array $daemon_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_daemon_set = [
      'metadata' => [
        'name' => $daemon_set['name'],
        'namespace' => $daemon_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'template' => [
          'spec' => [
            'containers' => [
              [
                'image' => 'perl',
              ],
            ],
          ],
        ],
      ],
    ];
    $mock_data['createDaemonSet'] = $create_daemon_set;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $daemon_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_daemon_sets = [];
    $get_daemon_sets[] = $create_daemon_set;
    $mock_data['getDaemonSets'] = $get_daemon_sets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateDaemonSet in mock data.
   *
   * @param array $daemon_set
   *   The daemon set mock data.
   */
  protected function updateDaemonSetMockData(array $daemon_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_daemon_set = [
      'metadata' => [
        'name' => $daemon_set['name'],
        'namespace' => $daemon_set['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'template' => [
          'spec' => [
            'containers' => [
              [
                'image' => 'perl',
              ],
            ],
          ],
        ],
      ],
    ];
    $mock_data['updateDaemonSet'] = $update_daemon_set;

    $get_daemon_sets = [];
    $get_daemon_sets[] = $update_daemon_set;
    $mock_data['getDaemonSets'] = $get_daemon_sets;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteDaemonSet in mock data.
   *
   * @param array $daemon_set
   *   The daemon set mock data.
   */
  protected function deleteDaemonSetMockData(array $daemon_set): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_daemon_set = [
      'metadata' => [
        'name' => $daemon_set['name'],
      ],
    ];
    $mock_data['deleteDaemonSet'] = $delete_daemon_set;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createEndpoint in mock data.
   *
   * @param array $endpoint
   *   The endpoint mock data.
   */
  protected function addEndpointMockData(array $endpoint): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_endpoint = [
      'metadata' => [
        'name' => $endpoint['name'],
        'namespace' => $endpoint['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'subsets' => [
        [
          'ports' => [
            [
              'name' => 'web',
              'port' => '80',
              'protocol' => 'TCP',
            ],
          ],
          'addresses' => [
            [
              'ip' => '192.168.114.234',
            ],
          ],
        ],
      ],
    ];
    $mock_data['createEndpoint'] = $create_endpoint;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $endpoint['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_endpoints = [];
    $get_endpoints[] = $create_endpoint;
    $mock_data['getEndpoints'] = $get_endpoints;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateEndpoint in mock data.
   *
   * @param array $endpoint
   *   The endpoint mock data.
   */
  protected function updateEndpointMockData(array $endpoint): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_endpoint = [
      'metadata' => [
        'name' => $endpoint['name'],
        'namespace' => $endpoint['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'subsets' => [
        [
          'ports' => [
            [
              'name' => 'web',
              'port' => '80',
              'protocol' => 'TCP',
            ],
          ],
          'addresses' => [
            [
              'ip' => '192.168.114.234',
              'hostname' => 'web',
              'nodeName' => 'ip-192-168-77-138.us-west-2.compute.internal',
            ],
          ],
        ],
      ],
    ];
    $mock_data['updateEndpoint'] = $update_endpoint;

    $get_endpoints = [];
    $get_endpoints[] = $update_endpoint;
    $mock_data['getEndpoints'] = $get_endpoints;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteEndpoint in mock data.
   *
   * @param array $endpoint
   *   The endpoint mock data.
   */
  protected function deleteEndpointMockData(array $endpoint): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_endpoint = [
      'metadata' => [
        'name' => $endpoint['name'],
      ],
    ];
    $mock_data['deleteEndpoint'] = $delete_endpoint;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update getEvents in mock data.
   *
   * @param array $data
   *   The mock data.
   */
  protected function updateEventsMockData(array $data): void {
    $mock_data = $this->getMockDataFromConfig();
    $random = new Random();

    $events = [];
    foreach ($data ?: [] as $event_data) {
      $events[] = [
        'kind' => 'Event',
        'type' => 'Warning',
        'reason' => 'BackOff',
        'involvedObject' => [
          'kind' => 'Pod',
          'name' => $event_data['name'],
        ],
        'message' => 'Back-off restarting failed container',
        'lastTimestamp' => date('Y/m/d H:i:s'),
        'metadata' => [
          'name' => $event_data['name'] . $random->string(8, TRUE),
          'namespace' => 'default',
        ],
      ];
    }
    $mock_data['getEvents'] = $events;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createPersistentVolumeClaim in mock data.
   *
   * @param array $persistent_volume_claim
   *   The persistent volume claim mock data.
   */
  protected function addPersistentVolumeClaimMockData(array $persistent_volume_claim): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_persistent_volume_claim = [
      'metadata' => [
        'name' => $persistent_volume_claim['name'],
        'namespace' => $persistent_volume_claim['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'storageClassName' => 'manual',
        'resources' => [
          'requests' => [
            [
              'storage' => '1Gi',
            ],
          ],
        ],
      ],
    ];
    $mock_data['createPersistentVolumeClaim'] = $create_persistent_volume_claim;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $persistent_volume_claim['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_persistent_volume_claims = [];
    $get_persistent_volume_claims[] = $create_persistent_volume_claim;
    $mock_data['getPersistentVolumeClaims'] = $get_persistent_volume_claims;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updatePersistentVolumeClaim in mock data.
   *
   * @param array $persistent_volume_claim
   *   The persistent volume claim mock data.
   */
  protected function updatePersistentVolumeClaimMockData(array $persistent_volume_claim): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_persistent_volume_claim = [
      'metadata' => [
        'name' => $persistent_volume_claim['name'],
        'namespace' => $persistent_volume_claim['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'storageClassName' => 'manual',
        'resources' => [
          'requests' => [
            [
              'storage' => '1Gi',
            ],
          ],
        ],
      ],
    ];
    $mock_data['updatePersistentVolumeClaim'] = $update_persistent_volume_claim;

    $get_persistent_volume_claims = [];
    $get_persistent_volume_claims[] = $update_persistent_volume_claim;
    $mock_data['getPersistentVolumeClaims'] = $get_persistent_volume_claims;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deletePersistentVolumeClaim in mock data.
   *
   * @param array $persistent_volume_claim
   *   The persistent volume claim mock data.
   */
  protected function deletePersistentVolumeClaimMockData(array $persistent_volume_claim): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_persistent_volume_claim = [
      'metadata' => [
        'name' => $persistent_volume_claim['name'],
      ],
    ];
    $mock_data['deletePersistentVolumeClaim'] = $delete_persistent_volume_claim;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createClusterRoleBinding in mock data.
   *
   * @param array $cluster_role_binding
   *   The cluster role binding mock data.
   */
  protected function addClusterRoleBindingMockData(array $cluster_role_binding): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_cluster_role_binding = [
      'metadata' => [
        'name' => $cluster_role_binding['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'subjects' => [
        'name' => 'tiller',
        'namespace' => 'gitlab-managed-apps',
      ],
    ];
    $mock_data['createClusterRoleBinding'] = $create_cluster_role_binding;

    $get_cluster_roles_binding = [];
    $get_cluster_roles_binding[] = $create_cluster_role_binding;
    $mock_data['getClusterRolesBinding'] = $get_cluster_roles_binding;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateClusterRoleBinding in mock data.
   *
   * @param array $cluster_role_binding
   *   The cluster role mock data.
   */
  protected function updateClusterRoleBindingMockData(array $cluster_role_binding): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_cluster_role_binding = [
      'metadata' => [
        'name' => $cluster_role_binding['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'subjects' => [
        'name' => 'tiller',
        'namespace' => 'gitlab-managed-apps',
      ],
      'roleRef' => [
        'name' => 'system:controller:route-controller',
      ],

    ];
    $mock_data['updateClusterRoleBinding'] = $update_cluster_role_binding;

    $get_cluster_roles_binding = [];
    $get_cluster_roles_binding[] = $update_cluster_role_binding;
    $mock_data['getClusterRolesBinding'] = $get_cluster_roles_binding;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteClusterRoleBinding in mock data.
   *
   * @param array $cluster_role_binding
   *   The cluster role binding mock data.
   */
  protected function deleteClusterRoleBindingMockData(array $cluster_role_binding): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_cluster_role_binding = [
      'metadata' => [
        'name' => $cluster_role_binding['name'],
      ],
    ];
    $mock_data['deleteClusterRoleBinding'] = $delete_cluster_role_binding;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createApiService in mock data.
   *
   * @param array $api_service
   *   The API service mock data.
   */
  protected function addApiServiceMockData(array $api_service): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_api_service = [
      'metadata' => [
        'name' => $api_service['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'spec' => [
        'insecureSkipTLSVerify' => 'true',
        'group' => "${api_service['name']}.example.com",
        'groupPriorityMinimum' => 17800,
        'versionPriority' => 15,
        'service' => [
          'name' => 'api',
          'namespace' => $api_service['name'],
        ],
        'version' => 'v1alpha1',
      ],
      'status' => [
        'conditions' => [
          'status' => 'True',
        ],
      ],
    ];
    $mock_data['createApiService'] = $create_api_service;

    $get_api_service = [];
    $get_api_service[] = $create_api_service;
    $get_api_service[0]['spec'] = [
      'insecureSkipTLSVerify' => 'true',
      'group' => "${api_service['name']}.example.com",
      'groupPriorityMinimum' => 17200,
      'versionPriority' => 15,
      'service' => [
        'name' => 'api',
        'namespace' => $api_service['name'],
      ],
      'version' => 'v1alpha1',
    ];
    $get_api_service[0]['status']['conditions'] = [
      'status' => 'True',
      'reason' => 'Local',
      'message' => 'Local APIServices are always available',
    ];
    $mock_data['getApiService'] = $get_api_service;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateApiService in mock data.
   *
   * @param array $api_service
   *   The API service mock data.
   */
  protected function updateApiServiceMockData(array $api_service): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_api_service = [
      'metadata' => [
        'name' => $api_service['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['updateApiService'] = $update_api_service;

    $get_api_service = [];
    $get_api_service[] = $update_api_service;
    $get_api_service[0]['spec'] = [
      'service' => NULL,
      'groupPriorityMinimum' => 17200,
      'versionPriority' => 15,
    ];
    $get_api_service[0]['status']['conditions'] = [
      'status' => 'True',
      'reason' => 'Local',
      'message' => 'Local APIServices are always available',
    ];
    $mock_data['getApiService'] = $get_api_service;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteApiService in mock data.
   *
   * @param array $api_service
   *   The API service mock data.
   */
  protected function deleteApiServiceMockData(array $api_service): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_api_service = [
      'metadata' => [
        'name' => $api_service['name'],
      ],
    ];
    $mock_data['deleteApiService'] = $delete_api_service;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createRoleBinding in mock data.
   *
   * @param array $role_binding
   *   The role binding mock data.
   */
  protected function addRoleBindingMockData(array $role_binding): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_role_binding = [
      'metadata' => [
        'name' => $role_binding['name'],
        'namespace' => $role_binding['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'subjects' => [
        'name' => 'tiller',
        'namespace' => 'gitlab-managed-apps',
      ],
    ];
    $mock_data['createRoleBinding'] = $create_role_binding;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $role_binding['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_role_bindings = [];
    $get_role_bindings[] = $create_role_binding;
    $mock_data['getRoleBindings'] = $get_role_bindings;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateRoleBinding in mock data.
   *
   * @param array $role_binding
   *   The role mock data.
   */
  protected function updateRoleBindingMockData(array $role_binding): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_role_binding = [
      'metadata' => [
        'name' => $role_binding['name'],
        'namespace' => $role_binding['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'subjects' => [
        'name' => 'tiller',
        'namespace' => 'gitlab-managed-apps',
      ],
      'roleRef' => [
        'name' => 'system:controller:route-controller',
      ],

    ];
    $mock_data['updateRoleBinding'] = $update_role_binding;

    $get_role_bindings = [];
    $get_role_bindings[] = $update_role_binding;
    $mock_data['getRoleBindings'] = $get_role_bindings;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteRoleBinding in mock data.
   *
   * @param array $role_binding
   *   The role binding mock data.
   */
  protected function deleteRoleBindingMockData(array $role_binding): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_role_binding = [
      'metadata' => [
        'name' => $role_binding['name'],
      ],
    ];
    $mock_data['deleteRoleBinding'] = $delete_role_binding;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createServiceAccount in mock data.
   *
   * @param array $service_account
   *   The service account mock data.
   */
  protected function addServiceAccountMockData(array $service_account): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_service_account = [
      'metadata' => [
        'name' => $service_account['name'],
        'namespace' => $service_account['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'secrets' => [
        '0' => [
          'name' => 'cloud-5-testing-8-x-2-x-xurihq-service-account-token-b8vdv',
        ],
      ],
    ];
    $mock_data['createServiceAccount'] = $create_service_account;

    $get_namespaces[] = [
      'metadata' => [
        'name' => $service_account['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
    ];
    $mock_data['getNamespaces'] = $get_namespaces;

    $get_service_accounts = [];
    $get_service_accounts[] = $create_service_account;
    $mock_data['getServiceAccounts'] = $get_service_accounts;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updateServiceAccount in mock data.
   *
   * @param array $service_account
   *   The service account mock data.
   */
  protected function updateServiceAccountMockData(array $service_account): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_service_account = [
      'metadata' => [
        'name' => $service_account['name'],
        'namespace' => $service_account['post_data']['namespace'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'secrets' => [
        '0' => [
          'name' => 'cloud-5-testing-8-x-2-x-xurihq-service-account-token-b8vdv',
        ],
      ],
    ];
    $mock_data['updateServiceAccount'] = $update_service_account;

    $get_service_accounts = [];
    $get_service_accounts[] = $update_service_account;
    $mock_data['getServiceAccounts'] = $get_service_accounts;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deleteServiceAccount in mock data.
   *
   * @param array $service_account
   *   The service_account mock data.
   */
  protected function deleteServiceAccountMockData(array $service_account): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_service_account = [
      'metadata' => [
        'name' => $service_account['name'],
      ],
    ];
    $mock_data['deleteServiceAccount'] = $delete_service_account;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update getNodeResourceUsage in mock data.
   *
   * @param array $nodes
   *   The node mock data.
   */
  protected function getNodeResourceUsageMockData(array $nodes): void {
    $mock_data = $this->getMockDataFromConfig();
    $total_costs = 1;
    $cpu_capacity = 0;
    $memory_capacity = 0;
    $pod_capacity = 0;
    foreach ($nodes ?: [] as $node) {
      $cpu_capacity += $node['cpu_capacity'];
      $memory_capacity += $node['memory_capacity'];
      $pod_capacity += $node['pod_capacity'];
    }
    $get_node_resource_usage = [
      'cpu_capacity' => $cpu_capacity,
      'memory_capacity' => $memory_capacity,
      'pod_capacity' => $pod_capacity,
      'total_costs' => $total_costs,
    ];
    $mock_data['getNodeResourceUsage'] = $get_node_resource_usage;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update getNodeResourceUsage in mock data.
   *
   * @param array $pods
   *   The node mock data.
   */
  protected function getNamespaceResourceUsageMockData(array $pods): void {
    $mock_data = $this->getMockDataFromConfig();
    $cpu_usage = 0;
    $memory_usage = 0;
    $pod_usage = 0;
    foreach ($pods ?: [] as $pod) {
      $cpu_usage += $pod['cpu_usage'];
      $memory_usage += $pod['memory_usage'];
      $pod_usage += 1;
    }
    $get_namespace_resource_usage = [
      'cpu_usage' => $cpu_usage,
      'memory_usage' => $memory_usage,
      'pod_usage' => $pod_usage,
    ];
    $mock_data['getNamespaceResourceUsage'] = $get_namespace_resource_usage;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update getNodeResourceUsage in mock data.
   */
  protected function calculateCostPerNamespaceMockData(): void {
    // Load data.
    $mock_data = $this->getMockDataFromConfig();
    $get_namespace_resource_usage = $mock_data['getNamespaceResourceUsage'];
    $get_node_resource_usage = $mock_data['getNodeResourceUsage'];

    $cost = $get_node_resource_usage['total_costs'] *
      ($get_namespace_resource_usage['cpu_usage'] / $get_node_resource_usage['cpu_capacity']
        + $get_namespace_resource_usage['memory_usage'] / $get_node_resource_usage['memory_capacity']
        + $get_namespace_resource_usage['pod_usage'] / $get_node_resource_usage['pod_capacity']
      ) / 3;
    $calculate_cost = [
      'cpu_usage' => $get_namespace_resource_usage['cpu_usage'],
      'memory_usage' => $get_namespace_resource_usage['memory_usage'],
      'pod_usage' => $get_namespace_resource_usage['pod_usage'],
      'cpu_capacity' => $get_node_resource_usage['cpu_capacity'],
      'memory_capacity' => $get_node_resource_usage['memory_capacity'],
      'pod_capacity' => $get_node_resource_usage['pod_capacity'],
      'instance_cost' => $get_node_resource_usage['total_costs'],
      'cost' => $cost,
    ];
    $mock_data['calculateCostPerNamespace'] = $calculate_cost;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update createPriorityClass in mock data.
   *
   * @param array $priority_class
   *   The priority class mock data.
   */
  protected function addPriorityClassMockData(array $priority_class): void {
    $mock_data = $this->getMockDataFromConfig();

    $create_priority_class = [
      'metadata' => [
        'name' => $priority_class['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'value' => 5,
      'globalDefault' => FALSE,
      'description' => 'foo',
    ];
    $mock_data['createPriorityClass'] = $create_priority_class;

    $get_priority_classes = [];
    $get_priority_classes[] = $create_priority_class;
    $mock_data['getPriorityClasses'] = $get_priority_classes;
    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update updatePriorityClass in mock data.
   *
   * @param array $priority_class
   *   The priority class mock data.
   */
  protected function updatePriorityClassMockData(array $priority_class): void {
    $mock_data = $this->getMockDataFromConfig();

    $update_priority_class = [
      'metadata' => [
        'name' => $priority_class['name'],
        'creationTimestamp' => date('Y/m/d H:i:s'),
      ],
      'value' => 5,
      'globalDefault' => FALSE,
      'description' => 'foo updated',

    ];
    $mock_data['updatePriorityClass'] = $update_priority_class;

    $get_priority_classes = [];
    $get_priority_classes[] = $update_priority_class;
    $mock_data['getPriorityClasses'] = $get_priority_classes;

    $this->updateMockDataToConfig($mock_data);
  }

  /**
   * Update deletePriorityClass in mock data.
   *
   * @param array $priority_class
   *   The priority_class mock data.
   */
  protected function deletePriorityClassMockData(array $priority_class): void {
    $mock_data = $this->getMockDataFromConfig();

    $delete_priority_class = [
      'metadata' => [
        'name' => $priority_class['name'],
      ],
    ];
    $mock_data['deletePriorityClass'] = $delete_priority_class;
    $this->updateMockDataToConfig($mock_data);
  }

}
