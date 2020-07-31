<?php

namespace Drupal\cloud_budget\Entity;

/**
 * Defines the Cloud Resource Storage entity.
 *
 * @ingroup cloud_budget
 *
 * @ContentEntityType(
 *   id = "cloud_resource_storage",
 *   id_plural = "cloud_resource_storages",
 *   label = @Translation("Cloud Resource Storage"),
 *   label_collection = @Translation("Cloud Resource Storages"),
 *   label_singular = @Translation("Cloud Resource Storage"),
 *   label_plural = @Translation("Cloud Resource Storages"),
 *   handlers = {
 *     "view_builder" = "Drupal\cloud_budget\Entity\CloudResourceStorageViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\cloud_budget\Entity\CloudResourceStorageViewsData",
 *     "access"       = "Drupal\cloud_budget\Controller\CloudCostStorageAccessControlHandler",
 *   },
 *   base_table = "cloud_resource_storage",
 *   entity_keys = {
 *     "id"    = "id",
 *     "uuid"  = "uuid",
 *   },
 *   field_ui_base_route = "cloud_resource_storage.settings"
 * )
 */
class CloudResourceStorage extends CloudCostStorage implements CloudResourceStorageInterface {
}
