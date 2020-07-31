<?php

/**
 * @file
 * Contains cloud_server_template.page.inc.
 *
 * Page callback for cloud server template entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for cloud server template templates.
 *
 * Default template: cloud_server_template.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_cloud_server_template(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) ?: [] as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
