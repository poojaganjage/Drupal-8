<?php

namespace Drupal\aws_cloud\Entity\Ec2;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Service\Util\EntityLinkWithNameHtmlGenerator;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Elastic IP entity.
 *
 * @ingroup aws_cloud
 *
 * @ContentEntityType(
 *   id = "aws_cloud_elastic_ip",
 *   id_plural = "aws_cloud_elastic_ips",
 *   label = @Translation("Elastic IP"),
 *   label_collection = @Translation("Elastic IPs"),
 *   label_singular = @Translation("Elastic IP"),
 *   label_plural = @Translation("Elastic IPs"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Ec2\ElasticIpViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\aws_cloud\Entity\Ec2\ElasticIpViewsData",
 *     "form" = {
 *       "default"                 = "Drupal\aws_cloud\Form\Ec2\ElasticIpEditForm",
 *       "add"                     = "Drupal\aws_cloud\Form\Ec2\ElasticIpCreateForm",
 *       "edit"                    = "Drupal\aws_cloud\Form\Ec2\ElasticIpEditForm",
 *       "delete"                  = "Drupal\aws_cloud\Form\Ec2\ElasticIpDeleteForm",
 *       "associate"               = "Drupal\aws_cloud\Form\Ec2\ElasticIpAssociateForm",
 *       "disassociate"            = "Drupal\aws_cloud\Form\Ec2\ElasticIpDisassociateForm",
 *       "delete-multiple-confirm" = "Drupal\aws_cloud\Form\Ec2\ElasticIpDeleteMultipleForm",
 *     },
 *     "access"       = "Drupal\aws_cloud\Controller\Ec2\ElasticIpAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "aws_cloud_elastic_ip",
 *   admin_permission = "administer aws cloud elastic ip",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id"  ,
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical"            = "/clouds/aws_cloud/{cloud_context}/elastic_ip/{aws_cloud_elastic_ip}",
 *     "edit-form"            = "/clouds/aws_cloud/{cloud_context}/elastic_ip/{aws_cloud_elastic_ip}/edit",
 *     "delete-form"          = "/clouds/aws_cloud/{cloud_context}/elastic_ip/{aws_cloud_elastic_ip}/delete",
 *     "collection"           = "/clouds/aws_cloud/{cloud_context}/elastic_ip",
 *     "associate-form"       = "/clouds/aws_cloud/{cloud_context}/elastic_ip/{aws_cloud_elastic_ip}/associate",
 *     "disassociate-form"    = "/clouds/aws_cloud/{cloud_context}/elastic_ip/{aws_cloud_elastic_ip}/disassociate",
 *     "delete-multiple-form" = "/clouds/aws_cloud/{cloud_context}/elastic_ip/delete_multiple",
 *   },
 *   field_ui_base_route = "aws_cloud_elastic_ip.settings"
 * )
 */
class ElasticIp extends CloudContentEntityBase implements ElasticIpInterface {

  /**
   * {@inheritdoc}
   */
  public function getPublicIp() {
    return $this->get('public_ip')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublicIp($public_ip = '') {
    return $this->set('public_ip', $public_ip);
  }

  /**
   * {@inheritdoc}
   */
  public function getDomain() {
    return $this->get('domain')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDomain($domain) {
    return $this->set('domain', $domain);
  }

  /**
   * {@inheritdoc}
   */
  public function getAssociationId() {
    return $this->get('association_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAssociationId($association_id = '') {
    return $this->set('association_id', $association_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAllocationId() {
    return $this->get('allocation_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAllocationId($allocation_id = '') {
    return $this->set('allocation_id', $allocation_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceId() {
    return $this->get('instance_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setInstanceId($instance_id = '') {
    return $this->set('instance_id', $instance_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getScope() {
    return $this->get('scope')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getNetworkInterfaceId() {
    return $this->get('network_interface_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNetworkInterfaceId($network_interface_id) {
    return $this->set('network_interface_id', $network_interface_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getPrivateIpAddress() {
    return $this->get('private_ip_address')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPrivateIpAddress($private_ip_address) {
    return $this->set('private_ip_address', $private_ip_address);
  }

  /**
   * {@inheritdoc}
   */
  public function getNetworkInterfaceOwner() {
    return $this->get('network_interface_owner')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNetworkInterfaceOwner($network_interface_owner) {
    return $this->set('network_interface_owner', $network_interface_owner);
  }

  /**
   * {@inheritdoc}
   */
  public function getRefreshed() {
    return $this->get('refreshed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRefreshed($time) {
    return $this->set('refreshed', $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Ec2ServiceElasticIp entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Ec2ServiceElasticIp entity.'))
      ->setReadOnly(TRUE);

    $fields['cloud_context'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Cloud Service Provider ID'))
      ->setDescription(t('A unique ID for the cloud service provider.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The @label address name.', ['@label' => $entity_type->getLabel()]))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['public_ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('@label', ['@label' => $entity_type->getLabel()]))
      ->setDescription(t('The @label Address.', ['@label' => $entity_type->getLabel()]))
      ->setSettings([
        'default_value' => '',
        'max_length' => 15,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['instance_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Instance ID'))
      ->setDescription(t('The instance the @label address is associated with, if applicable.', [
        '@label' => $entity_type->getLabel(),
      ]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_link',
        'settings' => [
          'target_type' => 'aws_cloud_instance',
          'field_name' => 'instance_id',
          'html_generator_class' => EntityLinkWithNameHtmlGenerator::class,
        ],
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['scope'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Scope'))
      ->setDescription(t('Indicates if the @label address is for use in EC2-Classic (standard) or in a VPC (vpc).', [
        '@label' => $entity_type->getLabel(),
      ]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['network_interface_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Network Interface ID'))
      ->setDescription(t('For instances in a VPC, indicates the ID of the network interface to which the @label is associated.', ['@label' => $entity_type->getLabel()]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_link',
        'settings' => [
          'target_type' => 'aws_cloud_network_interface',
          'field_name' => 'network_interface_id',
          'html_generator_class' => EntityLinkWithNameHtmlGenerator::class,
        ],
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['private_ip_address'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Private IP Address'))
      ->setDescription(t('The private IP address of the network interface to which the @label address is associated.', ['@label' => $entity_type->getLabel()]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['allocation_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Allocation ID'))
      ->setDescription(t('The allocation ID of the @label address. Only applicable to @label addresses used in a VPC.', ['@label' => $entity_type->getLabel()]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['association_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Association ID'))
      ->setDescription(t('The ID representing the association of the address with an instance in a VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['domain'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Domain (standard | vpc)'))
      ->setDescription(t('The instance the @label address is associated with, if applicable.', ['@label' => $entity_type->getLabel()]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['network_interface_owner'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Network Interface Owner'))
      ->setDescription(t('The AWS account number of the network interface owner.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => -5,
        'settings' => [
          'date_format' => 'short',
        ],
      ]);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['refreshed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Refreshed'))
      ->setDescription(t('The time that the entity was last refreshed.'));

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the Ec2ServiceElasticIp entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'match_limit' => 10,
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
