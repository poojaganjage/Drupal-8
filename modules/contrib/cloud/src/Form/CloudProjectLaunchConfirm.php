<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Plugin\cloud\Project\CloudProjectPluginManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides confirmation when launching an instance.
 */
class CloudProjectLaunchConfirm extends ContentEntityConfirmFormBase {

  /**
   * The CloudProjectPluginManager.
   *
   * @var \Drupal\cloud\Plugin\cloud\Project\CloudProjectPluginManagerPluginManager
   */
  protected $serverTemplatePluginManager;

  /**
   * Construct a CloudProjectLaunchConfirm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\cloud\Plugin\cloud\Project\CloudProjectPluginManagerInterface $project_plugin_manager
   *   The cloud project plugin manager.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, CloudProjectPluginManagerInterface $project_plugin_manager) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->serverTemplatePluginManager = $project_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('plugin.manager.cloud_project_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;
    return $this->t('Are you sure you want to launch an instance from %name?', [
      '%name' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $entity = $this->entity;
    $url = $entity->toUrl('canonical');
    $url->setRouteParameter('cloud_context', $entity->getCloudContext());
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Launching an instance can incur costs from the cloud service provider');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Launch');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // Launch the instance here.
    $redirect_route = $this->serverTemplatePluginManager->launch($this->entity, $form_state);
    // Let other modules alter the redirect after a cloud project has
    // been launched.
    $this->moduleHandler->invokeAll('cloud_project_post_launch_redirect_alter', [&$redirect_route, $this->entity]);
    $form_state->setRedirectUrl(new Url($redirect_route['route_name'], $redirect_route['params']));

    // Clear block and menu cache.
    CloudContentEntityBase::updateCache();
  }

}
