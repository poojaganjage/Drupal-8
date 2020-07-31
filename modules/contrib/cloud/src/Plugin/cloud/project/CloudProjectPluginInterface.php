<?php

namespace Drupal\cloud\Plugin\cloud\project;

use Drupal\cloud\Entity\CloudProjectInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Common interfaces for a cloud project.
 *
 * @package Drupal\cloud\Plugin
 */
interface CloudProjectPluginInterface {

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
   * The cloud project contains all the information needed for that
   * particular cloud.
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $cloud_project
   *   The cloud project entity.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state if launch is called from a form.
   *
   * @return array
   *   An associative array with a redirect route and any parameters to build
   *   the route.
   */
  public function launch(CloudProjectInterface $cloud_project, FormStateInterface $form_state = NULL);

}
