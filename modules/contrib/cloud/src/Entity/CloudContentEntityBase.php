<?php

namespace Drupal\cloud\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Base class for all cloud based entities.
 *
 * The main purpose of this class is to provide the
 * cloud_context as a parameter in urlRouteParameters method.
 */
class CloudContentEntityBase extends RevisionableContentEntityBase implements EntityOwnerInterface, CloudContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCloudContext() {
    return $this->get('cloud_context')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCloudContext($cloud_context) {
    $this->set('cloud_context', $cloud_context);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    return $this->set('uid', $uid);
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    return $this->set('uid', $account->id());
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerById($id) {
    return $this->set('uid', $id);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    return $this->set('name', $name);
  }

  /**
   * {@inheritdoc}
   */
  public function created() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function changed() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = [];

    if (!in_array($rel, ['collection', 'add-page', 'add-form'], TRUE)) {
      // The entity ID is needed as a route parameter.
      $uri_route_parameters[$this->getEntityTypeId()] = $this->id();
    }
    if ($rel === 'add-form'
    && (!empty($this->getEntityType())
    && !empty($this->getEntityType()->hasKey('bundle')))) {
      $parameter_name = !empty($this->getEntityType()->getBundleEntityType()) ?: $this->getEntityType()->getKey('bundle');
      $uri_route_parameters[$parameter_name] = $this->bundle();
    }
    if ($rel === 'revision' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    // Add in cloud context.
    $uri_route_parameters['cloud_context'] = $this->getCloudContext();

    return $uri_route_parameters;
  }

  /**
   * Helper method to update caches.
   *
   * Be careful using this function especially during batch processing
   * or entity imports.  Call this method only when necessary.
   */
  public static function updateCache() {
    // Clear block and menu cache.
    \Drupal::cache('menu')->invalidateAll();
    \Drupal::service('cache.render')->deleteAll();
    \Drupal::service('router.builder')->rebuild();
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
  }

}
