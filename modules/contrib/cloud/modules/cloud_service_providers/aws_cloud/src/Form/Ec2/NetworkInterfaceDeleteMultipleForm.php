<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * Provides an entities deletion confirmation form.
 */
class NetworkInterfaceDeleteMultipleForm extends AwsCloudDeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    $this->ec2Service->setCloudContext($entity->getCloudContext());

    return $this->ec2Service->deleteNetworkInterface(
      ['NetworkInterfaceId' => $entity->getNetworkInterfaceId()]
      ) !== NULL;
  }

}
