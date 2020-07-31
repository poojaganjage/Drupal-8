<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * Provides an entities deletion confirmation form.
 */
class ImageDeleteMultipleForm extends AwsCloudDeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    $this->cloudConfigPluginManager->setCloudContext($entity->getCloudContext());

    $account_id = $this->cloudConfigPluginManager->loadConfigEntity()->get('field_account_id')->value;

    // If the image isn't owned by the aws user, the calling for deregisterImage
    // will be skipped for AWS Cloud and it will be called for OpenStack.
    if ($entity->getEntityTypeId() === 'aws_cloud_image' && $entity->getAccountId() !== $account_id) {

      return TRUE;
    }

    $this->ec2Service->setCloudContext($entity->getCloudContext());
    return $this->ec2Service->deregisterImage(
      ['ImageId' => $entity->getImageId()]
    ) !== NULL;
  }

}
