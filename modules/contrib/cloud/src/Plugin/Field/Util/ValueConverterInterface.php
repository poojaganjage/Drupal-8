<?php

namespace Drupal\cloud\Plugin\Field\Util;

/**
 * Value converter interface for key_value field type.
 */
interface ValueConverterInterface {

  /**
   * Convert value.
   *
   * @param string $key
   *   The key.
   * @param string $value
   *   The value.
   *
   * @return string
   *   The value converted.
   */
  public function convert($key, $value): string;

}
