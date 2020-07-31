<?php

namespace Drupal\openstack\Form;

use Drupal\openstack\Entity\OpenStackInstance;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\aws_cloud\Form\Ec2\InstanceEditForm;

/**
 * Form controller for OpenStack Instance edit forms.
 *
 * @ingroup openstack
 */
class OpenStackInstanceEditForm extends InstanceEditForm {

  public const SECURITY_GROUP_DELIMITER = ', ';

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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form = parent::buildForm($form, $form_state);

    $form['instance']['instance_type']['#access'] = FALSE;
    $form['instance']['iam_role']['#access'] = FALSE;
    $form['options']['schedule']['#access'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\openstack\Entity\OpenStackInstance */
    $entity = $this->entity;

    $this->setTagsInAws($entity->getInstanceId(), [
      $entity->getEntityTypeId() . '_' . OpenStackInstance::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
      'Name' => $entity->getName(),
    ]);

    parent::save($form, $form_state);
    $this->clearCacheValues();
  }

}
