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

use Dkd\PhpCmis\DataObjects\AbstractFileableCmisObject;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of CmisElement.
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
class CmisElement
{

    use StringTranslationTrait;

    /**
     * Cmis element.
     *
     * @var object
     */
    private $_element;

    /**
     * Config.
     *
     * @var string
     */
    private $_config;

    /**
     * Popup flag.
     *
     * @var string
     */
    private $_popup;

    /**
     * Element parent id.
     *
     * @var string
     */
    private $_parent;

    /**
     * The query string from cmis query form.
     *
     * @var string
     */
    private $_queryString;

    /**
     * Render array of element.
     *
     * @var array
     */
    private $_data = [];

    /**
     * The type of caller.
     *
     * @var string
     */
    private $_type;

    /**
     * Root folder id.
     *
     * @var object
     */
    private $_rootId;

    /**
     * The string translation information.
     *
     * @var Drupal\Core\StringTranslation\TranslationInterface
     */
    protected $stringTranslation;

    /**
     * The current user interface.
     *
     * @var Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Contruct method.
     *
     * @param TranslationInterface       $string_translation The string translation.
     * @param AccountProxyInterface      $currentUser        The current user.
     * @param string                     $config             The configuration.
     * @param bool                       $popup              The popup.
     * @param AbstractFileableCmisObject $parent             The parent.
     * @param string                     $query              The query.
     * @param string                     $root_id            The root id.
     */
    public function __construct(TranslationInterface $string_translation, 
        AccountProxyInterface $currentUser, $config, $popup = false, 
        AbstractFileableCmisObject $parent = null, $query = '', $root_id = ''
    ) {
        $this->stringTranslation = $string_translation;
        $this->currentUser = $currentUser;
        $this->config = $config;
        $this->popup = $popup;
        $this->parent = $parent;
        $this->queryString = $query;
        $this->rootId = $root_id;
    }

    /**
     * The container Interface.
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
            $container->get('current_user')
        );
    }

    /**
     * Set element.
     *
     * @param string                     $type    The type.
     * @param AbstractFileableCmisObject $element The element.
     * 
     * @return array
     *   The array.
     */
    public function setElement($type, AbstractFileableCmisObject $element)
    {
        $this->type = $type;
        $this->element = $element;

        $this->prepare();
    }

    /**
     * Prepare element data.
     *
     * @return array
     *   The array.
     */
    protected function prepare()
    {
        $type_id = $this->element->getBaseTypeId()->__toString();
        $name = $this->element->getName();
        $id = $this->element->getId();
        $link_options = [];
        switch ($type_id) {
        case 'cmis:folder':
            switch ($this->type) {
            case 'browser':
                $url = Url::fromUserInput(
                    '/cmis/browser/nojs/' . 
                    $this->config . '/' . $id
                );
                $link_options = [
                'attributes' => [
                'class' => [
                  'use-ajax',
                ],
                ],
                ];
                break;

            case 'query':
                $url = Url::fromUserInput(
                    '/cmis/browser/' . 
                    $this->config . '/' . $id
                );
                break;
            }

            if ($this->popup) {
                $link_options['query'] = ['type' => 'popup'];
            }

            if (!empty($link_options)) {
                $url->setOptions($link_options);
            }

            $link = Link::fromTextAndUrl($name, $url)->toRenderable();
            $this->prepareElement('cmis_browser_folder_item', $link);
            break;

        case 'cmis:document':
            $this->prepareElement('cmis_browser_document_item', $name, $id);
            break;

        default:
            $element = [
            '#theme' => 'cmis_browser_other_item',
            '#element' => $name,
            //'#cache' => $this->cacheable,
            ];
            $this->data = [render($element)];
        }
    }

