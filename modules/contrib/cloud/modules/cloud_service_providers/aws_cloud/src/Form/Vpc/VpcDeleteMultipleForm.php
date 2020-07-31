<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Form\Ec2\AwsCloudDeleteMultipleForm;
use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * Provides an entities deletion confirmation form.
 */
class VpcDeleteMultipleForm extends AwsCloudDeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    $this->ec2Service->setCloudContext($entity->getCloudContext());

    return $this->ec2Service->deleteVpc(
      ['VpcId' => $entity->getVpcId()]
      ) !== NULL;
  }

}
