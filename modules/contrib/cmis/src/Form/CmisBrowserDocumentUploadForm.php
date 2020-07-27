<?php

declare(strict_types = 1);

namespace Drupal\cmis\Form;

use Dkd\PhpCmis\PropertyIds;
use Drupal\cmis\Controller\CmisRepositoryController;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Stream\Stream;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CmisBrowserDocumentUploadForm.
 *
 * @package Drupal\cmis\Form
 */
class CmisBrowserDocumentUploadForm extends FormBase {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->fileSystem = $container->get('file_system');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmis_browser_document_upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $directory = $this->fileSystem->getTempDirectory();

    $directory_is_writable = is_writable($directory);
    if (!$directory_is_writable) {
      $this->messenger()->addError($this->t('The directory %directory is not writable.', ['%directory' => $directory]));
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $form_state->setRedirect(
        'cmis.cmis_repository_controller_browser', ['config' => $values['config'], 'folder_id' => $values['folder_id']]
    );
    $directory = $this->fileSystem->getTempDirectory();

    $filename = $directory . '/' . $_FILES['files']['name']['local_file'];
    if (!is_uploaded_file($_FILES['files']['tmp_name']['local_file']) || !copy($_FILES['files']['tmp_name']['local_file'], $filename)) {
      // Can't create file.
      $this->messenger()->addWarning($this->t('File can not be uploaded.'));
      return;
    }

    // Open repository.
    if ($repository = new CmisRepositoryController($values['config'], $values['folder_id'])) {
      if (!empty($repository->getBrowser()->getConnection()->validObjectName($_FILES['files']['name']['local_file'], 'cmis:document', $values['folder_id']))) {
        // Document exists. Delete file from local.
        unlink($filename);
        $this->messenger()->addWarning($this->t('The document name @name exists in folder.', ['@name' => $_FILES['files']['name']['local_file']]));
        return;
      }

      $session = $repository->getBrowser()->getConnection()->getSession();
      $properties = [
        PropertyIds::OBJECT_TYPE_ID => 'cmis:document',
        PropertyIds::NAME => $_FILES['files']['name']['local_file'],
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
        $this->messenger()->addStatus($this->t('Document name @name has been created.', ['@name' => $_FILES['files']['name']['local_file']]));
      }
      catch (Exception $exception) {
        $this->messenger()->addWarning($this->t('Document name @name could not be created.', ['@name' => $_FILES['files']['name']['local_file']]));
      }
    }
  }

}
