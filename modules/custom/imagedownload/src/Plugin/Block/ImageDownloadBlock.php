<?php

namespace Drupal\imagedownload\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * @Block(
 *   id = "image_download",
 *   admin_label = @Translation("Image Download"),
 *   category = @Translation("Image")
 * )
 */
class ImageDownloadBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];


    $items = [
      'Click to download an image' => 'img1.jpg'
    ];


    foreach ($items as $item => $filename) {
      $url = Url::fromRoute('imagedownload.download.file', ['file_name' => $filename]);
      $links[$item] = [
        '#title' => $this->t($item),
        '#type' => 'link',
        '#url' => $url
      ];
    }

    $file_download_list = [
      '#theme' => 'item_list',
      '#items' => $links
    ];

    $build['file_downloads'] = [
      '#type' => 'container',
      'file_downloads_prefix' => ['#type' => 'html_tag', '#tag' => 'p', '#value' => $this->t('Download the image:')],
      'file_downloads_list' => $file_download_list,

    ];

    return $build;

  }

}