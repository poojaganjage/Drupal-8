<?php

namespace Drupal\cloud\Plugin\cloud\config;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for cloud_config_plugin managers.
 */
interface CloudConfigPluginManagerInterface extends PluginManagerInterface {

  /**
   * Set cloud_context.
   *
   * @param string $cloud_context
   *   The cloud context.
   */
  public function setCloudContext($cloud_context);

  /**
   * Load all configuration entities for a given bundle.
   *
   * @param string $entity_bundle
   *   The bundle to load.
   *
   * @return mixed
   *   An array of cloud_config entities.
   */
  public function loadConfigEntities($entity_bundle);

  /**
   * Load a plugin using the cloud_context.
   *
   * @return \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginInterface
   *   loaded The cloud service provider plugin (CloudConfigPlugin).
   */
  public function loadPluginVariant();

  /**
   * Load a config entity.
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   */
  public function loadConfigEntity();

  /**
   * Load credentials.
   *
   * @return mixed
   *   Array of credentials
   */
  public function loadCredentials();

  /**
   * Load routes for implementing class's instances.
   *
   * @return string
   *   The instance collection template name.
   */
  public function getInstanceCollectionTemplateName();

  /**
   * Load routes for implementing class's instances.
   *
   * @return string
   *   The instance collection template name.
   */
  public function getPricingPageRoute();

  /**
   * Load route for cloud server templates.
   *
   * @return string
   *   The cloud server template collection name.
   */
  public function getServerTemplateCollectionName();

}
