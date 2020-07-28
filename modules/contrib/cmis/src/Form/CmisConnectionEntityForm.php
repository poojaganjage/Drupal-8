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

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CmisConnectionEntityForm.
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
class CmisConnectionEntityForm extends EntityForm
{

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
    public function form(array $form, FormStateInterface $form_state)
    {
        $form = parent::form($form, $form_state);

        $cmis_connection_entity = $this->entity;
        $form['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#maxlength' => 255,
        '#default_value' => $cmis_connection_entity->label(),
        '#description' => $this->t('Label for the CMIS connection.'),
        '#required' => true,
        ];

        $form['id'] = [
        '#type' => 'machine_name',
        '#default_value' => $cmis_connection_entity->id(),
        '#machine_name' => [
        'exists' => '\Drupal\cmis\Entity\CmisConnectionEntity::load',
        ],
        '#disabled' => !$cmis_connection_entity->isNew(),
        ];

        $form['cmis_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CMIS browser url'),
        '#maxlength' => 255,
        '#default_value' => $cmis_connection_entity->getCmisUrl(),
        '#description' => $this->t('Enter CMIS browser url.'),
        '#required' => true,
        ];

        $form['cmis_user'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CMIS user'),
        '#maxlength' => 255,
        '#default_value' => $cmis_connection_entity->getCmisUser(),
        '#description' => $this->t('Enter CMIS user name.'),
        '#required' => true,
        ];

        $form['cmis_password'] = [
        '#type' => 'password',
        '#title' => $this->t('CMIS password'),
        '#maxlength' => 255,
        '#default_value' => $cmis_connection_entity->getCmisPassword(),
        '#description' => $this->t('Enter CMIS password.'),
        '#required' => true,
        ];

        $form['cmis_repository'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CMIS repository id'),
        '#maxlength' => 255,
        '#default_value' => $cmis_connection_entity->getCmisRepository(),
        '#description' => $this->t(
            'Enter CMIS repository id. 
            If empty the first repository will be used'
        ),
        '#required' => false,
        ];

        $form['cmis_cacheable'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('CMIS cacheable'),
        '#default_value' => $cmis_connection_entity->getCmisCacheable(),
        '#description' => $this->t('Check if repository will be cacheable'),
        '#required' => false,
        ];
        return $form;
    }

    /**
     * Save the form using $form varibale using.
     *
     * @param array              $form       Save the form.
     * @param FormStateInterface $form_state Save the form.
     *
     * @return array
     *   The array.
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        $cmis_connection_entity = $this->entity;
        $status = $cmis_connection_entity->save();

        switch ($status) {
        case SAVED_NEW:
            $this->messenger()->addStatus(
                $this->t(
                    'Created the %label CMIS connection.', [
                    '%label' => $cmis_connection_entity->label(),
                    ]
                )
            );
            break;

        default:
            $this->messenger()->addStatus(
                $this->t(
                    'Saved the %label CMIS connection.', [
                    '%label' => $cmis_connection_entity->label(),
                    ]
                )
            );
        }
        $form_state->setRedirectUrl($cmis_connection_entity->toUrl('collection'));
    }

}
