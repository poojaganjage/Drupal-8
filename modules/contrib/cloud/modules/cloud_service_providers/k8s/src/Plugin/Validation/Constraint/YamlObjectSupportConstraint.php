<?php

namespace Drupal\k8s\Plugin\Validation\Constraint;

/**
 * Check the "Kind" element in the YAML file.
 *
 * The kind element needs to be supported by the K8s cloud server template.
 *
 * @Constraint(
 *   id = "yaml_object_support",
 *   label = @Translation("Yaml", context = "Validation"),
 * )
 */
class YamlObjectSupportConstraint extends YamlArrayDataConstraint {

  /**
   * Error if object in "Kind" is not supported.
   *
   * @var string
   */
  public $unsupportedObjectType = 'Unsupported object in "Kind" element.';

  /**
   * Error if no "Kind" element found in K8s yaml file.
   *
   * @var string
   */
  public $noObjectFound = 'No "Kind" element found.';

}
