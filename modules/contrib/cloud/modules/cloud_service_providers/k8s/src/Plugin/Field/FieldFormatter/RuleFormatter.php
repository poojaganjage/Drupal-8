<?php

namespace Drupal\k8s\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'rule_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "rule_formatter",
 *   label = @Translation("Rule formatter"),
 *   field_types = {
 *     "rule"
 *   }
 * )
 */
class RuleFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $rows = [];

    foreach ($items ?: [] as $item) {
      /* @var \Drupal\k8s\Plugin\Field\FieldType\Rule $item */
      if (!$item->isEmpty()) {
        $rows[] = [
          $item->resources,
          $item->resource_names,
          $item->api_groups,
          $item->verbs,
        ];
      }
    }

    if (count($rows)) {
      $elements[0] = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Resources'),
          $this->t('Resource Names'),
          $this->t('API Groups'),
          $this->t('Verbs'),
        ],
        '#rows' => $rows,
      ];
    }

    return $elements;
  }

}
