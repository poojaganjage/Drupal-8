<?php

namespace Drupal\cloud\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManagerInterface;
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
class CloudServerTemplateLaunchConfirm extends ContentEntityConfirmFormBase {

  /**
   * The CloudServerTemplatePluginManager.
   *
   * @var \Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManager
   */
  protected $serverTemplatePluginManager;

  /**
   * Construct a CloudServerTemplateLaunchConfirm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginManagerInterface $server_template_plugin_manager
   *   The cloud server template plugin manager.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, CloudServerTemplatePluginManagerInterface $server_template_plugin_manager) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->serverTemplatePluginManager = $server_template_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('plugin.manager.cloud_server_template_plugin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->entity;
    return $this->t('Are you sure you want to launch instance(s) or resource(s) from %name?', [
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
    return $this->t('<p>Launching instance(s) or resource(s) can incur costs from the cloud service provider.</p>');
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
    // Let other modules alter the redirect after a cloud server template has
    // been launched.
    \Drupal::moduleHandler()->invokeAll('cloud_server_template_post_launch_redirect_alter', [&$redirect_route, $this->entity]);
    $form_state->setRedirectUrl(new Url($redirect_route['route_name'], $redirect_route['params']));

    // Clear block and menu cache.
    CloudContentEntityBase::updateCache();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $this->serverTemplatePluginManager->buildLaunchForm($this->entity, $form, $form_state);

    return $form;
  }

}
