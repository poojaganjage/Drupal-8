<?php

namespace Drupal\aws_cloud\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a link to AWS Service checks.
 *
 * @Block(
 *   id = "aws_cloud_service_link_block",
 *   admin_label = @Translation("Service Health"),
 *   category = @Translation("AWS Cloud")
 * )
 */
class ServiceHealthLinkBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['health'] = [
      '#type' => 'details',
      '#title' => $this->t('Service Health'),
      '#open' => TRUE,
    ];
    $build['health'][] = [
      '#markup' => '<span>' . $this->t('<a href=":health_link" target="new_window">View complete health details</a>',
          [
            ':health_link' => 'https://status.aws.amazon.com/',
          ]) . '</span>',
    ];
    return $build;
  }

}
