<?php

namespace Drupal\aws_cloud\Service\CloudWatch;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Low utilization checker for instances.
 *
 * Checks the Amazon Elastic Compute Cloud (Amazon EC2) instances
 * that were running at any time during the last 14 days and alerts you
 * if the daily CPU utilization was 10% or less and network I/O was 5 MB
 * or less.
 */
class LowUtilizationInstanceChecker {

  /**
   * The cloud watch service.
   *
   * @var \Drupal\aws_cloud\Service\CloudWatch\CloudWatchServiceInterface
   */
  private $cloudWatchService;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new LowUtilizationInstanceChecker object.
   *
   * @param \Drupal\aws_cloud\Service\CloudWatch\CloudWatchServiceInterface $cloud_watch_service
   *   The cloud watch service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory.
   */
  public function __construct(
    CloudWatchServiceInterface $cloud_watch_service,
    ConfigFactoryInterface $config_factory) {

    $this->cloudWatchService = $cloud_watch_service;
    $this->configFactory = $config_factory;
  }

  /**
   * Check if the instance is low or not.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param string $instance_id
   *   The instance ID.
   *
   * @return bool
   *   The instance is low or not.
   */
  public function isLow($cloud_context, $instance_id) {
    $one_day = 24 * 60 * 60;
    $this->cloudWatchService->setCloudContext($cloud_context);
    $dimensions = [
      [
        'Name' => 'InstanceId',
        'Value' => $instance_id,
      ],
    ];

    $queries = [];
    $queries[] = [
      'Id' => 'cpu',
      'MetricStat' => [
        'Metric' => [
          'Namespace' => 'AWS/EC2',
          'MetricName' => 'CPUUtilization',
          'Dimensions' => $dimensions,
        ],
        // 1day.
        'Period' => $one_day,
        'Stat' => 'Average',
      ],
    ];

    $queries[] = [
      'Id' => 'network_in',
      'MetricStat' => [
        'Metric' => [
          'Namespace' => 'AWS/EC2',
          'MetricName' => 'NetworkIn',
          'Dimensions' => $dimensions,
        ],
        // 1day.
        'Period' => $one_day,
        'Stat' => 'Sum',
      ],
    ];

    $queries[] = [
      'Id' => 'network_out',
      'MetricStat' => [
        'Metric' => [
          'Namespace' => 'AWS/EC2',
          'MetricName' => 'NetworkOut',
          'Dimensions' => $dimensions,
        ],
        // 1day.
        'Period' => $one_day,
        'Stat' => 'Sum',
      ],
    ];

    $period = $this->configFactory
      ->get('aws_cloud.settings')
      ->get('aws_cloud_low_utilization_instance_period');

    $result = $this->cloudWatchService->getMetricData([
      'StartTime' => strtotime("-$period days"),
      'EndTime' => strtotime('now'),
      'MetricDataQueries' => $queries,
    ]);

    return $this->isCpuLow($result['MetricDataResults'][0]['Values'] ?? [])
      && $this->isNetworkLow(
        $result['MetricDataResults'][1]['Values'] ?? [],
        $result['MetricDataResults'][2]['Values'] ?? []
      );
  }

  /**
   * Check if the utilization of CPU is 10% or less.
   *
   * @param array $cpus
   *   The CPU utilization.
   *
   * @return bool
   *   The utilization of CPU is low or not.
   */
  private function isCpuLow(array $cpus) {
    $result = TRUE;

    $threshold = $this->configFactory
      ->get('aws_cloud.settings')
      ->get('aws_cloud_low_utilization_instance_cpu_utilization_threshold');

    foreach ($cpus as $cpu) {
      if ($cpu > $threshold) {
        $result = FALSE;
        break;
      }
    }

    return $result;
  }

  /**
   * Check if the network I/O is 5MB or less.
   *
   * @param array $network_ins
   *   The traffics of network in.
   * @param array $network_outs
   *   The traffics of network out.
   *
   * @return bool
   *   The utilization of network is low or not.
   */
  private function isNetworkLow(array $network_ins, array $network_outs) {
    $result = TRUE;

    $threshold = $this->configFactory
      ->get('aws_cloud.settings')
      ->get('aws_cloud_low_utilization_instance_network_io_threshold') * 1024 * 1024;

    foreach ($network_ins as $index => $network_in) {
      if ($network_in + $network_outs[$index] > $threshold) {
        $result = FALSE;
        break;
      }
    }

    return $result;
  }

}
