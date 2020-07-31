<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the storage handler class for cloud project entities.
 *
 * This extends the base storage class, adding required special handling for
 * The cloud project entities.
 *
 * @ingroup cloud_project
 */
interface CloudProjectStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of cloud project revision IDs.
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $entity
   *   The cloud project entity.
   *
   * @return int[]
   *   Cloud project revision IDs (in ascending order).
   */
  public function revisionIds(CloudProjectInterface $entity);

  /**
   * Gets a list of revision IDs given a cloud project author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Cloud project revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\cloud\Entity\CloudProjectInterface $entity
   *   The cloud project entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(CloudProjectInterface $entity);

  /**
   * Unsets the language for all cloud project with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
