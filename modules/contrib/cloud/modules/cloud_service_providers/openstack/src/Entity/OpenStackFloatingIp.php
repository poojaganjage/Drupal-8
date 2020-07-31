<?php

namespace Drupal\openstack\Entity;

use Drupal\cloud\Service\Util\EntityLinkWithNameHtmlGenerator;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\aws_cloud\Entity\Ec2\ElasticIp;

/**
 * Defines the Floating IP entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_floating_ip",
 *   id_plural = "openstack_floating_ips",
 *   label = @Translation("Floating IP"),
 *   label_collection = @Translation("Floating IPs"),
 *   label_singular = @Translation("Floating IP"),
 *   label_plural = @Translation("Floating IPs"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Ec2\ElasticIpViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\openstack\Entity\OpenStackFloatingIpViewsData",
 *     "form" = {
 *       "add" = "Drupal\openstack\Form\OpenStackFloatingIpCreateForm",
 *       "default" = "Drupal\openstack\Form\OpenStackFloatingIpEditForm",
 *       "edit" = "Drupal\openstack\Form\OpenStackFloatingIpEditForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackFloatingIpDeleteForm",
 *       "associate" = "Drupal\openstack\Form\OpenStackFloatingIpAssociateForm",
 *       "disassociate" = "Drupal\openstack\Form\OpenStackFloatingIpDisassociateForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackFloatingIpDeleteMultipleForm",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\ElasticIpAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "openstack_floating_ip",
 *   admin_permission = "administer openstack floating ip",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id"  ,
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical"            = "/clouds/openstack/{cloud_context}/floating_ip/{openstack_floating_ip}",
 *     "edit-form"            = "/clouds/openstack/{cloud_context}/floating_ip/{openstack_floating_ip}/edit",
 *     "delete-form"          = "/clouds/openstack/{cloud_context}/floating_ip/{openstack_floating_ip}/delete",
 *     "collection"           = "/clouds/openstack/{cloud_context}/floating_ip",
 *     "associate-form"       = "/clouds/openstack/{cloud_context}/floating_ip/{openstack_floating_ip}/associate",
 *     "disassociate-form"    = "/clouds/openstack/{cloud_context}/floating_ip/{openstack_floating_ip}/disassociate",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/floating_ip/delete_multiple",
 *   },
 *   field_ui_base_route = "openstack_floating_ip.settings"
 * )
 */
class OpenStackFloatingIp extends ElasticIp {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['instance_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Instance ID'))
      ->setDescription(t('The instance the @label address is associated with, if applicable.', [
        '@label' => $entity_type->getLabel(),
      ]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_link',
        'settings' => [
          'target_type' => 'openstack_instance',
          'field_name' => 'instance_id',
          'html_generator_class' => EntityLinkWithNameHtmlGenerator::class,
        ],
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    return $fields;
  }

}
