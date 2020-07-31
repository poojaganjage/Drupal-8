<?php

namespace Drupal\cloud\Plugin\views\field;

use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\LinkBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to present a link to a list of cloud projects.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("cloud_list_templates")
 */
class CloudProjectListLink extends LinkBase {

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Constructs a LinkBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|null $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface|null $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface|null $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccessManagerInterface $access_manager,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              EntityTypeManagerInterface $entity_type_manager = NULL,
                              EntityRepositoryInterface $entity_repository = NULL,
                              LanguageManagerInterface $language_manager = NULL
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $access_manager, $entity_type_manager, $entity_repository, $language_manager);
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('access_manager'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getUrlInfo(ResultRow $row) {
    $this->cloudConfigPluginManager->setCloudContext($row->_entity->getCloudContext());
    $route = $this->cloudConfigPluginManager->getProjectCollectionName();
    return Url::fromRoute($route, ['cloud_context' => $row->_entity->getCloudContext()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultLabel() {
    return $this->t('view');
  }

}
