<?php

namespace Drupal\smart_content\Plugin\smart_content\SegmentSetStorage;

use Drupal\smart_content\Entity\SegmentSetConfig;
use Drupal\smart_content\SegmentSetStorage\SegmentSetStorageBase;

/**
 * Provides a 'segment_set' SegmentSetStorage.
 *
 * @SmartSegmentSetStorage(
 *  id = "global_segment_set",
 *  label = @Translation("Global Segment Sets"),
 *  global = true,
 *  deriver = "Drupal\smart_content\Plugin\smart_content\SegmentSetStorage\Derivative\GlobalSegmentSetDeriver"
 * )
 */
class GlobalSegmentSet extends SegmentSetStorageBase {

  /**
   * {@inheritdoc}
   */
  public function load() {
    list($plugin_id, $entity_id) = explode(':', $this->getPluginId());
    return SegmentSetConfig::load($entity_id)->getSegmentSet();
  }

}
