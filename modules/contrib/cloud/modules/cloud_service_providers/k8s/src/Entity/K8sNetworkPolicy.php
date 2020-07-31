<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Network Policy entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_network_policy",
 *   id_plural = "k8s_network_policies",
 *   label = @Translation("Network Policy"),
 *   label_collection = @Translation("Network Policies"),
 *   label_singular = @Translation("Network Policy"),
 *   label_plural = @Translation("Network Policies"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sNetworkPolicyViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sNetworkPolicyViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sNetworkPolicyAccessControlHandler",
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
 *   base_table = "k8s_network_policy",
 *   admin_permission = "administer k8s network policy",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/network_policy/{k8s_network_policy}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/network_policy",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/network_policy/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/network_policy/{k8s_network_policy}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/network_policy/{k8s_network_policy}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/network_policy/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_network_policy.settings"
 * )
 */
class K8sNetworkPolicy extends K8sEntityBase implements K8sNetworkPolicyInterface {

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
  public function getEgress() {
    return $this->get('egress');
  }

  /**
   * {@inheritdoc}
   */
  public function setEgress($egress) {
    return $this->set('egress', $egress);
  }

  /**
   * {@inheritdoc}
   */
  public function getIngress() {
    return $this->get('ingress');
  }

  /**
   * {@inheritdoc}
   */
  public function setIngress($ingress) {
    return $this->set('ingress', $ingress);
  }

  /**
   * {@inheritdoc}
   */
  public function getPolicyTypes() {
    return $this->get('policy_types');
  }

  /**
   * {@inheritdoc}
   */
  public function setPolicyTypes($policy_types) {
    return $this->set('policy_types', $policy_types);
  }

  /**
   * {@inheritdoc}
   */
  public function getPodSelector() {
    return $this->get('pod_selector');
  }

  /**
   * {@inheritdoc}
   */
  public function setPodSelector($pod_selector) {
    return $this->set('pod_selector', $pod_selector);
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
      ->setDescription(t('The namespace of network policy.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['egress'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Egress Rule'))
      ->setDescription(t('The list of egress rule.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['ingress'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Ingress Rule'))
      ->setDescription(t('The list of ingress rule.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['pod_selector'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Pod Selector'))
      ->setDescription(t('The pods to which this network policy object applied to.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['policy_types'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Policy Types'))
      ->setDescription(t('The list of rule types the network policy relates to.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

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
