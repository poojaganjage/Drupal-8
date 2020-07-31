<?php

namespace Drupal\k8s\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\k8s\Service\CostFieldsRendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the node view builders.
 */
class K8sNodeViewBuilder extends CloudViewBuilder {

  /**
   * The cost fields renderer.
   *
   * @var \Drupal\k8s\Service\CostFieldsRendererInterface
   */
  protected $costFieldsRenderer;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * Constructs a new EntityViewBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Theme\Registry $theme_registry
   *   The theme registry.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\k8s\Service\CostFieldsRendererInterface $cost_fields_renderer
   *   The cost fields renderer.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    Registry $theme_registry = NULL,
    EntityDisplayRepositoryInterface $entity_display_repository,
    CostFieldsRendererInterface $cost_fields_renderer,
    DateFormatterInterface $date_formatter
  ) {
    parent::__construct($entity_type, $entity_repository, $language_manager, $theme_registry, $entity_display_repository);

    $this->costFieldsRenderer = $cost_fields_renderer;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('theme.registry'),
      $container->get('entity_display.repository'),
      $container->get('k8s.cost_fields_renderer'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'node_allocated_resources',
        'title' => $this->t('Allocated Resources'),
        'open' => TRUE,
        'fields' => [],
      ],
      [
        'name' => 'node_heatmap',
        'title' => $this->t('Heatmap'),
        'open' => TRUE,
        'fields' => [],
      ],
      [
        'name' => 'entity_metrics',
        'title' => $this->t('Metrics'),
        'open' => TRUE,
        'fields' => [],
      ],
      [
        'name' => 'node',
        'title' => $this->t('Node'),
        'open' => TRUE,
        'fields' => [
          'name',
          'status',
          'labels',
          'annotations',
          'addresses',
          'pod_cidr',
          'provider_id',
          'unschedulable',
          'created',
        ],
      ],
      [
        'name' => 'system_info',
        'title' => $this->t('System Info'),
        'open' => TRUE,
        'fields' => [
          'machine_id',
          'system_uuid',
          'boot_id',
          'kernel_version',
          'os_image',
          'container_runtime_version',
          'kubelet_version',
          'kube_proxy_version',
          'operating_system',
          'architecture',
        ],
      ],
      [
        'name' => 'metrics',
        'title' => $this->t('Metrics'),
        'open' => TRUE,
        'fields' => [
          'cpu_capacity',
          'cpu_request',
          'cpu_limit',
          'cpu_usage',
          'memory_capacity',
          'memory_request',
          'memory_limit',
          'memory_usage',
          'pods_capacity',
          'pods_allocation',
        ],
      ],
      [
        'name' => 'costs',
        'title' => $this->t('Costs'),
        'open' => TRUE,
        'fields' => [],
      ],
      [
        'name' => 'node_conditions',
        'title' => $this->t('Conditions'),
        'open' => TRUE,
        'fields' => [],
      ],
      [
        'name' => 'node_pods',
        'title' => $this->t('Pods'),
        'open' => TRUE,
        'fields' => [],
      ],
      [
        'name' => 'node_detail',
        'title' => $this->t('Detail'),
        'open' => FALSE,
        'fields' => [
          'detail',
        ],
      ],
      [
        'name' => 'others',
        'title' => $this->t('Others'),
        'open' => FALSE,
        'fields' => [
          'cloud_context',
          'uid',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);

    $this->buildCosts($entity, $build);
    $this->buildConditions($entity, $build);

    return $build;
  }

  /**
   * Build costs fields.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array &$build
   *   The build array.
   */
  private function buildCosts(EntityInterface $entity, array &$build) {
    // Get instance type and region.
    $instance_type = NULL;
    $region = NULL;
    foreach ($entity->get('labels') ?: [] as $item) {
      if ($item->getItemKey() === 'beta.kubernetes.io/instance-type') {
        $instance_type = $item->getItemValue();
      }
      elseif ($item->getItemKey() === 'failure-domain.beta.kubernetes.io/region') {
        $region = $item->getItemValue();
      }
    }

    if (empty($instance_type)
    || empty($region)) {
      unset($build['costs']);
      return;
    }

    $cost_fields = $this->costFieldsRenderer->render($region, [$instance_type]);
    if (empty($cost_fields)) {
      unset($build['costs']);
      return;
    }

    $build['costs']['fields'] = $cost_fields;
  }

  /**
   * Build conditions fields.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array &$build
   *   The build array.
   */
  private function buildConditions(EntityInterface $entity, array &$build) {
    $detail = Yaml::decode($entity->getDetail());

    $rows = [];
    foreach ($detail['status']['conditions'] ?: [] as $condition) {
      $condition['lastHeartbeatTime'] = $this->dateFormatter->format(strtotime($condition['lastHeartbeatTime']), 'short');
      $condition['lastTransitionTime'] = $this->dateFormatter->format(strtotime($condition['lastTransitionTime']), 'short');
      $rows[] = $condition;
    }

    $table = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Type'),
        $this->t('Status'),
        $this->t('Last heartbeat time'),
        $this->t('Last transition time'),
        $this->t('Reason'),
        $this->t('Message'),
      ],
      '#rows' => $rows,
    ];

    $build['node_conditions']['fields'][] = $table;
  }

}
