<?php

namespace Drupal\k8s\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes for K8s Update Resources Queue.
 *
 * @QueueWorker(
 *   id = "k8s_update_resources_queue",
 *   title = @Translation("K8s Update Resources Queue"),
 * )
 */
class K8sUpdateResourcesQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The k8s service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  private $k8sService;

  /**
   * Constructs a new LocaleTranslation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s
   *   The k8s service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, K8sServiceInterface $k8s) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->k8sService = $k8s;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('k8s')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $cloud_context = $data['cloud_context'];
    $k8s_method_name = $data['k8s_method_name'];
    $this->k8sService->setCloudContext($cloud_context);
    if (method_exists($this->k8sService, $k8s_method_name)) {
      $this->k8sService->$k8s_method_name();
    }
  }

}
