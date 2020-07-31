<?php

namespace Drupal\aws_cloud\Entity\Ec2;

/**
 * Provides the key pair view builders.
 */
class KeyPairViewBuilder extends AwsCloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'key_pair',
        'title' => $this->t('Key Pair'),
        'open' => TRUE,
        'fields' => [
          'key_pair_name',
          'key_fingerprint',
          'key_material',
          'created',
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

}
