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

use Dkd\PhpCmis\Data\FolderInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\sm_cmis\CMISException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of CmisBrowser.
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
class CmisBrowser
{

    use StringTranslationTrait;

    /**
     * Configuration id.
     *
     * @var string
     */
    protected $config;

    /**
     * Connection object.
     *
     * @var object
     */
    protected $connection;

    /**
     * The renderable content data.
     *
     * @var array
     */
    protected $data;

    /**
     * Parent folders list data. The renderable breadcrumb data.
     *
     * @var array
     */
    protected $breadcrumbs = [];

    /**
     * Folder id to browse.
     *
     * @var string
     */
    protected $folderId;

    /**
     * Current object.
     *
     * @var object
     */
    protected $current;

    /**
     * The browser popup flag.
     *
     * @var bool
     */
    protected $popup;

    /**
     * The browser cacheable flag.
     *
     * @var bool
     */
    protected $cacheable;

    /**
     * The string translation interface.
     *
     * @var Drupal\Core\StringTranslation\TranslationInterface
     */
    protected $stringTranslation;

    /**
     * An array of found redirect IDs to avoid recursion.
     *
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * The current user interface.
     *
     * @var Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Construct.
     *
     * @param TranslationInterface  $string_translation The string translation.
     * @param RequestStack          $requestStack       The Request Stack.
     * @param AccountProxyInterface $currentUser        The current user.
     * @param string                $config             The configuartion.
     * @param string                $folder_id          The folder id.
     */
    public function __construct(TranslationInterface $string_translation, 
        RequestStack $requestStack, AccountProxyInterface $currentUser, 
        $config = '', $folder_id = ''
    ) {
        $this->stringTranslation = $string_translation;
        $this->requestStack = $requestStack;
        $this->currentUser = $currentUser;
        if (!empty($config)) {
            $this->init($config, $folder_id);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param $container The container variable.
     *
     * @return object
     *   The object.
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('string_translation'),
            $container->get('request_stack'),
            $container->get('current_user')
        );
    }

    /**
     * Call from ajaxify url.
     *
     * @param string $config    The configuration.
     * @param string $folder_id The folder id.
     *
     * @return object
     *   The object.
     */
    public function ajaxCall($config = '', $folder_id = '')
    {
        $this->init($config, $folder_id);
        if ($this->connection && !empty($this->current) 
            && $browse = $this->browse()
        ) {
            $response = new AjaxResponse();
            $content = render($browse);
            $response->addCommand(
                new HtmlCommand(
                    '#cmis-browser-wrapper', 
                    $content
                )
            );

            return $response;
        }
    }

    /**
     * Get document by id.
     *
     * @param string $config      The configuration.
     * @param string $document_id The document id.
     *
     * @return object
     *   The object.
     */
    public function getDocument($config = '', $document_id = '')
    {
        $this->init($config, $document_id, 'cmis:document');
        if ($this->connection && !empty($this->current) 
            && $this->current->getBaseTypeId()->__toString() == 'cmis:document'
        ) {
            $id = $this->current->getId();
            $content = '';
            try {
                $content = $this->current->getContentStream($id);
            }
            catch (CMISException $e) {
                // TODO: testing this.
                $headers = ['' => 'HTTP/1.1 503 Service unavailable'];
                $response = new Response($content, 503, $headers);
                $response->send();
                exit();
            }

            $mime = $this->current->getContentStreamMimeType();
            $headers = [
            'Cache-Control' => 'no-cache, must-revalidate',
            'Content-type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . 
            $this->current->getName() . '"',
            ];
            $response = new Response($content, 200, $headers);
            $response->send();

            // TODO: Why a print and an exit?
            print($content);
            exit();
        }
    }

    /**
     * Get document properties.
     *
     * @return array
     *   the renderable array
     */
    public function getDocumentProperties()
    {
        if ($this->connection && !empty($this->current)) {
            $type_id = $this->current->getBaseTypeId()->__toString();
            $path = [];
            if ($type_id == 'cmis:document') {
                $url = Url::fromUserInput(
                    '/cmis/document/' . $this->config . '/' . 
                    $this->current->getId()
                );
                $path = Link::fromTextAndUrl($this->t('Download'), $url)
                ->toRenderable();
            }

            return [
            '#theme' => 'cmis_content_properties',
            '#object' => $this->current,
            '#download' => render($path),
            ];
        }
    }

