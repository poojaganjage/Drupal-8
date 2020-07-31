<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the storage handler class for cloud server template entities.
 *
 * This extends the base storage class, adding required special handling for
 * The cloud server template entities.
 *
 * @ingroup cloud_server_template
 */
interface CloudServerTemplateStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of cloud server template revision IDs.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $entity
   *   The cloud server template entity.
   *
   * @return int[]
   *   Cloud server template revision IDs (in ascending order).
   */
  public function revisionIds(CloudServerTemplateInterface $entity);

  /**
   * Gets a list of revision IDs given a cloud server template author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Cloud server template revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $entity
   *   The cloud server template entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(CloudServerTemplateInterface $entity);

  /**
   * Unsets the language for all cloud server template with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
