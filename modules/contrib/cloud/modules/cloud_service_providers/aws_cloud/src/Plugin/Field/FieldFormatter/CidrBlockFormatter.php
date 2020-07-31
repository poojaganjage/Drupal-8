<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'cidr_block_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "cidr_block_formatter",
 *   label = @Translation("CIDR block formatter"),
 *   field_types = {
 *     "cidr_block"
 *   }
 * )
 */
class CidrBlockFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $rows = [];

    foreach ($items ?: [] as $item) {
      if (!$item->isEmpty()) {
        $rows[] = [
          $item->cidr,
          $item->state,
          $item->status_message,
          $item->association_id,
        ];
      }
    }

    if (count($rows)) {
      $elements[0] = [
        '#theme' => 'table',
        '#header' => [
          $this->t('CIDR'),
          $this->t('Status'),
          $this->t('Status reason'),
          $this->t('Association ID'),
        ],
        '#rows' => $rows,
      ];
    }

    return $elements;
  }

}
