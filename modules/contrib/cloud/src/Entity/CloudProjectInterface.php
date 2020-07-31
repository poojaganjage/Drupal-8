<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining cloud project entities.
 *
 * @ingroup cloud_project
 */
interface CloudProjectInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface, CloudContextInterface {

  /**
   * Gets the cloud project name.
   *
   * @return string
   *   Name of the cloud project.
   */
  public function getName();

  /**
   * Sets the cloud project name.
   *
   * @param string $name
   *   The cloud project name.
   *
   * @return \Drupal\cloud\Entity\CloudProjectInterface
   *   The called cloud project entity.
   */
  public function setName($name);

  /**
   * Gets the cloud project creation timestamp.
   *
   * @return int
   *   Creation timestamp of the cloud project.
   */
  public function getCreatedTime();

  /**
   * Sets the cloud project creation timestamp.
   *
   * @param int $timestamp
   *   The cloud project creation timestamp.
   *
   * @return \Drupal\cloud\Entity\CloudProjectInterface
   *   The called cloud project entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the cloud project published status indicator.
   *
   * Unpublished cloud projects are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the cloud project is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a cloud project.
   *
   * @param bool $published
   *   TRUE to set this cloud project to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\cloud\Entity\CloudProjectInterface
   *   The called cloud project entity.
   */
  public function setPublished($published);

  /**
   * Gets the cloud project revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the cloud project revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\cloud\Entity\CloudProjectInterface
   *   The called cloud project entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the cloud project revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the cloud project revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\cloud\Entity\CloudProjectInterface
   *   The called cloud project entity.
   */
  public function setRevisionUserId($uid);

}
