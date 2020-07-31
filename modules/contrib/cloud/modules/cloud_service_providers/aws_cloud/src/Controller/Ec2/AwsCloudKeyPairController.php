<?php

namespace Drupal\aws_cloud\Controller\Ec2;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller responsible for AWS KeyPair.
 */
class AwsCloudKeyPairController extends ControllerBase {

  /**
   * ApiController constructor.
   *
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Messenger Object.
   */
  public function __construct(Messenger $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Instance of ContainerInterface.
   *
   * @return AwsCloudKeyPairController
   *   return created object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * Download Key.
   *
   * @param string $cloud_context
   *   Cloud context string.
   * @param object $key_pair
   *   AWS Cloud KeyPair.
   * @param string $entity_type
   *   The entity type, such as cloud_server_template.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A binary file response object or redirect if key file doesn't exist.
   */
  public function downloadKey($cloud_context, $key_pair, $entity_type = 'aws_cloud') {
    $key_pair = \Drupal::entityTypeManager()->getStorage("{$entity_type}_key_pair")->load($key_pair);
    $file = $key_pair->getKeyFileLocation();
    if ($file !== FALSE) {
      $response = new BinaryFileResponse($file, 200, [], FALSE, 'attachment');
      $response->setContentDisposition('attachment', $key_pair->getKeyPairName() . '.pem');
      $response->deleteFileAfterSend(TRUE);
      return $response;
    }
    else {
      // Just redirect to Key Pair listing page.
      return $this->redirect("view.{$entity_type}_key_pair.list", [
        'cloud_context' => $cloud_context,
      ]);
    }
  }

}
