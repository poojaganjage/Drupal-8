<?php

namespace Drupal\smart_content\Reaction;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\smart_content\Segment;

/**
 * Base class for Reaction plugins.
 */
abstract class ReactionBase extends PluginBase implements ReactionInterface, ConfigurableInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * The Uuid of the Segment object.
   *
   * @var string
   */
  protected $segmentId;

  /**
   * {@inheritdoc}
   */
  public function getSegmentDependencyId() {
    return $this->segmentId;
  }

  /**
   * {@inheritdoc}
   */
  public function setSegmentDependency(Segment $segment) {
    $this->segmentId = $segment->getUuid();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'id' => $this->getPluginId(),
      'segment_id' => $this->segmentId,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'id' => $this->getPluginId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $configuration = $configuration + $this->defaultConfiguration();
    if (isset($configuration['segment_id'])) {
      $this->segmentId = $configuration['segment_id'];
    }
    return $this;
  }

}
