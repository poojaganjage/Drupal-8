<?php

namespace Drupal\openstack\Plugin\Field\FieldType;

use Drupal\aws_cloud\Plugin\Field\FieldType\BlockDeviceMappings;

/**
 * Plugin implementation of the 'block_device_mappings' field type.
 *
 * @FieldType(
 *   id = "block_device_mappings",
 *   label = @Translation("OpenStack Block Device Mappings"),
 *   description = @Translation("OpenStack Block Device Mappings."),
 *   default_widget = "block_device_mappings_item",
 *   default_formatter = "block_device_mappings_formatter"
 * )
 */
class OpenStackBlockDeviceMappings extends BlockDeviceMappings {

}
