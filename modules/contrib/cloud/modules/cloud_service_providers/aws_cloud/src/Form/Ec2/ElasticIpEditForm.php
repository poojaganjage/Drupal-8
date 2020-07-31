<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;
use Drupal\aws_cloud\Entity\Ec2\ElasticIp;
use Drupal\cloud\Service\Util\EntityLinkWithNameHtmlGenerator;

/**
 * Form controller for the ElasticIp entity edit forms.
 *
 * @ingroup aws_cloud
 */
class ElasticIpEditForm extends AwsCloudContentForm {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
   *
   * @param array $form
   *   Array of form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param string $cloud_context
   *   A cloud_context string value from URL "path".
   *   The cloud context.
   *
   * @return array
   *   Array of form object.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\ElasticIp */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $weight = -50;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $form['ip_address'] = [
      '#type' => 'details',
      '#title' => $this->t('@title', ['@title' => $entity->getEntityType()->getSingularLabel()]),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['ip_address']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#size' => 60,
      '#default_value' => $entity->label(),
    ];

    $form['ip_address']['public_ip'] = [
      '#type' => 'item',
      '#title' => $this->getItemTitle($this->t('@title', ['@title' => $entity->getEntityType()->getSingularLabel()])),
      '#markup' => $entity->getPublicIp(),
    ];

    $form['ip_address']['private_ip_address'] = [
      '#type' => 'item',
      '#title' => $this->getItemTitle($this->t('Private IP Address')),
      '#markup' => $entity->getPrivateIpAddress(),
    ];

    $form['ip_address']['created'] = [
      '#type' => 'item',
      '#title' => $this->getItemTitle($this->t('Created')),
      '#markup' => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    $form['assign'] = [
      '#type' => 'details',
      '#title' => $this->t('Assign'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['assign']['instance_id'] = $this->entityLinkRenderer->renderFormElements(
      $entity->getInstanceId(),
      "{$module_name}_instance",
      'instance_id',
      ['#title' => $this->getItemTitle($this->t('Instance ID'))],
      '',
      EntityLinkWithNameHtmlGenerator::class
    );

    $form['assign']['network_interface_id'] = $this->entityLinkRenderer->renderFormElements(
      $entity->getNetworkInterfaceId(),
      "{$module_name}_network_interface",
      'network_interface_id',
      ['#title' => $this->getItemTitle($this->t('Network Interface ID'))],
      '',
      EntityLinkWithNameHtmlGenerator::class
    );

    $form['assign']['allocation_id'] = [
      '#type' => 'item',
      '#title' => $this->getItemTitle($this->t('Allocation ID')),
      '#markup' => $entity->getAllocationId(),
    ];

    $form['assign']['association_id'] = [
      '#type' => 'item',
      '#title' => $this->getItemTitle($this->t('Association ID')),
      '#markup' => $entity->getAssociationId(),
    ];

    $form['assign']['domain'] = [
      '#type' => 'item',
      '#title' => $this->getItemTitle($this->t('Domain (standard | vpc)')),
      '#markup' => $entity->getDomain(),
    ];

    $form['assign']['network_interface_owner'] = [
      '#type' => 'item',
      '#title' => $this->getItemTitle($this->t('Network Interface Owner')),
      '#markup' => $entity->getNetworkInterfaceOwner(),
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);
    $association_id = $this->entity->getAssociationId();
    if (isset($association_id)) {
      // Unset the delete button because the IP is allocated.
      unset($form['actions']['delete']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $this->ec2Service->setCloudContext($this->entity->getCloudContext());

    // Update the tags.
    if ($this->entity->getEntityTypeId() === 'aws_cloud_elastic_ip') {
      $this->setTagsInAws($this->entity->getAllocationId(), [
        ElasticIp::TAG_CREATED_BY_UID => $this->entity->getOwner()->id(),
        'Name' => $this->entity->getName(),
      ]);
    }

    $this->clearCacheValues();
  }

}
