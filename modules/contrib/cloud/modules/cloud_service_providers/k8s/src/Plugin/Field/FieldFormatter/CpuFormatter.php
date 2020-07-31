<?php

namespace Drupal\k8s\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'cpu_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "cpu_formatter",
 *   label = @Translation("CPU formatter"),
 *   field_types = {
 *     "float",
 *   }
 * )
 */
class CpuFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the cpu.');
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
        $cpu_str = round($item->value, 2);
        if ($show_percentage) {
          $capacity = $entity->getCpuCapacity();
          $percentage = round($item->value / $capacity * 100, 2);
          $cpu_str = "$cpu_str ($percentage%)";
        }
        $elements[$delta] = ['#markup' => $cpu_str];
      }
    }

    return $elements;
  }

}
