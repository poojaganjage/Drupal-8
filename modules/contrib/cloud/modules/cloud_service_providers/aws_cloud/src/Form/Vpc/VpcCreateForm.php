<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Entity\Vpc\Vpc;
use Drupal\aws_cloud\Form\Ec2\AwsCloudContentForm;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the VPC entity create form.
 *
 * @ingroup aws_cloud
 */
class VpcCreateForm extends AwsCloudContentForm {

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

    $weight = -50;

    $form['vpc'] = [
      '#type' => 'details',
      '#title' => $this->t('VPC'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['vpc']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
    ];

    $form['vpc']['cidr_block'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('IPv4 CIDR block'),
      '#description'   => $this->t('The range of IPv4 addresses for your VPC in CIDR block format, for example, 10.0.0.0/24. Block sizes must be between a /16 netmask and /28 netmask.'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
    ];

    $form['vpc']['amazon_provided_ipv6_cidr_block'] = [
      '#type'          => 'select',
      '#title'         => $this->t('IPv6 CIDR block'),
      '#description'   => $this->t('You can associate an Amazon-provided IPv6 CIDR block with the VPC. Amazon provides a fixed size (/56) IPv6 CIDR block. You cannot choose the range of IPv6 addresses for the CIDR block.'),
      '#default_value' => 0,
      '#options'       => [
        0 => $this->t('No IPv6 CIDR Block'),
        1 => $this->t('Amazon provided IPv6 CIDR block'),
      ],
    ];

    $form['vpc']['instance_tenancy'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Tenancy'),
      '#description'   => $this->t('You can run instances in your VPC on single-tenant, dedicated hardware. Select Dedicated to ensure that instances launched in this VPC are dedicated tenancy instances, regardless of the tenancy attribute specified at launch. Select Default to ensure that instances launched in this VPC use the tenancy attribute specified at launch.'),
      '#default_value' => 'default',
      '#options'       => [
        'default' => $this->t('Default'),
        'dedicated' => $this->t('Dedicated'),
      ],
    ];

    $form['vpc']['flow_log'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Flow Log'),
      '#description' => $this->t('Enable create flow log automatically.'),
    ];

    unset($form['tags']);
    unset($form['cidr_blocks']);
    unset($form['ipv6_cidr_blocks']);

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

    $result = $this->ec2Service->createVpc([
      'AmazonProvidedIpv6CidrBlock' => $form_state->getValue('amazon_provided_ipv6_cidr_block') !== 0,
      'CidrBlock' => $entity->getCidrBlock(),
      'InstanceTenancy' => $entity->getInstanceTenancy(),
    ]);

    if (isset($result['Vpc'])
      && ($entity->setVpcId($result['Vpc']['VpcId']))
      && ($entity->save())
    ) {

      $this->setTagsInAws($entity->getVpcId(), [
        Vpc::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      // Update the vpc.
      $this->ec2Service->updateVpcs([
        'VpcIds' => [$entity->getVpcId()],
      ], FALSE);

      // Create flow log.
      if ($form_state->getValue('flow_log')) {
        aws_cloud_create_flow_log($cloud_context, $entity->getVpcId());
      }

      $this->processOperationStatus($entity, 'created');
      $this->clearCacheValues();

      $form_state->setRedirect('view.aws_cloud_vpc.list', ['cloud_context' => $entity->getCloudContext()]);
    }
    else {

      $this->processOperationErrorStatus($entity, 'created');
    }
  }

}
