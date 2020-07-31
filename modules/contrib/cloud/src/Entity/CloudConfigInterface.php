<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining cloud service providers (CloudConfig).
 *
 * @ingroup cloud
 */
interface CloudConfigInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the cloud service provider (CloudConfig) name.
   *
   * @return string
   *   The name of the cloud service provider (CloudConfig).
   */
  public function getName();

  /**
   * Sets the cloud service provider (CloudConfig) name.
   *
   * @param string $name
   *   The cloud service provider (CloudConfig) name.
   *
   * @return \Drupal\cloud\Entity\CloudConfigInterface
   *   The called cloud service provider (CloudConfig) entity.
   */
  public function setName($name);

  /**
   * Gets the cloud service provider (CloudConfig) creation timestamp.
   *
   * @return int
   *   Creation timestamp of the cloud service provider (CloudConfig).
   */
  public function getCreatedTime();

  /**
   * Sets the cloud service provider (CloudConfig) creation timestamp.
   *
   * @param int $timestamp
   *   The cloud service provider (CloudConfig) creation timestamp.
   *
   * @return \Drupal\cloud\Entity\CloudConfigInterface
   *   The called cloud service provider (CloudConfig) entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the cloud service provider published status indicator.
   *
   * Unpublished cloud service providers are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the cloud service provider (CloudConfig) is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a cloud service provider (CloudConfig).
   *
   * @param bool $published
   *   TRUE to set this cloud service provider (CloudConfig) to published,
   *   FALSE to set it to unpublished.
   *
   * @return \Drupal\cloud\Entity\CloudConfigInterface
   *   The called cloud service provider (CloudConfig) entity.
   */
  public function setPublished($published);

  /**
   * Gets the cloud service provider (CloudConfig) revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the cloud service provider (CloudConfig) revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\cloud\Entity\CloudConfigInterface
   *   The called cloud service provider (CloudConfig) entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the cloud service provider (CloudConfig) revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the cloud service provider (CloudConfig) revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\cloud\Entity\CloudConfigInterface
   *   The called cloud service provider (CloudConfig) entity.
   */
  public function setRevisionUserId($uid);

  /**
   * Helper method that returns fid of the image.
   *
   * If no image is uploaded, it will return the default icon.
   *
   * @return int
   *   File id or NULL if not found.
   */
  public function getIconFid();

}
