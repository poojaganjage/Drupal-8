<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the cloud project type entity.
 *
 * @ConfigEntityType(
 *   id = "cloud_project_type",
 *   id_plural = "cloud_project_types",
 *   label = @Translation("Cloud Project Type"),
 *   label_collection = @Translation("Cloud Project Types"),
 *   label_singular = @Translation("cloud project type"),
 *   label_plural = @Translation("cloud project types"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cloud\Entity\CloudProjectTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cloud\Form\CloudProjectTypeForm",
 *       "edit" = "Drupal\cloud\Form\CloudProjectTypeForm",
 *       "delete" = "Drupal\cloud\Form\CloudProjectTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cloud\Routing\CloudProjectTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cloud_project_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "cloud_project",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/cloud_project_type/{cloud_project_type}",
 *     "add-form" = "/admin/structure/cloud_project_type/add",
 *     "edit-form" = "/admin/structure/cloud_project_type/{cloud_project_type}/edit",
 *     "delete-form" = "/admin/structure/cloud_project_type/{cloud_project_type}/delete",
 *     "collection" = "/admin/structure/cloud_project_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class CloudProjectType extends ConfigEntityBundleBase implements CloudProjectTypeInterface {

  /**
   * The cloud project type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The cloud project type label.
   *
   * @var string
   */
  protected $label;

}
