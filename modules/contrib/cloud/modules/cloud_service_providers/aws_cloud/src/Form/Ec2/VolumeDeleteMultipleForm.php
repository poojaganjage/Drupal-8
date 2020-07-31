<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * Provides an entities deletion confirmation form.
 */
class VolumeDeleteMultipleForm extends AwsCloudDeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    $this->ec2Service->setCloudContext($entity->getCloudContext());

    return $this->ec2Service->deleteVolume(
      ['VolumeId' => $entity->getVolumeId()]
    ) !== NULL;
  }

}
