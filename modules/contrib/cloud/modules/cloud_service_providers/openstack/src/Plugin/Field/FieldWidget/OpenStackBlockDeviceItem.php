<?php

namespace Drupal\openstack\Plugin\Field\FieldWidget;

use Drupal\aws_cloud\Plugin\Field\FieldWidget\BlockDeviceMappingsItem;

/**
 * Plugin implementation of the 'block_device_mappings_item' widget.
 *
 * This plugin is not used at the moment.  Providing it so the
 * BlockDeviceMappings field is complete.
 *
 * @FieldWidget(
 *   id = "block_device_mappings_item",
 *   label = @Translation("OpenStack Block Device Mapping"),
 *   field_types = {
 *     "block_device_mappings"
 *   }
 * )
 */
class OpenStackBlockDeviceItem extends BlockDeviceMappingsItem {

}
