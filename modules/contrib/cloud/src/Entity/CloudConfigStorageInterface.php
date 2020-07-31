<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * The storage handler class for cloud service provider (CloudConfig) entities.
 *
 * This extends the base storage class, adding required special handling for
 * cloud service provider (CloudConfig) entities.
 *
 * @ingroup cloud
 */
interface CloudConfigStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of revision IDs for a specific cloud service provider.
   *
   * @param \Drupal\cloud\Entity\CloudConfigInterface $entity
   *   The cloud service provider (CloudConfig) entity.
   *
   * @return int[]
   *   The cloud service provider (CloudConfig) revision IDs in ascending order.
   */
  public function revisionIds(CloudConfigInterface $entity);

  /**
   * Gets a list of revision IDs having a user as cloud service provider author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   The cloud service provider (CloudConfig) revision IDs (in ascending
   *   order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\cloud\Entity\CloudConfigInterface $entity
   *   The cloud service provider (CloudConfig) entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(CloudConfigInterface $entity);

  /**
   * Unsets the language for all cloud service providers w/ the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