    /**
     * Get element data.
     *
     * @return array
     *   The array.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set element to render array.
     *
     * @param string $theme The theme.
     * @param array  $data  The data.
     * @param string $id    The id.
     *
     * @return array
     *   The array.
     */
    protected function prepareElement($theme, array $data, $id = '')
    {
        $author = $this->element->getCreatedBy();
        $created = $this->element->getCreationDate()->format('Y-m-d H:i:s');
        $description = $this->element->getDescription();

        $title = '';
        if ($title_property = $this->element->getProperty('cm:title')) {
            $title = $title_property->getFirstValue();
        }

        $size = 0;
        if ($size_property = $this->element->getProperty('cmis:contentStreamLength')
        ) {
            $size = $size_property->getFirstValue();
        }

        $mime_type = '';
        $operations = '';
        if ($theme == 'cmis_browser_document_item') {
            $this->prepareDocumentElement($data, $operations, $id);
        }
        if (!$this->popup) {
            $this->preparePropertiesLink($operations);
        }

        $element = [
        '#theme' => $theme,
        '#element' => $data,
        //'#cache' => $this->cacheable,
        ];

        $details = [
        '#theme' => 'cmis_browser_document_details',
        '#title' => $title,
        '#mime_type' => $mime_type,
        '#size' => number_format($size, 0, '', ' '),
        //'#cache' => $this->cacheable,
        ];

        $this->data = [
        render($element),
        render($details),
        $author,
        $created,
        $description,
        $operations,
        ];
    }

    /**
     * Prepare document element.
     *
     * @param array  $data       The data.
     * @param string $operations The operations.
     * @param string $id         The id.
     *
     * @return array
     *   The array.
     */
    private function _prepareDocumentElement(array &$data, &$operations, $id)
    {
        if ($this->popup) {
            $url = Url::fromUserInput('/');
            $link_options = [
            'attributes' => [
            'class' => [
            'cmis-field-insert',
            ],
            'id' => $this->element->getProperty('cmis:objectId')
                ->getFirstValue(),
            'name' => $data,
            ],
            ];
            $url->setOptions($link_options);
            $path = Link::fromTextAndUrl($this->t('Choose'), $url)
            ->toRenderable();
            $operations = render($path);
        }

        $url = Url::fromUserInput(
            '/cmis/document/' . $this
            ->config . '/' . $id
        );
        $path = Link::fromTextAndUrl($data, $url)->toRenderable();
        $data = ['#markup' => render($path)];
    }

    /**
     * Prepare properties link.
     *
     * @param string $operations The operations.
     *
     * @return array
     *   The array.
     */
    private function _preparePropertiesLink(&$operations)
    {
        $url = Url::fromUserInput(
            '/cmis/object-properties/' . $this
                ->config . '/' . $this->element->getId()
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
        $path = Link::fromTextAndUrl($this->t('Properties'), $url)
        ->toRenderable();
        $links[] = [
        '#markup' => render($path),
        '#wrapper_attributes' => [
        'class' => [
          'object-properties',
        ],
        ],
        ];

        if ($this->rootId != $this->element->getId() 
            && $this->currentUser->hasPermission('access cmis operations')
        ) {
            $url = Url::fromUserInput(
                '/cmis/object-delete-verify/' . $this
                    ->config . '/' . $this->element->getId()
            );
            $link_options = [
            'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode(
                [
                'height' => 120,
                'width' => 600,
                ]
            ),
            ],
            'query' => [
            'type' => $this->type,
            ],
            ];
            switch ($this->type) {
            case 'browser':
                $link_options['query']['parent'] = $this->parent->getId();
                break;

            case 'query':
                $link_options['query']['query_string'] = $this->queryString;
                break;
            }
            $url->setOptions($link_options);
            $path = Link::fromTextAndUrl($this->t('Delete'), $url)->toRenderable();
            $links[] = [
            '#markup' => render($path),
            '#wrapper_attributes' => [
            'class' => [
            'object-delete',
            ],
            ],
            ];
        }

        $list = [
        '#theme' => 'item_list',
        '#items' => $links,
        '#type' => 'ul',
        ];

        $operations = render($list);
    }

}
