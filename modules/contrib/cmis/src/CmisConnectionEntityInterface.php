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

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining CMIS connection entities.
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
interface CmisConnectionEntityInterface extends ConfigEntityInterface
{
    
    /**
     * Get CMIS url.
     *
     * @return string
     *   The string.
     */
    public function getCmisUrl();

    /**
     * Get CMIS user name.
     *
     * @return string
     *   The string.
     */
    public function getCmisUser();

    /**
     * Get CMIS password.
     *
     * @return string
     *   The string.
     */
    public function getCmisPassword();

    /**
     * Get CMIS repository id.
     *
     * @return int
     *   The int.
     */
    public function getCmisRepository();

}
