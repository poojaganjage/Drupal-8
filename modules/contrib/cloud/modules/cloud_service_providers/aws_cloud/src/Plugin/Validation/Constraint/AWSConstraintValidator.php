<?php

namespace Drupal\aws_cloud\Plugin\Validation\Constraint;

use Drupal\cloud\Entity\CloudServerTemplateInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Perform AWS specific validations.
 */
class AWSConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    // Only perform validations for aws_cloud bundles.
    if ($entity->bundle() === 'aws_cloud') {

      $name = $entity->getName();

      // Validate name's characters.
      if (!$this->validateNameChars($name)) {
        $this->context
          ->buildViolation($constraint->nameInvalidChars, ['%name' => $name])
          ->atPath('name')
          ->addViolation();
      }

      // Validate name is not used.
      if (!$this->validateNameUnused($entity)) {
        $this->context
          ->buildViolation($constraint->nameUsed, ['%name' => $name])
          ->atPath('name')
          ->addViolation();
      }

      $instance_type = $entity->field_instance_type->value;
      $field_network = $entity->field_network->entity;
      $image = $entity->field_image_id->entity;
      $shutdown = $entity->field_instance_shutdown_behavior->value;

      // Make sure a network is specified when launching a t2.* instance.
      if (strpos($instance_type, 't2.') !== FALSE) {
        if (!isset($field_network)) {
          $this->context->buildViolation($constraint->noNetwork, ['%instance_type' => $instance_type])->atPath('field_network')->addViolation();
        }
      }

      // If the image is an instance-store, it cannot use stop for shutdown.
      if ($image !== NULL && $image->root_device_type->value === 'instance-store' && isset($shutdown)) {
        if ($shutdown === 'stop') {
          $this->context->buildViolation($constraint->shutdownBehavior)->atPath('field_instance_shutdown_behavior')->addViolation();
        }
      }
    }
  }

  /**
   * Validate whether characters of the name are valid or not.
   *
   * A launch template name must be between 3 and 125 characters,
   * and may contain letters, numbers, and the following characters:
   * - ( ) . / _.
   *
   * @param string $name
   *   The name.
   *
   * @return bool
   *   Whether characters are valid or not.
   */
  private function validateNameChars($name) {
    if (strlen($name) < 3 || strlen($name) > 125) {
      return FALSE;
    }

    return preg_match('/^[a-zA-Z0-9\-().\/_]+$/', $name) === 1;
  }

  /**
   * Validate whether the name of the entity is unused or not.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $entity
   *   The cloud server template entity.
   *
   * @return bool
   *   Whether the name of the entity is unused or not.
   */
  private function validateNameUnused(CloudServerTemplateInterface $entity) {
    $name = $entity->getName();
    $storage = \Drupal::entityTypeManager()->getStorage('cloud_server_template');

    if ($entity->isNew()) {
      $entity_ids = $storage->getQuery()
        ->condition('type', 'aws_cloud')
        ->condition('cloud_context', $entity->getCloudContext())
        ->condition('name', $name)
        ->execute();

      return empty($entity_ids);
    }

    return TRUE;
  }

}
