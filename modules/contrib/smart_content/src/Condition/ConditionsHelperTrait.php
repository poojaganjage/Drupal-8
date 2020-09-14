<?php

namespace Drupal\smart_content\Condition;

/**
 * A helper trait for getting/setting conditions in a ConditionPluginCollection.
 *
 * @package Drupal\smart_content\Condition
 */
trait ConditionsHelperTrait {

  /**
   * An array of conditions settings.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * The plugin collection that holds the block plugin for this entity.
   *
   * @var \Drupal\smart_content\Condition\ConditionPluginCollection
   */
  protected $conditionCollection;

  /**
   * Returns the conditions of the Segment.
   *
   * @return \Drupal\smart_content\Condition\ConditionPluginCollection
   *   The conditions.
   */
  public function getConditions() {
    return $this->getConditionPluginCollection();
  }

  /**
   * Gets the segment for a given UUID.
   *
   * @param string $id
   *   The ID of the condition to retrieve.
   *
   * @return \Drupal\smart_content\Condition\ConditionInterface
   *   The condition.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the expected ID does not exist.
   */
  public function getCondition($id) {
    return $this->getConditionPluginCollection()->get($id);
  }

  /**
   * Helper method to set a condition.
   *
   * @param string $instance_id
   *   The condition instance id.
   * @param \Drupal\smart_content\Condition\ConditionInterface $condition
   *   The condition.
   *
   * @return $this
   *   Return $this.
   */
  public function setCondition($instance_id, ConditionInterface $condition) {
    $this->getConditionPluginCollection()->set($instance_id, $condition);
    return $this;
  }

  /**
   * Append a condition to the collection.
   *
   * Append will automatically generate a unique instance id if collision
   * with other plugin of same type.
   *
   * @param \Drupal\smart_content\Condition\ConditionInterface $condition
   *   The condition plugin.
   *
   * @return $this
   */
  public function appendCondition(ConditionInterface $condition) {
    $this->getConditionPluginCollection()->add($condition);
    return $this;
  }

  /**
   * Removes a given condition from the Segment.
   *
   * @param string $id
   *   The ID of the condition to remove.
   *
   * @return $this
   */
  public function removeCondition($id) {
    $this->getConditionPluginCollection()->removeInstanceId($id);
    return $this;
  }

  /**
   * Gets the plugin collections used by this object.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection[]
   *   An array of plugin collections, keyed by the property name they use to
   *   store their configuration.
   */
  abstract public function getPluginCollections();

  /**
   * Encapsulates the creation of the conditions's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The block's plugin collection.
   */
  protected function getConditionPluginCollection() {
    if (!$this->conditionCollection) {
      $this->conditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.smart_content.condition'),
        (array) $this->conditions);
    }
    return $this->conditionCollection;
  }

}
