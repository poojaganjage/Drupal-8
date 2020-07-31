<?php

namespace Drupal\aws_cloud\Plugin\Field\Util;

use Drupal\cloud\Plugin\Field\Util\ReservedKeyCheckerInterface;

/**
 * Aws cloud reserved key checker interface for key_value field type.
 */
class AwsCloudReservedKeyChecker implements ReservedKeyCheckerInterface {

  private const RESERVED_KEYS = [
    'Name',
  ];

  /**
   * {@inheritdoc}
   */
  public function isReservedWord($key): bool {
    if (empty($key)) {
      return FALSE;
    }

    if (in_array($key, self::RESERVED_KEYS)) {
      return TRUE;
    }

    // Reserve for special tags.
    if ($key !== NULL && strpos($key, 'aws:') === 0) {
      return TRUE;
    }

    // Reserve for aws_cloud_* tags.
    if ($key !== NULL && preg_match('/^aws_cloud_[a-z_]+$/', $key)) {
      return TRUE;
    }

    // Reserve for cloud_server_template_* tags.
    if ($key !== NULL && preg_match('/^cloud_server_template_[a-z_]+$/', $key)) {
      return TRUE;
    }

    return FALSE;
  }

}
