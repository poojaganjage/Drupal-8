<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Plugin\Field\Util\AwsCloudReservedKeyChecker;
use Drupal\aws_cloud\Plugin\Field\Util\AwsCloudValueConverter;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the VPC entity.
 *
 * @ingroup aws_cloud
 *
 * @ContentEntityType(
 *   id = "aws_cloud_vpc",
 *   id_plural = "aws_cloud_vpcs",
 *   label = @Translation("VPC"),
 *   label_collection = @Translation("VPCs"),
 *   label_singular = @Translation("VPC"),
 *   label_plural = @Translation("VPCs"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Vpc\VpcViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\aws_cloud\Entity\Vpc\VpcViewsData",
 *     "form" = {
 *       "default"                 = "Drupal\aws_cloud\Form\Vpc\VpcEditForm",
 *       "add"                     = "Drupal\aws_cloud\Form\Vpc\VpcCreateForm",
 *       "edit"                    = "Drupal\aws_cloud\Form\Vpc\VpcEditForm"  ,
 *       "delete"                  = "Drupal\aws_cloud\Form\Vpc\VpcDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\aws_cloud\Form\Vpc\VpcDeleteMultipleForm",
 *     },
 *     "access"       = "Drupal\aws_cloud\Controller\Vpc\VpcAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "aws_cloud_vpc",
 *   admin_permission = "administer aws cloud vpc",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id"  ,
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical"            = "/clouds/aws_cloud/{cloud_context}/vpc/{aws_cloud_vpc}",
 *     "edit-form"            = "/clouds/aws_cloud/{cloud_context}/vpc/{aws_cloud_vpc}/edit",
 *     "delete-form"          = "/clouds/aws_cloud/{cloud_context}/vpc/{aws_cloud_vpc}/delete",
 *     "collection"           = "/clouds/aws_cloud/{cloud_context}/vpc",
 *     "delete-multiple-form" = "/clouds/aws_cloud/{cloud_context}/vpc/delete_multiple",
 *   },
 *   field_ui_base_route = "aws_cloud_vpc.settings"
 * )
 */
class Vpc extends CloudContentEntityBase implements VpcInterface {

  public const TAG_CREATED_BY_UID = 'aws_cloud_vpc_created_by_uid';

  /**
   * {@inheritdoc}
   */
  public function getVpcId() {
    return $this->get('vpc_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVpcId($vpc_id = '') {
    return $this->set('vpc_id', $vpc_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceTenancy() {
    return $this->get('instance_tenancy')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setInstanceTenancy($instance_tenancy) {
    return $this->set('instance_tenancy', $instance_tenancy);
  }

  /**
   * {@inheritdoc}
   */
  public function isDefault() {
    return $this->get('is_default')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefault($is_default) {
    return $this->set('is_default', $is_default);
  }

  /**
   * {@inheritdoc}
   */
  public function getCidrBlock() {
    return $this->get('cidr_block')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCidrBlock($cidr_block) {
    return $this->set('cidr_block', $cidr_block);
  }

  /**
   * {@inheritdoc}
   */
  public function getDhcpOptionsId() {
    return $this->get('dhcp_options_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDhcpOptionsId($dhcp_options_id) {
    return $this->set('dhcp_options_id', $dhcp_options_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccountId() {
    return $this->get('account_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccountId($account_id) {
    return $this->set('account_id', $account_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setState($state) {
    return $this->set('state', $state);
  }

  /**
   * {@inheritdoc}
   */
  public function getTags() {
    return $this->get('tags')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setTags(array $tags) {
    return $this->set('tags', $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function getCidrBlocks() {
    return $this->get('cidr_blocks')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setCidrBlocks(array $cidr_blocks) {
    return $this->set('cidr_blocks', $cidr_blocks);
  }

  /**
   * {@inheritdoc}
   */
  public function getIpv6CidrBlocks() {
    return $this->get('ipv6_cidr_blocks')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setIpv6CidrBlocks(array $ipv6_cidr_blocks) {
    return $this->set('ipv6_cidr_blocks', $ipv6_cidr_blocks);
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
  public function setCreated($created = 0) {
    return $this->set('created', $created);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Ec2ServiceVpc entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Ec2ServiceVpc entity.'))
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
      ->setDescription(t('The name of the Ec2ServiceVpc entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('Description of Vpc.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['vpc_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VPC ID'))
      ->setDescription(t('The VPC ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['cidr_block'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('CIDR Block'))
      ->setDescription(t('Information about the IPv4 CIDR blocks associated with the VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['cidr_blocks'] = BaseFieldDefinition::create('cidr_block')
      ->setLabel(t('IPv4 CIDR'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDescription(t('IPv4 CIDR Blocks.'))
      ->setDisplayOptions('view', [
        'type' => 'cidr_block_formatter',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'cidr_block_item',
      ]);

    $fields['ipv6_cidr_blocks'] = BaseFieldDefinition::create('cidr_block')
      ->setLabel(t('IPv6 CIDR'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDescription(t('IPv6 CIDR Blocks.'))
      ->setDisplayOptions('view', [
        'type' => 'cidr_block_formatter',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'cidr_block_item',
      ]);

    $fields['state'] = BaseFieldDefinition::create('string')
      ->setLabel(t('State'))
      ->setDescription(t('The current state of the VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['dhcp_options_id'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('DHCP Options ID'))
      ->setDescription(t("The ID of the set of DHCP options you've associated with the VPC (or default if the default options are associated with the VPC)."))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['instance_tenancy'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Instance Tenancy'))
      ->setDescription(t('The allowed tenancy of instances launched into the VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['is_default'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Default VPC'))
      ->setDescription(t('Indicates whether the VPC is the default VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setSettings([
        'on_label' => t('Yes'),
        'off_label' => t('No'),
      ])
      ->setReadOnly(TRUE);

    $fields['account_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('AWS Account ID'))
      ->setDescription(t('The AWS account ID of the VPC owner, without dashes.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('Date/time the Amazon VPC was created.'))
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
      ->setDescription(t('The user ID of the Ec2ServiceVpc entity author.'))
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

    $fields['tags'] = BaseFieldDefinition::create('key_value')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDescription(t('Tags.'))
      ->setDisplayOptions('view', [
        'type' => 'key_value_formatter',
        'weight' => -5,
        'settings' => [
          'value_converter_class' => AwsCloudValueConverter::class,
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'key_value_item',
        'settings' => [
          'reserved_key_checker_class' => AwsCloudReservedKeyChecker::class,
          'value_converter_class' => AwsCloudValueConverter::class,
        ],
      ])
      ->addConstraint('tags_data');

    return $fields;
  }

}
