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

namespace Drupal\cmis\Plugin\Field\FieldWidget;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'cmis_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "cmis_field_widget",
 *   label = @Translation("Cmis field widget"),
 *   field_types = {
 *     "cmis_field"
 *   }
 * )
 */

/**
 * Class CmisFieldWidget.
 *
 * @category Module
 *
 * @package Drupal\cmis\Plugin\Field\FieldWidget
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "Release: 8"
 *
 * @link https://www.drupal.org/
 */
class CmisFieldWidget extends WidgetBase
{

    use StringTranslationTrait;

    /**
     * The cmis configuration.
     *
     * @var array
     */
    private $_cmisConfigurations = [];

    /**
     * The string translation information.
     *
     * @var Drupal\Core\StringTranslation\TranslationInterface
     */
    protected $stringTranslation;

    /**
     * Creates a new instance.
     *
     * @param TranslationInterface $string_translation The string translation.
     */
    public function __construct(TranslationInterface $string_translation)
    {
        $this->stringTranslation = $string_translation;
    }

    /**
     * The container Interface.
     *
     * @param $container The container variable.
     *
     * @return object 
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('string_translation')
        );
    }

    /**
     * Default Settings.
     *
     * @return object
     *   The object.
     */
    public static function defaultSettings()
    {
        return [
        'size' => 60,
        'placeholder' => '',
        'cmis_configuration' => '',
        ] + parent::defaultSettings();
    }

    /**
     * Create a Settings Form.
     *
     * @param array              $form       Build the form.
     * @param FormStateInterface $form_state Build the form using.
     *
     * @return array
     *   The array.
     */
    public function settingsForm(array $form, FormStateInterface $form_state)
    {
        $elements = [];

        $elements['size'] = [
        '#type' => 'number',
        '#title' => $this->t('Size of textfield'),
        '#default_value' => $this->getSetting('size'),
        '#required' => true,
        '#min' => 1,
        ];
        $elements['placeholder'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Placeholder'),
        '#default_value' => $this->getSetting('placeholder'),
        '#description' => $this->t(
            'Text that will be shown inside the 
            field until a value is entered. This hint is usually a 
            sample value or a brief description of the expected format.'
        ),
        ];

        if (empty($this->cmisConfigurations)) {
            $this->getConfigurations();
        }
        $elements['cmis_configuration'] = [
        '#type' => 'select',
        '#title' => $this->t('CMIS configuration'),
        '#description' => $this->t('Please choose one from CMIS configuration.'),
        '#options' => $this->cmisConfigurations,
        '#require' => true,
        '#default_value' => $this->getSetting('cmis_configuration'),
        ];

        return $elements;
    }

    /**
     * The Settings Summary.
     *
     * @return array
     *   The array.
     */
    public function settingsSummary()
    {
        if (empty($this->cmisConfigurations)) {
            $this->getConfigurations();
        }
        $summary = [];

        $summary[] = $this->t(
            'Textfield size: !size', 
            ['!size' => $this->getSetting('size')]
        );
        if (!empty($this->getSetting('placeholder'))) {
            $summary[] = $this->t(
                'Placeholder: @placeholder', 
                ['@placeholder' => $this->getSetting('placeholder')]
            );
        }
        $cmis_configuration = $this->getSetting('cmis_configuration');
        if (!empty($cmis_configuration)) {
            $summary[] = $this->t(
                'CMIS configuration: @cmis_configuration', 
                ['@cmis_configuration' => $this->cmisConfigurations
                [$cmis_configuration]]
            );
        }

        return $summary;
    }

    /**
     * Creating a form elements.
     *
     * @param FieldItemListInterface $items      The field list interface.
     * @param string                 $delta      A delta item.
     * @param array                  $element    The array elements.
     * @param array                  $form       The array form.
     * @param FormStateInterface     $form_state The form state interface.
     *
     * @return array
     *   The array.
     */
    public function formElement(FieldItemListInterface $items, 
        $delta, array $element, array &$form, 
        FormStateInterface $form_state
    ) {
        $title = isset($items[$delta]->title) ? $items[$delta]->title : null;
        $path = isset($items[$delta]->path) ? $items[$delta]->path : null;

        $element = [
        '#prefix' => '<div id="cmis-field-wrapper">',
        '#suffix' => '</div>',
        ];

        $element['title'] = [
        '#type' => 'textfield',
        '#default_value' => $title,
        '#size' => $this->getSetting('size'),
        '#placeholder' => $this->getSetting('placeholder'),
        '#maxlength' => $this->getFieldSetting('max_length'),
        '#attributes' => [
        'class' => ['edit-field-cmis-field'],
        ],
        ];

        $element['path'] = [
        '#type' => 'hidden',
        '#default_value' => $path,
        '#attributes' => [
        'class' => ['edit-field-cmis-path'],
        ],
        ];

        $url = Url::fromUserInput(
            '/cmis/browser/' . $this
            ->getSetting('cmis_configuration')
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
        'query' => ['type' => 'popup'],
        ];
        $url->setOptions($link_options);
        $element['cmis_browser'] = Link::fromTextAndUrl(
            $this->t('Browse'), $url
        )->toRenderable();
        $element['#attached']['library'][] = 'cmis/cmis-field';

        return $element;
    }

    /**
     * Message form values.
     *
     * @param array              $values     The values.
     * @param array              $form       The array form.
     * @param FormStateInterface $form_state The form state interface.
     *
     * @return array
     *   The array.
     */
    public function massageFormValues(array $values, array $form, 
        FormStateInterface $form_state
    ) {
        foreach ($values as &$item) {
            if (!empty($item['path'])) {
                $args = explode('/', $item['path']);
                $id = end($args);
                $item['path'] = '/cmis/document/' . $this
                ->getSetting('cmis_configuration') . '/' . $id;
            }
        }

        return $values;
    }

    /**
     * Get configuration entity to private variable.
     *
     * @return mixed
     *   The mixed.
     */
    private function _getConfigurations()
    {
        $this->cmisConfigurations = cmis_get_configurations();
    }

}
