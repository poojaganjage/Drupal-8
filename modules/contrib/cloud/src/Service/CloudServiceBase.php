<?php

namespace Drupal\cloud\Service;

use Drupal\cloud\Traits\CloudContentEntityTrait;

/**
 * Provides a base class for Cloud plugin.
 */
class CloudServiceBase implements CloudServiceBaseInterface {

  use CloudContentEntityTrait;

  /**
   * The CloudService constructor.
   */
  public function __construct() {

    $this->messenger();
  }

}
