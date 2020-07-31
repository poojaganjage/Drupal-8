<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'cidr_block_simple_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "cidr_block_simple_formatter",
 *   label = @Translation("CIDR block simple formatter"),
 *   field_types = {
 *     "cidr_block"
 *   }
 * )
 */
class CidrBlockSimpleFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $cidrs = [];

    foreach ($items ?: [] as $item) {
      if (!$item->isEmpty()) {
        $cidrs[] = $item->cidr;
      }
    }

    $elements[] = [
      '#markup' => implode(', ', $cidrs),
    ];

    return $elements;
  }

}
