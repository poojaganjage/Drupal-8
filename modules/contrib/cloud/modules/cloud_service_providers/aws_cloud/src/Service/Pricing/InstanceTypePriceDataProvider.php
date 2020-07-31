<?php

namespace Drupal\aws_cloud\Service\Pricing;

use Drupal\cloud\Service\CloudServiceBase;

/**
 * The data provider of instance type prices.
 */
class InstanceTypePriceDataProvider extends CloudServiceBase {

  public const ONE_YEAR = 365.25;

  /**
   * The AWS Pricing Service.
   *
   * @var \Drupal\aws_cloud\Service\Pricing\PricingServiceInterface
   */
  protected $pricingService;

  /**
   * Instance type.
   *
   * @var string
   */
  private $instanceType;

  /**
   * Constructor.
   *
   * @param \Drupal\aws_cloud\Service\Pricing\PricingServiceInterface $pricing_service
   *   AWS Pricing service.
   */
  public function __construct(PricingServiceInterface $pricing_service) {

    // The parent constructor takes care of $this->messenger object.
    parent::__construct();

    $this->pricingService = $pricing_service;
  }

  /**
   * Get fields.
   *
   * @return array
   *   Fields.
   */
  public function getFields() {
    return [
      'instance_type'     => $this->t('Instance Type'),
      'on_demand_hourly'  => $this->t('On-demand<br>Hourly ($)'),
      'on_demand_daily'   => $this->t('On-demand<br>Daily ($)'),
      'on_demand_monthly' => $this->t('On-demand<br>Monthly ($)'),
      'on_demand_yearly'  => $this->t('On-demand<br>Yearly ($)'),
      'ri_one_year'       => $this->t('RI<br>1 Year ($)'),
      'ri_three_year'     => $this->t('RI<br>3 Years ($)'),
      'vcpu'              => $this->t('vCPU'),
      'ecu'               => $this->t('ECU'),
      'gpu'               => $this->t('GPU'),
      'clock_speed'       => $this->t('Clock<br>Speed (GHz)'),
      'memory'            => $this->t('Memory<br>(GiB)'),
      'storage'           => $this->t('Storage'),
      'network'           => $this->t('Network'),
      'processor'         => $this->t('Processor'),
      'instance_family'   => $this->t('Instance Family'),
    ];
  }

  /**
   * Get field widths.
   *
   * @return array
   *   Fields.
   */
  public function getFieldWidths() {
    return [
      'instance_type'     => 120,
      'on_demand_hourly'  => NULL,
      'on_demand_daily'   => NULL,
      'on_demand_monthly' => NULL,
      'on_demand_yearly'  => NULL,
      'ri_one_year'       => NULL,
      'ri_three_year'     => NULL,
      'vcpu'              => 60,
      'ecu'               => 60,
      'gpu'               => 60,
      'clock_speed'       => 100,
      'memory'            => NULL,
      'storage'           => 200,
      'network'           => 140,
      'processor'         => 280,
      'instance_family'   => 140,
    ];
  }

  /**
   * Get pricing data.
   *
   * @param string $cloud_context
   *   Cloud context.
   * @param string $instance_type
   *   The instance type to be used as a filter.
   * @param string $sort
   *   The sort.
   * @param string $order_field
   *   The order field.
   *
   * @return array
   *   The pricing data.
   */
  public function getData(
    $cloud_context,
    $instance_type = NULL,
    $sort = NULL,
    $order_field = NULL
  ) {
    $instance_types = aws_cloud_get_instance_types($cloud_context);
    return $this->getInstanceTypePriceData($instance_types, $instance_type, $sort, $order_field);
  }

  /**
   * Get pricing data.
   *
   * @param string $region
   *   The name of a region.
   * @param string $instance_type
   *   The instance type to be used as a filter.
   * @param string $sort
   *   The sort condition.
   * @param string $order_field
   *   The order field.
   *
   * @return array
   *   The pricing data.
   */
  public function getDataByRegion(
    $region,
    $instance_type = NULL,
    $sort = NULL,
    $order_field = NULL
  ) {
    $instance_types = aws_cloud_get_instance_types_by_region($region);
    return $this->getInstanceTypePriceData($instance_types, $instance_type, $sort, $order_field);
  }

