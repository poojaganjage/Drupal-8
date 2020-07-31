<?php

namespace Drupal\terraform\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Traits\CloudDeleteMultipleFormTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\terraform\Service\TerraformServiceException;
use Drupal\terraform\Service\TerraformServiceInterface;

/**
 * Provides an entities deletion confirmation form.
 */
class TerraformDeleteMultipleForm extends TerraformProcessMultipleForm {

  use CloudDeleteMultipleFormTrait;

  /**
   * Constructs a new TerraformDeleteMultipleForm object.
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
   * @param \Drupal\terraform\Service\TerraformServiceInterface $terraform_service
   *   The Terraform Service.
   */
  public function __construct(AccountInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              PrivateTempStoreFactory $temp_store_factory,
                              MessengerInterface $messenger,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              TerraformServiceInterface $terraform_service) {

    parent::__construct(
      $current_user,
      $entity_type_manager,
      $temp_store_factory,
      $messenger,
      $cloud_config_plugin_manager,
      $terraform_service
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
   * @throws \Drupal\terraform\Service\TerraformServiceException
   *    Thrown when unable to delete entity.
   */
  protected function deleteCloudResource(CloudContentEntityBase $entity, $method_name) {
    $this->terraformService->setCloudContext($entity->getCloudContext());
    try {
      $this->terraformService->$method_name($entity->getName());
      return TRUE;
    }
    catch (TerraformServiceException $e) {
      return FALSE;
    }
  }

}
