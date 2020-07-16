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
     * @param $type       The type variable.
     * @param $identifier The identifier variable.
     *
     * @return string
     */
    public function flood_unblock_clear_event($event, $identifier);
}
