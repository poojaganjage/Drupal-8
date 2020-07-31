<?php

namespace Drupal\k8s\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * YAML URL  field validation.
 *
 * @Constraint(
 *   id = "k8s_yaml_url",
 *   label = @Translation("K8s YAML URL", context = "Validation"),
 *   type = "entity:cloud_server_template"
 * )
 */
class YamlUrlConstraint extends CompositeConstraintBase {

  /**
   * Error message if URL isn't set when 'Git' selected on the source type.
   *
   * @var array
   */
  public $requiredGitUrl = 'The git repository URL field is required.';

  /**
   * Error message if YAML URL and Detail fields aren't set.
   *
   * @var array
   */
  public $requiredYamlUrlOrDetail = 'YAML URL or Detail fields are required.';

  /**
   * Error message if both YAML URL and Detail fields are set.
   *
   * @var array
   */
  public $prohibitYamlUrlAndDetail = 'You cannot set both YAML URL and detail fields since you modified either YAML URL or detail field.';

  /**
   * Error message if YAML URL isn't correct.
   *
   * @var array
   */
  public $invalidYamlUrl = "The YAML URL isn't correct.";

  /**
   * Error message if the file linked with YAML URL isn't YAML format.
   *
   * @var array
   */
  public $invalidYamlFormat = "The file linked with YAML URL isn't YAML format.";

  /**
   * Error message if Resource URL isn't correct.
   *
   * @var array
   */
  public $invalidGitUrl = 'The git repository URL should end with <em>.git</em>.';

  /**
   * Error message if Resource URL could not be reached.
   *
   * @var array
   */
  public $unreachableGitUrl = 'The git repository URL is unreachable. Please confirm whether it is correct.';

  /**
   * Error message if object in "Kind" is not supported.
   *
   * @var string
   */
  public $unsupportedObjectType = 'Unsupported "Kind" element %kind in the file linked with YAML URL.';

  /**
   * Error message if no "Kind" element found in K8s yaml file.
   *
   * @var string
   */
  public $noKindFound = 'No "Kind" element found in the file linked with YAML URL.';

  /**
   * Error message on decoding Yaml.
   *
   * @var array
   */
  public $invalidYaml = 'Invalid Yaml format: ';

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['field_yaml_url', 'field_detail'];
  }

}
