<?php

namespace Drupal\cloud\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'key_value_list_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "key_value_list_formatter",
 *   label = @Translation("Key value list formatter"),
 *   field_types = {
 *     "key_value"
 *   }
 * )
 */
class KeyValueListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $rows = [];

    foreach ($items ?: [] as $item) {
      /* @var \Drupal\cloud\Plugin\Field\FieldType\KeyValue $item */
      if (!$item->isEmpty()) {
        $rows[] = "{$item->item_key}:{$item->item_value}";
      }
    }

    if (count($rows)) {
      $elements[0] = [
        '#markup' => implode(', ', $rows),
      ];
    }

    return $elements;
  }

}
