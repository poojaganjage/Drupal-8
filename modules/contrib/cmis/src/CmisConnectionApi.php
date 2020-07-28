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

use Dkd\PhpCmis\Enum\BindingType;
use Dkd\PhpCmis\SessionFactory;
use Dkd\PhpCmis\SessionParameter;
use GuzzleHttp\Client;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of CmisConnectionApi.
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
class CmisConnectionApi
{

    /**
     * The configuration entity.
     *
     * @var object
     */
    private $_config;

    /**
     * Http invoker.
     *
     * @var object
     */
    private $_httpInvoker;

    /**
     * The parameters for connection type.
     *
     * @var array
     */
    private $_parameters;

    /**
     * The session factory for connection.
     *
     * @var object
     */
    private $_sessionFactory;

    /**
     * The session of connection.
     *
     * @var type
     */
    private $_session;

    /**
     * The root folder of CMIS repository.
     *
     * @var type
     */
    private $_rootFolder;

    /**
     * The Entity Type Manager.
     *
     * @var Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * Constructs an CmisConnectionApi object.
     *
     * @param EntityTypeManagerInterface $entityTypeManager The Entity Type Manager.
     * @param object                     $config            The configuration object.
     */
    public function __construct(EntityTypeManagerInterface $entityTypeManager, 
        $config = ''
    ) {
        $this->entityTypeManager = $entityTypeManager;
        $this->checkClient();
        $this->setConfig($config);
    }

    /**
     * The create method.
     *
     * @param $container The container variable.
     *
     * @return object
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

    /**
     * Check if cmis exists in drupal root vendor.
     *
     * The module use php-cmis-client library but the current version 1.0
     * depend the guzzle 5 version. Drupal use the guzzle 6 version.
     * In order to we able using this client we need install it to module vendor
     * folder.
     *
     * To install temporary to time when will ready new version of cmis client
     * you need to go to cmis module root folder (eg. modules/cmis or
     * modules/contrib/cmis) and call the next command:
     *    composer require dkd/php-cmis
     *
     * @return string
     *   The string.
     */
    public function checkClient()
    {
        // If not exists load it from cmis module vendor folder.
        if (!class_exists('BindingType')) {
            // Load CMIS using classes if composer not able to install them
            // to root vendor folder because guzzle 5 dependency.
            $path = drupal_get_path('module', 'cmis');
            if (file_exists($path . '/vendor/autoload.php')) {
                include_once $path . '/vendor/autoload.php';
            } else {
                throw new \Exception(
                    'Php CMIS Client 
                    library is not properly installed.'
                );
            }
        }
    }

    /**
     * Set the configuration fom configuration id.
     *
     * @param string $config_id The Configuration id.
     *
     * @return int
     *   The int.
     */
    private function _setConfig($config_id)
    {
        $storage = $this->entityTypeManager
            ->getStorage('cmis_connection_entity');
        if ($this->config = $storage->load($config_id)) {
            $this->setHttpInvoker();
        }
    }

    /**
     * Get configuration of this connection.
     *
     * @return type
     *   The type.
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set Http invoker.
     *
     * @return object
     *   The object.
     */
    private function _setHttpInvoker()
    {
        $auth = [
        'auth' => [
        $this->config->getCmisUser(),
        $this->config->getCmisPassword(),
        ],
        ];
        $this->httpInvoker = new Client($auth);
    }

    /**
     * Get Http invoker.
     *
     * @return object
     *   The object.
     */
    public function getHttpInvoker()
    {
        return $this->httpInvoker;
    }

    /**
     * Set default parameters.
     *
     * @return object
     *   The object.
     */
    public function setDefaultParameters()
    {
        $parameters = [
        SessionParameter::BINDING_TYPE => BindingType::BROWSER,
        SessionParameter::BROWSER_URL => $this->getConfig()->getCmisUrl(),
        SessionParameter::BROWSER_SUCCINCT => false,
        SessionParameter::HTTP_INVOKER_OBJECT => $this->getHttpInvoker(),
        ];

        $this->setParameters($parameters);
    }

    /**
     * Set parameters.
     *
     * @param array $parameters The array of parameters.
     *
     * @return array
     *   The array.
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        $this->setSessionFactory();
    }

    /**
     * Get parameters.
     *
     * @return array
     *   The array.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set session factory.
     *
     * @return type
     *   The type.
     */
    private function _setSessionFactory()
    {
        $this->sessionFactory = new SessionFactory();
        $this->setRepository();
    }

    /**
     * Get session factory.
     *
     * @return type
     *   The type.
     */
    public function getSessionFactory()
    {
        return $this->sessionFactory;
    }

    /**
     * Set repository.
     *
     * @return object
     *   The object.
     */
    private function _setRepository()
    {
        $repository_id = $this->config->getCmisRepository();
        // If no repository id is defined use the first repository
        if ($repository_id === null || $repository_id == '') {
            $repositories = $this->sessionFactory
                ->getRepositories($this->parameters);
            $this->parameters[SessionParameter::REPOSITORY_ID] 
                = $repositories[0]->getId();
        } else {
            $this->parameters[SessionParameter::REPOSITORY_ID] 
                = $repository_id;
        }

        $this->session = $this->sessionFactory
            ->createSession($this->parameters);
        $this->setRootFolder();
    }

    /**
     * Get session.
     *
     * @return object
     *   The object.
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set the root folder of the repository.
     *
     * @return object
     *   The object.
     */
    private function _setRootFolder()
    {
        $this->rootFolder = $this->session->getRootFolder();
    }

    /**
     * Get root folder of repository.
     *
     * @return object
     *   The object.
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * Get object by object id.
     *
     * @param string $id The object id.
     *
     * @return object
     *   Return the current object or null.
     */
    public function getObjectById($id = '')
    {
        if (empty($id)) {
            return null;
        }
         
        $var = $this->validObjectId($id); 
        if (!empty($var || !empty($this->validObjectId($id, 'cmis:document')))
        ) {
            $cid = $this->session->createObjectId($id);
            $object = $this->session->getObject($cid);

            return $object;
        }

        return null;
    }

    /**
     * Check the id is valid object.
     *
     * @param string $id       The Id Object.
     * @param string $type     The type of folder.
     * @param string $parentId The parent id.
     *
     * @return object
     *   the result object or empty array
     */
    public function validObjectId($id, $type = 'cmis:folder', 
        $parentId = ''
    ) {
        $where = "cmis:objectId='$id'";
        if (!empty($parentId)) {
            $where .= " AND IN_FOLDER('$parentId')";
        }

        $result = $this->session->queryObjects($type, $where);

        return $result;
    }

    /**
     * Check the name is valid object.
     *
     * @param string $name     The name of object.
     * @param string $type     The type of folder.
     * @param string $parentId The Parent id.
     *
     * @return object
     *   the result object or empty array
     */
    public function validObjectName($name, $type = 'cmis:folder', 
        $parentId = ''
    ) {
        $query = "SELECT * FROM $type WHERE cmis:name='$name'";
        if (!empty($parentId)) {
            $query .= " and IN_FOLDER('$parentId')";
        }
        $result = $this->session->query($query);

        return $result;
    }

}
