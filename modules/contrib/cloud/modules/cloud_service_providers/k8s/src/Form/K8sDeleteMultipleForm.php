<?php

namespace Drupal\k8s\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Traits\CloudDeleteMultipleFormTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\k8s\Service\K8sServiceException;
use Drupal\k8s\Service\K8sServiceInterface;

/**
 * Provides an entities deletion confirmation form.
 */
class K8sDeleteMultipleForm extends K8sProcessMultipleForm {

  use CloudDeleteMultipleFormTrait;

  /**
   * Constructs a new K8sDeleteMultipleForm object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   The K8s Service.
   */
  public function __construct(AccountInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              PrivateTempStoreFactory $temp_store_factory,
                              MessengerInterface $messenger,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              K8sServiceInterface $k8s_service) {

    parent::__construct(
      $current_user,
      $entity_type_manager,
      $temp_store_factory,
      $messenger,
      $cloud_config_plugin_manager,
      $k8s_service
    );
    $this->tempStore = $temp_store_factory->get('entity_delete_multiple_confirm');
  }

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    $name_camel = $this->getShortEntityTypeNameCamel($entity);

    return $this->deleteCloudResource($entity, "delete{$name_camel}");
  }

  /**
   * Delete a cloud resource.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   The entity object.
   * @param string $method_name
   *   The name of the method used to delete resource.
   *
   * @return bool
   *   Whether the resource is deleted successfully or not.
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *    Thrown when unable to delete entity.
   */
  protected function deleteCloudResource(CloudContentEntityBase $entity, $method_name) {
    $this->k8sService->setCloudContext($entity->getCloudContext());
    try {
      if (method_exists($entity, 'getNamespace')) {
        $this->k8sService->$method_name(
          $entity->getNamespace(),
          [
            'metadata' => [
              'name' => $entity->getName(),
            ],
          ]
        );
      }
      else {
        $this->k8sService->$method_name(
          [
            'metadata' => [
              'name' => $entity->getName(),
            ],
          ]
        );
      }

      return TRUE;
    }
    catch (K8sServiceException $e) {
      return FALSE;
    }
  }

}
