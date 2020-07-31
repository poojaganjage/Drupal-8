<?php

namespace Drupal\Tests\cloud\Functional;

/**
 * Utility for testing.
 */
class Utils {

  /**
   * Get a random public IP address.
   *
   * @return string
   *   a random public IP address.
   *
   * @throws \Exception
   */
  public static function getRandomPublicIp(): string {
    return implode('.', [
      random_int(0, 254),
      random_int(0, 255),
      random_int(0, 255),
      random_int(1, 255),
    ]);
  }

  /**
   * Get a random private IP address.
   *
   * @return string
   *   a random private IP address.
   *
   * @throws \Exception
   */
  public static function getRandomPrivateIp(): string {
    $private_ips = [
      implode('.', [
        '10',
        random_int(0, 255),
        random_int(0, 255),
        random_int(1, 255),
      ]),
      implode('.', [
        '172',
        random_int(16, 31),
        random_int(0, 255),
        random_int(1, 255),
      ]),
      implode('.', [
        '192',
        '168',
        random_int(0, 255),
        random_int(1, 255),
      ]),
    ];
    return $private_ips[array_rand($private_ips)];
  }

  /**
   * Get a random cidr.
   *
   * @return string
   *   a random cidr.
   *
   * @throws \Exception
   */
  public static function getRandomCidr(): string {
    $cidrs = [
      implode('.', [
        '10',
        random_int(0, 255),
        random_int(0, 255),
        random_int(1, 255),
      ]) . '/8',
      implode('.', [
        '172',
        random_int(16, 31),
        '0',
        '0',
      ]) . '/16',
      implode('.', [
        '192',
        '168',
        random_int(0, 255),
        '0',
      ]) . '/24',
    ];
    return $cidrs[array_rand($cidrs)];
  }

  /**
   * Get a random CIDR v6.
   *
   * @return string
   *   a random CIDR v6.
   *
   * @throws \Exception
   */
  public static function getRandomCidrV6(): string {
    $cidrs = [
      implode(':', [
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
      ]) . '::/64',
      implode(':', [
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
        sprintf('%04x', random_int(1, 65535)),
      ]) . '::/96',
    ];
    return $cidrs[array_rand($cidrs)];
  }

  /**
   * Get a random from port.
   *
   * @param int $start
   *   If passed, used as the min value.
   * @param int $end
   *   If passed, used as the max value.
   *
   * @return int
   *   Random int.
   *
   * @throws \Exception
   */
  public static function getRandomFromPort($start = 0, $end = 37676): int {
    return random_int($start, $end);
  }

  /**
   * Get a random to port.
   *
   * @param int $start
   *   If passed, used as the min value.
   * @param int $end
   *   If passed, used as the max value.
   *
   * @return int
   *   Random int.
   *
   * @throws \Exception
   */
  public static function getRandomToPort($start = 37677, $end = 65535): int {
    return random_int($start, $end);
  }

  /**
   * Get a public DNS corresponding to specified region and IP address.
   *
   * @param string $region
   *   A region.
   * @param string $ip
   *   An IP address.
   *
   * @return string
   *   a public DNS.
   */
  public static function getPublicDns($region, $ip): string {
    $ip_parts = explode('.', $ip);
    return sprintf('ec2-%d-%d-%d-%d.%s.compute.amazonaws.com',
      $ip_parts[0], $ip_parts[1], $ip_parts[2], $ip_parts[3], $region);
  }

  /**
   * Get a private DNS corresponding to specified region and IP address.
   *
   * @param string $region
   *   A region.
   * @param string $ip
   *   An IP address.
   *
   * @return string
   *   a private DNS.
   */
  public static function getPrivateDns($region, $ip): string {
    $ip_parts = explode('.', $ip);
    return sprintf('ip-%d-%d-%d-%d.%s.compute.internal',
      $ip_parts[0], $ip_parts[1], $ip_parts[2], $ip_parts[3], $region);
  }

  /**
   * Get a random user ID.
   *
   * @return int
   *   Random user ID.
   *
   * @throws \Exception
   */
  public static function getRandomUid(): int {
    return random_int(1, 50);
  }

}
