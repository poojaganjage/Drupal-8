<?php

namespace Drupal\cloud\Plugin\Field\Util;

/**
 * Reserved key checker interface for key_value field type.
 */
interface ReservedKeyCheckerInterface {

  /**
   * Check whether the key is reserved word or not.
   *
   * @param string $key
   *   The key.
   *
   * @return bool
   *   The html of link.
   */
  public function isReservedWord($key): bool;

}
