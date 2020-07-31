<?php

namespace Drupal\terraform\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'aws_cloud_secret_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "aws_cloud_secret_formatter",
 *   label = @Translation("AWS Cloud secret formatter"),
 *   field_types = {
 *     "string",
 *   }
 * )
 */
class AwsCloudSecretFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();
    foreach ($items ?: [] as $delta => $item) {
      if (!$item->isEmpty()) {
        $value = $item->value;
        if ($entity->getEntityType()->id() === 'terraform_variable' && $entity->getAttributeKey() === 'AWS_SECRET_ACCESS_KEY') {
          $value = str_repeat('*', strlen($value));
        }
        $elements[$delta] = [
          '#type' => 'inline_template',
          '#template' => '{{ value|nl2br }}',
          '#context' => ['value' => $value],
        ];
      }
    }

    return $elements;
  }

}
