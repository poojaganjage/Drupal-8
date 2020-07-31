<?php

namespace Drupal\openstack\Plugin\Field\FieldFormatter;

use Drupal\aws_cloud\Plugin\Field\FieldFormatter\BlockDeviceMappingsFormatter;

/**
 * Plugin implementation of the 'block_device_mappings_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "block_device_mappings_formatter",
 *   label = @Translation("Block Device Mappings formatter"),
 *   field_types = {
 *     "block_device_mappings"
 *   }
 * )
 */
class OpenStackBlockDeviceFormatter extends BlockDeviceMappingsFormatter {

}
