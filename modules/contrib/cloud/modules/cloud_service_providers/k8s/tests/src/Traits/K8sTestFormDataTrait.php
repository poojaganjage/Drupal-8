<?php

namespace Drupal\Tests\k8s\Traits;

use Drupal\Component\Utility\Random;

/**
 * The trait creating form data for k8s testing.
 */
trait K8sTestFormDataTrait {

  /**
   * Create random value using regex as '[a-z0-9]([-a-z0-9]*[a-z0-9])
   *
   * @param int $length
   *   The length of random value.
   *
   * @return string
   *   Random value.
   *
   * @throws \Exception
   */
  private function makeRandomString($length): string {
    $str = array_merge(range('a', 'z'), range('0', '9'));
    $r_str = NULL;
    for ($i = 0; $i < $length; $i++) {
      $r_str .= $str[random_int(0, count($str) - 1)];
    }
    return $r_str;
  }

  /**
   * Create test data for cloud service provider (CloudConfig).
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createCloudConfigTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0, $num = 1; $i < $repeat_count; $i++, $num++) {

      $data[] = [
        'name[0][value]'             => sprintf('config-entity-#%d-%s - %s', $num, $random->name(8, TRUE), date('Y/m/d H:i:s')),
        'cloud_context'              => strtolower($random->name(16, TRUE)),
        'field_api_server[0][value]' => 'https://www.k8s-test.com/',
        'field_token[0][value]'      => $random->name(128, TRUE),
      ];
    }

    return $data;
  }

  /**
   * Create cloud server template test data.
   *
   * @param array $test_data
   *   Test data array.
   * @param string $source_type
   *   Source type of resources.
   * @param string $object
   *   Object type.
   * @param array $entities
   *   Array of resource entities.
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createServerTemplateTestFormData(array $test_data, $source_type, $object = NULL, array $entities = [], $repeat_count = 1): array {
    $random = new Random();
    $data = [];

    for ($i = 0; $i < $repeat_count; $i++) {
      $data[] = [
        'cloud_context[0][value]' => $this->cloudContext,
        'name[0][value]' => !empty($test_data[$i]['name']) ? $test_data[$i]['name'] : 'name-' . $random->name(8, TRUE),
        'field_namespace' => $this->namespace,
        'field_source_type' => $source_type,
      ];
      if (isset($test_data[$i]['post_data']['detail[0][value]'])) {
        $data[$i]['field_detail[0][value]'] = $test_data[$i]['post_data']['detail[0][value]'];
      }
      if (isset($object)) {
        $data[$i]['field_object'] = $object;
      }
      if (!empty($entities)) {
        foreach ($entities ?: [] as $entity) {
          $data[$i]['field_launch_resources'][] = ['item_key' => $entity->getEntityTypeId(), 'item_value' => $entity->id()];
        }
      }
    }
    return $data;
  }

  /**
   * Create test data for k8s node.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createNodeTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $data[] = [
        'name' => sprintf('Node-%s - %s', $random->name(8, TRUE), date('Y/m/d H:i:s')),
        'cloud_context' => $this->cloudContext,
        'cpu_capacity' => 100,
        'memory_capacity' => 1024,
        'pod_capacity' => 64,
        'label' => [
          'beta.kubernetes.io/instance-type' => 'm3.medium',
          'failure-domain.beta.kubernetes.io/region' => 'us-east-1',
        ],
      ];
    }

    return $data;
  }

  /**
   * Create test data for k8s namespace.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createNamespaceTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $data[] = [
        'name' => sprintf('Namespace-%s - %s', $random->name(8, TRUE), date('Y/m/d H:i:s')),
        'labels[0][item_key]' => 'key-' . $random->name(8, TRUE),
        'labels[0][item_value]' => 'value-' . $random->name(8, TRUE),
        'annotations[0][item_key]' => 'key-' . $random->name(8, TRUE),
        'annotations[0][item_value]' => 'value-' . $random->name(8, TRUE),
      ];
    }

    return $data;
  }

  /**
   * Create random namespaces.
   *
   * @return array
   *   Random namespaces array.
   *
   * @throws \Exception
   */
  protected function createNamespacesRandomTestFormData(): array {
    $namespaces = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $key = 'key-' . $this->random->name(8, TRUE);
      $value = 'value-' . $this->random->name(16, TRUE);
      $namespaces[] = [
        'name' => sprintf('namespace-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'labels' => [
          [$key => $value],
        ],
        'annotations' => [
          [$key => $value],
        ],
      ];
    }

