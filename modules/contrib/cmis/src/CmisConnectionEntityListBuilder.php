<?php

declare(strict_types = 1);

namespace Drupal\cmis;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a listing of CMIS connection entities.
 */
class CmisConnectionEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('CMIS connection');
    $header['id'] = $this->t('Machine name');
    $header['process'] = $this->t('Browse');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $url = Url::fromUserInput('/cmis/browser/' . $entity->id());
    $link = Link::fromTextAndUrl($this->t('Browse'), $url);
    $row['process'] = $link;
    return $row + parent::buildRow($entity);
  }

}
