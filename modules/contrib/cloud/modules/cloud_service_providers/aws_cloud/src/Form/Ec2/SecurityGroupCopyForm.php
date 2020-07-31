<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;

/**
 * Form controller for the CloudScripting entity copy forms.
 *
 * @ingroup aws_cloud
 */
class SecurityGroupCopyForm extends SecurityGroupEditForm {

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
    $form = parent::buildForm($form, $form_state, $cloud_context);

    $entity = $this->entity;

    $form['group_name']['#access'] = TRUE;
    $form['security_group']['group_name'] = $form['group_name'];
    $form['security_group']['group_name']['widget'][0]['value']['#default_value'] = $this->t(
      'Copy of @name',
      [
        '@name' => $entity->getGroupName(),
      ]);
    unset($form['security_group']['name'], $form['security_group']['group_id'], $form['group_name']);

    $vpcs = $this->ec2Service->getVpcs();
    if (count($vpcs) === 0) {
      $this->messenger->addWarning($this->t('You do not have any VPCs. You need a VPC in order to create a security group. You can <a href=":create_vpc_link">create a VPC</a>.', [
        ':create_vpc_link' => Url::fromRoute(
          'entity.aws_cloud_vpc.add_form', [
            'cloud_context' => $cloud_context,
          ])->toString(),
      ]));
    }
    ksort($vpcs);
    $form['security_group']['vpc_id'] = [
      '#type' => 'select',
      '#title' => $this->t('VPC CIDR (ID)'),
      '#options' => $vpcs,
      '#default_value' => $entity->getVpcId(),
      '#required' => TRUE,
      '#weight' => 1,
    ];

    $form['security_group']['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#cols' => 60,
      '#rows' => 3,
      '#default_value' => $entity->getDescription(),
      '#required' => TRUE,
      '#weight' => 2,
    ];

    foreach ($form['rules'] ?: [] as &$ip_permission) {
      if (is_array($ip_permission) && isset($ip_permission['widget']) && !empty($ip_permission['widget'])) {
        foreach ($ip_permission['widget'] ?: [] as &$widget) {
          if (is_array($widget)) {
            if (isset($widget['group_id']) && $widget['group_id']['#default_value'] === $entity->getGroupId()) {
              $widget['group_id']['#description'] = $this->t('* Update with new Group ID.');
              $widget['group_id']['#attributes']['readonly'] = TRUE;
            }
          }
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $entity = $this->entity->createDuplicate();
    $this->source_entity = $this->entity;
    $this->entity = $entity;
    $this->trimTextfields($form, $form_state);

    $result = $this->ec2Service->createSecurityGroup([
      'GroupName' => $form_state->getValue('group_name')[0]['value'],
      'VpcId'       => $entity->getVpcId(),
      'Description' => $entity->getDescription(),
    ]);

    if (isset($result['GroupId'])
        && ($entity->setGroupId($result['GroupId']))
        && ($entity->set('name', $form_state->getValue('group_name')[0]['value']))) {
      // Keep intentionally blank.
    }
    else {
      $form_state->setError($form, t('Unable to update security group.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Create the new security group.
    $this->trimTextfields($form, $form_state);
    $entity = $this->entity;

    if ($entity->save()) {

      $this->setTagsInAws($entity->getGroupId(), [
        SecurityGroup::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      // Fetch the most up to date security group data from Ec2.
      $existing_group = $this->ec2Service->describeSecurityGroups([
        'GroupIds' => [$entity->getGroupId()],
      ]);

      $this->changeSelfGroupId($form);

      // Update Ingress and Egress permissions.
      $this->updateIngressEgressPermissions($entity, $existing_group);

      // Have the system refresh the security group.
      $this->ec2Service->updateSecurityGroups([
        'GroupIds' => [$entity->getGroupId()],
      ], FALSE);

      if (count($this->messenger->messagesByType('error')) === 0) {
        // Check API calls, see if the permissions updates were
        // successful or not.
        $this->validateAuthorize($entity);
      }

      if (count($this->messenger->messagesByType('status')) === 1) {

        $this->messenger->deleteAll();

        // Use the custom message since Security Group uses 'group_name' for
        // its own label.  So don't change the following code.
        $this->messenger->addStatus($this->t('The @type %label has been created.', [
          '@type' => $entity->getEntityType()->getSingularLabel(),
          '%label' => $entity->toLink($entity->getGroupName())->toString(),
        ]));
        $this->logOperationMessage($entity, 'created');

        $form_state->setRedirectUrl($entity->toUrl('canonical'));
      }
    }
    else {
      $this->messenger->addError($this->t('Unable to update security group.'));
      $this->logOperationErrorMessage($entity, 'updated');
    }

    if (count($this->messenger->messagesByType('error')) > 0) {
      if ($entity->id()) {
        $form_state->setRedirect('entity.aws_cloud_security_group.edit_form', [
          'cloud_context' => $entity->getCloudContext(),
          'aws_cloud_security_group' => $entity->id(),
        ]);
      }
      else {
        $form_state->setRedirect('view.aws_cloud_security_group.list', ['cloud_context' => $entity->getCloudContext()]);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if (isset($actions['delete'])) {
      unset($actions['delete']);
    }
    if (isset($actions['submit'])) {
      $actions['submit']['#value'] = $this->t('Copy');
    }
    $entity = $this->entity;
    $url = $entity->toUrl('canonical');
    $url->setRouteParameter('cloud_context', $entity->getCloudContext());
    $actions['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $url,
      '#attributes' => ['class' => ['button']],
    ];
    return $actions;
  }

  /**
   * Change Group ID in in/outbound permission.
   *
   * @param array $form
   *   The complete form array.
   */
  private function changeSelfGroupId(array $form) {
    foreach ($form['rules'] ?: [] as $key => $permission) {
      if (is_array($permission) && isset($permission['widget']) && !empty($permission['widget'])) {
        foreach ($permission['widget'] ?: [] as $idx => $widget) {
          if (is_array($widget)) {
            if (isset($widget['group_id']) && isset($widget['group_id']['#attributes']['readonly'])) {
              if ($key === 1) {
                $field = 'outbound_permission';
              }
              else {
                $field = 'ip_permission';
              }
              $ip_permission = $this->entity->$field->get($idx);
              $ip_permission->group_id = $this->entity->getGroupId();
              $this->entity->$field->set($idx, $ip_permission);
            }
          }
        }
      }
    }
  }

}
