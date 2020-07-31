<?php

namespace Drupal\aws_cloud\Service\Pricing;

use Drupal\aws_cloud\Service\S3\S3Service;
use Drupal\cloud\Entity\CloudConfig;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\CloudServiceBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Client;

/**
 * Service PricingService.
 */
class PricingService extends CloudServiceBase implements PricingServiceInterface {

  public const DEFAULT_ENDPOINT = 'https://pricing.us-east-1.amazonaws.com/offers/v1.0/aws/AmazonEC2/current';

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Cloud context string.
   *
   * @var string
   */
  private $cloudContext;

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Guzzle http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * TRUE to run the operation, FALSE to run the operation in validation mode.
   *
   * @var bool
   */
  private $dryRun;

  /**
   * The cloud service provider (CloudConfig) entity.
   *
   * @var \Drupal\cloud\Entity\CloudConfig
   */
  private $cloudConfigEntity = FALSE;

  /**
   * The Amazon S3 Service.
   *
   * @var \Drupal\aws_cloud\Service\S3\S3ServiceInterface
   */
  private $s3Service;

  /**
   * Constructs a new Ec2Service object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \GuzzleHttp\Client $http_client
   *   The Guzzle Http client.
   * @param \Drupal\aws_cloud\Service\S3\S3Service $s3_service
   *   The S3 service.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              Client $http_client,
                              S3Service $s3_service) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    // Setup the configuration factory.  Not really needed at this point.
    // Could be useful at a later date.
    $this->configFactory = $config_factory;

    // Setup the dryRun flag.
    $this->dryRun = (bool) $this->configFactory->get('aws_cloud.settings')->get('aws_cloud_test_mode');
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->httpClient = $http_client;
    $this->s3Service = $s3_service;
  }

  /**
   * {@inheritdoc}
   */
  public function setCloudConfigEntity(CloudConfig $cloud_config_entity) {
    $this->cloudConfigEntity = $cloud_config_entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setCloudContext($cloud_context) {
    $this->cloudContext = $cloud_context;
  }

  /**
   * Generate the full pricing endpoint.
   *
   * @return bool|string
   *   The full pricing endpoint.
   */
  private function getPricingEndpoint() {
    $endpoint = FALSE;
    if ($this->cloudConfigEntity !== FALSE) {
      $pricing_endpoint = $this->configFactory
        ->get('aws_cloud.settings')
        ->get('aws_cloud_ec2_pricing_endpoint');

      if (empty($pricing_endpoint)) {
        $pricing_endpoint = self::DEFAULT_ENDPOINT;
      }

      $endpoint = "$pricing_endpoint/{$this->cloudConfigEntity->get('field_region')->value}/index.json";
    }
    return $endpoint;
  }

  /**
   * Retrieve the instance type pricing information..
   *
   * @return array
   *   The instance type pricing information.
   */
  public function getInstanceTypes() : array {

    $config = $this->configFactory->get('aws_cloud.settings');
    $mock_data = $config->get('aws_cloud_mock_instance_types');
    if ($this->dryRun && $mock_data) {
      return json_decode($mock_data, TRUE);
    }

    $price_rate = $config->get('aws_cloud_price_rate_ec2') ?: 100;
    $price_rate /= 100.0;

    // Give max memory for json decoding.
    ini_set('memory_limit', '-1');
    $instance_types = [];

    $url = $this->getPricingEndpoint();
    if (empty($url)) {
      $this->messenger
        ->addError($this->t('Unable to set pricing endpoint for @cloud_context.  Cannot retrieve instance types.', [
          '@cloud_context' => $this->cloudContext,
        ]));
      return [];
    }

    try {

      $data = NULL;
      // $matched: [1] A bucket name, [2] s3 or s3-, [3] AWS region ID such as
      // us-west2, [4] A Key.
      preg_match('/https?:\/\/(.*)\.(s3\.|s3-)(.*)?\.amazonaws\.com\/(.*)/', $url, $matches);

      // First, try to access to an S3 object if 'aws_cloud_service_provider'
      // is set.
      if (!empty($matches[0])
        && !empty($config->get('aws_cloud_service_provider'))) {

        $this->s3Service->setCloudContext($config->get('aws_cloud_service_provider'));
        // Assume $url is an S3 URL.
        try {
          $result = $this->s3Service->getObject([
            'Bucket' => $matches[1],
            'Key' => $matches[4],
          ]);
        }
        catch (\Exception $e) {
          // Do nothing to handle the exception, just log the error since we
          // will try to access $url as a normal HTTP access below.
          $this->logger('aws_cloud_pricing_service')->error($this->t('S3Service::getObject failed for the URL: %URL', [
            '%url' => $url,
          ]));
        }

        // Process $data if we can get $result.
        if (!empty($result)
          && !empty($result['ContentLength'])
          && !empty($result['Body'])) {

          $length = $result['ContentLength'];
          $result['Body']->rewind();
          $data = $result['Body']->read($length);
        }
      }

      // Second, switch the strategy to get $data through the normal HTTP access
      // if the S3 access fails above.
      if (empty($data)) {

        try {
          $response = $this->httpClient->get($url, [
            'timeout' => 0,
          ]);
          $data = (string) $response->getBody();
        }
        catch (RequestException $e) {
          $this->messenger->addError(t('Error retrieving instance types.'));

          // Empty out the array so it does not get cached with empty values.
          return [];
        }
      }

      // Error handling by checking if $data has nothing.
      if (empty($data)) {

        $cloud_config = CloudConfig::load($config->get('aws_cloud_service_provider'));
        if (!empty($cloud_config)) {
          $page_link = Link::fromTextAndUrl(
            $cloud_config->getName(), Url::fromRoute(
            'entity.cloud_config.edit_form', [
              'cloud_config' => $cloud_config->id(),
            ])
          )->toString();

          $this->messenger->addError(
            $this->t('Cannot retrieve instance types: @page_link', [
              '@page_link' => $page_link,
            ])
          );
        }

        return [];
      }

      // Assume $data should contain something about the pricing information.
      $instance_products = [];
      $pricing = \GuzzleHttp\json_decode($data);

      foreach ($pricing->products ?: [] as $product) {

        if ($product->productFamily === 'Compute Instance'
          && $product->attributes->operatingSystem === 'Linux'
          && $product->attributes->tenancy !== 'Dedicated'
          && $product->attributes->preInstalledSw === 'NA'
          && isset($product->attributes->instancesku)
        ) {
          $instance_products[$product->attributes->instancesku] = $product->attributes;
        }
      }

      // Add on-demand price.
      foreach ($pricing->terms->OnDemand ?: [] as $term) {
        $items = array_values((array) $term);

        foreach ($items ?: [] as $item) {
          $prices = array_values((array) $item->priceDimensions);

          foreach ($prices ?: [] as $price) {
            if (!empty($price)
              && $price->unit === 'Hrs'
              && $price->pricePerUnit->USD
              && !empty($instance_products[$item->sku])
              && $this->isOnDemandInstance($price)
            ) {
              $instance_products[$item->sku]->price = (float) $price->pricePerUnit->USD;
              break 2;
            }
          }
        }
      }

      // Add reserved instance price.
      foreach ($pricing->terms->Reserved ?: [] as $term) {

        $items = array_values((array) $term);
        foreach ($items ?: [] as $item) {
          if (!isset($item->termAttributes->LeaseContractLength)) {
            continue;
          }

          if ($item->termAttributes->LeaseContractLength !== '1yr'
            && $item->termAttributes->LeaseContractLength !== '3yr'
          ) {
            continue;
          }

          if ($item->termAttributes->OfferingClass !== 'standard'
            || $item->termAttributes->PurchaseOption !== 'All Upfront'
          ) {
            continue;
          }

          $prices = array_values((array) $item->priceDimensions);

          foreach ($prices ?: [] as $price) {
            if ($price
              && $price->unit === 'Quantity'
              && $price->pricePerUnit->USD
            ) {
              if (isset($instance_products[$item->sku])) {
                $property_name = $item->termAttributes->LeaseContractLength === '1yr'
                  ? 'one_year_price'
                  : 'three_year_price';
                $instance_products[$item->sku]->$property_name = (float) $price->pricePerUnit->USD;

                break;
              }
            }
          }
        }
      }

      uasort($instance_products, static function ($a, $b) {
        $a_type = explode('.', $a->instanceType)[0];
        $b_type = explode('.', $b->instanceType)[0];
        if ($a_type < $b_type) {
          return -1;
        }
        elseif ($a_type > $b_type) {
          return 1;
        }

        return (float) $a->price < (float) $b->price ? -1 : 1;
      });

      foreach ($instance_products ?: [] as $product) {
        $instance_types[$product->instanceType] = sprintf(
          '%s:%s:%s:%s:%s:%s:%s:%s:%s:%s:%s:%s:%s',
          $product->instanceType,
          $product->vcpu,
          $product->ecu !== 'NA' ? $product->ecu : 'N/A',
          $product->memory,
          !empty($product->price) ? $product->price * $price_rate : '',
          !empty($product->one_year_price) ? $product->one_year_price * $price_rate : '',
          !empty($product->three_year_price) ? $product->three_year_price * $price_rate : '',
          !empty($product->instanceFamily) ? $product->instanceFamily : 'N/A',
          !empty($product->gpu) ? $product->gpu : 'N/A',
          !empty($product->physicalProcessor) ? $product->physicalProcessor : 'N/A',
          !empty($product->clockSpeed) ? $product->clockSpeed : 'N/A',
          !empty($product->storage) ? $product->storage : 'N/A',
          !empty($product->networkPerformance) ? $product->networkPerformance : 'N/A'
        );
      }
    }
    catch (\Exception $e) {
      $link = $GLOBALS['base_url'] . '/admin/config/services/cloud/aws_cloud/settings';
      $this->messenger->addError($this->t('An error occurred while accessing the pricing API endpoint: %url. Please check the EC2 Pricing API Point URL at <a href="@link">AWS Cloud Settings</a>.',
        [
          '@link' => $link,
          '%url' => $url,
        ]));
      $this->handleException($e);
    }

    return $instance_types;
  }

  /**
   * Helper function to check whether the instance is on demand.
   *
   * @param object $price
   *   The price object.
   *
   * @return bool
   *   The result of check.
   */
  private function isOnDemandInstance($price) {
    $forbidden_words = ['Windows', 'Reservation', 'Dedicated'];
    foreach ($forbidden_words ?: [] as $word) {
      if (strpos($price->description, $word) !== FALSE) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
