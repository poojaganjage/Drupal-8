<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a VPC entity.
 *
 * @ingroup aws_cloud
 */
interface VpcInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getCloudContext();

  /**
   * Get the VPC ID.
   */
  public function getVpcId();

  /**
   * Set VPC ID.
   *
   * @param string $vpc_id
   *   The VPC ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setVpcId($vpc_id = '');

  /**
   * Get the CIDR block.
   */
  public function getCidrBlock();

  /**
   * Set CIDR block.
   *
   * @param string $cidr_block
   *   The CIDR block.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setCidrBlock($cidr_block);

  /**
   * Get the DHCP options ID.
   */
  public function getDhcpOptionsId();

  /**
   * Set DHCP options ID.
   *
   * @param string $dhcp_options_id
   *   The DHCP options ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setDhcpOptionsId($dhcp_options_id);

  /**
   * Get the instance tenancy.
   */
  public function getInstanceTenancy();

  /**
   * Set instance tenancy.
   *
   * @param string $instance_tenancy
   *   The instance tenancy.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setInstanceTenancy($instance_tenancy);

  /**
   * Get the tags.
   */
  public function getTags();

  /**
   * Set the tags.
   *
   * @param array $tags
   *   The tags.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setTags(array $tags);

  /**
   * Get the CIDR blocks.
   */
  public function getCidrBlocks();

  /**
   * Set the CIDR blocks.
   *
   * @param array $cidr_blocks
   *   The CIDR blocks.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setCidrBlocks(array $cidr_blocks);

  /**
   * Get the IPv6 CIDR blocks.
   */
  public function getIpv6CidrBlocks();

  /**
   * Set the IPv6 CIDR blocks.
   *
   * @param array $ipv6_cidr_blocks
   *   The IPv6 CIDR blocks.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setIpv6CidrBlocks(array $ipv6_cidr_blocks);

  /**
   * Get is_default.
   */
  public function isDefault();

  /**
   * Set is_default.
   *
   * @param bool $is_default
   *   Whether is default.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setDefault($is_default);

  /**
   * Get the account ID.
   */
  public function getAccountId();

  /**
   * Set account ID.
   *
   * @param string $account_id
   *   The account ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setAccountId($account_id);

  /**
   * Get the state.
   */
  public function getState();

  /**
   * Set state.
   *
   * @param string $state
   *   The state.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Vpc
   *   The VPC entity.
   */
  public function setState($state);

  /**
   * {@inheritdoc}
   */
  public function created();

  /**
   * {@inheritdoc}
   */
  public function setCreated($created = 0);

  /**
   * {@inheritdoc}
   */
  public function changed();

  /**
   * {@inheritdoc}
   */
  public function getRefreshed();

  /**
   * {@inheritdoc}
   */
  public function setRefreshed($time);

}
