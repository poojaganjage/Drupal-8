<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining cloud server template entities.
 *
 * @ingroup cloud_server_template
 */
interface CloudServerTemplateInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface, CloudContextInterface {

  /**
   * Gets the cloud server template name.
   *
   * @return string
   *   Name of the cloud server template.
   */
  public function getName();

  /**
   * Sets the cloud server template name.
   *
   * @param string $name
   *   The cloud server template name.
   *
   * @return \Drupal\cloud\Entity\CloudServerTemplateInterface
   *   The called cloud server template entity.
   */
  public function setName($name);

  /**
   * Gets the cloud server template creation timestamp.
   *
   * @return int
   *   Creation timestamp of the cloud server template.
   */
  public function getCreatedTime();

  /**
   * Sets the cloud server template creation timestamp.
   *
   * @param int $timestamp
   *   The cloud server template creation timestamp.
   *
   * @return \Drupal\cloud\Entity\CloudServerTemplateInterface
   *   The called cloud server template entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the cloud server template published status indicator.
   *
   * Unpublished cloud server templates are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the cloud server template is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a cloud server template.
   *
   * @param bool $published
   *   TRUE to set this cloud server template to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\cloud\Entity\CloudServerTemplateInterface
   *   The called cloud server template entity.
   */
  public function setPublished($published);

  /**
   * Gets the cloud server template revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the cloud server template revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\cloud\Entity\CloudServerTemplateInterface
   *   The called cloud server template entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the cloud server template revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the cloud server template revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\cloud\Entity\CloudServerTemplateInterface
   *   The called cloud server template entity.
   */
  public function setRevisionUserId($uid);

}
