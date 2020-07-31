<?php

/**
 * @file
 * Contains openstack_network_interface.page.inc.
 *
 * Page callback for OpenStack Network Interface entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for OpenStack Network Interface templates.
 *
 * Default template: openstack_network_interface.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_openstack_network_interface(array &$variables) {

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) ?: [] as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
