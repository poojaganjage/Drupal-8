<?php

namespace Drupal\cloud\Service;

/**
 * Interface AnsiStringRendererInterface.
 */
interface AnsiStringRendererInterface {

  /**
   * Render.
   *
   * @param string $value
   *   The value.
   *
   * @return array
   *   The build array of ansi string element.
   */
  public function render($value) : array;

}
