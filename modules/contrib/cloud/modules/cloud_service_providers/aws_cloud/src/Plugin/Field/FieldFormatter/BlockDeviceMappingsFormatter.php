<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'block_device_mappings_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "block_device_mappings_formatter",
 *   label = @Translation("Block Device Mappings formatter"),
 *   field_types = {
 *     "block_device_mappings"
 *   }
 * )
 */
class BlockDeviceMappingsFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $rows = [];

    foreach ($items ?: [] as $item) {
      if (!$item->isEmpty()) {
        $rows[] = [
          $item->device_name,
          $item->virtual_name,
          $item->delete_on_termination,
          $item->snapshot_id,
          $item->volume_size,
          $item->volume_type,
          $item->encrypted,
        ];
      }
    }

    if (count($rows)) {
      $elements[0] = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Device Name'),
          $this->t('Virtual Name'),
          $this->t('Delete On Termination'),
          $this->t('Snapshot ID'),
          $this->t('Volume Size'),
          $this->t('Volume Type'),
          $this->t('Encrypted'),
        ],
        '#rows' => $rows,
      ];
    }

    return $elements;
  }

}
