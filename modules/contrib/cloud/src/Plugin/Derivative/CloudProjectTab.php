<?php

namespace Drupal\cloud\Plugin\Derivative;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides plugin definitions for custom local task.
 */
class CloudProjectTab extends LocalTaskDefault {

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    $parameters = parent::getRouteParameters($route_match);
    $template = \Drupal::entityTypeManager()->getStorage('cloud_project')->load($parameters['cloud_project']);
    $parameters['cloud_context'] = !empty($template) ? $template->getCloudContext() : '';
    return $parameters;
  }

}
