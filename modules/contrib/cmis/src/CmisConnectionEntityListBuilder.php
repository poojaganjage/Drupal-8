<?php

/**
 * Provides cmis module Implementation.
 *
 * @category Module
 *
 * @package Contrib
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "GIT: <1001>"
 *
 * @link https://www.drupal.org/
 */

declare(strict_types = 1);

namespace Drupal\cmis;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a listing of CMIS connection entities.
 *
 * @category Module
 *
 * @package Drupal\cmis
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "Release: 8"
 *
 * @link https://www.drupal.org/
 */
class CmisConnectionEntityListBuilder extends ConfigEntityListBuilder
{

    /**
     * The build header.
     *
     * @return object.
     *   The object.
     */
    public function buildHeader()
    {
        $header['label'] = $this->t('CMIS connection');
        $header['id'] = $this->t('Machine name');
        $header['process'] = $this->t('Browse');
        return $header + parent::buildHeader();
    }

    /**
     * The build row.
     *
     * @param EntityInterface $entity The entity interface.
     *
     * @return object.
     *   The object.
     */
    public function buildRow(EntityInterface $entity)
    {
        $row['label'] = $entity->label();
        $row['id'] = $entity->id();
        $url = Url::fromUserInput('/cmis/browser/' . $entity->id());
        $link = Link::fromTextAndUrl($this->t('Browse'), $url);
        $row['process'] = $link;
        return $row + parent::buildRow($entity);
    }

}
