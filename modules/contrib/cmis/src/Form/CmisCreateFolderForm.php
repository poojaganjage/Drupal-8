<?php

declare(strict_types = 1);

namespace Drupal\cmis\Form;

use Dkd\PhpCmis\PropertyIds;
use Drupal\cmis\Controller\CmisRepositoryController;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CmisCreateFolder.
 *
 * @package Drupal\cmis\Form
 */
class CmisCreateFolderForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmis_create_folder';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['folder_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Folder name'),
      '#description' => $this->t('Enter the new folder name'),
      '#maxlength' => 255,
      '#size' => 64,
      '#required' => TRUE,
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $form_state->setRedirect('cmis.cmis_repository_controller_browser', [
      'config' => $values['config'],
      'folder_id' => $values['folder_id'],
    ]);
    if (!empty($values['folder_name'])) {
      $repository = new CmisRepositoryController($values['config'], $values['folder_id']);
      if (!empty($repository->getBrowser()->getConnection()->validObjectName($values['folder_name'], 'cmis:folder', $values['folder_id']))) {
        $this->messenger()->addWarning($this->t('The folder name @folder_name exists in folder.', [
          '@folder_name' => $values['folder_name'],
        ]));
        return;
      }
      $session = $repository->getBrowser()->getConnection()->getSession();
      $properties = [
        PropertyIds::OBJECT_TYPE_ID => 'cmis:folder',
        PropertyIds::NAME => $values['folder_name'],
      ];
      if (!empty($values['folder_description'])) {
        $properties[PropertyIds::DESCRIPTION] = $values['folder_description'];
      }

      try {
        $session->createFolder($properties, $session->createObjectId($values['folder_id']));
        $this->messenger()->addStatus($this->t('The folder name @folder_name has been created.', [
          '@folder_name' => $values['folder_name'],
        ]));
      }
      catch (Exception $exception) {
        $this->messenger()->addWarning($this->t('Impossible to create fhe folder name @folder_name.', [
          '@folder_name' => $values['folder_name'],
        ]));
      }
    }
  }

}
