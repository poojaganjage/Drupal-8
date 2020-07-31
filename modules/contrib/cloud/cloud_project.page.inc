<?php

/**
 * @file
 * Contains cloud_project.page.inc.
 *
 * Page callback for cloud project entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for cloud project templates.
 *
 * Default template: cloud_project.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_cloud_project(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
