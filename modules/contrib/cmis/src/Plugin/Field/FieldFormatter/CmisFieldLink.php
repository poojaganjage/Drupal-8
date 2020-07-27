<?php

declare(strict_types = 1);

namespace Drupal\cmis\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'cmis_field_link' formatter.
 *
 * @FieldFormatter(
 *   id = "cmis_field_link",
 *   label = @Translation("Cmis field link"),
 *   field_types = {
 *     "cmis_field"
 *   }
 * )
 */
class CmisFieldLink extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return array
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $url = Url::fromUserInput($item->get('path')->getValue());
    if (empty($url)) {
      return [];
    }
    $path = Link::fromTextAndUrl($item->get('title')->getValue(), $url)->toRenderable();

    return $path;
  }

}
