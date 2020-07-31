<?php

namespace Drupal\terraform\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\terraform\Service\TerraformServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes for Terraform Update Resources Queue.
 *
 * @QueueWorker(
 *   id = "terraform_update_resources_queue",
 *   title = @Translation("Terraform Update Resources Queue"),
 * )
 */
class TerraformUpdateResourcesQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The terraform service.
   *
   * @var \Drupal\terraform\Service\TerraformServiceInterface
   */
  private $terraformService;

  /**
   * Constructs a new LocaleTranslation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\terraform\Service\TerraformServiceInterface $terraform_service
   *   The terraform service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TerraformServiceInterface $terraform_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->terraformService = $terraform_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('terraform')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $cloud_context = $data['cloud_context'];
    $terraform_method_name = $data['terraform_method_name'];
    $this->terraformService->setCloudContext($cloud_context);
    if (method_exists($this->terraformService, $terraform_method_name)) {
      $this->terraformService->$terraform_method_name();
    }
  }

}
