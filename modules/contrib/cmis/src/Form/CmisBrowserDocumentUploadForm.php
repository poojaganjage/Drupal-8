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
use GuzzleHttp\Stream\Stream;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CmisBrowserDocumentUploadForm.
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
class CmisBrowserDocumentUploadForm extends FormBase
{

    /**
     * The file system.
     *
     * @var \Drupal\Core\File\FileSystemInterface
     */
    protected $fileSystem;

    /**
     * An array of found redirect IDs to avoid recursion.
     *
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * Constructs an CmisBrowserDocumentUploadForm object.
     *
     * @param RequestStack $requestStack The Request Stack.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * The create method.
     *
     * @param ContainerInterface $container The container.
     *
     * @return object.
     *   The object.
     */
    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        return new static(
            $instance->fileSystem = $container->get('file_system'),
            $container->get('request_stack')
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
        return 'cmis_browser_document_upload_form';
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
        $directory = $this->fileSystem->getTempDirectory();

        $directory_is_writable = is_writable($directory);
        if (!$directory_is_writable) {
            $this->messenger()->addError(
                $this->t(
                    'The directory %directory is not writable.', 
                    ['%directory' => $directory]
                ),
            );
        }
        $form['local_file'] = [
        '#type' => 'file',
        '#title' => $this->t('Local file'),
        '#description' => $this->t('Choose the local file to uploading'),
        ];

        $form['description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Document description'),
        '#description' => $this->t('Enter the document description'),
        '#default_value' => $form_state->getValue('description'),
        ];

        $form['config'] = [
        '#type' => 'hidden',
        '#default_value' => $this->getRouteMatch()->getParameter('config'),
        ];

        $form['folder_id'] = [
        '#type' => 'hidden',
        '#default_value' => $this->getRouteMatch()->getParameter('folder_id'),
        ];

        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Upload'),
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
            'cmis.cmis_repository_controller_browser', 
            ['config' => $values['config'], 'folder_id' => 
            $values['folder_id']]
        );
        $directory = $this->fileSystem->getTempDirectory();

        $filename = $directory . '/' . $this->requestStack
            ->getCurrentRequest()->files->get('files');
        if (!is_uploaded_file(
            $this->requestStack->getCurrentRequest()->files->get('files') || 
            !copy(
                $this->requestStack->getCurrentRequest()->files->get('files')
            ),
        ),
        ) {
            // Can't create file.
            $this->messenger()->addWarning($this->t('File can not be uploaded.'));
            return;
        }

        // Open repository.
        if ($repository = new CmisRepositoryController(
            $values['config'], $values['folder_id']
        ),
        ) {
            
            $var = $repository->getBrowser()->getConnection()->validObjectName(
                $this->requestStack->getCurrentRequest()->files->get('files'),
                'cmis:document', $values['folder_id']
            )
            
            if (!empty($var))
            ) {
                // Document exists. Delete file from local.
                unlink($filename);
                $this->messenger()->addWarning(
                    $this->t(
                        'The document name @name exists in folder.', ['@name' => 
                        $this->requestStack->getCurrentRequest()
                            ->files->get('files')]
                    ),
                );
                return;
            }

            $session = $repository->getBrowser()->getConnection()
                ->getSession();
            $properties = [
            PropertyIds::OBJECT_TYPE_ID => 'cmis:document',
            PropertyIds::NAME => $this->requestStack->getCurrentRequest()
                ->files->get('files'),
            ];
            if (!empty($values['description'])) {
                $properties[PropertyIds::DESCRIPTION] = $values['description'];
            }

            // Create document.
            try {
                $session->createDocument(
                    $properties,
                    $session->createObjectId($values['folder_id']),
                    Stream::factory(fopen($filename, 'r'))
                );
                // Delete file from local.
                unlink($filename);
                $this->messenger()->addStatus(
                    $this->t(
                        'Document name @name has been created.', 
                        ['@name' => $this->requestStack->getCurrentRequest()
                            ->files->get('files')]
                    ),
                );
            }
            catch (Exception $exception) {
                $this->messenger()->addWarning(
                    $this->t(
                        'Document name @name could not be created.', 
                        ['@name' => $this->requestStack->getCurrentRequest()
                            ->files->get('files')]
                    ),
                );
            }
        }
    }

}
