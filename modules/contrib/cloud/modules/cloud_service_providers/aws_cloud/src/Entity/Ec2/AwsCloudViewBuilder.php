<?php

namespace Drupal\aws_cloud\Entity\Ec2;

use Drupal\cloud\Entity\CloudViewBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the EC2 base entity view builders.
 */
abstract class AwsCloudViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($entity, $view_mode, $langcode);
    $build['#attached']['library'][] = 'aws_cloud/aws_cloud_view_builder';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return array_merge(parent::trustedCallbacks(), ['reorderServerTemplate']);
  }

  /**
   * Reorder fields of AWS Cloud cloud server template.
   *
   * @param array $build
   *   Build array.
   *
   * @return array
   *   Build array reordered.
   */
  public static function reorderServerTemplate(array $build) : array {
    $build['name']['#label_display'] = 'inline';
    $build['instance']['name'] = $build['name'];
    unset($build['name']);
    return $build;
  }

}
