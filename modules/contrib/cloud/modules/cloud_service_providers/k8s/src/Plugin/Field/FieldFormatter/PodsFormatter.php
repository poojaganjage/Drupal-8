<?php

namespace Drupal\k8s\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'pods_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "pods_formatter",
 *   label = @Translation("Pods formatter"),
 *   field_types = {
 *     "integer",
 *   }
 * )
 */
class PodsFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();
    foreach ($items ?: [] as $delta => $item) {
      if (!$item->isEmpty()) {
        $allocation = $item->value;
        $capacity = $entity->getPodsCapacity();
        $percentage = round((float) $allocation / $capacity * 100, 2);
        $elements[$delta] = ['#markup' => "$allocation/$capacity ($percentage%)"];
      }
    }

    return $elements;
  }

}
