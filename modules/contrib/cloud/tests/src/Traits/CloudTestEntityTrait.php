<?php

namespace Drupal\Tests\cloud\Traits;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Component\Utility\Random;

/**
 * The base test trait for common basic primitive methods.
 */
trait CloudTestEntityTrait {

  /**
   * Internal random number.
   *
   * @var int
   */
  private static $internalRandom;

  /**
   * Generates a random string for AWS long ID.
   *
   * The string is containing 32 length-letters and numbers.
   *
   * @return string
   *   32 length randomly generated string.
   *
   * @see https://www.drupal.org/project/cloud/issues/3025228
   */
  protected function getRandomId(): string {
    if (!isset(self::$internalRandom)) {
      self::$internalRandom = new Random();
    }

    return self::$internalRandom->name(32, TRUE);
  }

  /**
   * Helper function to create a test entity.
   *
   * @param string $class_name
   *   The class name.
   * @param array $params
   *   The form data.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The entity.
   */
  protected function createTestEntity($class_name, array $params): CloudContentEntityBase {

    $params['cloud_context'] = $params['cloud_context'] ?: $this->cloudContext;

    // If $param['name'] is missing, fill out $this->getRandomId().
    if (!array_key_exists('name', $params)
    || empty($params['name'])) {
      $params['name'] = $this->getRandomId();
    }

    $entity = $class_name::create($params);
    $entity->save();
    return $entity;
  }

}
