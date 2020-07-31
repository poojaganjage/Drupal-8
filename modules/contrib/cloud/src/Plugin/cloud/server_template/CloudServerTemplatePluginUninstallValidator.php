<?php

namespace Drupal\cloud\Plugin\cloud\server_template;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Validates module uninstall readiness based on existing content entities.
 */
class CloudServerTemplatePluginUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CloudServerTemplateUninstallValidator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) : array {

    field_purge_batch(10000);

    // @TODO: As long as CloudServerTemplateType for each bundl is deleted by
    // Drupal core, we don't have to handle UninstallValidator, therefore this
    // function returns an empty array [].
    if (TRUE) {
      return [];
    }

    // First, check if there are entities related to $module.
    $entity_types = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types ?: [] as $entity_type) {
      if ($entity_type instanceof ContentEntityTypeInterface
        && $module === $entity_type->getProvider()
        && $this->entityTypeManager->getStorage($entity_type->id())->hasData()) {

        // If there are still entities related ows_cloud module,
        // return an empty value [] ('no reason') by skipping the
        // cloud_server_template_type.$module check as shown below.
        return [];
      }
    }

    // Second, if there are no entities related to $module, ask a user
    // to delete all cloud_server_template_type.$module entities.
    $reasons = [];
    try {
      $bundle = $this->entityTypeManager
        ->getStorage('cloud_server_template_type')
        ->load($module);
      if (!empty($bundle)
        && $bundle->id() === $module) {
        $entity_type = $this->entityTypeManager->getDefinition('cloud_server_template_type');
        $reasons[] = $this->t('There is content for the entity type: @entity_type. <a href=":url">Remove @entity_type @entity_type_plural</a>.', [
          '@entity_type' => $bundle->label(),
          '@entity_type_plural' => $entity_type->getPluralLabel(),
          ':url' => Url::fromRoute('entity.cloud_server_template_type.delete_form', [
            'cloud_server_template_type' => $module,
          ])->toString(),
        ]);
      }
    }
    catch (\Exception $e) {
    }

    return $reasons;
  }

}
