<?php

namespace Drupal\k8s\Controller;

use Drupal\cloud\Controller\CloudContentListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of K8s Entity.
 */
class K8sListBuilder extends CloudContentListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    $account = \Drupal::currentUser();

    if ($entity->getEntityTypeId() === 'k8s_deployment') {
      if ($account->hasPermission('edit any k8s deployment')
      || ($account->hasPermission('edit own k8s deployment')
      && $account->id() === $entity->getOwner()->id())) {
        $operations['scale'] = [
          'title' => $this->t('Scale'),
          'url' => $entity->toUrl('scale-form'),
          'weight' => -50,
        ];
      }
    }
    return $operations;
  }

}
