<?php

namespace Drupal\cloud\Plugin\cloud\project;

use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for cloud_project_plugin managers.
 */
interface CloudProjectPluginManagerInterface extends PluginManagerInterface {

  /**
   * Load a plugin using the cloud_context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return CloudProjectPluginInterface
   *   loaded CloudProjectPlugin.
   */
  public function loadPluginVariant($cloud_context);

  /**
   * Launch a cloud project.
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The cloud project entity.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state if launch is called from a form.
   *
   * @return mixed
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  public function launch(CloudProjectInterface $cloud_project, FormStateInterface $form_state = NULL);

  /**
   * Build the header array for CloudProjectListBuilder.
   *
   * @param string $cloud_context
   *   Cloud context.
   *
   * @return array
   *   An array of headers.
   */
  public function buildListHeader($cloud_context);

  /**
   * Build the row for CloudProjectListBuilder.
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $entity
   *   A loaded cloud project entity.
   *
   * @return array
   *   An array of values for the row.
   */
  public function buildListRow(CloudProjectInterface $entity);

  /**
   * Update cloud project list.
   *
   * @param string $cloud_context
   *   The cloud context of cloud project entity.
   *
   * @return mixed
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  public function updateCloudProjectList($cloud_context);

}
