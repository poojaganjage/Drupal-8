<?php

/**
 * @file
 * Hooks related to openstack module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the parameter array before being sent through the OpenStack API.
 *
 * @param array $params
 *   An array of parameters for operation.
 * @param string $operation
 *   The operation to perform.
 * @param string $cloud_context
 *   Cloud context string.
 */
function hook_openstack_pre_execute_alter(array &$params, $operation, $cloud_context) {

}

/**
 * Alter the results before it gets processed by openstack.
 *
 * @param array $results
 *   A result array of execution.
 * @param string $operation
 *   The operation to perform.
 * @param string $cloud_context
 *   Cloud context string.
 */
function hook_openstack_post_execute_alter(array &$results, $operation, $cloud_context) {

}

/**
 * @} End of "addtogroup hooks".
 */
