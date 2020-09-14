<?php

namespace Drupal\smart_content\Plugin\smart_content\Decision\Storage;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\smart_content\Decision\Storage\DecisionStorageEntityBase;
use Drupal\smart_content\Entity\DecisionConfig;
use Drupal\smart_content\Entity\DecisionEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'config_entity' ReactionSetStorage.
 *
 * @SmartDecisionStorage(
 *  id = "config_entity",
 *  label = @Translation("Config Entity"),
 * )
 */
class ConfigEntity extends DecisionStorageEntityBase implements ContainerFactoryPluginInterface {

  /**
   * The uuid generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * Constructs a new EntityOperations object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Uuid\UuidInterface $uuidGenerator
   *   The uuid generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UuidInterface $uuidGenerator) {
    $this->uuidGenerator = $uuidGenerator;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $configuration = [] + parent::getConfiguration();
    if ($entity = $this->getEntity()) {
      $configuration['id'] = $this->getEntity()->id();
    }
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'id' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    $this->deleteTokens();
    $this->getEntity()->delete();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function loadDecisionFromToken($token) {
    $query = \Drupal::database()->select('decision_config_token', 'd');
    $query->condition('token', $token);
    $query->addField('d', 'id');
    $result = $query->execute()->fetchAssoc();
    if (!empty($result)) {
      // We load the entity, which subsequently loads the decision.
      $this->loadEntityFromConfig($result);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function registerToken() {
    $entity = $this->getEntity();
    \Drupal::database()->insert('decision_config_token')->fields([
      'id' => $entity->id(),
      'token' => $entity->getDecision()->getToken(),
    ])->execute();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTokens() {
    if ($entity = $this->getEntity()) {
      \Drupal::database()->delete('decision_config_token')
        ->condition('id', $entity->id())
        ->execute();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function loadEntityFromConfig(array $configuration) {
    if (!empty($configuration['id'])) {
      $this->entity = DecisionConfig::load($configuration['id']);
    }
    // @todo: Determine if we are happy with autogenerated entity_id's.
    if (!$this->entity instanceof DecisionEntityInterface) {
      $id = isset($configuration['id']) ? $configuration['id'] : $this->uuidGenerator->generate();
      $this->entity = DecisionConfig::create(['id' => $id]);
    }
    return $this;
  }

}
