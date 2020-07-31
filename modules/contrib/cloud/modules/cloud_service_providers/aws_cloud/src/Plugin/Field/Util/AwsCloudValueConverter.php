<?php

namespace Drupal\aws_cloud\Plugin\Field\Util;

use Drupal\cloud\Plugin\Field\Util\ValueConverterInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aws cloud value converter interface for key_value field type.
 */
class AwsCloudValueConverter implements ValueConverterInterface, ContainerInjectionInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * Constructs an AwsCloudValueConverter instance.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(DateFormatterInterface $date_formatter) {

    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function convert($key, $value): string {

    // If $key contains a keyword '_timestamp'.
    // e.g. $key = 'cloud_termination_timestamp'.
    if ((mb_strpos($key, '_timestamp') !== FALSE)
    && !empty($value)
    && is_numeric($value)) {
      $value = $this->dateFormatter->format($value, 'short');
    }

    return $value;
  }

}
