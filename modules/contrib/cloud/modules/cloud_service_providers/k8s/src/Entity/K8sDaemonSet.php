<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Daemon Set entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_daemon_set",
 *   id_plural = "k8s_daemon_sets",
 *   label = @Translation("Daemon Set"),
 *   label_collection = @Translation("Daemon Sets"),
 *   label_singular = @Translation("Daemon Set"),
 *   label_plural = @Translation("Daemon Sets"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sDaemonSetViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sDaemonSetViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sDaemonSetAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\k8s\Form\K8sCreateForm",
 *       "edit"       = "Drupal\k8s\Form\K8sEditForm",
 *       "delete"     = "Drupal\k8s\Form\K8sDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\k8s\Form\K8sDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_daemon_set",
 *   admin_permission = "administer k8s daemon set",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/daemon_set/{k8s_daemon_set}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/daemon_set",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/daemon_set/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/daemon_set/{k8s_daemon_set}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/daemon_set/{k8s_daemon_set}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/daemon_set/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_daemon_set.settings"
 * )
 */
class K8sDaemonSet extends K8sEntityBase implements K8sDaemonSetInterface {

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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of daemon set.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

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

    return $fields;
  }

}
