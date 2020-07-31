<?php

namespace Drupal\cloud\Plugin\cloud\server_template;

use Drupal\cloud\Entity\CloudServerTemplateInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Common interfaces for a cloud server template.
 *
 * @package Drupal\cloud\Plugin
 */
interface CloudServerTemplatePluginInterface {

  /**
   * Get the entity bundle defined for a particular plugin.
   *
   * @return string
   *   The entity bundle used to store and interact with a particular cloud
   */
  public function getEntityBundleName();

  /**
   * Method interacts with the implementing cloud's launch functionality.
   *
   * The cloud server template contains all the information needed for that
   * particular cloud.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $cloud_server_template
   *   The cloud server template entity.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state if launch is called from a form.
   *
   * @return array
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  public function launch(CloudServerTemplateInterface $cloud_server_template, FormStateInterface $form_state = NULL);

}
