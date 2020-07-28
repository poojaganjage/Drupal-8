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

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete CMIS connection entities.
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
class CmisConnectionEntityDeleteForm extends EntityConfirmFormBase
{

    /**
     * Get Questions.
     *
     * @return string
     *   The string.
     */
    public function getQuestion()
    {
        return $this->t(
            'Are you sure you want to delete %name?', 
            ['%name' => $this->entity->label()]
        );
    }

    /**
     * Get Cancel Url.
     *
     * @return string
     *   The string.
     */
    public function getCancelUrl()
    {
        return new Url('entity.cmis_connection_entity.collection');
    }

    /**
     * Get Confirm Text.
     *
     * @return string
     *   The string.
     */
    public function getConfirmText()
    {
        return $this->t('Delete');
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
        $this->entity->delete();
        $this->messenger()->addStatus(
            $this->t(
                'content @type: deleted @label.',
                [
                '@type' => $this->entity->bundle(),
                '@label' => $this->entity->label(),
                ]
            )
        );

        $form_state->setRedirectUrl($this->getCancelUrl());
    }

}
