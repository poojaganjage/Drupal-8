<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * Provides an entities deletion confirmation form.
 */
class ElasticIpDeleteMultipleForm extends AwsCloudDeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    $this->ec2Service->setCloudContext($entity->getCloudContext());

    $allocation_id = $entity->getAllocationId();
    $public_ip = $entity->getPublicIp();
    $params = [];

    if ($entity->getDomain() === 'standard' && !empty($public_ip)) {
      $params['PublicIp'] = $public_ip;
    }
    elseif ($entity->getDomain() === 'vpc' && !empty($allocation_id)) {
      $params['AllocationId'] = $allocation_id;
    }

    return $this->ec2Service->releaseAddress($params) !== NULL;
  }

  /**
   * Returns the message to show the user after an item was processed.
   *
   * @param int $count
   *   Count of processed translations.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The item processed message.
   */
  protected function getProcessedMessage($count) {

    $this->ec2Service->updateElasticIp();
    $this->ec2Service->updateInstances();
    $this->ec2Service->updateNetworkInterfaces();

    return $this->formatPlural($count, 'Deleted @count Elastic IP.', 'Deleted @count Elastic IPs.');
  }

}
