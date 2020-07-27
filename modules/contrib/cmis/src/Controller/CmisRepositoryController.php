<?php

declare(strict_types = 1);

namespace Drupal\cmis\Controller;

use Drupal\cmis\CmisConnectionApi;
use Drupal\Core\Controller\ControllerBase;
use Drupal\cmis\CmisBrowser;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CmisRepositoryController.
 *
 * @package Drupal\cmis\Controller
 */
class CmisRepositoryController extends ControllerBase {

  private $browser;
  private $connection;

  /**
   * Construct.
   *
   * @param string $config
   * @param string $folder_id
   */
  public function __construct($config = '', $folder_id = '') {
    if (!empty($config) && !empty($folder_id)) {
      $this->initBrowser($config, $folder_id);
    }
  }

  /**
   * Browse.
   *
   * @param string $config
   * @param string $folder_id
   *
   * @return string
   *   Return cmis browser render array or warning.
   */
  public function browse($config = '', $folder_id = '') {
    if (empty($this->browser)) {
      $this->initBrowser($config, $folder_id);
    }
    if (!empty($this->browser->getCurrent())) {
      $cacheable = $this->browser->getConnection()->getConfig()->getCmisCacheable();
      return $this->browser->browse(!$cacheable);
    }
  }

  /**
   * Get properties.
   *
   * @param string $config
   * @param string $document_id
   *
   * @return array
   *   Return properties table render array.
   */
  public function getProperties($config = '', $document_id = '') {
    if (empty($this->browser)) {
      $this->initBrowser($config, $document_id);
    }
    if (!empty($this->browser->getCurrent())) {
      return $this->browser->getDocumentProperties();
    }
  }

  /**
   * Object delete verify popup.
   *
   * @param string $config
   * @param string $object_id
   */
  public function objectDeleteVerify($config = '', $object_id = '') {
    $parameters = \Drupal::request()->query->all();
    unset($parameters['_wrapper_format']);
    $type = '';
    $name = '';
    if (!empty($parameters['type']) && !empty($config) && !empty($object_id) &&
        (!empty($parameters['parent']) || !empty($parameters['query_string']))) {
      $this->setConnection($config);
      if ($this->connection) {
        if ($current = $this->connection->getObjectById($object_id)) {
          $type = $current->getBaseTypeId()->__toString();
          $name = $current->getName();
        }
        else {
          return [
            '#theme' => 'cmis_object_delete_verify',
            '#title' => $this->t("Object can't delete"),
            '#description' => $this->t('Object not found in repository.'),
            '#link' => '',
          ];
        }
      }
    }
    else {
      return [
        '#theme' => 'cmis_object_delete_verify',
        '#title' => $this->t("Object can't delete"),
        '#description' => $this->t('Argument or parameter missed.'),
        '#link' => '',
      ];
    }

    $args = [
      '@type' => str_replace('cmis:', '', $type),
      '@name' => $name,
    ];

    $url = Url::fromUserInput('/cmis/object-delete/' . $config . '/' . $object_id);

    $link_options = ['query' => $parameters];
    $url->setOptions($link_options);
    $path = Link::fromTextAndUrl($this->t('Delete'), $url)->toRenderable();
    $link = render($path);

    return [
      '#theme' => 'cmis_object_delete_verify',
      '#title' => $this->t('Are you sure you want to delete @type name @name', $args),
      '#description' => $this->t('This action cannot be undone.'),
      '#link' => $link,
    ];
  }

  /**
   * Object delete popup.
   *
   * @param string $config
   * @param string $object_id
   */
  public function objectDelete($config = '', $object_id = '') {
    $parameters = \Drupal::request()->query->all();

    if (
      !empty($parameters['type']) &&
      !empty($config) &&
      !empty($object_id) &&
      (!empty($parameters['parent']) || !empty($parameters['query_string']))
    ) {
      switch ($parameters['type']) {
        case 'browser':
          $redirect = $this->redirect('cmis.cmis_repository_controller_browser', ['config' => $config]);
          break;

        case 'query':
          $parameters += ['config' => $config];
          $redirect = $this->redirect('cmis.cmis_query_form_callback', [], ['query' => $parameters]);
          break;

        default:
          // Back to frontpage if not browser or not query.
          $redirect = new RedirectResponse('/');
      }

      $this->setConnection($config);
      if ($this->connection) {
        $root = $this->connection->getRootFolder();
        if ($root->getId() != $object_id && $current = $this->connection->getObjectById($object_id)) {
          // Exists object and not root folder.
          $type = $current->getBaseTypeId()->__toString();
          $name = $current->getName();

          $args = [
            '@type' => str_replace('cmis:', '', $type),
            '@name' => $name,
          ];

          $current->delete(TRUE);

          $this->messenger()->addStatus($this->t('The @type name @name has now been deleted.', $args));
          if ($parameters['type'] == 'browser') {
            $redirect = $this->redirect('cmis.cmis_repository_controller_browser', ['config' => $config, 'folder_id' => $parameters['parent']]);
          }
        }
        else {
          if ($root->getId() != $object_id) {
            $this->messenger()->addWarning($this->t('Could not delete object. Object is not exists in repository.'));
          }
          else {
            $this->messenger()->addWarning($this->t('Could not delete root folder.'));
          }
        }
      }
    }
    else {
      $this->messenger()->addWarning($this->t('Argument or parameter missed.'));
      // Back to frontpage.
      $redirect = new RedirectResponse('/');
    }

    return $redirect;
  }

  /**
   * Set connection.
   *
   * @param string $config
   *   The connection ID.
   */
  private function setConnection($config = '') {
    if (!empty($config)) {
      if ($this->connection = new CmisConnectionApi($config)) {
        $this->connection->setDefaultParameters();
      }
    }
  }

  /**
   * Init browser.
   *
   * @param string $config
   * @param string $folder_id
   *
   * @return array|void
   *   A renderable array in case of error.
   */
  private function initBrowser($config, $folder_id) {
    if (!empty($config)) {
      $browser = new CmisBrowser($config, $folder_id);
      if ($browser->getConnection()) {
        $this->browser = $browser;
      }
      else {
        return $this->connectionError($config);
      }
    }
    else {
      return $this->configureError();
    }
  }

  /**
   * Get browser.
   *
   * @return object
   */
  public function getBrowser() {
    return $this->browser;
  }

  /**
   * Prepare configure error.
   *
   * @return array
   */
  private function configureError() {
    return [
      '#markup' => $this->t('No configure defined. Please go to CMIS configure page and create configure.'),
    ];
  }

  /**
   * Prepare connection error.
   *
   * @param string $config
   *
   * @return array
   */
  private function connectionError($config) {
    return [
      '#markup' => $this->t('No connection ready of config: @config. Please go to CMIS configure page and create properly configure.', ['@config' => $config]),
    ];
  }

}
