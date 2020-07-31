<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a K8s entity.
 *
 * @ingroup k8s
 */
interface K8sEntityInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getCloudContext();

  /**
   * {@inheritdoc}
   */
  public function getName();

  /**
   * {@inheritdoc}
   */
  public function setName($name);

  /**
   * {@inheritdoc}
   */
  public function getLabels();

  /**
   * {@inheritdoc}
   */
  public function setLabels(array $labels);

  /**
   * {@inheritdoc}
   */
  public function getAnnotations();

  /**
   * {@inheritdoc}
   */
  public function setAnnotations(array $annotations);

  /**
   * {@inheritdoc}
   */
  public function getDetail();

  /**
   * {@inheritdoc}
   */
  public function setDetail($detail);

  /**
   * {@inheritdoc}
   */
  public function created();

  /**
   * {@inheritdoc}
   */
  public function changed();

  /**
   * {@inheritdoc}
   */
  public function getRefreshed();

  /**
   * {@inheritdoc}
   */
  public function setRefreshed($time);

}
