<?php

namespace Drupal\k8s\Entity;

/**
 * Provides an interface defining an exportable entity.
 *
 * @ingroup k8s
 */
interface K8sExportableEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function getCreationYaml();

  /**
   * {@inheritdoc}
   */
  public function setCreationYaml($creation_yaml);

}
