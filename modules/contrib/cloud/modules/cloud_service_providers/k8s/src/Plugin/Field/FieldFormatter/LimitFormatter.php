<?php

namespace Drupal\k8s\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'limit_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "limit_formatter",
 *   label = @Translation("Limit formatter"),
 *   field_types = {
 *     "limit"
 *   }
 * )
 */
class LimitFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $rows = [];

    foreach ($items ?: [] as $item) {
      /* @var \Drupal\k8s\Plugin\Field\FieldType\Limit $item */
      if (!$item->isEmpty()) {
        $rows[] = [
          $item->limit_type,
          $item->resource,
          $item->min,
          $item->max,
          $item->default,
          $item->default_request,
          $item->max_limit_request_ratio,
        ];
      }
    }

    if (count($rows)) {
      $elements[0] = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Type'),
          $this->t('Resource'),
          $this->t('Min'),
          $this->t('Max'),
          $this->t('Default'),
          $this->t('Default Request'),
          $this->t('Max Limit Request Ratio'),
        ],
        '#rows' => $rows,
      ];
    }

    return $elements;
  }

}
