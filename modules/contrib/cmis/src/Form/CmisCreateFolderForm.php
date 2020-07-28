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

use Dkd\PhpCmis\PropertyIds;
use Drupal\cmis\Controller\CmisRepositoryController;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CmisCreateFolder.
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
class CmisCreateFolderForm extends FormBase
{

    use StringTranslationTrait;

    /**
     * The string translation information.
     *
     * @var Drupal\Core\StringTranslation\TranslationInterface
     */
    protected $stringTranslation;

    /**
     * Contruct method.
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
     * Get Form Id.
     *
     * @return int
     *   The int.
     */
    public function getFormId()
    {
        return 'cmis_create_folder';
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
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['folder_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Folder name'),
        '#description' => $this->t('Enter the new folder name'),
        '#maxlength' => 255,
        '#size' => 64,
        '#required' => true,
        ];

        $form['folder_description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Folder description'),
        '#description' => $this->t('Enter the folder description'),
        ];

        $form['config'] = [
        '#type' => 'hidden',
        '#default_value' => $this->getRouteMatch()->getParameter('config'),
        ];

        $form['folder_id'] = [
        '#type' => 'hidden',
        '#default_value' => $this->getRouteMatch()->getParameter('folder_id'),
        ];

        $form['operation']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Create folder'),
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
        $values = $form_state->getValues();
        $form_state->setRedirect(
            'cmis.cmis_repository_controller_browser', [
            'config' => $values['config'],
            'folder_id' => $values['folder_id'],
            ]
        );
        if (!empty($values['folder_name'])) {
            $repository = new CmisRepositoryController(
                $values['config'], $values['folder_id']
            );
            
            $var = $repository->getBrowser()->getConnection()->validObjectName(
                $values['folder_name'], 'cmis:folder',
                $values['folder_id']
            ),
            
            if (!empty($var))
            ) {
                $this->messenger()->addWarning(
                    $this->t(
                        'The folder name @folder_name exists in folder.', [
                        '@folder_name' => $values['folder_name'],
                        ]
                    )
                );
                return;
            }
            $session = $repository->getBrowser()->getConnection()->getSession();
            $properties = [
            PropertyIds::OBJECT_TYPE_ID => 'cmis:folder',
            PropertyIds::NAME => $values['folder_name'],
            ];
            if (!empty($values['folder_description'])) {
                $properties[PropertyIds::DESCRIPTION] 
                    = $values['folder_description'];
            }

            try {
                $session->createFolder(
                    $properties, $session->createObjectId(
                        $values['folder_id']
                    ),
                );
                $this->messenger()->addStatus(
                    $this->t(
                        'The folder name @folder_name has been created.', [
                        '@folder_name' => $values['folder_name'],
                        ]
                    )
                );
            }
            catch (Exception $exception) {
                $this->messenger()->addWarning(
                    $this->t(
                        'Impossible to create fhe folder name @folder_name.', [
                        '@folder_name' => $values['folder_name'],
                        ]
                    )
                );
            }
        }
    }

}
