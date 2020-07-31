<?php

namespace Drupal\cloud_budget\Plugin\cloud_cost_storage;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\cloud\Plugin\cloud\CloudPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base in-place editor implementation.
 *
 * @see \Drupal\cloud_budget\Annotation\CloudCostStorage
 * @see \Drupal\cloud_budget\Plugin\cloud_cost_storage\CloudCostStorageInterface
 * @see \Drupal\cloud_budget\Plugin\cloud_cost_storage\CloudCostStorageManager
 * @see plugin_api
 */
abstract class CloudCostStorageBase extends CloudPluginBase implements CloudCostStorageInterface, ContainerFactoryPluginInterface {

  /**
   * The Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CloudCostStorage constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Insert content into entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $cloud_context
   *   The cloud context.
   * @param string $payer
   *   The payer of the cost.
   * @param float $cost
   *   The contents to be put into entity.
   * @param float $resource
   *   The resource to be put into entity.
   * @param float $time_range
   *   The time range user used resources.
   */
  public function save($entity_type_id, $cloud_context, $payer, $cost, $resource, $time_range): void {
    $data = [
      'cloud_context' => $cloud_context,
      'payer' => $payer,
      'cost' => $cost,
      'resources' => $resource,
      'refreshed' => $time_range,
    ];
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($data);
    $entity->save();
  }

  /**
   * Delete content from entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $payer
   *   The payer of the cost.
   */
  public function deleteAll($entity_type_id, $payer): void {
    $entities = $this->entityTypeManager->getStorage($entity_type_id)->loadByproperty([
      'payer' => $payer,
    ]);
    foreach ($entities ?: [] as $entity) {
      $entity->delete();
    }
  }

  /**
   * Delete content from entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param int $from_time
   *   The unix timestamp of the starting time.
   * @param int $to_time
   *   The unix timestamp of the ending time.
   */
  public function deleteTimeBased($entity_type_id, $from_time, $to_time): void {
    $entities = $this->entityTypeManager->getStorage($entity_type_id)->loadByproperty([]);
    foreach ($entities ?: [] as $entity) {
      $created_time = $entity->get('created')->value;
      if ($to_time > $created_time && $created_time <= $from_time) {
        continue;
      }
      $entity->delete();
    }
  }

  /**
   * Extract data based on time range.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $cloud_context
   *   The cloud context.
   * @param string $payer
   *   The payer of the cost.
   * @param int $from_time
   *   The unix timestamp of the starting time.
   * @param int $to_time
   *   The unix timestamp of the ending time.
   *
   * @return array
   *   The result including cost and unix time.
   */
  public function extract($entity_type_id, $cloud_context, $payer, $from_time, $to_time): array {
    $entities = $this->entityTypeManager->getStorage($entity_type_id)->loadByproperty([
      'payer' => $payer,
      'cloud_context' => $cloud_context,
    ]);

    $result = [];
    $total_cost = 0;
    foreach ($entities ?: [] as $entity) {
      $created_time = $entity->get('created')->value;
      if ($to_time > $created_time || $created_time <= $from_time) {
        continue;
      }
      $cost = $entity->get('cost')->value;
      $total_cost += $cost;
      $result[] = [
        'cost' => $cost,
        'created' => $created_time,
      ];
    }
    $result['total_cost'] = $total_cost;
    return $result;
  }

}
