<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Pod entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_pod",
 *   id_plural = "k8s_pods",
 *   label = @Translation("Pod"),
 *   label_collection = @Translation("Pods"),
 *   label_singular = @Translation("Pod"),
 *   label_plural = @Translation("Pods"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sPodViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sPodViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sPodAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\k8s\Form\K8sCreateForm",
 *       "edit"       = "Drupal\k8s\Form\K8sEditForm",
 *       "delete"     = "Drupal\k8s\Form\K8sDeleteForm",
 *       "log"        = "Drupal\k8s\Form\K8sPodLogForm",
 *       "delete-multiple-confirm" = "Drupal\k8s\Form\K8sDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_pod",
 *   admin_permission = "administer k8s pod",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/pod/{k8s_pod}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/pod",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/pod/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/pod/{k8s_pod}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/pod/{k8s_pod}/delete",
 *     "log-form"             = "/clouds/k8s/{cloud_context}/pod/{k8s_pod}/log",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/pod/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_pod.settings"
 * )
 */
class K8sPod extends K8sEntityBase implements K8sPodInterface {

  /**
   * {@inheritdoc}
   */
  public function getNamespace() {
    return $this->get('namespace')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNamespace($namespace) {
    return $this->set('namespace', $namespace);
  }

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
  public function getQosClass() {
    return $this->get('qos_class')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQosClass($qos_class) {
    return $this->set('qos_class', $qos_class);
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeName() {
    return $this->get('node_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNodeName($node_name) {
    return $this->set('node_name', $node_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getPodIp() {
    return $this->get('pod_ip')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPodIp($pod_ip) {
    return $this->set('pod_ip', $pod_ip);
  }

  /**
   * {@inheritdoc}
   */
  public function getContainers() {
    return $this->get('containers')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setContainers($containers) {
    return $this->set('containers', $containers);
  }

  /**
   * {@inheritdoc}
   */
  public function getRestarts() {
    return $this->get('restarts')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRestarts($restarts) {
    return $this->set('restarts', $restarts);
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
  public function getCreationYaml() {
    return $this->get('creation_yaml')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreationYaml($creation_yaml) {
    return $this->set('creation_yaml', $creation_yaml);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of pod.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of pod.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['qos_class'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Qos Class'))
      ->setDescription(t('The Quality of Service (QOS) classification assigned to the pod based on resource requirements.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['node_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Node'))
      ->setDescription(t('NodeName is a request to schedule this pod onto a specific node.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['pod_ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Pod IP'))
      ->setDescription(t('IP address allocated to the pod.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['containers'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Containers'))
      ->setDescription(t('Containers.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ]);

    $fields['restarts'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Restarts'))
      ->setDescription(t('The restarts number of pod.'));

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
      ->setDescription(t('The requested cpu.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
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

    $fields['creation_yaml'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Creation YAML'))
      ->setDescription(t('The YAML content was used to create the entity.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
