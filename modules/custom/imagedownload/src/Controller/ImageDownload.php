<?php

namespace Drupal\imagedownload\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handle /download/file/ URLs to redirect to content download based on filename.
 */
class ImageDownload extends ControllerBase {

  /**
   * Download file.
   *
   * @param string $filename
   *   The filename.
   */
  public function downloadFile($filename, $status =200, $headers =array()) {

    // Do some file validation here, like checking for extension.

    // File lives in /files/downloads.

    
    $uri = 'Downloads/';

    $uri = $uri_prefix . $filename;
    //echo $uri;
    //die();
    $headers = [
      'Content-Type' => 'ForceType application/octet-stream .jpg', // Would want a condition to check for extension and set Content-Type dynamically
      'Content-Description' => 'Image Download',
      'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ];

    // Return and trigger file donwload.
    return new Response($uri, 200, $headers, true);

  }
}