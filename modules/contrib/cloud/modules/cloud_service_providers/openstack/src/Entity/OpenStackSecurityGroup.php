<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\aws_cloud\Entity\Ec2\SecurityGroupInterface;

/**
 * Defines the OpenStack Security Group entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_security_group",
 *   id_plural = "openstack_security_groups",
 *   label = @Translation("Security Group"),
 *   label_collection = @Translation("Security Groups"),
 *   label_singular = @Translation("Security Group"),
 *   label_plural = @Translation("Security Groups"),
 *   handlers = {
 *     "view_builder" = "Drupal\openstack\Entity\OpenStackSecurityGroupViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\openstack\Entity\OpenStackSecurityGroupViewsData",
 *     "form" = {
 *       "default" = "Drupal\openstack\Form\OpenStackSecurityGroupEditForm",
 *       "add" = "Drupal\openstack\Form\OpenStackSecurityGroupCreateForm",
 *       "edit" = "Drupal\openstack\Form\OpenStackSecurityGroupEditForm",
 *       "revoke" = "Drupal\openstack\Form\OpenStackSecurityGroupRevokeForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackSecurityGroupDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackSecurityGroupDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\SecurityGroupAccessControlHandler",
 *   },
 *   base_table = "openstack_security_group",
 *   admin_permission = "administer openstack cloud security group entities",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/clouds/openstack/{cloud_context}/security_group/{openstack_security_group}",
 *     "add-form" = "/admin/structure/openstack_security_group/add",
 *     "edit-form" = "/clouds/openstack/{cloud_context}/security_group/{openstack_security_group}/edit",
 *     "delete-form" = "/clouds/openstack/{cloud_context}/security_group/{openstack_security_group}/delete",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/security_group/delete_multiple",
 *     "revoke-form" = "/clouds/openstack/{cloud_context}/security_group/{openstack_security_group}/revoke",
 *     "collection" = "/clouds/openstack/{cloud_context}/security_group",
 *   },
 *   field_ui_base_route = "openstack_security_group.settings"
 * )
 */
class OpenStackSecurityGroup extends SecurityGroup implements SecurityGroupInterface {

}
