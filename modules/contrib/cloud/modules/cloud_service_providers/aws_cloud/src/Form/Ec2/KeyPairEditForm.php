<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the CloudScripting entity edit forms.
 *
 * @ingroup aws_cloud
 */
class KeyPairEditForm extends AwsCloudContentForm {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
   *
   * @param array $form
   *   Array of form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param string $cloud_context
   *   A cloud_context string value from URL "path".
   *
   * @return array
   *   Array of form object.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\KeyPair */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $weight = -50;

    $form['key_pair'] = [
      '#type' => 'details',
      '#title' => $this->t('Key Pair'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['key_pair']['key_pair_name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Key Pair Name')),
      '#markup'        => $entity->getKeyPairName(),
    ];

    $form['key_pair']['key_fingerprint'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Fingerprint')),
      '#markup'        => $entity->getKeyFingerprint(),
    ];

    $form['key_pair']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

}
