<?php

/**
 * @file
 * Contains openstack_volume.page.inc.
 *
 * Page callback for OpenStack Volume entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for OpenStack Volume templates.
 *
 * Default template: openstack_volume.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_openstack_volume(array &$variables) {

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) ?: [] as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
