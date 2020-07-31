<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'block_device_mappings_item' widget.
 *
 * This plugin is not used at the moment.  Providing it so the
 * BlockDeviceMappings field is complete.
 *
 * @FieldWidget(
 *   id = "block_device_mappings_item",
 *   label = @Translation("AWS Block Device Mapping"),
 *   field_types = {
 *     "block_device_mappings"
 *   }
 * )
 */
class BlockDeviceMappingsItem extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $device_name = $items[$delta]->device_name ?? NULL;
    $delete_on_termination = $items[$delta]->delete_on_termination ?? NULL;
    $snapshot_id = $items[$delta]->snapshot_id ?? NULL;
    $volume_size = $items[$delta]->volume_size ?? NULL;
    $volume_type = $items[$delta]->volume_type ?? NULL;
    $encrypted = $items[$delta]->encrypted ?? NULL;

    $element['device_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Device Name'),
      '#size' => 60,
      '#default_value' => $device_name,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-2">',
      '#suffix' => '</div>',
    ];

    $element['delete_on_termination'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Delete On Termination'),
      '#size' => 60,
      '#default_value' => $delete_on_termination,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-2">',
      '#suffix' => '</div>',
    ];

    $element['snapshot_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Snapshot Id'),
      '#size' => 60,
      '#default_value' => $snapshot_id,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-2">',
      '#suffix' => '</div>',
    ];

    $element['volume_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Volume Size'),
      '#size' => 60,
      '#default_value' => $volume_size,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-2">',
      '#suffix' => '</div>',
    ];

    $element['volume_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Volume Type'),
      '#size' => 60,
      '#default_value' => $volume_type,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-2">',
      '#suffix' => '</div>',
    ];

    $element['encrypted'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Encrypted'),
      '#size' => 60,
      '#default_value' => $encrypted,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-2">',
      '#suffix' => '</div>',
    ];

    return $element;
  }

}
