<?php

namespace Drupal\smart_content_a_b\Plugin\smart_content\Condition;

use Drupal\smart_content\Condition\ConditionBase;

/**
 * Provides a 'ValueAB' condition.
 *
 * @SmartCondition(
 *   id = "smart_content_a_b_value",
 *   label = @Translation("Value A/B"),
 *   group = "hidden",
 *   weight = 0,
 * )
 */
class ValueAB extends ConditionBase {

  protected $entity_id;

  protected $letter;


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'id' => $this->getPluginId(),
      'weight' => $this->getWeight(),
      'negate' => $this->isNegated(),
      'type' => $this->getTypeId(),
      'entity_id' => NULL,
      'letter' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'id' => $this->getPluginId(),
      'weight' => $this->getWeight(),
      'negate' => $this->isNegated(),
      'type' => $this->getTypeId(),
      'entity_id' => $this->entity_id,
      'letter' => $this->letter,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $configuration = $configuration + $this->defaultConfiguration();

    if (isset($configuration['weight'])) {
      $this->weight = (int) $configuration['weight'];
    }
    if (isset($configuration['negate'])) {
      $this->negate = (bool) $configuration['negate'];
    }
    $this->entity_id = $configuration['entity_id'];
    $this->letter = $configuration['letter'];
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return ['smart_content_a_b/smart_content_a_b'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedSettings() {
    return parent::getAttachedSettings() + [
      'settings' => [
        'entity_id' => $this->entity_id,
        'letter' => $this->letter,
      ],
    ];
  }

}
