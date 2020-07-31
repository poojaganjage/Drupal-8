<?php

/**
 * @file
 * Contains openstack_instance.page.inc.
 *
 * Page callback for OpenStack Instance entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for OpenStack Instance templates.
 *
 * Default template: openstack_instance.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_openstack_instance(array &$variables) {

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) ?: [] as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
