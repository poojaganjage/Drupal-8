<?php

namespace Drupal\k8s\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'rule_item' widget.
 *
 * @FieldWidget(
 *   id = "rule_item",
 *   label = @Translation("K8s rule"),
 *   field_types = {
 *     "rule"
 *   }
 * )
 */
class RuleItem extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['verbs'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Verbs'),
      '#size' => 60,
      '#default_value' => $items[$delta]->verbs ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['resources'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resources'),
      '#size' => 60,
      '#default_value' => $items[$delta]->resources ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['api_groups'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Groups'),
      '#size' => 60,
      '#default_value' => $items[$delta]->api_groups ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    $element['resource_names'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resource Names'),
      '#size' => 60,
      '#default_value' => $items[$delta]->resource_names ?? NULL,
      '#maxlength' => 255,
      '#prefix' => '<div class="col-sm-6">',
      '#suffix' => '</div>',
    ];

    return $element;
  }

}
