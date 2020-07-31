<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'cidr_block_item' widget.
 *
 * @FieldWidget(
 *   id = "cidr_block_item",
 *   label = @Translation("AWS CIDR Block"),
 *   field_types = {
 *     "cidr_block"
 *   }
 * )
 */
class CidrBlockItem extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $cidr = $items[$delta]->cidr ?? NULL;
    $state = $items[$delta]->state ?? NULL;
    $status_message = $items[$delta]->status_message ?? NULL;
    $association_id = $items[$delta]->association_id ?? NULL;

    $element['cidr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CIDR'),
      '#size' => 60,
      '#default_value' => $cidr,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['state'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Status'),
      '#size' => 60,
      '#default_value' => $state,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
      '#disabled' => TRUE,
    ];

    $element['status_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Status reason'),
      '#size' => 60,
      '#default_value' => $status_message,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
      '#disabled' => TRUE,
    ];

    $element['association_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Association ID'),
      '#size' => 60,
      '#default_value' => $association_id,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
      '#disabled' => TRUE,
    ];

    return $element;
  }

}
