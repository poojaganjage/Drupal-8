<?php

namespace Drupal\text_field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'text_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "text_field_formatter",
 *   label = @Translation("Text field formatter"),
 *   field_types = {
 *     "string",
 *   },
 *   edit = {
 *     "editor" = "form"
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class TextFieldFormatter extends StringFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'wrap_tag' => '_none',
      'wrap_class' => '',
      'wrap_attributes' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultWrapTagOptions() {
    $wrappers = [
      'div' => t('Div'),
      'h1' => t('H1'),
      'h2' => t('H2'),
      'h3' => t('H3'),
      'h4' => t('H4'),
      'h5' => t('H5'),
      'h6' => t('H6'),
      'span' => t('Span'),
    ];

    \Drupal::moduleHandler()->alter('default_wrap_tags', $wrappers);

    if (isset($wrappers['a'])) {
      unset($wrappers['a']);
      \Drupal::service('messenger')->addWarning(t('Tag "a" is not allowed here since it can conflict with other functional.'));
    }

    return $wrappers;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['wrap_tag'] = [
      '#title' => $this->t('Field wrapper'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('wrap_tag'),
      '#empty_option' => $this->t('- None -'),
      '#options' => $this->defaultWrapTagOptions(),
    ];

    $form['wrap_class'] = [
      '#title' => $this->t('Wrapper classes'),
      '#type' => 'textfield',
      '#maxlength' => 128,
      '#default_value' => $this->getSetting('wrap_class'),
      '#description' => $this->t('Separate multiple classes with space or comma. Works only with the selected
      wrapper tag.'),
    ];

    $form['wrap_attributes'] = [
      '#title' => $this->t('Wrapper attributes'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('wrap_attributes'),
      '#description' => $this->t('Set attributes for this wrapper. Enter one value per line,
      in the format attribute|value. The value is optional.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $wrap_tag = $this->getSetting('wrap_tag');
    if ('_none' == $wrap_tag) {
      $summary[] = $this->t('No wrap tag defined.');
    }
    else {
      $summary[] = $this->t('Wrap text with tag: @tag', ['@tag' => $wrap_tag]);
    }

    $class = $this->getSetting('wrap_class');
    $class = $this->prepareClasses($class);
    if ($class) {
      $summary[] = $this->formatPlural(count($class),
        $this->t('Wrapper additional CSS class: @class.', ['@class' => implode('', $class)]),
        $this->t('Wrapper additional CSS classes: @class.', ['@class' => implode(' ', $class)])
      );
    }
    else {
      $summary[] = $this->t('No additional CSS class defined.');
    }

    $attributes = $this->getSetting('wrap_attributes');
    $attributes = $this->prepareAttributes($attributes);
    $additional_attributes = '';

    if ($attributes) {
      foreach ($attributes as $attribute => $value) {
        if ($value) {
          $additional_attributes .= $attribute . '="' . $value . '" ';
        }
        else {
          $additional_attributes .= $attribute;
        }
      }
    }
    else {
      $additional_attributes = $this->t('No additional attributes defined.');
    }

    $summary[] = $this->t('Wrapper additional attributes:<br>@attributes', ['@attributes' => $additional_attributes]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $wrap_tag = $this->getSetting('wrap_tag');
    $class = $this->prepareClasses($this->getSetting('wrap_class'));
    $attributes = $this->prepareAttributes($this->getSetting('wrap_attributes'));

    foreach ($items as $delta => $item) {
      if ($wrap_tag !== '' && $elements[$delta]["#type"] == 'link') {
        $temp = $elements[$delta]["#title"]["#context"]["value"];
        $elements[$delta]["#title"]["#context"]["value"] = [
          '#type' => 'html_tag',
          '#tag' => $wrap_tag,
          '#value' => $temp,
          '#attributes' => [
            'class' => $class,
          ] + $attributes,
        ];
        unset($temp);
      }
      elseif ($wrap_tag !== '' && $elements[$delta]["#type"] != 'link') {
        $elements[$delta] = [
          '#type' => 'html_tag',
          '#tag' => $wrap_tag,
          '#value' => $item->value,
          '#attributes' => [
            'class' => $class,
          ] + $attributes,
        ];
      }
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
   *   The textual output generated as a render array.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return [
      '#type' => 'inline_template',
      '#template' => '{{ value }}',
      '#context' => ['value' => $item->value],
    ];
  }

  /**
   * Build classes.
   *
   * @param string $classes
   *   String of classes.
   *
   * @return array
   *   Return prepared list of classes.
   */
  public function prepareClasses(string $classes) {
    $classes = preg_replace('! !', ',', $classes);
    $classes = explode(',', $classes);
    $prepared = [];
    foreach ($classes as $class) {
      $class = trim($class);
      if ($class) {
        $prepared[] = Html::getClass($class);
      }
    }

    return $prepared;
  }

  /**
   * Build attributes.
   *
   * @param string $attributes
   *   String of attributes.
   *
   * @return array
   *   Return prepared list of attributes.
   */
  public function prepareAttributes(string $attributes) {
    $attributes = explode("\r\n", $attributes);
    $prepared = [];
    foreach ($attributes as $attribute) {
      $attribute = explode("|", $attribute);
      $prepared[$attribute[0]] = isset($attribute[1]) ? $attribute[1] : '';
    }

    return $prepared;
  }

}
