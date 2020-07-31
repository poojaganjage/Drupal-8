<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Plugin\Field\Util\AwsCloudReservedKeyChecker;
use Drupal\aws_cloud\Plugin\Field\Util\AwsCloudValueConverter;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Service\Util\EntityLinkWithNameHtmlGenerator;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the VPC entity.
 *
 * @ingroup aws_cloud
 *
 * @ContentEntityType(
 *   id = "aws_cloud_subnet",
 *   id_plural = "aws_cloud_subnets",
 *   label = @Translation("Subnet"),
 *   label_collection = @Translation("Subnets"),
 *   label_singular = @Translation("Subnet"),
 *   label_plural = @Translation("Subnets"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Vpc\SubnetViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\aws_cloud\Entity\Vpc\SubnetViewsData",
 *     "form" = {
 *       "default"                 = "Drupal\aws_cloud\Form\Vpc\SubnetEditForm",
 *       "add"                     = "Drupal\aws_cloud\Form\Vpc\SubnetCreateForm",
 *       "edit"                    = "Drupal\aws_cloud\Form\Vpc\SubnetEditForm",
 *       "delete"                  = "Drupal\aws_cloud\Form\Vpc\SubnetDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\aws_cloud\Form\Vpc\SubnetDeleteMultipleForm",
 *     },
 *     "access"       = "Drupal\aws_cloud\Controller\Vpc\SubnetAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "aws_cloud_subnet",
 *   admin_permission = "administer aws cloud subnet",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id"  ,
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical"            = "/clouds/aws_cloud/{cloud_context}/subnet/{aws_cloud_subnet}",
 *     "edit-form"            = "/clouds/aws_cloud/{cloud_context}/subnet/{aws_cloud_subnet}/edit",
 *     "delete-form"          = "/clouds/aws_cloud/{cloud_context}/subnet/{aws_cloud_subnet}/delete",
 *     "collection"           = "/clouds/aws_cloud/{cloud_context}/subnet",
 *     "delete-multiple-form" = "/clouds/aws_cloud/{cloud_context}/subnet/delete_multiple",
 *   },
 *   field_ui_base_route = "aws_cloud_subnet.settings"
 * )
 */
class Subnet extends CloudContentEntityBase implements SubnetInterface {

  public const TAG_CREATED_BY_UID = 'aws_cloud_subnet_created_by_uid';

  /**
   * {@inheritdoc}
   */
  public function getSubnetId() {
    return $this->get('subnet_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubnetId($subnet_id = '') {
    return $this->set('subnet_id', $subnet_id);
  }

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

    $fields['subnet_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subnet ID'))
      ->setDescription(t('The Subnet ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['vpc_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VPC'))
      ->setDescription(t('The VPC Name or ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_link',
        'settings' => [
          'target_type' => 'aws_cloud_vpc',
          'field_name' => 'vpc_id',
          'html_generator_class' => EntityLinkWithNameHtmlGenerator::class,
        ],
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

    $fields['state'] = BaseFieldDefinition::create('string')
      ->setLabel(t('State'))
      ->setDescription(t('The current state of the VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
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
      ->setDescription(t('The user ID of the Ec2ServiceSubnet entity author.'))
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
