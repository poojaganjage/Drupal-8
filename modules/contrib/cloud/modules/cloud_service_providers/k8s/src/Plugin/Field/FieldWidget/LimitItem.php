<?php

namespace Drupal\k8s\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'limit_item' widget.
 *
 * @FieldWidget(
 *   id = "limit_item",
 *   label = @Translation("K8s limit"),
 *   field_types = {
 *     "limit"
 *   }
 * )
 */
class LimitItem extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['limit_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Type'),
      '#size' => 60,
      '#default_value' => $items[$delta]->limit_type ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['resource'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resource'),
      '#size' => 60,
      '#default_value' => $items[$delta]->resource ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['default'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default'),
      '#size' => 60,
      '#default_value' => $items[$delta]->default ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['default_request'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Request'),
      '#size' => 60,
      '#default_value' => $items[$delta]->default_request ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['max'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max'),
      '#size' => 60,
      '#default_value' => $items[$delta]->max ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['min'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Min'),
      '#size' => 60,
      '#default_value' => $items[$delta]->min ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['max_limit_request_ratio'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max Limit Request Ratio'),
      '#size' => 60,
      '#default_value' => $items[$delta]->max_limit_request_ratio ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    return $element;
  }

}
