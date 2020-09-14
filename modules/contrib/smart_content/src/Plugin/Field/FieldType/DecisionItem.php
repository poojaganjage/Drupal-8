<?php

namespace Drupal\smart_content\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'decision' type.
 *
 * @internal
 *   Plugin classes are internal.
 *
 * @FieldType(
 *   id = "smart_content_decision",
 *   label = @Translation("Decision"),
 *   description = @Translation("Decision"),
 *   no_ui = TRUE,
 * )
 */
class DecisionItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['decision'] = DataDefinition::create('smart_content_decision')
      ->setLabel(new TranslatableMarkup('Decision Settings'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    // @todo \Drupal\Core\Field\FieldItemBase::__get() does not return default
    //   values for uninstantiated properties. This will forcibly instantiate
    //   all properties with the side-effect of a performance hit, resolve
    //   properly in https://www.drupal.org/node/2413471.
    $this->getProperties();

    return parent::__get($name);
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'decision';
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'decision' => [
          'type' => 'blob',
          'size' => 'normal',
          'serialize' => TRUE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->decision);
  }

}
