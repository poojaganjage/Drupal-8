<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\Instance;
use Drupal\aws_cloud\Entity\Ec2\InstanceInterface;

/**
 * Defines the OpenStack Instance entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_instance",
 *   label = @Translation("Instance"),
 *   id_plural = "openstack_instances",
 *   label_collection = @Translation("Instances"),
 *   label_singular = @Translation("Instance"),
 *   label_plural = @Translation("Instances"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Ec2\InstanceViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data" = "Drupal\openstack\Entity\OpenStackInstanceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\openstack\Form\OpenStackInstanceEditForm",
 *       "add" = "Drupal\openstack\Form\OpenStackInstanceLaunchForm",
 *       "edit" = "Drupal\openstack\Form\OpenStackInstanceEditForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackInstanceDeleteForm",
 *       "start" = "Drupal\openstack\Form\OpenStackInstanceStartForm",
 *       "stop" = "Drupal\openstack\Form\OpenStackInstanceStopForm",
 *       "reboot" = "Drupal\openstack\Form\OpenStackInstanceRebootForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackInstanceDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\InstanceAccessControlHandler",
 *   },
 *   base_table = "openstack_instance",
 *   admin_permission = "administer openstack instance entities",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/clouds/openstack/{cloud_context}/instance/{openstack_instance}",
 *     "edit-form" = "/clouds/openstack/{cloud_context}/instance/{openstack_instance}/edit",
 *     "delete-form" = "/clouds/openstack/{cloud_context}/instance/{openstack_instance}/terminate",
 *     "collection" = "/clouds/openstack/{cloud_context}/instance",
 *     "start-form" = "/clouds/openstack/{cloud_context}/instance/{openstack_instance}/start",
 *     "stop-form" = "/clouds/openstack/{cloud_context}/instance/{openstack_instance}/stop",
 *     "reboot-form" = "/clouds/openstack/{cloud_context}/instance/{openstack_instance}/reboot",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/instance/delete_multiple",
 *   },
 *   field_ui_base_route = "openstack_instance.settings"
 * )
 */
class OpenStackInstance extends Instance implements InstanceInterface {

}
