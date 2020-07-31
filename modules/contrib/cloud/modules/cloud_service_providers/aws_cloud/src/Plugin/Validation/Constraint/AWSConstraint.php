<?php

namespace Drupal\aws_cloud\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * AWS specific validation for user specified fields.
 *
 * @Constraint(
 *   id = "AWSConstraint",
 *   label = @Translation("Instance Type", context = "Validation"),
 *   type = "entity:cloud_server_template"
 * )
 */
class AWSConstraint extends CompositeConstraintBase {

  /**
   * Error message if a network is not selected.
   *
   * @var string
   */
  public $noNetwork = 'The %instance_type requires a network selection';

  /**
   * Error message if shutdown behavior = stop.
   *
   * @var string
   */
  public $shutdownBehavior = 'Only EBS backed images can use Stop as the Instance Shutdown Behavior';

  /**
   * Error message if name has already been used.
   *
   * @var string
   */
  public $nameUsed = 'The Name %name has already been used by other launch template. Please input another one.';

  /**
   * Error message if characters of the name are invalid.
   *
   * @var string
   */
  public $nameInvalidChars = "The Name %name must be between 3 and 125 characters without any white spaces, and may contain letters (a-z, A-Z), numbers (0-9), and the following characters: '-' (hyphen), '_' (underscore), '.' (period), '/' (forward slash), '(' (open parenthesis) and ')' (close parenthesis).";

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return [
      'name',
      'field_instance_type',
      'field_network',
      'field_image_id',
      'field_instance_shutdown_behavior',
    ];
  }

}
