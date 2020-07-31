<?php

namespace Drupal\k8s\Service\K8sClientExtension;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\k8s\Service\K8sServiceException;
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Exceptions\BadRequestException;

/**
 * K8s client.
 */
class K8sClient extends Client {

  use CloudContentEntityTrait;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\k8s\Service\K8sServiceException
   *    Thrown when unable to sendRequest.
   */
  public function sendRequest(
    $method,
    $uri,
    $query = [],
    $body = [],
    $namespace = TRUE,
    $apiVersion = NULL
  ) {
    try {
      if ($method === 'PUT' || $method === 'POST') {
        // If the resource type is cluster role, the apiGroups maybe empty,
        // and it can't be removed.
        if ($uri !== '/clusterroles') {
          $array = $this->removeEmptyProperties(json_decode($body, TRUE));
          $body = json_encode($array, JSON_PRETTY_PRINT);
        }
      }

      if (empty($this->namespace)) {
        $namespace = FALSE;
      }

      return parent::sendRequest(
        $method,
        $uri,
        $query,
        $body,
        $namespace,
        $apiVersion
      );
    }
    catch (BadRequestException $e) {
      $error_info = json_decode($e->getMessage(), TRUE);
      if (empty($error_info)) {
        throw new K8sServiceException($this->t(
          'Unknown error occurred when calling K8s API.'
        ));
      }

      $this->messenger()->addError($this->t('An error occurred when calling K8s API: @method @uri', [
        '@method' => $method,
        '@uri' => $uri,
      ]));

      $this->messenger()->addError($this->t('Status Code: @status_code', [
        '@status_code' => $error_info['code'],
      ]));

      $this->messenger()->addError($this->t('Error reason: @error_reason', [
        '@error_reason' => $error_info['reason'],
      ]));

      $this->messenger()->addError($this->t('Message: @msg', [
        '@msg' => $error_info['message'],
      ]));

      throw new K8sServiceException($this->t(
        'An error occurred when calling K8s API.'
      ));
    }
    catch (\Exception $e) {
      throw new K8sServiceException($e->getMessage());
    }
  }

  /**
   * Remove empty properties.
   *
   * @param array $haystack
   *   The array.
   *
   * @return array
   *   The array whose empty properties were removed.
   */
  private function removeEmptyProperties(array $haystack) {
    foreach ($haystack ?: [] as $key => $value) {
      if (is_array($value)) {
        $haystack[$key] = $this->removeEmptyProperties($value);
      }

      if (empty($haystack[$key])) {
        unset($haystack[$key]);
      }
    }

    return $haystack;
  }

}
