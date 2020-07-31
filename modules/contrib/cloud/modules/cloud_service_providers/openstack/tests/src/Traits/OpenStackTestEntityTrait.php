<?php

namespace Drupal\Tests\openstack\Traits;

use Drupal\openstack\Entity\OpenStackImage;
use Drupal\openstack\Entity\OpenStackFloatingIp;
use Drupal\Tests\aws_cloud\Traits\AwsCloudTestEntityTrait;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\cloud\Entity\CloudServerTemplate;

/**
 * The trait creating test entity for openstack testing.
 */
trait OpenStackTestEntityTrait {

  // Most of functions depends on OpenStackTestEntityTrait.
  use AwsCloudTestEntityTrait;

  /**
   * Create Cloud Server Template.
   *
   * @param array $iam_roles
   *   The IAM Roles.
   * @param \Drupal\openstack\Entity\OpenStackImage $image
   *   The Image Entity.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The CloudServerTemplate entity.
   */
  protected function createOpenStackServerTemplateTestEntity(array $iam_roles,
                                                    OpenStackImage $image,
                                                    $cloud_context): CloudContentEntityBase {
    // Create template.
    $template = $this->createTestEntity(CloudServerTemplate::class, [
      'cloud_context' => $cloud_context,
      'type' => 'openstack',
      'name' => 'test_template1',
    ]);

    $template->field_test_only->value = '1';
    $template->field_max_count->value = 1;
    $template->field_min_count->value = 1;
    $template->field_monitoring->value = '0';
    $template->field_instance_type->value = 'm1.nano';
    $template->field_openstack_image_id->value = $image->getImageId()
      ?: "ami-{$this->getRandomId()}";

    $template->save();
    return $template;
  }

  /**
   * Create an OpenStack Floating IP test entity.
   *
   * @param int $index
   *   The Floating IP index.
   * @param string $floating_ip_name
   *   The Floating IP name.
   * @param string $public_ip
   *   The public IP address.
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return \Drupal\cloud\Entity\CloudContentEntityBase
   *   The Floating IP entity.
   *
   * @throws \Exception
   */
  protected function createFloatingIpTestEntity($index = 0, $floating_ip_name = '', $public_ip = '', $cloud_context = ''): CloudContentEntityBase {
    $timestamp = time();

    return $this->createTestEntity(OpenStackFloatingIp::class, [
      'cloud_context' => $cloud_context ?: $this->cloudContext,
      'name' => $floating_ip_name ?: sprintf('eip-entity #%d - %s - %s', $index + 1, date('Y/m/d H:i:s'), $this->random->name(32, TRUE)),
      'public_ip' => $public_ip ?: Utils::getRandomPublicIp(),
      'instance_id' => NULL,
      'network_interface_id' => NULL,
      'private_ip_address' => NULL,
      'network_interface_owner' => NULL,
      'allocation_id' => NULL,
      'association_id' => NULL,
      'domain' => 'standard',
      'created' => $timestamp,
      'changed' => $timestamp,
      'refreshed' => $timestamp,
    ]);
  }

}
