<?php

namespace Drupal\smart_content_a_b\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\smart_content\Entity\SegmentSetConfig;

/**
 * Defines the Segment Set A/B entity.
 *
 * @ConfigEntityType(
 *   id = "smart_content_a_b",
 *   label = @Translation("Segment Set A/B"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\smart_content_a_b\SegmentSetABListBuilder",
 *     "form" = {
 *       "add" = "Drupal\smart_content_a_b\Form\SegmentSetABForm",
 *       "edit" = "Drupal\smart_content_a_b\Form\SegmentSetABForm",
 *       "delete" = "Drupal\smart_content_a_b\Form\SegmentSetABDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\smart_content_a_b\SegmentSetABHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "smart_content_a_b.segment_set",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "settings"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/smart_content_a_b/{smart_content_a_b}",
 *     "add-form" = "/admin/structure/smart_content_a_b/add",
 *     "edit-form" = "/admin/structure/smart_content_a_b/{smart_content_a_b}/edit",
 *     "delete-form" = "/admin/structure/smart_content_a_b/{smart_content_a_b}/delete",
 *     "collection" = "/admin/structure/smart_content_a_b"
 *   }
 * )
 */
class SegmentSetAB extends SegmentSetConfig implements SegmentSetABInterface {

}
