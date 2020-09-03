<?php

namespace Drupal\flood_unblock;

/**
 * Interface for FloodUnblockManager.
 */
interface FloodUnblockManagerInterface {

  /**
   * Generate rows from the entries in the flood table.
   *
   * @return array
   *   Entries of the flood table grouped by identifier (UID/IP).
   */
  public function getEntries();

  /**
   * The function that clear the flood.
   *
   * @param string $event
   *   The event variable.
   * @param int $identifier
   *   The identifier variable.
   *
   * @return string
   *   Returns the cleared entries from the flood table.
   */
  public function floodUnblockClearEvent($event, $identifier);

}