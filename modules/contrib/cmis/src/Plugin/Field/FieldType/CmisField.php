<?php

/**
 * Provides cmis module Implementation.
 *
 * @category Module
 *
 * @package Contrib
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "GIT: <1001>"
 *
 * @link https://www.drupal.org/
 */

declare(strict_types = 1);

namespace Drupal\cmis\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'cmis_field' field type.
 *
 * @FieldType(
 *   id = "cmis_field",
 *   label = @Translation("Cmis Field"),
 *   description = @Translation("Attach a CMIS object to a Drupal entity"),
 *   default_widget = "cmis_field_widget",
 *   default_formatter = "cmis_field_link"
 * )
 */

/**
 * Class CmisField.
 *
 * @category Module
 *
 * @package Drupal\cmis\Plugin\Field\FieldType
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "Release: 8"
 *
 * @link https://www.drupal.org/
 */
class CmisField extends FieldItemBase
{

    /**
     * Defines schema.
     *
     * @param FieldStorageDefinitionInterface $field_definition The field definition.
     *
     * @return array
     *   The array.
     */
    public static function schema(FieldStorageDefinitionInterface $field_definition)
    {
        return [
        'columns' => [
        'title' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'path' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        ],
        ];
    }

    /**
     * Defines isEmpty.
     *
     * @return array
     *   The array.
     */
    public function isEmpty()
    {
        $value1 = $this->get('title')->getValue();
        $value2 = $this->get('path')->getValue();
        return empty($value1) && empty($value2);
    }

    /**
     * Defines propertyDefinitions.
     *
     * @param FieldStorageDefinitionInterface $field_definition The field definition.
     *
     * @return array
     *   The array.
     */
    public static function propertyDefinitions(FieldStorageDefinitionInterface 
        $field_definition
    ) {
        // Add our properties.
        $properties['title'] = DataDefinition::create('string')
            ->setLabel(t('Title'))
            ->setDescription(t('The title of CMIS object.'));

        $properties['path'] = DataDefinition::create('string')
            ->setLabel(t('Path'))
            ->setDescription(t('The path of CMIS object.'));

        return $properties;
    }

}
