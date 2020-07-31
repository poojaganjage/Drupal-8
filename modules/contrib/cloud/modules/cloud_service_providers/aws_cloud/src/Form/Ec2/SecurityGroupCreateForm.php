<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form controller for the SecurityGroup entity create form.
 *
 * @ingroup aws_cloud
 */
class SecurityGroupCreateForm extends AwsCloudContentForm {

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

    $this->ec2Service->setCloudContext($cloud_context);

    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\SecurityGroup */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $weight = -50;

    $form['security_group'] = [
      '#type' => 'details',
      '#title' => $entity->getEntityType()->getSingularLabel(),
      '#open' => TRUE,
      '#weight'        => $weight++,
    ];

    $form['security_group']['group_name'] = $form['group_name'];
    unset($form['group_name']);

    $vpcs = $this->ec2Service->getVpcs();
    if (count($vpcs) === 0) {
      $this->messenger->addWarning(
        $this->t('You do not have any VPCs. You need a VPC in order to create a security group. You can <a href=":create_vpc_link">create a VPC</a>.',
            [':create_vpc_link' => Url::fromRoute('entity.aws_cloud_vpc.add_form', ['cloud_context' => $cloud_context])->toString()]));
    }
    $vpcs[$entity->getVpcId()] = 'N/A';
    ksort($vpcs);
    $form['security_group']['vpc_id'] = [
      '#type'          => 'select',
      '#title'         => $this->t('VPC CIDR (ID)'),
      '#options'       => $vpcs,
      '#default_value' => $entity->getVpcId(),
      '#required'      => TRUE,
    ];

    $form['security_group']['description'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Description'),
      '#cols'          => 60,
      '#rows'          => 3,
      '#default_value' => $entity->getDescription(),
      '#required'      => TRUE,
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    // Unset these until and present them on the edit security group form.
    unset($form['ip_permission'], $form['outbound_permission']);

    if (isset($form['actions'])) {
      $form['actions']['submit']['#weight'] = $weight++;
    }

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

    $entity = $this->entity;

    if ($entity->getEntityTypeId() === 'aws_cloud_security_group') {
      $result = $this->ec2Service->createSecurityGroup([
        'GroupName'   => $entity->getGroupName(),
        'VpcId'       => $entity->getVpcId(),
        'Description' => $entity->getDescription(),
      ]);
    }
    else {
      $result = $this->ec2Service->createSecurityGroup([
        'GroupName'   => $entity->getGroupName(),
        'Description' => $entity->getDescription(),
      ]);
    }

    if (isset($result['GroupId'])
    && ($entity->setGroupId($result['GroupId']))
    && ($entity->set('name', $entity->getGroupName()))
    && ($entity->save())) {

      $this->setTagsInAws($entity->getGroupId(), [
        $entity->getEntityTypeId() . '_' . SecurityGroup::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      $this->processOperationStatus($entity, 'created');
      $this->messenger->addStatus('Please setup the IP permissions.');

      $form_state->setRedirect("entity.{$entity->getEntityTypeId()}.edit_form", [
        'cloud_context' => $entity->getCloudContext(),
        $entity->getEntityTypeId() => $entity->id(),
      ]);
      $this->clearCacheValues();
    }
    else {

      // Use the custom message since Security Group uses 'group_name' for its
      // own label.  So don't change the following code.
      $message = $this->t('The @type @label could not be created.', [
        '@type'  => $entity->getEntityType()->getSingularLabel(),
        '@label' => $entity->getGroupName(),
      ]);
      $this->messenger->addError($message);
      $this->logOperationErrorMessage($entity, 'created');
    }
  }

}
