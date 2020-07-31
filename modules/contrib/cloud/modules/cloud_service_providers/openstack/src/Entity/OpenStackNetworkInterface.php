<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\NetworkInterface;
use Drupal\aws_cloud\Entity\Ec2\NetworkInterfaceInterface;

/**
 * Defines the OpenStack NetworkInterface entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_network_interface",
 *   id_plural = "openstack_network_interfaces",
 *   label = @Translation("Network Interface"),
 *   label_collection = @Translation("Network Interfaces"),
 *   label_singular = @Translation("Network Interface"),
 *   label_plural = @Translation("Network Interfaces"),
 *   handlers = {
 *     "view_builder" = "Drupal\openstack\Entity\OpenStackNetworkInterfaceViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\aws_cloud\Entity\Ec2\NetworkInterfaceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\openstack\Form\OpenStackNetworkInterfaceEditForm",
 *       "add" = "Drupal\openstack\Form\OpenStackNetworkInterfaceCreateForm",
 *       "edit" = "Drupal\openstack\Form\OpenStackNetworkInterfaceEditForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackNetworkInterfaceDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackNetworkInterfaceDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\NetworkInterfaceAccessControlHandler",
 *   },
 *   base_table = "openstack_network_interface",
 *   admin_permission = "administer openstack network interface",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/clouds/openstack/{cloud_context}/network_interface/{openstack_network_interface}",
 *     "edit-form" = "/clouds/openstack/{cloud_context}/network_interface/{openstack_network_interface}/edit",
 *     "delete-form" = "/clouds/openstack/{cloud_context}/network_interface/{openstack_network_interface}/delete",
 *     "collection" = "/clouds/openstack/{cloud_context}/network_interface",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/network_interface/delete_multiple",
 *   },
 *   field_ui_base_route = "openstack_network_interface.settings"
 * )
 */
class OpenStackNetworkInterface extends NetworkInterface implements NetworkInterfaceInterface {

}
