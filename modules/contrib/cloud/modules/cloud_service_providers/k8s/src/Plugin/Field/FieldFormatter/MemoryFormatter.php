<?php

namespace Drupal\k8s\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'memory_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "memory_formatter",
 *   label = @Translation("Memory formatter"),
 *   field_types = {
 *     "float",
 *   }
 * )
 */
class MemoryFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the memory.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'show_percentage' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['show_percentage'] = [
      '#title' => $this->t('Show percentage'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('show_percentage'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $show_percentage = $this->getSetting('show_percentage');
    $entity = $items->getEntity();
    foreach ($items ?: [] as $delta => $item) {
      if (!$item->isEmpty()) {
        $memory_str = k8s_format_memory($item->value);
        if ($show_percentage) {
          $capacity = $entity->getMemoryCapacity();
          $percentage = round($item->value / $capacity * 100, 2);
          $memory_str = "$memory_str ($percentage%)";
        }
        $elements[$delta] = ['#markup' => $memory_str];
      }
    }

    return $elements;
  }

}
