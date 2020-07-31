<?php

namespace Drupal\k8s\Service;

/**
 * Interface CostFieldsRendererInterface.
 */
interface CostFieldsRendererInterface {

  /**
   * Render cost fields.
   *
   * @param string $region
   *   The region.
   * @param array $instance_types
   *   The instance types.
   *
   * @return array
   *   The build array of cost fields.
   */
  public function render(
    $region,
    array $instance_types
  );

}
