<?php

namespace Drupal\aws_cloud\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Security Group entity validation.
 *
 * @Constraint(
 *   id = "SecurityGroupName",
 *   label = @Translation("Security Group", context = "Validation"),
 * )
 */
class SecurityGroupNameConstraint extends Constraint {

  /**
   * A message: 'Cannot create group with Security Group Name "default".'.
   *
   * @var string
   */
  public $defaultName = 'Cannot create group with Security Group Name "default".';

  /**
   * A message: "The group name already exists".
   *
   * @var string
   */
  public $duplicateGroupName = 'The Security Group Name "@name" already exists.';

}
