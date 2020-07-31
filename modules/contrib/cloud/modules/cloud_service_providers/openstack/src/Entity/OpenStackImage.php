<?php

namespace Drupal\openstack\Entity;

use Drupal\aws_cloud\Entity\Ec2\Image;
use Drupal\aws_cloud\Entity\Ec2\ImageInterface;

/**
 * Defines the Image entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_image",
 *   id_plural = "openstack_images",
 *   label = @Translation("Image"),
 *   label_collection = @Translation("Images"),
 *   label_singular = @Translation("Image"),
 *   label_plural = @Translation("Images"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Ec2\ImageViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\openstack\Entity\OpenStackImageViewsData",
 *     "form" = {
 *       "add"     = "Drupal\openstack\Form\OpenStackImageCreateForm",
 *       "default" = "Drupal\openstack\Form\OpenStackImageEditForm",
 *       "edit" = "Drupal\openstack\Form\OpenStackImageEditForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackImageDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackImageDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\ImageAccessControlHandler",
 *   },
 *   base_table = "openstack_image",
 *   admin_permission = "administer openstack image",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "ami_name",
 *     "uuid"  = "uuid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/clouds/openstack/{cloud_context}/image/{openstack_image}",
 *     "edit-form" = "/clouds/openstack/{cloud_context}/image/{openstack_image}/edit",
 *     "delete-form" = "/clouds/openstack/{cloud_context}/image/{openstack_image}/delete",
 *     "collection" = "/clouds/openstack/{cloud_context}/image",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/image/delete_multiple",
 *   },
 *   field_ui_base_route = "openstack_image.settings"
 * )
 */
class OpenStackImage extends Image implements ImageInterface {

}
