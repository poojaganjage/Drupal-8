<?php

namespace Drupal\aws_cloud\Plugin\Validation\Constraint;

use Drupal\cloud\Service\CloudService;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\Validation\TypedDataAwareValidatorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Security Group name validation class.
 */
class SecurityGroupNameConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  use TypedDataAwareValidatorTrait;
  use StringTranslationTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cloud Service.
   *
   * @var \Drupal\cloud\Service\CloudService
   */
  protected $cloudService;

  /**
   * Constructs a new constraint validator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager instance.
   * @param \Drupal\cloud\Service\CloudService $cloud_service
   *   Cloud Service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CloudService $cloud_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cloudService = $cloud_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cloud')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    /* @var \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $entity */
    $entity = $items->getEntity();
    if (isset($entity) && $entity->isNew()) {
      // AWS does not allow group name set to 'default'.
      $group_name = $items->first()->value;
      if ($this->validateDefaultGroupName($group_name) === TRUE) {
        $this->context->addViolation($constraint->defaultName);
      }
      // Test if the group name is a duplicate.
      if ($this->validateDuplicateGroupName($group_name, $entity->getCloudContext()) === TRUE) {
        $this->context->addViolation($constraint->duplicateGroupName, ['@name' => $group_name]);
      }

      $violations = $this->context->getViolations();
    }
  }

  /**
   * Test if the group name exists.
   *
   * @param string $name
   *   The group name to test.
   * @param string $cloud_context
   *   The cloud_context to test in.
   *
   * @return bool
   *   True if it has a duplicate name, false otherwise.
   */
  private function validateDuplicateGroupName($name, $cloud_context) : bool {
    $has_duplicate_name = FALSE;
    try {
      $groups = $this->entityTypeManager
        ->getStorage('aws_cloud_security_group')
        ->loadByProperties(
          [
            'name' => $name,
            'cloud_context' => $cloud_context,
          ]
        );
      if (!empty($groups)) {
        $has_duplicate_name = TRUE;
      }
    }
    catch (\Exception $e) {
      $this->cloudService->handleException($e);
    }
    return $has_duplicate_name;
  }

  /**
   * Check if the group name is set to 'default'.
   *
   * @param string $name
   *   The group name.
   *
   * @return bool
   *   Whether default or not.
   */
  private function validateDefaultGroupName($name) : bool {
    $has_default = FALSE;
    if ($name === 'default') {
      $has_default = TRUE;
    }
    return $has_default;
  }

}
