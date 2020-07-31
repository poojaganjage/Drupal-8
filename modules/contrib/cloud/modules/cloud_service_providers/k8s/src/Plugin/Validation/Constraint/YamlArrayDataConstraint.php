<?php

namespace Drupal\k8s\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Tags field validation.
 *
 * @Constraint(
 *   id = "yaml_array_data",
 *   label = @Translation("Yaml", context = "Validation"),
 * )
 */
class YamlArrayDataConstraint extends Constraint {

  /**
   * The error message: 'Invalid Yaml array format.'.
   *
   * @var array
   */
  public $invalidYamlArray = 'Invalid Yaml array format.';

  /**
   * The error message header: 'Invalid Yaml format:'.
   *
   * @var array
   */
  public $invalidYaml = 'Invalid Yaml format: ';

}
