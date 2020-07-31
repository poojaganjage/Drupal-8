<?php

namespace Drupal\aws_cloud\Entity\Ec2;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the security group view builders.
 */
class SecurityGroupViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'security_group',
        'title' => $this->t('Security Group'),
        'open' => TRUE,
        'fields' => [
          'group_id',
          'group_name',
          'description',
          'vpc_id',
          'created',
        ],
      ],
      [
        'name' => 'rules',
        'title' => $this->t('Rules'),
        'open' => TRUE,
        'fields' => [
          'ip_permission',
          'outbound_permission',
        ],
      ],
      [
        'name' => 'others',
        'title' => $this->t('Others'),
        'open' => FALSE,
        'fields' => [
          'cloud_context',
          'uid',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);
    $build['#pre_render'][] = [$this, 'removeIpPermissionsField'];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return array_merge(parent::trustedCallbacks(), ['removeIpPermissionsField']);
  }

  /**
   * Show a default message if not permissions are configured.
   *
   * @param array $build
   *   Build array.
   *
   * @return array
   *   The updated renderable array.
   */
  public function removeIpPermissionsField(array $build) : array {
    /* @var \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $security */
    $security = $build['#aws_cloud_security_group'];

    $inbound = $security->getIpPermission();
    $outbound = $security->getOutboundPermission();
    if ($inbound->count() === 0 && $outbound->count() === 0) {
      unset($build['rules'][0]);
      $build['rules'][] = $this->getNoPermissionMessage($security);
    }
    return $build;
  }

  /**
   * Build message when there are no permissions configured.
   *
   * @param \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $securityGroup
   *   Security Group entity.
   *
   * @return array
   *   Message array.
   */
  protected function getNoPermissionMessage(SecurityGroup $securityGroup) : array {
    $message = [];
    try {
      $link = $securityGroup->toLink('Inbound and Outbound Rules', 'edit-form')->toString();
      $message = [
        '#markup' => $this->t('No permissions configured. Please configure @rules_link.', [
          '@rules_link' => $link,
        ]),
      ];
    }
    catch (\Exception $e) {
      $this->handleException($e);
      $message = [
        '#markup' => $this->t('No permissions configured.'),
      ];
    }
    return $message;
  }

}
