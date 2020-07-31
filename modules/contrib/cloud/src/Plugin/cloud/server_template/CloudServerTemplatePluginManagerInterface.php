<?php

namespace Drupal\cloud\Plugin\cloud\server_template;

use Drupal\cloud\Entity\CloudServerTemplateInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for cloud_server_template_plugin managers.
 */
interface CloudServerTemplatePluginManagerInterface extends PluginManagerInterface {

  /**
   * Load a plugin using the cloud_context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   *
   * @return CloudServerTemplatePluginInterface
   *   loaded CloudServerTemplatePlugin.
   */
  public function loadPluginVariant($cloud_context);

  /**
   * Launch a cloud server template.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $cloud_server_template
   *   The cloud server template entity.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state if launch is called from a form.
   *
   * @return mixed
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  public function launch(CloudServerTemplateInterface $cloud_server_template, FormStateInterface $form_state = NULL);

  /**
   * Build the header array for CloudServerTemplateListBuilder.
   *
   * @param string $cloud_context
   *   Cloud context.
   *
   * @return array
   *   An array of headers.
   */
  public function buildListHeader($cloud_context);

  /**
   * Build the row for CloudServerTemplateListBuilder.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $entity
   *   A loaded cloud server template entity.
   *
   * @return array
   *   An array of values for the row.
   */
  public function buildListRow(CloudServerTemplateInterface $entity);

  /**
   * Update cloud server template list.
   *
   * @param string $cloud_context
   *   The cloud context of cloud server template entity.
   *
   * @return mixed
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  public function updateCloudServerTemplateList($cloud_context);

  /**
   * Build the launch form for K8s.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $cloud_server_template
   *   The cloud server template entity.
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state if launch is called from a form.
   */
  public function buildLaunchForm(CloudServerTemplateInterface $cloud_server_template, array &$form, FormStateInterface $form_state);

}
