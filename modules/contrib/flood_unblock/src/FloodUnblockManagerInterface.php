<?php

namespace Drupal\flood_unblock;

/**
 * Interface for FloodUnblockManager.
 */
interface FloodUnblockManagerInterface {

    /**
     * {@inheritdoc}
     */
    public function getEntries();

    /**
     * {@inheritdoc}
     */
    public function flood_unblock_clear_event($event, $identifier);
}
