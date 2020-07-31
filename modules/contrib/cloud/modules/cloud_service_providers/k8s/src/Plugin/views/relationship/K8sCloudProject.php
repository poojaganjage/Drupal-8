<?php

namespace Drupal\k8s\Plugin\views\relationship;

use Drupal\views\Plugin\ViewsHandlerManager;
use Drupal\views\Plugin\views\relationship\RelationshipPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Relationship handler to return the K8s cloud projects.
 *
 * @ingroup views_relationship_handlers
 *
 * @ViewsRelationship("k8s_cloud_project")
 */
class K8sCloudProject extends RelationshipPluginBase {

  /**
   * The join manager.
   *
   * @var \Drupal\views\Plugin\ViewsHandlerManager
   */
  protected $joinManager;

  /**
   * Constructs the K8sNodeId object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\views\Plugin\ViewsHandlerManager $join_manager
   *   The views plugin join manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ViewsHandlerManager $join_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->joinManager = $join_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.views.join')
    );
  }

  /**
   * Called to implement a relationship in a query.
   */
  public function query() {
    $this->ensureMyTable();
    $def = $this->definition;

    $first = [
      'table' => 'cloud_project__field_k8s_clusters',
      'field' => 'field_k8s_clusters_value',
      'left_table' => $this->tableAlias,
      'left_field' => $this->realField,
      'adjusted' => TRUE,
    ];
    if (!empty($this->options['required'])) {
      $first['type'] = 'INNER';
    }

    if (!empty($this->definition['join_extra'])) {
      $first['extra'] = $this->definition['join_extra'];
    }

    if (!empty($def['join_id'])) {
      $id = $def['join_id'];
    }
    else {
      $id = 'standard';
    }
    $first_join = $this->joinManager->createInstance($id, $first);

    $this->first_alias = $this->query->addTable('cloud_project__field_k8s_clusters', $this->relationship, $first_join);

    $second = [
      'left_table' => $this->first_alias,
      'left_field' => 'entity_id',
      'table' => $this->definition['base'],
      'field' => $this->definition['base field'],
      'adjusted' => TRUE,
    ];

    if (!empty($this->options['required'])) {
      $second['type'] = 'INNER';
    }

    if (!empty($def['join_id'])) {
      $id = $def['join_id'];
    }
    else {
      $id = 'standard';
    }
    $second_join = $this->joinManager->createInstance($id, $second);
    $second_join->adjusted = TRUE;

    $alias = $second['table'] . '_' . $this->table;

    $this->alias = $this->query->addRelationship($alias, $second_join, 'cloud_project_field_data', $this->relationship);
  }

}
