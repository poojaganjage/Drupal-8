<?php

namespace Drupal\cloud_budget\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'credit_list_formatter' formatter.
 *
 * This formatter links a cloud service provider name to the list of
 * cloud credits.
 *
 * @FieldFormatter(
 *   id = "credit_list_formatter",
 *   label = @Translation("Cloud Credit List"),
 *   field_types = {
 *     "string",
 *     "uri",
 *   }
 * )
 */
class CloudCreditListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entity = $items->getEntity();
    foreach ($items ?: [] as $delta => $item) {
      if (!$item->isEmpty()) {
        $label = $item->value;
        if (empty($label)) {
          $label = $item->entity->label();
        }

        $elements[$delta] = [
          '#type' => 'link',
          '#url' => Url::fromRoute('view.cloud_credit.list', ['cloud_context' => $entity->getCloudContext()]),
          '#title' => $label,
        ];
      }
    }
    return $elements;
  }

}
