<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class CloudConfigStorage extends SqlContentEntityStorage implements CloudConfigStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(CloudConfigInterface $entity) {
    try {
      return $this->database->query(
        'SELECT vid FROM {cloud_config_revision} WHERE id=:id ORDER BY vid',
        [':id' => $entity->id()]
      )->fetchCol();
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    try {
      return $this->database->query(
        'SELECT vid FROM {cloud_config_field_revision} WHERE uid = :uid ORDER BY vid',
        [':uid' => $account->id()]
      )->fetchCol();
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(CloudConfigInterface $entity) {
    try {
      return $this->database->query('SELECT COUNT(*) FROM {cloud_config_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
        ->fetchField();
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('cloud_config_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
