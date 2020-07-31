<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\aws_cloud\Plugin\Field\Util\AwsCloudReservedKeyChecker;
use Drupal\aws_cloud\Plugin\Field\Util\AwsCloudValueConverter;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the VPC Peering Connection entity.
 *
 * @ingroup aws_cloud
 *
 * @ContentEntityType(
 *   id = "aws_cloud_vpc_peering_connection",
 *   id_plural = "aws_cloud_vpc_peering_connections",
 *   label = @Translation("VPC Peering Connection"),
 *   label_collection = @Translation("VPC Peering Connections"),
 *   label_singular = @Translation("VPC Peering Connection"),
 *   label_plural = @Translation("VPC Peering Connections"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnectionViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnectionViewsData",
 *     "form" = {
 *       "default"                 = "Drupal\aws_cloud\Form\Vpc\VpcPeeringConnectionEditForm",
 *       "add"                     = "Drupal\aws_cloud\Form\Vpc\VpcPeeringConnectionCreateForm",
 *       "edit"                    = "Drupal\aws_cloud\Form\Vpc\VpcPeeringConnectionEditForm",
 *       "delete"                  = "Drupal\aws_cloud\Form\Vpc\VpcPeeringConnectionDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\aws_cloud\Form\Vpc\VpcPeeringConnectionDeleteMultipleForm",
 *       "accept"                  = "Drupal\aws_cloud\Form\Vpc\VpcPeeringConnectionAcceptForm",
 *     },
 *     "access"       = "Drupal\aws_cloud\Controller\Vpc\VpcPeeringConnectionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "aws_cloud_vpc_peering_connection",
 *   admin_permission = "administer aws cloud vpc peering connection",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id"  ,
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical"            = "/clouds/aws_cloud/{cloud_context}/vpc_peering_connection/{aws_cloud_vpc_peering_connection}",
 *     "edit-form"            = "/clouds/aws_cloud/{cloud_context}/vpc_peering_connection/{aws_cloud_vpc_peering_connection}/edit",
 *     "delete-form"          = "/clouds/aws_cloud/{cloud_context}/vpc_peering_connection/{aws_cloud_vpc_peering_connection}/delete",
 *     "accept-form"          = "/clouds/aws_cloud/{cloud_context}/vpc_peering_connection/{aws_cloud_vpc_peering_connection}/accept",
 *     "collection"           = "/clouds/aws_cloud/{cloud_context}/vpc_peering_connection",
 *     "delete-multiple-form" = "/clouds/aws_cloud/{cloud_context}/vpc_peering_connection/delete_multiple",
 *   },
 *   field_ui_base_route = "aws_cloud_vpc_peering_connection.settings"
 * )
 */
class VpcPeeringConnection extends CloudContentEntityBase implements VpcPeeringConnectionInterface {

  public const TAG_CREATED_BY_UID = 'aws_cloud_vpc_peering_connection_created_by_uid';

  /**
   * {@inheritdoc}
   */
  public function getVpcPeeringConnectionId() {
    return $this->get('vpc_peering_connection_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVpcPeeringConnectionId($vpc_peering_connection_id = '') {
    return $this->set('vpc_peering_connection_id', $vpc_peering_connection_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequesterAccountId() {
    return $this->get('requester_account_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequesterAccountId($requester_account_id = '') {
    return $this->set('requester_account_id', $requester_account_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequesterVpcId() {
    return $this->get('requester_vpc_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequesterVpcId($requester_vpc_id = '') {
    return $this->set('requester_vpc_id', $requester_vpc_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequesterCidrBlock() {
    return $this->get('requester_cidr_block')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequesterCidrBlock($requester_cidr_block) {
    return $this->set('requester_cidr_block', $requester_cidr_block);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequesterRegion() {
    return $this->get('requester_region')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequesterRegion($requester_region) {
    return $this->set('requester_region', $requester_region);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccepterAccountId() {
    return $this->get('accepter_account_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccepterAccountId($accepter_account_id = '') {
    return $this->set('accepter_account_id', $accepter_account_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccepterVpcId() {
    return $this->get('accepter_vpc_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccepterVpcId($accepter_vpc_id = '') {
    return $this->set('accepter_vpc_id', $accepter_vpc_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccepterCidrBlock() {
    return $this->get('accepter_cidr_block')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccepterCidrBlock($accepter_cidr_block) {
    return $this->set('accepter_cidr_block', $accepter_cidr_block);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccepterRegion() {
    return $this->get('accepter_region')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccepterRegion($accepter_region) {
    return $this->set('accepter_region', $accepter_region);
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusCode() {
    return $this->get('status_code')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusCode($status_code) {
    return $this->set('status_code', $status_code);
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusMessage() {
    return $this->get('status_message')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusMessage($status_message) {
    return $this->set('status_message', $status_message);
  }

  /**
   * {@inheritdoc}
   */
  public function getExpirationTime() {
    return $this->get('expiration_time')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setExpirationTime($expiration_time) {
    return $this->set('expiration_time', $expiration_time);
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
      ->setDescription(t('The ID of the VpcPeeringConnection entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the VpcPeeringConnection entity.'))
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
      ->setDescription(t('The name of the VpcPeeringConnection entity.'))
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
      ->setDescription(t('Description of VpcPeeringConnection.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['vpc_peering_connection_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VPC Peering Connection ID'))
      ->setDescription(t('The VPC Peering Connection ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['requester_vpc_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Requester VPC ID'))
      ->setDescription(t('The Requester VPC ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['requester_cidr_block'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Requester CIDR Block'))
      ->setDescription(t('Information about the CIDR block associated with the Requester VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['requester_account_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Requester AWS Account ID'))
      ->setDescription(t('The Requester AWS account ID of the VPC owner, without dashes.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['requester_region'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Requester Region'))
      ->setDescription(t('The Requester Region.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['accepter_vpc_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Accepter VPC ID'))
      ->setDescription(t('The Accepter VPC ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['accepter_cidr_block'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Accepter CIDR Block'))
      ->setDescription(t('Information about the CIDR block associated with the Accepter VPC.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['accepter_account_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Accepter AWS Account ID'))
      ->setDescription(t('The Accepter AWS account ID of the VPC owner, without dashes.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['accepter_region'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Accepter Region'))
      ->setDescription(t('The Accepter Region.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['status_code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status Code'))
      ->setDescription(t('The current status code of the VPC Peering Connection.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['status_message'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status Message'))
      ->setDescription(t('The current status message of the VPC Peering Connection.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['expiration_time'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Expiration Time'))
      ->setDescription(t('The time that an unaccepted VPC peering connection will expire.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => -5,
        'settings' => [
          'date_format' => 'short',
        ],
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
