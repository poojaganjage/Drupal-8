<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the cloud server template type entity.
 *
 * @ConfigEntityType(
 *   id = "cloud_server_template_type",
 *   id_plural = "cloud_server_template_types",
 *   label = @Translation("Launch Template Type"),
 *   label_collection = @Translation("Launch Template Types"),
 *   label_singular = @Translation("launch template type"),
 *   label_plural = @Translation("launch template types"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cloud\Entity\CloudServerTemplateTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cloud\Form\CloudServerTemplateTypeForm",
 *       "edit" = "Drupal\cloud\Form\CloudServerTemplateTypeForm",
 *       "delete" = "Drupal\cloud\Form\CloudServerTemplateTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cloud\Routing\CloudServerTemplateTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cloud_server_template_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "cloud_server_template",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/cloud_server_template_type/{cloud_server_template_type}",
 *     "add-form" = "/admin/structure/cloud_server_template_type/add",
 *     "edit-form" = "/admin/structure/cloud_server_template_type/{cloud_server_template_type}/edit",
 *     "delete-form" = "/admin/structure/cloud_server_template_type/{cloud_server_template_type}/delete",
 *     "collection" = "/admin/structure/cloud_server_template_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class CloudServerTemplateType extends ConfigEntityBundleBase implements CloudServerTemplateTypeInterface {

  /**
   * The cloud server template type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The cloud server template type label.
   *
   * @var string
   */
  protected $label;

}
