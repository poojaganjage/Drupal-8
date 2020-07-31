<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\KeyPair;
use Drupal\aws_cloud\Entity\Ec2\KeyPairInterface;

/**
 * Defines the KeyPair entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_key_pair",
 *   id_plural = "openstack_key_pair",
 *   label = @Translation("Key Pair"),
 *   label_collection = @Translation("Key Pairs"),
 *   label_singular = @Translation("Key Pair"),
 *   label_plural = @Translation("Key Pairs"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Ec2\KeyPairViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\openstack\Entity\OpenStackKeyPairViewsData",
 *     "form" = {
 *       "default" = "Drupal\openstack\Form\OpenStackKeyPairEditForm",
 *       "add"     = "Drupal\openstack\Form\OpenStackKeyPairCreateForm",
 *       "edit" = "Drupal\aws_cloud\Form\Ec2\KeyPairEditForm",
 *       "import" = "Drupal\openstack\Form\OpenStackKeyPairImportForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackKeyPairDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackKeyPairDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\KeyPairAccessControlHandler",
 *   },
 *   base_table = "openstack_key_pair",
 *   admin_permission = "administer openstack key pair entities",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "key_pair_name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/clouds/openstack/{cloud_context}/key_pair/{openstack_key_pair}",
 *     "add-form" = "/admin/structure/openstack_key_pair/add",
 *     "edit-form" = "/clouds/openstack/{cloud_context}/key_pair/{openstack_key_pair}/edit",
 *     "delete-form" = "/clouds/openstack/{cloud_context}/key_pair/{openstack_key_pair}/delete",
 *     "collection" = "/clouds/openstack/{cloud_context}/key_pair",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/key_pair/delete_multiple",
 *   },
 *   field_ui_base_route = "openstack_key_pair.settings"
 * )
 */
class OpenStackKeyPair extends KeyPair implements KeyPairInterface {

}
