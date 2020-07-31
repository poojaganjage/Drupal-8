<?php

namespace Drupal\k8s\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes for K8s Cost Storage Queue.
 *
 * @QueueWorker(
 *   id = "k8s_update_cost_storage_queue",
 *   title = @Translation("K8s Cost Storage Queue"),
 * )
 */
class K8sCostStorageQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

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
    $export_function_name = $data['export_function_name'];
    $params = !empty($data['params'])
      ? $data['params']
      : NULL;

    if (function_exists($export_function_name)) {
      $params !== NULL
        ? $export_function_name($params)
        : $export_function_name();
    }
  }

}
