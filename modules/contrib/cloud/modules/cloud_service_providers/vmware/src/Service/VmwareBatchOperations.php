<?php

namespace Drupal\vmware\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\vmware\Entity\VmwareVm;

/**
 * Entity update methods for Batch API processing.
 */
class VmwareBatchOperations {

  use StringTranslationTrait;

  /**
   * The finish callback function.
   *
   * Deletes stale entities from the database.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $stale
   *   The stale entities to delete.
   * @param bool $clear
   *   TRUE to clear entities, FALSE keep them.
   */
  public static function finished($entity_type, array $stale, $clear = TRUE) {
    $entity_type_manager = \Drupal::entityTypeManager();
    if (count($stale) && $clear === TRUE) {
      $entity_type_manager->getStorage($entity_type)->delete($stale);
    }
  }

  /**
   * Update or create a vmware vm entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $vm
   *   The VM array.
   *
   * @throws \Drupal\vmware\Service\VmwareServiceException
   *   Thrown when unable to get VMs.
   */
  public static function updateVm($cloud_context, array $vm) {
    $vmware_service = \Drupal::service('vmware');
    $vmware_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $vm['name'];
    $entity_id = $vmware_service->getEntityId('vmware_vm', 'name', $name);

    if (!empty($entity_id)) {
      $entity = VmwareVm::load($entity_id);
    }
    else {
      $entity = VmwareVm::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => $timestamp,
        'changed' => $timestamp,
      ]);
    }

    $entity->setVmId($vm['vm']);
    $entity->setPowerState($vm['power_state']);
    $entity->setCpuCount($vm['cpu_count']);
    $entity->setMemorySize($vm['memory_size_MiB']);
    $entity->setRefreshed($timestamp);

    $entity->save();
  }

}