  /**
   * Get pricing data.
   *
   * @param array $instance_types
   *   The information of all the instance types.
   * @param string $instance_type
   *   The instance type to be used as a filter.
   * @param string $sort
   *   The sort condition.
   * @param string $order_field
   *   The order field.
   *
   * @return array
   *   The pricing data.
   */
  private function getInstanceTypePriceData(
    array $instance_types,
    $instance_type = NULL,
    $sort = NULL,
    $order_field = NULL
  ) {
    $data = [];
    foreach ($instance_types ?: [] as $value) {
      $parts = explode(':', $value);
      $name = $parts[0];
      $hourly_rate = $parts[4];
      $parts[10] = $parts[10] ?? '';
      $clock_speed = explode(' ', $parts[10]);
      $parts[3] = $parts[3] ?? '';
      $memory = explode(' ', $parts[3]);
      if ($instance_type !== NULL) {
        $instance_type_family = explode('.', $instance_type)[0];
        if (strpos($name, $instance_type_family . '.') !== 0) {
          continue;
        }
      }
      $parts[7] = $parts[7] ?? '';
      $parts[8] = $parts[8] ?? '';
      $parts[9] = $parts[9] ?? '';
      $parts[11] = $parts[11] ?? '';
      $parts[12] = $parts[12] ?? '';
      $parts[13] = $parts[13] ?? '';
      $parts[2] = $parts[2] ?? '';
      $parts[1] = $parts[1] ?? '';
      $data[] = [
        'instance_type'     => $name,
        'on_demand_hourly'  => (float) $hourly_rate,
        'on_demand_daily'   => (float) $hourly_rate * 24,
        'on_demand_monthly' => (float) $hourly_rate * 24 * self::ONE_YEAR / 12,
        'on_demand_yearly'  => (float) $hourly_rate * 24 * self::ONE_YEAR,
        'ri_one_year'       => (float) $parts[5],
        'ri_three_year'     => (float) $parts[6],
        'vcpu'              => $parts[1],
        'ecu'               => $parts[2],
        'gpu'               => $parts[8],
        'clock_speed'       => (float) $clock_speed[0],
        'memory'            => $memory[0],
        'storage'           => $parts[11],
        'network'           => $parts[12],
        'processor'         => $parts[9],
        'instance_family'   => $parts[7],
      ];
    }
    // Get sort and order parameters.
    if (empty($sort)) {
      $sort = 'asc';
    }
    if (empty($order_field)) {
      $order_field = 'instance_type';
    }

    // Sort data.
    usort($data, static function ($a, $b) use ($sort, $order_field) {
      if ($order_field === 'instance_type') {
        $a_type = explode('.', $a[$order_field])[0];
        $b_type = explode('.', $b[$order_field])[0];
        if ($a_type < $b_type) {
          $result = -1;
        }
        elseif ($a_type > $b_type) {
          $result = 1;
        }
        else {
          $result = $a['on_demand_hourly'] < $b['on_demand_hourly'] ? -1 : 1;
        }
      }
      else {
        $result = $a[$order_field] < $b[$order_field] ? -1 : 1;
      }

      if ($sort === 'desc') {
        $result *= -1;
      }

      return $result;
    });

    // Format numbers.
    foreach ($data as &$row) {
      $row['on_demand_hourly'] = $this->convertToNumber($row['on_demand_hourly'], 4);
      $row['on_demand_daily'] = $this->convertToNumber($row['on_demand_daily'], 2);
      $row['on_demand_monthly'] = $this->convertToNumber($row['on_demand_monthly'], 0);
      $row['on_demand_yearly'] = $this->convertToNumber($row['on_demand_yearly'], 0);
      $row['ri_one_year'] = $this->convertToNumber($row['ri_one_year'], 0);
      $row['ri_three_year'] = $this->convertToNumber($row['ri_three_year'], 0);
      $row['clock_speed'] = $this->convertToNumber($row['clock_speed'], 1);
      $row['memory'] = $this->convertToNumber($row['memory'], 0);
    }

    return $data;
  }

  /**
   * Convert to the string formatted with grouped thousands.
   *
   * @param float $float
   *   The float variable.
   * @param int $precision
   *   The optional number of decimal digits.
   *
   * @return string
   *   The formatted string.
   */
  private function convertToNumber($float, $precision = 0) {
    $float = round($float, $precision);
    return number_format($float, $precision);
  }

}
