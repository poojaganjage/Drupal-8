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

namespace Drupal\cmis\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cmis\CmisConnectionEntityInterface;

/**
 * Defines the CMIS connection entity.
 *
 * @ConfigEntityType(
 *   id = "cmis_connection_entity",
 *   label = @Translation("CMIS connection"),
 *   handlers = {
 *     "list_builder" = "Drupal\cmis\CmisConnectionEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmis\Form\CmisConnectionEntityForm",
 *       "edit" = "Drupal\cmis\Form\CmisConnectionEntityForm",
 *       "delete" = "Drupal\cmis\Form\CmisConnectionEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cmis\CmisConnectionEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cmis_connection_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmis/connection/cmis_connection_entity/{cmis_connection_entity}",
 *     "add-form" = "/admin/config/cmis/connection/cmis_connection_entity/add",
 *     "edit-form" = "/admin/config/cmis/connection/cmis_connection_entity/{cmis_connection_entity}/edit",
 *     "delete-form" = "/admin/config/cmis/connection/cmis_connection_entity/{cmis_connection_entity}/delete",
 *     "collection" = "/admin/config/cmis/connection/cmis_connection_entity"
 *   }
 * )
 */

/**
 * Class CmisConnectionEntity.
 *
 * @category Module
 *
 * @package Drupal\cmis\Entity
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "Release: 8"
 *
 * @link https://www.drupal.org/
 */
class CmisConnectionEntity extends ConfigEntityBase implements 
CmisConnectionEntityInterface
{

    /**
     * The CMIS connection ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The CMIS connection label.
     *
     * @var string
     */
    protected $label;

    /**
     * The CMIS connection url.
     *
     * @var string
     */
    protected $cmis_url;

    /**
     * The CMIS connection user.
     *
     * @var string
     */
    protected $cmis_user;

    /**
     * The CMIS connection password.
     *
     * @var string
     */
    protected $cmis_password;

    /**
     * The CMIS connection repository id.
     *
     * @var string
     */
    protected $cmis_repository;

    /**
     * The CMIS repository cacheable flag.
     *
     * @var bool
     */
    protected $cmis_cacheable;

    /**
     * The CMIS repository cacheable flag.
     *
     * @return string
     *   The string.
     */
    public function getCmisUrl()
    {
        return $this->cmis_url;
    }

    /**
     * The CMIS repository cacheable flag.
     *
     * @return string
     *   The string.
     */
    public function getCmisUser()
    {
        return $this->cmis_user;
    }

    /**
     * The CMIS repository cacheable flag.
     *
     * @return string
     *   The string.
     */
    public function getCmisPassword()
    {
        return $this->cmis_password;
    }

    /**
     * The CMIS repository cacheable flag.
     *
     * @return string
     *   The string.
     */
    public function getCmisRepository()
    {
        return $this->cmis_repository;
    }

    /**
     * Get CMIS repository cacheable flag.
     *
     * @return bool
     *   The bool.
     */
    public function getCmisCacheable()
    {
        return $this->cmis_cacheable;
    }

}
