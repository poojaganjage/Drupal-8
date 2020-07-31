<?php

namespace Drupal\k8s\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\NumericArgument;
use Drupal\views\Plugin\ViewsHandlerManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a K8s node ID.
 *
 * @ViewsArgument("k8s_node_id")
 */
class K8sNodeId extends NumericArgument {

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
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $this->ensureMyTable();

    if (!empty($this->options['break_phrase'])) {
      $break = static::breakString($this->argument, FALSE);
      $this->value = $break->value;
      $this->operator = $break->operator;
    }
    else {
      $this->value = [$this->argument];
    }

    $placeholder = $this->placeholder();
    $null_check = empty($this->options['not']) ? '' : " OR $this->tableAlias.$this->realField IS NULL";

    $definition = [
      'table' => 'k8s_node',
      'field' => 'name',
      'left_table' => $this->tableAlias,
      'left_field' => $this->realField,
    ];
    $join = $this->joinManager->createInstance('standard', $definition);
    $this->query->addTable('k8s_node', NULL, $join);

    if (count($this->value) > 1) {
      $operator = empty($this->options['not']) ? 'IN' : 'NOT IN';
      $placeholder .= '[]';
      $this->query->addWhereExpression(0, "k8s_node.id $operator($placeholder)" . $null_check, [$placeholder => $this->value]);
    }
    else {
      $operator = empty($this->options['not']) ? '=' : '!=';
      $this->query->addWhereExpression(0, "k8s_node.id $operator $placeholder" . $null_check, [$placeholder => $this->argument]);
    }
  }

}
