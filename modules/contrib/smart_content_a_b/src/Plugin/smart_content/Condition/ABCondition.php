<?php

namespace Drupal\smart_content_a_b\Plugin\smart_content\Condition;

use Drupal\smart_content\Condition\ConditionTypeConfigurableBase;
use Drupal\smart_content_a_b\Entity\SegmentSetAB;

/**
 * Provides a Demandbase condition plugin.
 *
 * @SmartCondition(
 *   id = "smart_content_a_b",
 *   label = @Translation("A/B"),
 *   group = "smart_content_a_b",
 *   type = "select",
 *   unique = true,
 *   deriver = "Drupal\smart_content_a_b\Plugin\smart_content\Condition\Derivative\ABConditionDeriver"
 * )
 */
class ABCondition extends ConditionTypeConfigurableBase {

  /**
   * {@inheritdoc}
   */
  public function getAttachedSettings() {
    $settings = parent::getAttachedSettings();

    list($plugin_id, $entity_id) = explode(':', $this->getPluginId());
    if ($entity_id) {
      if ($entity = SegmentSetAB::load($entity_id)) {

        $count = count($entity->getSegmentSet()->getSegments());
        $settings['field']['settings']['test'] = [
          'id' => $entity_id,
          'count' => $count
        ];
      }
    }
    return $settings;
  }
  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    $libraries = array_unique(array_merge(parent::getLibraries(), ['smart_content_a_b/smart_content_a_b']));
    return $libraries;
  }

}
