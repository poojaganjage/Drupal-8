<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Job entity.
 *
 * @ingroup k8s
 */
interface K8sJobInterface extends ContentEntityInterface, EntityOwnerInterface, K8sExportableEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function getNamespace();

  /**
   * {@inheritdoc}
   */
  public function setNamespace($namespace);

  /**
   * {@inheritdoc}
   */
  public function getImage();

  /**
   * {@inheritdoc}
   */
  public function setImage($image);

  /**
   * {@inheritdoc}
   */
  public function getCompletions();

  /**
   * {@inheritdoc}
   */
  public function setCompletions($completions);

  /**
   * {@inheritdoc}
   */
  public function getParallelism();

  /**
   * {@inheritdoc}
   */
  public function setParallelism($parallelism);

  /**
   * {@inheritdoc}
   */
  public function getActive();

  /**
   * {@inheritdoc}
   */
  public function setActive($active);

  /**
   * {@inheritdoc}
   */
  public function getFailed();

  /**
   * {@inheritdoc}
   */
  public function setFailed($failed);

  /**
   * {@inheritdoc}
   */
  public function getSucceeded();

  /**
   * {@inheritdoc}
   */
  public function setSucceeded($succeeded);

}
