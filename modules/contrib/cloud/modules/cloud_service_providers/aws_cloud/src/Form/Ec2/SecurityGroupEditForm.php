<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemList;
use Aws\Result;

/**
 * Form controller for the CloudScripting entity edit forms.
 *
 * @ingroup aws_cloud
 */
class SecurityGroupEditForm extends AwsCloudContentForm {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
   *
   * @param array $form
   *   Form data array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param string $cloud_context
   *   A cloud_context string value from URL "path".
   *
   * @return array
   *   Return form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $this->ec2Service->setCloudContext($cloud_context);

    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\SecurityGroup */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $weight = -50;

    $form['security_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Security Group'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['security_group']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => TRUE,
    ];

    $form['security_group']['group_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('ID')),
      '#markup'        => $entity->getGroupId(),
    ];

    $form['security_group']['group_name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Security Group Name')),
      '#markup'        => $entity->getGroupName(),
    ];

    $form['security_group']['description'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Description')),
      '#markup'        => $entity->getDescription(),
    ];

    $form['security_group']['vpc_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('VPC ID')),
      '#markup'        => $entity->getVpcId(),
    ];

    $form['security_group']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    // Put all rules into HTML5 details.
    $form['rules'] = [
      '#type' => 'details',
      '#title' => $this->t('Rules'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['rules'][] = $form['ip_permission'];

    if (!empty($entity->getVpcId())) {
      $form['rules'][] = $form['outbound_permission'];

    }
    unset($form['ip_permission'], $form['outbound_permission']);
    $form['group_name']['#access'] = FALSE;

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['#attached']['library'][] = 'aws_cloud/aws_cloud_security_groups';

    if (isset($form['actions'])) {
      $form['actions']['submit']['#weight'] = $weight++;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Call copyFormItemValues() to ensure the form array is intact.
    $this->copyFormItemValues($form);

    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;

    $this->ec2Service->setCloudContext($entity->getCloudContext());
    if ($entity->save()) {

      $this->setTagsInAws($entity->getGroupId(), [
        $entity->getEntityTypeId() . '_' . SecurityGroup::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      // Fetch the most up to date security group data from Ec2.
      $existing_group = $this->ec2Service->describeSecurityGroups([
        'GroupIds' => [$entity->getGroupId()],
      ]);

      if (!empty($existing_group)) {
        // Update the inbound/outbound permissions.
        $this->updateIngressEgressPermissions($entity, $existing_group);

        // Have the system refresh the security group.
        $this->ec2Service->updateSecurityGroups([
          'GroupIds' => [$this->entity->getGroupId()],
        ], FALSE);

        if (count($this->messenger->messagesByType('error')) === 0) {
          // Check API calls, see if the permissions updates were
          // successful or not.
          $this->validateAuthorize($entity);
          $form_state->setRedirect("entity.{$entity->getEntityTypeId()}.canonical", [
            'cloud_context' => $entity->getCloudContext(),
            $entity->getEntityTypeId() => $entity->id(),
          ]);
        }
        $this->clearCacheValues();
      }
      else {
        $this->messenger->addError($this->t('Cannot update security group.'));
      }
    }
    else {
      $this->processOperationErrorStatus($this->entity, 'updated');
    }
  }

  /**
   * Helper method to update both ingress and egress permissions.
   *
   * @param \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $entity
   *   Security group entity.
   * @param \Aws\Result $existing_group
   *   The security group array, just fetched from AWS.
   */
  protected function updateIngressEgressPermissions(SecurityGroup $entity, Result $existing_group) {
    $inbound_result = $this->updatePermissions(
      $entity->getGroupId(),
      $existing_group['SecurityGroups'][0]['IpPermissions'] ?? [],
      'IpPermissions',
      'getIpPermission',
      'revokeSecurityGroupIngress',
      'authorizeSecurityGroupIngress'
    );

    // Update the outbound permissions.  This only applies to
    // VPC security groups.
    $outbound_result = TRUE;
    if (!empty($entity->getVpcId())) {
      $outbound_result = $this->updatePermissions(
        $entity->getGroupId(),
        $existing_group['SecurityGroups'][0]['IpPermissionsEgress'] ?? [],
        'IpPermissions',
        'getOutboundPermission',
        'revokeSecurityGroupEgress',
        'authorizeSecurityGroupEgress'
      );
    }

    if (!$inbound_result && !$outbound_result) {
      $this->messenger->addError($this->t('Inbound and Outbound Rules could not be saved because of the error from AWS.'));
    }
    elseif (!$inbound_result) {
      $this->messenger->addError($this->t('Inbound Rules could not be saved because of the error from AWS.'));
    }
    elseif (!$outbound_result) {
      $this->messenger->addError($this->t('Outbound Rules could not be saved because of the error from AWS.'));
    }
  }

  /**
   * Main method for updating permissions.
   *
   * Supports Ingress and Egress permissions.
   *
   * @param string $group_id
   *   Security Group ID.
   * @param array $existing_permissions
   *   Existing permission array.  Used in an attempt to revert permissions
   *   if the update failed.
   * @param string $permission_name
   *   The permission field name.
   * @param string $entity_func
   *   The entity function name to retrieve permissions.  Options are
   *   getIpPermission or getOutboundPermission.
   * @param string $revoke_func
   *   The API function name to revoke permissions.  Options are
   *   revokeSecurityGroupIngress or revokeSecurityGroupEgress.
   * @param string $authorize_func
   *   The API function name to authorize permissions.  Options are
   *   authorizeSecurityGroupIngress or authorizeSecurityGroupEgress.
   *
   * @return bool
   *   Indicates success or failure
   */
  protected function updatePermissions($group_id,
                                       array $existing_permissions,
                                       $permission_name,
                                       $entity_func,
                                       $revoke_func,
                                       $authorize_func) : bool {
    // Clear out the existing permissions.
    if (!empty($existing_permissions)) {
      $security_group = $this->formatIpPermissionForRevoke($existing_permissions);
      $this->revokeIpPermissions($revoke_func, $security_group, $group_id);
    }

    // Format the user entered permissions into AWS accepted array.
    $permissions = $this->formatIpPermissions($this->entity->$entity_func());
    if (!empty($permissions[$permission_name])) {
      // Setup permissions array for AuthorizeSecurityGroupIngress.
      $permissions['GroupId'] = $group_id;
      $result = $this->ec2Service->$authorize_func($permissions);

      // $result === NULL means an exception was thrown.  Attempt to add
      // the old set of permissions so the user's configurations are not lost.
      if ($result === NULL || count($this->messenger->messagesByType('error')) >= 1) {
        if (!empty($existing_permissions)) {
          $existing_permissions = $this->formatIpPermissionsForReadd(
            $existing_permissions,
            $this->entity->getGroupId()
          );
          $result = $this->ec2Service->$authorize_func($existing_permissions);
          if ($result === NULL) {
            $this->messenger->addError($this->t('Cannot re-add existing @name rules', [
              '@name' => $permission_name,
            ]));
          }
        }
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Revoke a set of permissions.
   *
   * @param string $method
   *   String value of revokeSecurityGroupEgress or revokeSecurityGroupIngress.
   * @param array $revoke_permissions
   *   Array of permissions to revoke.
   * @param string $group_id
   *   Security group id to revoke.
   */
  private function revokeIpPermissions($method, array $revoke_permissions, $group_id) {
    if (!empty($revoke_permissions)) {
      // Delete the existing egress permissions.
      $this->ec2Service->$method([
        'GroupId' => $group_id,
        'IpPermissions' => $revoke_permissions,
      ]);
    }
  }

  /**
   * Helper method to format a permissions array for resubmission.
   *
   * This is used in case of any error during the permissions update process.
   *
   * @param array $permissions
   *   Permissions array extracted from AWS Results array.
   * @param string $group_id
   *   Group id.
   *
   * @return array
   *   Formatted permissions array.
   */
  private function formatIpPermissionsForReadd(array $permissions, $group_id) : array {
    $existing_permissions['GroupId'] = $group_id;
    foreach ($permissions ?: [] as $permission) {
      foreach ($permission ?: [] as $key => $value) {
        if ($value !== 0 && $value !== FALSE && empty($value)) {
          unset($permission[$key]);
        }
      }
      $existing_permissions['IpPermissions'][] = $permission;
    }
    return $existing_permissions;
  }

  /**
   * Format the IpPermission object.
   *
   * Format returned from the DescribeSecurityGroup
   * Amazon EC2 API call.  This method unset array objects that have no values.
   *
   * @param array $security_group
   *   The security group.
   *
   * @return array
   *   Formatted IpPermission object.
   */
  protected function formatIpPermissionForRevoke(array $security_group) : array {
    foreach ($security_group ?: [] as $key => $group) {
      if (empty($group['IpRanges'])) {
        unset($security_group[$key]['IpRanges']);
      }
      if (empty($group['Ipv6Ranges'])) {
        unset($security_group[$key]['Ipv6Ranges']);
      }
      if (empty($group['PrefixListIds'])) {
        unset($security_group[$key]['PrefixListIds']);
      }

      if (empty($group['UserIdGroupPairs'])) {
        unset($security_group[$key]['UserIdGroupPairs']);
      }
      else {
        // Loop them and unset GroupName.
        foreach ($group['UserIdGroupPairs'] ?: [] as $pair_keys => $pairs) {
          unset($security_group[$key]['UserIdGroupPairs'][$pair_keys]['GroupName']);
        }
      }
    }
    return $security_group;
  }

  /**
   * Take the ip_permissions field type and format it for AWS API call.
   *
   * @param \Drupal\Core\Field\FieldItemList $permissions
   *   Array of IpPermission fields.
   *
   * @return array
   *   Array of formatted permissions for AWS.
   */
  private function formatIpPermissions(FieldItemList $permissions) : array {
    $ip_permissions = [];
    foreach ($permissions as $permission) {
      $ip_permissions['IpPermissions'][] = $this->formatIpPermissionForAuthorize($permission);
    }
    return $ip_permissions;
  }

  /**
   * Format the IpPermission object.
   *
   * Format the IpPermission object for use with
   * the AuthorizeSecurityGroup [Ingress and Egress] Amazon EC2 API call.
   *
   * @param \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $ip_permission
   *   The IP permission object.
   * @param bool $outbound
   *   The flag whether the data is outbound.
   *
   * @return array
   *   The permission.
   */
  protected function formatIpPermissionForAuthorize(IpPermission $ip_permission, $outbound = FALSE) : array {
    $permission = [
      'FromPort' => (int) $ip_permission->getFromPort(),
      'ToPort' => (int) $ip_permission->getToPort(),
      'IpProtocol' => $ip_permission->getIpProtocol(),
    ];
    if ($ip_permission->getSource() === 'ip4') {
      $permission['IpRanges'][]['CidrIp'] = $ip_permission->getCidrIp();
    }
    elseif ($ip_permission->getSource() === 'ip6') {
      $permission['Ipv6Ranges'][]['CidrIpv6'] = $ip_permission->getCidrIpv6();
    }
    elseif ($ip_permission->getSource() === 'prefix') {
      $permission['PrefixListIds'][]['PrefixListId'] = $ip_permission->getPrefixListId();
    }
    else {
      $group = [];
      // Use GroupID if nondefault VPC or EC2-Classic.
      // For other permissions, use Group Name.
      $vpc_id = $this->entity->getVpcId();
      if ((!$outbound && empty($vpc_id))
      || ($this->entity->isDefaultVpc() === TRUE)) {
        $security_groups = $this->getSecurityGroup($ip_permission->getGroupId());
        if (!empty($security_groups)) {
          $security_group = reset($security_groups);
          $group['GroupName'] = $security_group->getGroupName();
        }
      }
      else {
        $group['GroupId'] = $ip_permission->getGroupId();
      }
      $group['UserId'] = $ip_permission->getUserId();
      $group['PeeringStatus'] = $ip_permission->getPeeringStatus();
      $group['VpcId'] = $ip_permission->getVpcId();
      $group['VpcPeeringConnectionId'] = $ip_permission->getPeeringConnectionId();

      $permission['UserIdGroupPairs'][] = $group;
    }
    return $permission;
  }

  /**
   * Verify the authorize call was successful.
   *
   * Since EC2 does not return any error codes from any of the authorize
   * API calls, the only way to verify is to count the permissions array
   * from the current entity,  and the entity that is newly updated from
   * the updateSecurityGroups API call.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $group
   *   The security group.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function validateAuthorize(CloudContentEntityBase $group) {
    /* @var \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $updated_group */
    $updated_group = $this->entityTypeManager
      ->getStorage($group->getEntityTypeId())
      ->load($group->id());

    $entity = $this->entity;

    if ($group->getIpPermission()->count() !== $updated_group->getIpPermission()->count()) {
      $this->messenger->addError(
        $this->t('Error updating inbound permissions for security group @name', [
          '@name' => $entity->label(),
        ])
      );
    }

    if (!empty($group->getVpcId())) {
      if ($group->getOutboundPermission()->count() !== $updated_group->getOutboundPermission()->count()) {
        $this->messenger->addError(
          $this->t('Error updating outbound permissions for security group @name', [
            '@name' => $entity->label(),
          ])
        );
      }
    }

    if (count($this->messenger->messagesByType('error')) === 0) {
      // No errors, success.
      $this->processOperationStatus($entity, 'updated');
    }
  }

  /**
   * Load a security group.
   *
   * @param string $group_id
   *   Group ID.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   Array of aws_cloud_security_group.
   */
  private function getSecurityGroup($group_id) : array {
    $security_groups = [];
    try {
      $security_groups = $this->entityTypeManager
        ->getStorage($this->entity->getEntityTypeId())
        ->loadByProperties([
          'group_id' => $group_id,
        ]);
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
    return $security_groups;
  }

}
