<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the cloud service provider type (CloudConfigType) entity.
 *
 * @ConfigEntityType(
 *   id = "cloud_config_type",
 *   id_plural = "cloud_config_types",
 *   label = @Translation("Cloud Service Provider Type"),
 *   label_collection = @Translation("Cloud Service Provider Types"),
 *   label_singular = @Translation("cloud service provider type"),
 *   label_plural = @Translation("cloud service provider types"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudConfigTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cloud\Form\CloudConfigTypeForm",
 *       "edit" = "Drupal\cloud\Form\CloudConfigTypeForm",
 *       "delete" = "Drupal\cloud\Form\CloudConfigTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cloud\Routing\CloudConfigTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cloud_config_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "cloud_config",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/cloud_config_type/{cloud_config_type}",
 *     "add-form" = "/admin/structure/cloud_config_type/add",
 *     "edit-form" = "/admin/structure/cloud_config_type/{cloud_config_type}/edit",
 *     "delete-form" = "/admin/structure/cloud_config_type/{cloud_config_type}/delete",
 *     "collection" = "/admin/structure/cloud_config_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class CloudConfigType extends ConfigEntityBundleBase implements CloudConfigTypeInterface {

  /**
   * The cloud service provider type (CloudConfigType) ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The cloud service provider type (CloudConfigType) label.
   *
   * @var string
   */
  protected $label;

}
