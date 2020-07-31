<?php

namespace Drupal\cloud\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of cloud service provider (CloudConfig) type entities.
 */
class CloudConfigTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Cloud service provider type');
    $header['id'] = $this->t('ID');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
