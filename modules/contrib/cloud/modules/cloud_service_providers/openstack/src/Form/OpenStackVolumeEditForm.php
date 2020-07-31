<?php

namespace Drupal\openstack\Form;

use Drupal\aws_cloud\Form\Ec2\VolumeEditForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the CloudScripting entity edit forms.
 *
 * @ingroup openstack
 */
class OpenStackVolumeEditForm extends VolumeEditForm {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openstack.ec2'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('messenger'),
      $container->get('entity.link_renderer'),
      $container->get('entity_type.manager'),
      $container->get('cache.render'),
      $container->get('plugin.cache_clearer'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

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
    $form = parent::buildForm($form, $form_state, $cloud_context);

    /* @var $entity \Drupal\openstack\Entity\Volume */
    $entity = $this->entity;

    $form['volume']['volume_type'] = [
      '#type'          => 'select',
      '#title'         => $this->getItemTitle($this->t('Volume Type')),
      '#options'       => ['lvmdriver-1' => $this->t('lvmdriver-1')],
      '#default_value' => $entity->getVolumeType(),
    ];

    unset($form['volume']['iops']);
    unset($form['volume']['kms_key_id']);
    unset($form['volume']['encrypted']);

    return $form;
  }

}
