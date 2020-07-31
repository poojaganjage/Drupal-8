<?php

namespace Drupal\openstack\Form;

use Drupal\aws_cloud\Form\Ec2\InstanceLaunchForm;
use Drupal\openstack\Entity\Config\Config;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Instance entity launch form.
 *
 * @TODO: Remove this form.  This is not in use anymore.
 * Use the cloud server templates to launch instances.
 *
 * @ingroup openstack
 */
class OpenStackInstanceLaunchForm extends InstanceLaunchForm {

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

    // @FIXME: Maybe this is a bug.
    $cloudContext = Config::load($cloud_context);

    if (isset($cloudContext)) {
      $this->ec2Service->setCloudContext($cloudContext->getCloudContext());
    }
    else {
      $this->messenger->addError($this->t("Not found: OpenStack service provider '@cloud_context'", [
        '@cloud_context'  => $cloud_context,
      ]));
    }

    /* @var $entity \Drupal\openstack\Entity\Instance */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $form['key_pair_name'] = [
      '#type'          => 'entity_autocomplete',
      '#target_type'   => 'openstack_key_pair',
      '#title'         => $this->t('Key Pair Name'),
      '#size'          => 60,
      '#default_value' => $entity->getKeyPairName(),
      '#weight'        => -5,
      '#required'      => TRUE,
    ];

    $form['security_groups'] = [
      '#type'          => 'entity_autocomplete',
      '#target_type'   => 'openstack_security_group',
      '#title'         => $this->t('Security Groups'),
      '#size'          => 60,
      '#default_value' => $entity->getSecurityGroups(),
      '#weight'        => -5,
      '#required'      => FALSE,
    ];

    return $form;
  }

}
