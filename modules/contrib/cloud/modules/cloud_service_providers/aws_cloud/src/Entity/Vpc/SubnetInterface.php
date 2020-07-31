<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining an subnet entity.
 *
 * @ingroup aws_cloud
 */
interface SubnetInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getCloudContext();

  /**
   * Get the subnet ID.
   */
  public function getSubnetId();

  /**
   * Set subnet ID.
   *
   * @param string $subnet_id
   *   The subnet ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\Subnet
   *   The Subnet Entity.
   */
  public function setSubnetId($subnet_id = '');

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
