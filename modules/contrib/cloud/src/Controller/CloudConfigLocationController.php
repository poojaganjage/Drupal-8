<?php

namespace Drupal\cloud\Controller;

use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Locale\CountryManager;

/**
 * Controller responsible for Cloud Config Location.
 */
class CloudConfigLocationController extends ControllerBase {

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * CloudConfigLocationController constructor.
   *
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   Country Manager.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   Route Provider.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   */
  public function __construct(CountryManagerInterface $country_manager, RouteProviderInterface $route_provider, Request $request, CloudConfigPluginManagerInterface $cloud_config_plugin_manager) {
    $this->countryManager = $country_manager;
    $this->routeProvider = $route_provider;
    $this->request = $request;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
  }

  /**
   * Dependency Injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Instance of ContainerInterface.
   *
   * @return CloudConfigLocationController
   *   return created object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('country_manager'),
      $container->get('router.route_provider'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('plugin.manager.cloud_config_plugin')
    );
  }

  /**
   * Checks user access for cloud config location.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Symfony\Component\Routing\Route $route
   *   The route object.
   * @param int $cloud_config
   *   Cloud Config entity id.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(AccountInterface $account, Route $route, $cloud_config = NULL) {
    if (!isset($cloud_config)) {
      $view = Views::getView('cloud_config');
      if ($view->access('admin', $account) || $view->access('list', $account)) {
        return AccessResult::allowed();
      }
    }
    else {
      $cloud_config_entity = $this->entityTypeManager()->getStorage('cloud_config')->load($cloud_config);
      if ($cloud_config_entity) {
        if ($cloud_config_entity->access('view')) {
          return AccessResult::allowed();
        }
      }
    }

    return AccessResult::forbidden();
  }

  /**
   * Get Cloud Config location.
   *
   * @param int $cloud_config
   *   Cloud Config entity id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of a Cloud Config location.
   */
  public function getCloudConfigLocation($cloud_config = NULL) {
    global $base_url;

    $country_allowed_values = CountryManager::getStandardList();

    $cloud_configs = isset($cloud_config)
    ? $this->entityTypeManager()->getStorage('cloud_config')
      ->loadByProperties([
        'id' => $cloud_config,
      ])
    : $this->entityTypeManager()->getStorage('cloud_config')
      ->loadMultiple();

    // Support loading all cloud configs based on a particular cloud_config
    // type such as only aws_cloud or k8s.
    $cloud_service_provider = $this->request->get('cloud_service_provider') ?? NULL;
    if (isset($cloud_service_provider)) {
      $cloud_configs = $this->entityTypeManager()->getStorage('cloud_config')
        ->loadByProperties([
          'type' => $cloud_service_provider,
        ]);
    }

    // Get the referer url.
    $referer = $this->request->headers->get('referer');
    // Get the alias or the referer.
    $alias = substr($referer, strlen($base_url));
    // Get the route name of the referer.
    $url = Url::fromUri('internal:' . $alias);
    $params = $url->getRouteParameters();

    $cloud_context = NULL;
    if (isset($params['cloud_context'])) {
      $cloud_context = $params['cloud_context'];
    }
    elseif (isset($cloud_config) && !empty($cloud_configs) && isset($cloud_configs[$cloud_config])) {
      $cloud_config_entity = $cloud_configs[$cloud_config];
      $cloud_context = $cloud_config_entity->getCloudContext();
    }

    if ($this->moduleHandler()->moduleExists('aws_cloud')) {
      $config = $this->config('aws_cloud.settings');
      $aws_location = $config->get('aws_cloud_region_locations');
    }
    else {
      $aws_location = [];
    }

    $locations = [];

    foreach ($cloud_configs ?: [] as $cloud_config) {
      if (isset($cloud_config)) {
        $this->cloudConfigPluginManager->setCloudContext($cloud_config->getCloudContext());
        $route = $this->cloudConfigPluginManager->getInstanceCollectionTemplateName();
        $region = NULL;
        $region_id = NULL;

        if (isset($cloud_config->field_location_longitude) && !empty($cloud_config->field_location_longitude->value)
          && isset($cloud_config->field_location_latitude) && !empty($cloud_config->field_location_latitude->value)
          && (!isset($cloud_config->field_region) || empty($cloud_config->field_region->value))) {

          foreach ($aws_location ?: [] as $id => $values) {
            if ($cloud_config->field_location_latitude->value === $values['latitude']
              && $cloud_config->field_location_longitude->value === $values['longitude']) {
              $region_id = $id;
              $region = $values;
              break;
            }
          }
        }

        if (isset($cloud_config->field_region) && !empty($cloud_config->field_region->value)
          && isset($aws_location) && isset($aws_location[$cloud_config->field_region->value])
          && isset($aws_location[$cloud_config->field_region->value])) {
          $region_id = $cloud_config->field_region->value;
          $region = $aws_location[$region_id];
        }

        if (isset($region)
          || (isset($cloud_config->field_location_country) && !empty($cloud_config->field_location_country->value)
          && isset($cloud_config->field_location_city) && !empty($cloud_config->field_location_city->value)
          && isset($cloud_config->field_location_longitude) && !empty($cloud_config->field_location_longitude->value)
          && isset($cloud_config->field_location_latitude) && !empty($cloud_config->field_location_latitude->value))) {
          if (!isset($region_id)) {
            $region_id = $cloud_config->field_location_latitude->value . '|' . $cloud_config->field_location_longitude->value;
          }
          if (!isset($locations[$region_id])) {
            $locations[$region_id] = [
              'Type' => $cloud_config->bundle(),
              'Country' => $country_allowed_values[$region ? $region['country'] : $cloud_config->field_location_country->value],
              'City' => $region ? $region['city'] : $cloud_config->field_location_city->value,
              'Latitude' => $region ? $region['latitude'] : $cloud_config->field_location_latitude->value,
              'Longitude' => $region ? $region['longitude'] : $cloud_config->field_location_longitude->value,
            ];
          }
          $location = &$locations[$region_id];
          if ($location['Type'] !== $cloud_config->bundle()) {
            $location['Type'] = 'multiple';
          }

          $image_url = '';
          try {
            // Add icon url.
            $file_target = $cloud_config->getIconFid();
            $image = File::load($file_target);
            $image_url = ImageStyle::load('icon_32x32')
              ->buildUrl($image->uri->value);
          }
          catch (\Exception $e) {
            $this->handleException($e);
          }

          $location['Items'][] = [
            'Name' => $cloud_config->getName(),
            'Url' => Url::fromRoute($route, ['cloud_context' => $cloud_config->getCloudContext()])->toString(),
            'Image' => $image_url,
          ];
          if (isset($cloud_context) && $cloud_config->getCloudContext() === $cloud_context) {
            $location['OwnLocation'] = TRUE;
          }
        }
      }
    }
    $response = array_values($locations);

    foreach ($response ?: [] as $idx => $values) {
      if (isset($values['OwnLocation']) && $values['OwnLocation'] === TRUE) {
        unset($response[$idx]);
        $response[] = $values;
        break;
      }
    }

    return new JsonResponse(array_values($response));

  }

  /**
   * Get geoocation by country code and city.
   *
   * @param string $country
   *   The country code.
   * @param string $city
   *   The city.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response of a Cloud Config location.
   */
  public function getGeoLocation($country, $city) {
    $response = [];

    if ($this->moduleHandler()->moduleExists('geocoder')) {
      $config = $this->config('cloud.settings');
      $provider_id = $config->get('cloud_location_geocoder_plugin');
      $provider = $this->entityTypeManager()->getStorage('geocoder_provider')
        ->load($provider_id);

      if (empty($provider)) {
        return new JsonResponse($response);;
      }
      $address = "$city $country";

      // Call geocoder service without dependency injection as an error occurs
      // when geocoder module does not exist.
      $locations = \Drupal::service('geocoder')->geocode($address, [$provider]);
      if ($locations) {
        $latitude = $locations->first()->getCoordinates()->getLatitude();
        $longitude = $locations->first()->getCoordinates()->getLongitude();

        $response = [
          'latitude' => $latitude,
          'longitude' => $longitude,
        ];
      }
    }

    return new JsonResponse($response);
  }

}
