<?php

namespace Drupal\k8s\Form;

use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * Provides an entities deletion confirmation form.
 */
class K8sNamespaceDeleteMultipleForm extends K8sDeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    // Delete the role if it exists.
    $roles = $this->entityTypeManager->getStorage('user_role')
      ->loadByProperties([
        'id' => $entity->getName(),
      ]);

    if (!empty($roles)) {
      $role = reset($roles);
      $role->delete();
    }

    return $this->deleteCloudResource($entity, 'deleteNamespace', FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {

    return $this->formatPlural(count($this->selection),
      'Are you sure you want to delete this @item?<br>CAUTION: The role is also going to be deleted.',
      'Are you sure you want to delete these @items?<br>CAUTION: The roles are also going to be deleted.', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]
    );
  }

}
