<?php

/**
 * @file
 * Contains openstack_security_group.page.inc.
 *
 * Page callback for OpenStack Security Group entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for OpenStack Security Group templates.
 *
 * Default template: openstack_security_group.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_openstack_security_group(array &$variables) {

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) ?: [] as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