    /**
     * Init variables.
     *
     * @param string $config    The configuration.
     * @param string $folder_id The folder id.
     *
     * @return object
     *   The object.
     */
    private function _init($config, $folder_id)
    {
        $this->config = $config;
        $this->folderId = $folder_id;
        $this->connection = new CmisConnectionApi($this->config);
        //$cacheable = $this->connection->getConfig()->getCmisCacheable();
        // TODO: find out the best cache options.
        //$cache_parameters = [
        //  'contexts' => ['user'],
        //  'max-age' => $cacheable ? 300 : 0,
        //];
        //$this->cacheable = $cache_parameters;
        if (!empty($this->connection->getHttpInvoker())) {
            $popup = $this->requestStack->getCurrentRequest()->query->get('type');
            $this->popup = ($popup == 'popup');
            $this->connection->setDefaultParameters();

            if (empty($this->folderId)) {
                $root_folder = $this->connection->getRootFolder();
                $this->folderId = $root_folder->getId();
                $this->current = $root_folder;
            } else {
                $this->current = $this->connection->getObjectById($this->folderId);
            }
        }
    }

    /**
     * Get current object.
     *
     * @return object
     *   The object.
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Get connection.
     *
     * @return object
     *   The object.
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Browse.
     *
     * @param bool $reset The reset.
     *
     * @return array
     *   Return cmis browser render array.
     */
    public function browse($reset = false)
    {
        if ($this->connection && !empty($this->current)) {

            $this->setBreadcrumbs($this->current, 'last');
            $this->printFolderContent($this->current);

            $table_header = [
            $this->t('Name'),
            $this->t('Details'),
            $this->t('Author'),
            $this->t('Created'),
            $this->t('Description'),
            $this->t('Operation'),
            ];

            $browse = [
            '#theme' => 'cmis_browser',
            '#header' => $table_header,
            '#elements' => $this->data,
            '#breadcrumbs' => $this->breadcrumbs,
            '#operations' => $this->prepareOperations(),
            '#attached' => [
            'library' => [
            'cmis/cmis-browser',
            ],
            ],
            ];

            return $browse;
        }

        return [];
    }

    /**
     * Prepare operation links.
     *
     * @return string
     *   The string.
     */
    private function _prepareOperations()
    {
        if (!$this->currentUser->hasPermission('access cmis operations')) {
            return '';
        }

        $routes = [
        '/cmis/browser-create-folder/' => $this->t('Create folder'),
        '/cmis/browser-upload-document/' => $this->t('Add document'),
        ];

        $links = [];
        foreach ($routes as $route => $title) {
            $url = Url::fromUserInput(
                $route . $this->config . '/' . 
                $this->current->getId()
            );
            $link_options = [
            'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode(
                [
                'height' => 400,
                'width' => 700,
                ]
            ),
            ],
            ];
            $url->setOptions($link_options);
            $path = Link::fromTextAndUrl($title, $url)->toRenderable();
            $links[] = [
            '#markup' => render($path),
            '#wrapper_attributes' => [
            'class' => ['object-properties'],
            ],
            ];
        }

        $list = [
        '#theme' => 'item_list',
        '#items' => $links,
        '#type' => 'ul',
        ];

        return render($list);
    }

    /**
     * Add folder objects to render array.
     *
     * @param \Dkd\PhpCmis\Data\FolderInterface $folder A CMIS folder object.
     *
     * @return object
     *   The object.
     */
    protected function printFolderContent(FolderInterface $folder)
    {
        $root = $this->connection->getRootFolder();
        $element = new CmisElement(
            $this->config, $this->popup, 
            $this->current, '', $root->getId()
        );
        foreach ($folder->getChildren() as $children) {
            $element->setElement('browser', $children);
            $this->data[] = $element->getData();
        }
    }

    /**
     * Create breadcrumbs from parent folders.
     *
     * @param type   $folder The folder.
     * @param string $class  The class.
     *
     * @return string
     *   The string.
     */
    protected function setBreadcrumbs($folder, $class = '')
    {
        $name = $folder->getName();
        $id = $folder->getId();
        $this->setBreadcrumb($name, $id, $class);
        if ($parent = $folder->getFolderParent()) {
            $this->setBreadcrumbs($parent);
        } else {
            $this->breadcrumbs[0]['#wrapper_attributes']['class'] 
                = ['first'];
        }
    }

    /**
     * Prepare a breadcrumb url.
     *
     * @param type   $label The label.
     * @param string $id    The id.
     * @param $class The class.
     *
     * @return array
     *   The array.
     */
    protected function setBreadcrumb($label, $id, $class)
    {
        $path = '/cmis/browser/nojs/' . $this->config;
        if (!empty($id)) {
            $path .= '/' . $id;
        }
        $url = Url::fromUserInput($path);
        $link_options = [
        'attributes' => [
        'class' => [
          'use-ajax',
        ],
        ],
        ];
        if ($this->popup) {
            $link_options['query'] = ['type' => 'popup'];
        }
        $url->setOptions($link_options);

        $item = [
        'value' => Link::fromTextAndUrl($label, $url)->toRenderable(),
        '#wrapper_attributes' => [
        'class' => [$class],
        ],
        ];

        array_unshift($this->breadcrumbs, $item);
    }

}
