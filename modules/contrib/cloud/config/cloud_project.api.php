<?php

/**
 * @file
 * Hooks related to cloud_project module.
 */

use Drupal\cloud\Entity\CloudProjectInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the route array after a template is launched.
 *
 * @param array $route
 *   Associate array with route_name, params.
 * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
 *   The cloud project entity.
 */
function hook_cloud_project_post_launch_redirect_alter(array &$route, CloudProjectInterface $cloud_project) {

}

/**
 * @} End of "addtogroup hooks".
 */
