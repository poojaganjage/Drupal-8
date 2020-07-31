<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the K8s Node entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_node",
 *   id_plural = "k8s_nodes",
 *   label = @Translation("Node"),
 *   label_collection = @Translation("Nodes"),
 *   label_singular = @Translation("Node"),
 *   label_plural = @Translation("Nodes"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sNodeViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sNodeViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sNodeAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_node",
 *   admin_permission = "administer k8s node",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/node/{k8s_node}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/node",
 *   },
 *   field_ui_base_route = "k8s_node.settings"
 * )
 */
class K8sNode extends K8sEntityBase implements K8sNodeInterface {

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    return $this->set('status', $status);
  }

  /**
   * {@inheritdoc}
   */
  public function getAddresses() {
    return $this->get('addresses')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAddresses(array $addresses) {
    return $this->set('addresses', $addresses);
  }

  /**
   * {@inheritdoc}
   */
  public function getPodCidr() {
    return $this->get('pod_cidr')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPodCidr($pod_cidr) {
    return $this->set('pod_cidr', $pod_cidr);
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderId() {
    return $this->get('provider_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setProviderId($provider_id) {
    return $this->set('provider_id', $provider_id);
  }

  /**
   * {@inheritdoc}
   */
  public function isUnschedulable() {
    return $this->get('unschedulable')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnschedulable($unschedulable) {
    return $this->set('unschedulable', $unschedulable);
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineId() {
    return $this->get('machine_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMachineId($machine_id) {
    return $this->set('machine_id', $machine_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getSystemUuid() {
    return $this->get('system_uuid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSystemUuid($system_uuid) {
    return $this->set('system_uuid', $system_uuid);
  }

  /**
   * {@inheritdoc}
   */
  public function getBootId() {
    return $this->get('boot_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBootId($boot_id) {
    return $this->set('boot_id', $boot_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getKernelVersion() {
    return $this->get('kernel_version')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setKernelVersion($kernel_version) {
    return $this->set('kernel_version', $kernel_version);
  }

  /**
   * {@inheritdoc}
   */
  public function getOsImage() {
    return $this->get('os_image')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setOsImage($os_image) {
    return $this->set('os_image', $os_image);
  }

  /**
   * {@inheritdoc}
   */
  public function getContainerRuntimeVersion() {
    return $this->get('container_runtime_version')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setContainerRuntimeVersion($container_runtime_version) {
    return $this->set('container_runtime_version', $container_runtime_version);
  }

  /**
   * {@inheritdoc}
   */
  public function getKubeletVersion() {
    return $this->get('kubelet_version')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setKubeletVersion($kubelet_version) {
    return $this->set('kubelet_version', $kubelet_version);
  }

  /**
   * {@inheritdoc}
   */
  public function getKubeProxyVersion() {
    return $this->get('kube_proxy_version')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setKubeProxyVersion($kube_proxy_version) {
    return $this->set('kube_proxy_version', $kube_proxy_version);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperatingSystem() {
    return $this->get('operating_system')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setOperatingSystem($operating_system) {
    return $this->set('operating_system', $operating_system);
  }

  /**
   * {@inheritdoc}
   */
  public function getArchitecture() {
    return $this->get('architecture')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setArchitecture($architecture) {
    return $this->set('architecture', $architecture);
  }

  /**
   * {@inheritdoc}
   */
  public function getCpuCapacity() {
    return $this->get('cpu_capacity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCpuCapacity($cpu_capacity) {
    return $this->set('cpu_capacity', $cpu_capacity);
  }

  /**
   * {@inheritdoc}
   */
  public function getCpuRequest() {
    return $this->get('cpu_request')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCpuRequest($cpu_request) {
    return $this->set('cpu_request', $cpu_request);
  }

  /**
   * {@inheritdoc}
   */
  public function getCpuLimit() {
    return $this->get('cpu_limit')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCpuLimit($cpu_limit) {
    return $this->set('cpu_limit', $cpu_limit);
  }

  /**
   * {@inheritdoc}
   */
  public function getCpuUsage() {
    return $this->get('cpu_usage')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCpuUsage($cpu_usage) {
    return $this->set('cpu_usage', $cpu_usage);
  }

  /**
   * {@inheritdoc}
   */
  public function getMemoryCapacity() {
    return $this->get('memory_capacity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMemoryCapacity($memory_capacity) {
    return $this->set('memory_capacity', $memory_capacity);
  }

  /**
   * {@inheritdoc}
   */
  public function getMemoryRequest() {
    return $this->get('memory_request')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMemoryRequest($memory_request) {
    return $this->set('memory_request', $memory_request);
  }

  /**
   * {@inheritdoc}
   */
  public function getMemoryLimit() {
    return $this->get('memory_limit')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMemoryLimit($memory_limit) {
    return $this->set('memory_limit', $memory_limit);
  }

  /**
   * {@inheritdoc}
   */
  public function getMemoryUsage() {
    return $this->get('memory_usage')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMemoryUsage($memory_usage) {
    return $this->set('memory_usage', $memory_usage);
  }

  /**
   * {@inheritdoc}
   */
  public function getPodsCapacity() {
    return $this->get('pods_capacity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPodsCapacity($pods_capacity) {
    return $this->set('pods_capacity', $pods_capacity);
  }

  /**
   * {@inheritdoc}
   */
  public function getPodsAllocation() {
    return $this->get('pods_allocation')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPodsAllocation($pods_allocation) {
    return $this->set('pods_allocation', $pods_allocation);
  }

  /**
   * {@inheritdoc}
   */
  public function getDirty() {
    return $this->get('dirty')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDirty($dirty) {
    return $this->set('dirty', $dirty);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['detail'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Detail'))
      ->setDescription(t('Entity detail.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ])
      ->addConstraint('yaml_array_data');

    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of k8s node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['addresses'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Addresses'))
      ->setDescription(t('List of addresses reachable to the node.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['pod_cidr'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Pod CIDR'))
      ->setDescription(t('PodCIDR represents the pod IP range assigned to the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['provider_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Provider ID'))
      ->setDescription(t('ID of the node assigned by the cloud provider in the format: <ProviderName>://<ProviderSpecificNodeID>.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['unschedulable'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Unschedulable'))
      ->setDescription(t('Unschedulable controls node schedulability of new pods. By default, node is schedulable.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['machine_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Cloud Service Provider ID'))
      ->setDescription(t('Cloud service provider reported by the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['system_uuid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('System UUID'))
      ->setDescription(t('SystemUUID reported by the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['boot_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Boot ID'))
      ->setDescription(t('Boot ID reported by the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['kernel_version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Kernel Version'))
      ->setDescription(t("Kernel Version reported by the node from 'uname -r' (e.g. 3.16.0-0.bpo.4-amd64)."))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['os_image'] = BaseFieldDefinition::create('string')
      ->setLabel(t('OS Image'))
      ->setDescription(t('OS Image reported by the node from /etc/os-release (e.g. Debian GNU/Linux 7 (wheezy)).'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['container_runtime_version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Container Runtime Version'))
      ->setDescription(t('ContainerRuntime Version reported by the node through runtime remote API (e.g. docker://1.5.0).'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['kubelet_version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Kubelet Version'))
      ->setDescription(t('Kubelet Version reported by the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['kube_proxy_version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('KubeProxy Version'))
      ->setDescription(t('KubeProxy Version reported by the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['operating_system'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Operating System'))
      ->setDescription(t('The Operating System reported by the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['architecture'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Architecture'))
      ->setDescription(t('The Architecture reported by the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['cpu_capacity'] = BaseFieldDefinition::create('float')
      ->setLabel(t('CPU (Capacity)'))
      ->setDescription(t('The cpu capacity of the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['cpu_request'] = BaseFieldDefinition::create('float')
      ->setLabel(t('CPU (Request)'))
      ->setDescription(t('The requested cpu.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['cpu_limit'] = BaseFieldDefinition::create('float')
      ->setLabel(t('CPU (Limit)'))
      ->setDescription(t('The limited cpu.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['cpu_usage'] = BaseFieldDefinition::create('float')
      ->setLabel(t('CPU (Usage)'))
      ->setDescription(t('The cpu usage.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['memory_capacity'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Memory (Capacity)'))
      ->setDescription(t('The memory capacity of the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'memory_formatter',
        'weight' => -5,
      ]);

    $fields['memory_request'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Memory (Request)'))
      ->setDescription(t('The requested memory.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'memory_formatter',
        'weight' => -5,
      ]);

    $fields['memory_limit'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Memory (Limit)'))
      ->setDescription(t('The limited memory.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'memory_formatter',
        'weight' => -5,
      ]);

    $fields['memory_usage'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Memory (Usage)'))
      ->setDescription(t('The memory usage.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'memory_formatter',
        'weight' => -5,
      ]);

    $fields['pods_capacity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pods (Capacity)'))
      ->setDescription(t('The pods capacity of the node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['pods_allocation'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pods (Allocation)'))
      ->setDescription(t('The pods allocated.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['dirty'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Dirty'))
      ->setDescription(t('Dirty.'))
      ->setDefaultValue(FALSE);

    return $fields;
  }

}
