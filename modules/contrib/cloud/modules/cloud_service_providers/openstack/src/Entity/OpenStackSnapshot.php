<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\Snapshot;
use Drupal\aws_cloud\Entity\Ec2\SnapshotInterface;

/**
 * Defines the OpenStack Snapshot entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_snapshot",
 *   id_plural = "openstack_snapshots",
 *   label = @Translation("Snapshot"),
 *   label_collection = @Translation("Snapshots"),
 *   label_singular = @Translation("Snapshot"),
 *   label_plural = @Translation("Snapshots"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Ec2\SnapshotViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\openstack\Entity\OpenStackSnapshotViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\openstack\Form\OpenStackSnapshotEditForm",
 *       "add" = "Drupal\openstack\Form\OpenStackSnapshotCreateForm",
 *       "edit" = "Drupal\openstack\Form\OpenStackSnapshotEditForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackSnapshotDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackSnapshotDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\SnapshotAccessControlHandler",
 *   },
 *   base_table = "openstack_snapshot",
 *   admin_permission = "administer openstack snapshot entities",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id"  ,
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/clouds/openstack/{cloud_context}/snapshot/{openstack_snapshot}",
 *     "edit-form" = "/clouds/openstack/{cloud_context}/snapshot/{openstack_snapshot}/edit",
 *     "delete-form" = "/clouds/openstack/{cloud_context}/snapshot/{openstack_snapshot}/delete",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/snapshot/delete_multiple",
 *     "collection" = "/clouds/openstack/{cloud_context}/snapshot",
 *   },
 *   field_ui_base_route = "openstack_snapshot.settings"
 * )
 */
class OpenStackSnapshot extends Snapshot implements SnapshotInterface {

}
