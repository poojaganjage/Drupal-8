<?php

declare(strict_types = 1);

namespace Drupal\cmis\Plugin\Field\FieldWidget;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'cmis_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "cmis_field_widget",
 *   label = @Translation("Cmis field widget"),
 *   field_types = {
 *     "cmis_field"
 *   }
 * )
 */
class CmisFieldWidget extends WidgetBase {

  private $cmisConfigurations = [];

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
      'cmis_configuration' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    if (empty($this->cmisConfigurations)) {
      $this->getConfigurations();
    }
    $elements['cmis_configuration'] = [
      '#type' => 'select',
      '#title' => $this->t('CMIS configuration'),
      '#description' => $this->t('Please choose one from CMIS configuration.'),
      '#options' => $this->cmisConfigurations,
      '#require' => TRUE,
      '#default_value' => $this->getSetting('cmis_configuration'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    if (empty($this->cmisConfigurations)) {
      $this->getConfigurations();
    }
    $summary = [];

    $summary[] = $this->t('Textfield size: !size', ['!size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = $this->t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }
    $cmis_configuration = $this->getSetting('cmis_configuration');
    if (!empty($cmis_configuration)) {
      $summary[] = $this->t('CMIS configuration: @cmis_configuration', ['@cmis_configuration' => $this->cmisConfigurations[$cmis_configuration]]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $title = isset($items[$delta]->title) ? $items[$delta]->title : NULL;
    $path = isset($items[$delta]->path) ? $items[$delta]->path : NULL;

    $element = [
      '#prefix' => '<div id="cmis-field-wrapper">',
      '#suffix' => '</div>',
    ];

    $element['title'] = [
      '#type' => 'textfield',
      '#default_value' => $title,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#attributes' => [
        'class' => ['edit-field-cmis-field'],
      ],
    ];

    $element['path'] = [
      '#type' => 'hidden',
      '#default_value' => $path,
      '#attributes' => [
        'class' => ['edit-field-cmis-path'],
      ],
    ];

    $url = Url::fromUserInput('/cmis/browser/' . $this->getSetting('cmis_configuration'));
    $link_options = [
      'attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'height' => 400,
          'width' => 700,
        ]),
      ],
      'query' => ['type' => 'popup'],
    ];
    $url->setOptions($link_options);
    $element['cmis_browser'] = Link::fromTextAndUrl($this->t('Browse'), $url)->toRenderable();
    $element['#attached']['library'][] = 'cmis/cmis-field';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      if (!empty($item['path'])) {
        $args = explode('/', $item['path']);
        $id = end($args);
        $item['path'] = '/cmis/document/' . $this->getSetting('cmis_configuration') . '/' . $id;
      }
    }

    return $values;
  }

  /**
   * Get configuration entity to private variable.
   *
   * @return mixed
   */
  private function getConfigurations() {
    $this->cmisConfigurations = cmis_get_configurations();
  }

}
