<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Ingress entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_ingress",
 *   id_plural = "k8s_ingresses",
 *   label = @Translation("Ingress"),
 *   label_collection = @Translation("Ingresses"),
 *   label_singular = @Translation("Ingress"),
 *   label_plural = @Translation("Ingresses"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sIngressViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sIngressViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sIngressAccessControlHandler",
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
 *   base_table = "k8s_ingress",
 *   admin_permission = "administer k8s ingress",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/ingress/{k8s_ingress}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/ingress",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/ingress/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/ingress/{k8s_ingress}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/ingress/{k8s_ingress}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/ingress/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_ingress.settings"
 * )
 */
class K8sIngress extends K8sEntityBase implements K8sIngressInterface {

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
  public function getBackend() {
    return $this->get('backend');
  }

  /**
   * {@inheritdoc}
   */
  public function setBackend($backend) {
    return $this->set('backend', $backend);
  }

  /**
   * {@inheritdoc}
   */
  public function getRules() {
    return $this->get('rules')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRules($rules) {
    return $this->set('rules', $rules);
  }

  /**
   * {@inheritdoc}
   */
  public function getTls() {
    return $this->get('tls')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTls($tls) {
    return $this->set('tls', $tls);
  }

  /**
   * {@inheritdoc}
   */
  public function getLoadBalancer() {
    return $this->get('load_balancer')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLoadBalancer($load_balancer) {
    return $this->set('load_balancer', $load_balancer);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of ingress.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['backend'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Backend'))
      ->setDescription(t("A default backend capable of servicing requests that don't match any rule."))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['rules'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Rules'))
      ->setDescription(t('A list of host rules used to configure the Ingress.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['tls'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('TLS'))
      ->setDescription(t('TLS configuration.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    $fields['load_balancer'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Load Balancer'))
      ->setDescription(t('LoadBalancer contains the current status of the load-balancer.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 4096)
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
