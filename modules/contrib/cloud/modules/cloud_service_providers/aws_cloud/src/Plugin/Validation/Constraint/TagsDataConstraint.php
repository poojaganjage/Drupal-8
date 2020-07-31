<?php

namespace Drupal\aws_cloud\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Tags field validation.
 *
 * @Constraint(
 *   id = "tags_data",
 *   label = @Translation("Tags", context = "Validation"),
 * )
 */
class TagsDataConstraint extends Constraint {

  /**
   * The tag with key %value already exists.
   *
   * @var string
   */
  public $keyExists = 'The tag with key %value already exists.';

}