    return $namespaces;
  }

  /**
   * Create test data for k8s pod.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createPodTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: apps/v1
kind: Pod
metadata:
  name: $name
  labels:
    app: nginx
  annotations:
    key: value
spec:
  replicas: 3
  selector:
    matchLabels:
      app: nginx
  template:
    metadata:
      labels:
        app: nginx
    spec:
      containers:
      - name: nginx
        image: nginx:1.7.9
        ports:
        - containerPort: 80
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random pods.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random pods array.
   *
   * @throws \Exception
   */
  protected function createPodsRandomTestFormData($namespace): array {
    $pods = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $pods[] = [
        'name' => sprintf('pod-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
        'cloud_context' => $this->cloudContext,
        'memory_usage' => random_int(1, 128),
        'cpu_usage' => (float) (random_int(1, 100) / 100),
      ];
    }

    return $pods;
  }

  /**
   * Create test data for k8s deployment.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createDeploymentTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: Deployment
metadata:
  name: $name
spec:
  selector:
    app: MyApp
  ports:
    - protocol: TCP
      port: 80
      targetPort: 9376
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random deployments.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random deployments array.
   *
   * @throws \Exception
   */
  protected function createDeploymentsRandomTestFormData($namespace): array {
    $deployments = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $deployments[] = [
        'name' => sprintf('deployment-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $deployments;
  }

  /**
   * Create test data for k8s replica set.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createReplicaSetTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: ReplicaSet
metadata:
  name: $name
spec:
  selector:
    app: MyApp
  ports:
    - protocol: TCP
      port: 80
      targetPort: 9376
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random replica set.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random replica sets array.
   *
   * @throws \Exception
   */
  protected function createReplicaSetsRandomTestFormData($namespace): array {
    $replica_sets = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $replica_sets[] = [
        'name' => sprintf('replica-set-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $replica_sets;
  }

  /**
   * Create test data for k8s service.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createServiceTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: Service
metadata:
  name: $name
spec:
  selector:
    app: MyApp
  ports:
    - protocol: TCP
      port: 80
      targetPort: 9376
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random services.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random services array.
   *
   * @throws \Exception
   */
  protected function createServicesRandomTestFormData($namespace): array {
    $services = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $services[] = [
        'name' => sprintf('service-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $services;
  }

  /**
   * Create test data for k8s cron job.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createCronJobTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: batch/v1beta1
kind: CronJob
metadata:
  name: $name
spec:
  schedule: "* * */1 * *"
  jobTemplate:
    spec:
      template:
        spec:
          containers:
          - name: sleep
            image: alpine
            command: ["sh", "-c"]
            args:
            - |
              sleep 5
          restartPolicy: Never
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random cron jobs.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random cron jobs array.
   *
   * @throws \Exception
   */
  protected function createCronJobsRandomTestFormData($namespace): array {
    $cron_jobs = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $cron_jobs[] = [
        'name' => sprintf('cron-job-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $cron_jobs;
  }

  /**
   * Create test data for k8s job.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createJobTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: batch/v1
kind: Job
metadata:
  name: $name
spec:
  template:
    spec:
      containers:
      - name: sleep
        image: alpine
        command: ["sh",  "-c"]
        args:
        - |
          sleep 5
      restartPolicy: Never
  backoffLimit: 4
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random jobs.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random jobs array.
   *
   * @throws \Exception
   */
  protected function createJobsRandomTestFormData($namespace): array {
    $jobs = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $jobs[] = [
        'name' => sprintf('job-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $jobs;
  }

  /**
   * Create test data for k8s resource quota.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createResourceQuotaTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: ResourceQuota
metadata:
  name: $name
spec:
  hard:
    cpu: "10"
    memory: 20Gi
    pods: "10"
  scopeSelector:
    matchExpressions:
    - operator : In
      scopeName: PriorityClass
      values: ["medium"]
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
        'spec' => [
          'hard' => [
            'cpu' => "10",
            'memory' => "20Gi",
            'pods' => "10",
          ],
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random resource quotas.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random resource quotas array.
   *
   * @throws \Exception
   */
  protected function createResourceQuotasRandomTestFormData($namespace): array {
    $resource_quotas = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $resource_quotas[] = [
        'name' => sprintf('resource-quota-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $resource_quotas;
  }

  /**
   * Create test data for k8s limit range.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createLimitRangeTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: LimitRange
metadata:
  name: $name
spec:
  limits:
  - maxLimitRequestRatio:
      memory: 2
    type: Pod
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random limit ranges.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random limit ranges array.
   *
   * @throws \Exception
   */
  protected function createLimitRangesRandomTestFormData($namespace): array {
    $limit_ranges = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $limit_ranges[] = [
        'name' => sprintf('limit-range-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $limit_ranges;
  }

  /**
   * Create test data for k8s secret.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createSecretTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: Secret
metadata:
  name: $name
type: Opaque
data:
  username: YWRtaW4=
  password: MWYyZDFlMmU2N2Rm
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random secrets.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random secrets array.
   *
   * @throws \Exception
   */
  protected function createSecretsRandomTestFormData($namespace): array {
    $secrets = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $secrets[] = [
        'name' => sprintf('secret-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $secrets;
  }

  /**
   * Create test data for K8s ConfigMap.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createConfigMapTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: ConfigMap
metadata:
  name: $name
data:
  property1: hello
  property2: world
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random ConfigMaps.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random ConfigMaps array.
   *
   * @throws \Exception
   */
  protected function createConfigMapsRandomTestFormData($namespace): array {
    $config_maps = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $config_maps[] = [
        'name' => sprintf('config-map-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $config_maps;
  }

  /**
   * Create test data for k8s network policy.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createNetworkPolicyTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: networking.k8s.io/v1
kind: NetworkPolicy
metadata:
  name: $name
spec:
  policyTypes:
  - Ingress
  ingress:
  - from:
    - ipBlock:
        cidr: 172.17.0.0/16
        except:
        - 172.17.1.0/24
    ports:
    - protocol: TCP
      port: 6379
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random network policies.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random network polices array.
   *
   * @throws \Exception
   */
  protected function createNetworkPoliciesRandomTestFormData($namespace): array {
    $network_polices = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $network_polices[] = [
        'name' => sprintf('network-policy-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $network_polices;
  }

  /**
   * Create test data for k8s role.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createRoleTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: rbac.authorization.k8s.io/v1
kind: Role
metadata:
  name: $name
rules:
- apiGroups:
  - apps
  - extensions
  resources:
  - deployments
  verbs:
  - "get"
  - "list"
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random roles.
   *
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Random roles array.
   *
   * @throws \Exception
   */
  protected function createRolesRandomTestFormData($namespace): array {
    $roles = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $roles[] = [
        'name' => sprintf('role-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
        'namespace' => $namespace,
      ];
    }

    return $roles;
  }

  /**
   * Create test data for k8s cluster role.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createClusterRoleTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRole
metadata:
  name: $name
rules:
- apiGroups:
  - apps
  - extensions
  resources:
  - deployments
  verbs:
  - "get"
  - "list"
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random cluster roles.
   *
   * @return array
   *   Random cluster roles array.
   *
   * @throws \Exception
   */
  protected function createClusterRolesRandomTestFormData(): array {
    $cluster_roles = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $cluster_roles[] = [
        'name' => sprintf('role-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $cluster_roles;
  }

  /**
   * Create test data for creating project.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   *
   * @throws \Exception
   */
  protected function createProjectTestFormData($repeat_count): array {
    $random = new Random();

    $current_user = \Drupal::currentUser()->getAccountName();
    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $data[] = [
        'field_username' => $current_user,
        'name[0][value]' => sprintf('project-%s', $this->makeRandomString(8)),
        'field_enable_resource_scheduler[value]' => rand(0, 1),
        'field_enable_time_scheduler[value]' => rand(0, 1),
        'field_k8s_clusters' => $this->cloudContext,
        'field_pod_count[0][value]' => rand(0, 20),
        'field_request_cpu[0][value]' => rand(0, 1000),
        'field_request_memory[0][value]' => rand(0, 1000),
        'field_startup_time_hour' => sprintf('%01d', rand(0, 23)),
        'field_startup_time_minute' => sprintf('%01d', rand(0, 59)),
        'field_stop_time_hour' => sprintf('%01d', rand(0, 23)),
        'field_stop_time_minute' => sprintf('%01d', rand(0, 59)),
      ];
    }

    return $data;
  }

  /**
   * Create test data for k8s persistent volume.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createPersistentVolumeTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: PersistentVolume
metadata:
  name: $name
spec:
  capacity:
    storage: 5Gi
  volumeMode: Filesystem
  accessModes:
    - ReadWriteOnce
  persistentVolumeReclaimPolicy: Recycle
  storageClassName: slow
  mountOptions:
    - hard
    - nfsvers=4.1
  nfs:
    path: /tmp
    server: 172.17.0.2
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random persistent volume.
   *
   * @return array
   *   Random persistent volume array.
   *
   * @throws \Exception
   */
  protected function createPersistentVolumeRandomTestFormData(): array {
    $persistent_volume = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $persistent_volume[] = [
        'name' => sprintf('persistent-volume-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $persistent_volume;
  }

  /**
   * Create test data for k8s storage class.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createStorageClassTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: storage.k8s.io/v1
kind: StorageClass
metadata:
  name: $name
provisioner: kubernetes.io/aws-ebs
parameters:
  type: gp2
reclaimPolicy: Retain
allowVolumeExpansion: true
mountOptions:
  - debug
volumeBindingMode: Immediate
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random storage classes.
   *
   * @return array
   *   Random storage classes array.
   *
   * @throws \Exception
   */
  protected function createStorageClassesRandomTestFormData(): array {
    $storage_classes = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $storage_classes[] = [
        'name' => sprintf('storage-class-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $storage_classes;
  }

  /**
   * Create test data for k8s stateful set.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createStatefulSetTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: $name
spec:
  selector:
    app: MyApp
  ports:
    - protocol: TCP
      port: 80
      targetPort: 9376
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random stateful sets.
   *
   * @return array
   *   Random stateful sets array.
   *
   * @throws \Exception
   */
  protected function createStatefulSetsRandomTestFormData(): array {
    $stateful_sets = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $stateful_sets[] = [
        'name' => sprintf('stateful-set-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $stateful_sets;
  }

  /**
   * Create test data for k8s ingress.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createIngressTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: extensions/v1beta1
kind: Ingress
metadata:
  name: $name
spec:
  rules:
    - host: sslexample.foo.com
      http:
        paths:
        - path: /
          backend:
            serviceName: service1
            servicePort: 80
  tls:
  - hosts:
    - sslexample.foo.com
    secretName: testsecret-tls1
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random ingresses.
   *
   * @return array
   *   Random ingresses array.
   *
   * @throws \Exception
   */
  protected function createIngressesRandomTestFormData(): array {
    $ingresses = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $ingresses[] = [
        'name' => sprintf('ingress-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $ingresses;
  }

  /**
   * Create test data for k8s daemon set.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createDaemonSetTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: apps/v1
kind: DaemonSet
metadata:
  name: $name
  labels:
    app: $name
spec:
  selector:
    matchLabels:
      app: $name
  template:
    metadata:
      labels:
        app: $name
    spec:
      containers:
      - name: busybox
        image: busybox
        args:
        - sleep
        - "10000"
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random daemon sets.
   *
   * @return array
   *   Random daemon sets array.
   *
   * @throws \Exception
   */
  protected function createDaemonSetsRandomTestFormData(): array {
    $daemon_sets = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $daemon_sets[] = [
        'name' => sprintf('daemon-set-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $daemon_sets;
  }

  /**
   * Create test data for k8s endpoint.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createEndpointTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: Endpoints
metadata:
  name: $name
subsets:
  ports:
    - protocol: TCP
      name: web
      port: 80
  addresses:
    ip: 192.168.233.92
    hostname: web-1
    nodeName: ip-192-168-243-46.us-west-2.compute.internal
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random endpoints.
   *
   * @return array
   *   Random endpoints array.
   *
   * @throws \Exception
   */
  protected function createEndpointsRandomTestFormData(): array {
    $endpoints = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $endpoints[] = [
        'name' => sprintf('endpoint-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $endpoints;
  }

  /**
   * Create test data for k8s event.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createEventTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $data[] = [
        'name' => sprintf('Event-%s - %s', $random->name(8, TRUE), date('Y/m/d H:i:s')),
      ];
    }

    return $data;
  }

  /**
   * Create test data for k8s persistent volume claim.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createPersistentVolumeClaimTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
 name: $name
spec:
 storageClassName: manual
 accessModes:
   - ReadWriteOnce
 resources:
   requests:
     storage: 1Gi
EOS;
      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random persistent volume claims.
   *
   * @return array
   *   Random persistent volume claims array.
   *
   * @throws \Exception
   */
  protected function createPersistentVolumeClaimsRandomTestFormData(): array {
    $persistent_volume_claims = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $persistent_volume_claims[] = [
        'name' => sprintf('persistent-volume-claim-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $persistent_volume_claims;
  }

  /**
   * Create test data for k8s cluster role binding.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createClusterRoleBindingTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRoleBinding
metadata:
  name: $name
subjects:
  -
    kind: ServiceAccount
    name: tiller
    namespace: gitlab-managed-apps
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: ClusterRole
  name: cluster-admin
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random cluster roles binding.
   *
   * @return array
   *   Random cluster roles binding array.
   *
   * @throws \Exception
   */
  protected function createClusterRoleBindingsRandomTestFormData(): array {
    $cluster_role_bindings = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $cluster_role_bindings[] = [
        'name' => sprintf('role-binding-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $cluster_role_bindings;
  }

  /**
   * Create test data for k8s API service.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createApiServiceTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $prefix = strtolower($random->name(8, TRUE));
      $detail = <<<EOS
apiVersion: apiregistration.k8s.io/v1
kind: APIService
metadata:
  name: v1alpha1.${prefix}.example.com
spec:
  insecureSkipTLSVerify: true
  group: ${prefix}.example.com
  groupPriorityMinimum: 17800
  versionPriority: 15
  service:
    name: api
    namespace: ${prefix}
  version: v1alpha1
EOS;

      $data[] = [
        'name' => $prefix,
        'post_data' => [
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random API services.
   *
   * @return array
   *   Random API services array.
   *
   * @throws \Exception
   */
  protected function createApiServicesRandomTestFormData(): array {
    $api_services = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $api_services[] = [
        'name' => sprintf('api-service-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $api_services;
  }

  /**
   * Create test data for k8s role binding.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createRoleBindingTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: rbac.authorization.k8s.io/v1
kind: RoleBindingList
metadata:
  name: $name
subjects:
  -
    kind: ServiceAccount
    name: tiller
    namespace: gitlab-managed-apps
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: Role
  name: -admin
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random roles binding.
   *
   * @return array
   *   Random roles binding array.
   *
   * @throws \Exception
   */
  protected function createRoleBindingsRandomTestFormData(): array {
    $role_bindings = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $role_bindings[] = [
        'name' => sprintf('role-binding-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $role_bindings;
  }

  /**
   * Create test data for k8s Service Account.
   *
   * @param int $repeat_count
   *   Repeat count.
   * @param string $namespace
   *   The name of namespace.
   *
   * @return array
   *   Test data.
   */
  protected function createServiceAccountTestFormData($repeat_count, $namespace): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
apiVersion: v1
kind: ServiceAccount
metadata:
  name: $name
secrets:
  -
    name: cloud-5-testing-8-x-2-x-xurihq-service-account-token-b8vdv
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'namespace' => $namespace,
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random service accounts.
   *
   * @return array
   *   Random service accounts array.
   *
   * @throws \Exception
   */
  protected function createServiceAccountsRandomTestFormData(): array {
    $service_accounts = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $service_accounts[] = [
        'name' => sprintf('service-account-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $service_accounts;
  }

  /**
   * Create test data for k8s Priority Class.
   *
   * @param int $repeat_count
   *   Repeat count.
   *
   * @return array
   *   Test data.
   */
  protected function createPriorityClassTestFormData($repeat_count): array {
    $random = new Random();

    // Input Fields.
    $data = [];
    for ($i = 0; $i < $repeat_count; $i++) {
      $name = 'name-' . $random->name(8, TRUE);
      $detail = <<<EOS
kind: PriorityClass
metadata:
  name: $name
value: 5
description: foo
EOS;

      $data[] = [
        'name' => $name,
        'post_data' => [
          'detail[0][value]' => $detail,
        ],
      ];
    }

    return $data;
  }

  /**
   * Create random Priority Classes.
   *
   * @return array
   *   Random priority classes array.
   *
   * @throws \Exception
   */
  protected function createPriorityClassesRandomTestFormData(): array {
    $priority_classes = [];
    $count = random_int(1, 10);
    for ($i = 0, $num = 1; $i < $count; $i++, $num++) {
      $priority_classes[] = [
        'name' => sprintf('priority-class-random-data #%d - %s - %s', $num, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      ];
    }

    return $priority_classes;
  }

}
