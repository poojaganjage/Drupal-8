<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Entity\Vpc\Subnet;
use Drupal\aws_cloud\Form\Ec2\AwsCloudContentForm;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the subnet entity create form.
 *
 * @ingroup aws_cloud
 */
class SubnetCreateForm extends AwsCloudContentForm {

  use CloudContentEntityTrait;

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
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Snapshot */
    $form = parent::buildForm($form, $form_state);
    $this->ec2Service->setCloudContext($cloud_context);
    $entity = $this->entity;

    $weight = -50;

    $form['subnet'] = [
      '#type' => 'details',
      '#title' => $this->t('Subnet'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['subnet']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
    ];

    $vpcs = $this->ec2Service->getVpcs();
    ksort($vpcs);
    $form['subnet']['vpc_id'] = [
      '#type'          => 'select',
      '#title'         => $this->t('VPC CIDR (ID)'),
      '#options'       => $vpcs,
      '#default_value' => $entity->getVpcId(),
      '#required'      => TRUE,
    ];

    $form['subnet']['cidr_block'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('IPv4 CIDR block'),
      '#description'   => $this->t('The range of IPv4 addresses for your VPC in CIDR block format, for example, 10.0.0.0/24. Block sizes must be between a /16 netmask and /28 netmask.'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
    ];

    unset($form['tags']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

    return $form;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * @param array $form
   *   Array of form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    $entity = $this->entity;
    $entity->setCloudContext($cloud_context);

    $result = $this->ec2Service->createSubnet([
      'VpcId' => $entity->getVpcId(),
      'CidrBlock' => $entity->getCidrBlock(),
    ]);

    if (isset($result['Subnet'])
      && ($entity->setSubnetId($result['Subnet']['SubnetId']))
      && ($entity->save())
    ) {

      $this->setTagsInAws($entity->getSubnetId(), [
        Subnet::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      // Update the subnet.
      $this->ec2Service->updateSubnets([
        'SubnetIds' => [$entity->getSubnetId()],
      ], FALSE);

      $this->processOperationStatus($entity, 'created');
      $this->clearCacheValues();

      $form_state->setRedirect('view.aws_cloud_subnet.list', ['cloud_context' => $entity->getCloudContext()]);
    }
    else {

      $this->processOperationErrorStatus($entity, 'created');
    }
  }

}
