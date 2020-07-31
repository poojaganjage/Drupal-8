<?php

/**
 * @file
 * Contains openstack_image.page.inc.
 *
 * Page callback for OpenStack Image entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for OpenStack Image templates.
 *
 * Default template: openstack_image.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_openstack_image(array &$variables) {

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) ?: [] as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
