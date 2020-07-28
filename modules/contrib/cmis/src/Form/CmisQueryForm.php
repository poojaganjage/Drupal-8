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

namespace Drupal\cmis\Form;

use Drupal\cmis\CmisConnectionApi;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cmis\CmisElement;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CmisQueryForm.
 *
 * @category Module
 *
 * @package Drupal\cmis\Form
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "Release: 8"
 *
 * @link https://www.drupal.org/
 */
class CmisQueryForm extends FormBase
{
    
    use StringTranslationTrait;

    /**
     * Configuration ID.
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
     *   The object. 
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('string_translation')
        );
    }
    
    /**
     * {@inheritdoc}
     *
     * @return int
     *   The int.
     */
    public function getFormId()
    {
        return 'cmis_query_form';
    }

    /**
     * Defines form and form state interface and build form.
     *
     * Build the form using $form varibale using.
     *
     * @param array              $form       Build the form.
     * @param FormStateInterface $form_state Build the form.
     *
     * @return array
     *   The array.
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $parameters = $this->getRequest()->query->all();
        unset($parameters['type']);
        $configuration_options = cmis_get_configurations();
        unset($configuration_options['_none']);
        $first_config = reset($configuration_options);
        $input = $form_state->getUserInput();
        $user_inputs = array_merge($parameters, $input);
        if (!empty($user_inputs)) {
            $form_state->setUserInput($user_inputs);
        }
        $input = $user_inputs;

        $form['config'] = [
        '#type' => 'select',
        '#title' => $this->t('Configuration'),
        '#description' => $this->t('Select the configuration for repository.'),
        '#options' => $configuration_options,
        '#default_value' => !empty($input['config']) ? $input['config'] : 
        key($first_config),
        ];

        $form['query_string'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Query string'),
        '#description' => $this->t('Enter a valid CMIS query.'),
        '#default_value' => !empty($input['query_string']) ? 
        $input['query_string'] : '',
        ];

        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Run'),
        '#ajax' => [
        'callback' => '::ajaxGetResult',
        'wrapper' => 'query-result-wrapper',
        ],
        ];

        $result = '';
        if (!empty($input['query_string']) 
            && !empty($input['config'])
        ) {
            $this->config = $input['config'];
            if (empty($this->connection)) {
                $this->connection = new CmisConnectionApi($this->config);
            }
            if (!empty($this->connection->getHttpInvoker())) {
                $result = $this->queryExec($this->config, $input['query_string']);
            }
        }

        $form['result'] = [
        '#markup' => $result,
        '#prefix' => '<div id="query-result-wrapper">',
        '#suffix' => '</div>',
        ];

        return $form;
    }

    /**
     * Submit the form using $form varibale using.
     *
     * @param array              $form       Submit the form.
     * @param FormStateInterface $form_state Submit the form.
     *
     * @return array
     *   The array.
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // No submit handling here.
    }

    /**
     * Execute query string.
     *
     * @param string $config The configuration.
     * @param string $query  The query.
     *
     * @return string
     *   The string.
     */
    public function queryExec($config = '', $query = '')
    {
        $content = '';
        if (empty($config)) {
            if (!empty($this->config)) {
                $config = $this->config;
            } else {
                return $content;
            }
        }

        if (!empty($query)) {
            $this->connection->setDefaultParameters();
            $session = $this->connection->getSession();
            $results = $session->query($query);
            $content = $this->prepareResult($results, $query);
        }

        return $content;
    }

    /**
     * Prepare results to rendered table.
     *
     * @param array  $results The results.
     * @param string $query   The query.
     *
     * @return string
     *   The string.
     */
    private function _prepareResult(array $results, $query)
    {
        $content = '';
        $rows = [];
        $table_header = [
        $this->t('Name'),
        $this->t('Details'),
        $this->t('Author'),
        $this->t('Created'),
        $this->t('Description'),
        $this->t('Operation'),
        ];
        $root = $this->connection->getRootFolder();
        $element = new CmisElement(
            $this->config, false, null, $query, $root->getId()
        );
        if ($session = $this->connection->getSession()) {
            foreach ($results as $result) {
                $id = $result->getPropertyValueById('cmis:objectId');
                $cid = $session->createObjectId($id);
                if ($object = $session->getObject($cid)) {
                    $element->setElement('query', $object);
                    $rows[] = $element->getData();
                }
            }

            if (!empty($rows)) {
                $table = [
                '#theme' => 'cmis_browser',
                '#header' => $table_header,
                '#elements' => $rows,
                ];

                $content = render($table);
            }
        }

        return $content;
    }

    /**
     * Submit button ajax callback.
     *
     * @param array              $form       The form array.
     * @param FormStateInterface $form_state The form state object.
     *
     * @return array
     *   Subform.
     */
    public function ajaxGetResult(array &$form, FormStateInterface $form_state)
    {
        return $form['result'];
    }

}
