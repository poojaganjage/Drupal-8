<?php

namespace Drupal\k8s;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for K8s namespace.
 */
class K8sNamespacePermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs new CloudPermissions object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns an array of cloud service provider (CloudConfig) permissions.
   */
  public function configPermissions() {
    $permissions = [];
    $namespaces = $this->entityTypeManager->getStorage('k8s_namespace')->loadMultiple();
    foreach ($namespaces ?: [] as $namespace) {
      $permissions['view k8s namespace ' . $namespace->getName()] = [
        'title' => $this->t('Access entities belonging to k8s namespace %entity', ['%entity' => $namespace->getName()]),
        'description' => $this->t('Allows access to entities belonging to k8s namespace %entity.', ['%entity' => $namespace->getName()]),
      ];
    }
    return $permissions;
  }

}
