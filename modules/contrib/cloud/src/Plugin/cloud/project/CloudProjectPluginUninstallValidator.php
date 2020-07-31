<?php

namespace Drupal\cloud\Plugin\cloud\project;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;

/**
 * Validates module uninstall readiness based on existing content entities.
 */
class CloudProjectPluginUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CloudProjectUninstallValidator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(EntityTypeManager $entity_type_manager,
                              TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) : array {

    field_purge_batch(10000);

    // First, check if there are entities related to $module.
    $entity_types = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types as $entity_type) {
      if ($entity_type instanceof ContentEntityTypeInterface
        && $module === $entity_type->getProvider()
        && $this->entityTypeManager->getStorage($entity_type->id())->hasData()) {

        // If there are still entities related ows_cloud module,
        // return an empty value [] ('no reason') by skipping the
        // cloud_project_type.$module check as shown below.
        return [];
      }
    }

    // Second, if there are no entities related to $module, ask a user
    // to delete all cloud_project_type.$module entities.
    $reasons = [];
    try {
      $bundle = $this->entityTypeManager
        ->getStorage('cloud_project_type')
        ->load($module);
      if (!empty($bundle)
        && $bundle->id() === $module) {
        $entity_type = $this->entityTypeManager->getDefinition('cloud_project_type');
        $reasons[] = $this->t('There is content for the entity type: @entity_type. <a href=":url">Remove @entity_type @entity_type_plural</a>.', [
          '@entity_type' => $bundle->label(),
          '@entity_type_plural' => $entity_type->getPluralLabel(),
          ':url' => Url::fromRoute('entity.cloud_project_type.delete_form', [
            'cloud_project_type' => $module,
          ])->toString(),
        ]);
      }
    }
    catch (\Exception $e) {
    }

    return $reasons;
  }

}
