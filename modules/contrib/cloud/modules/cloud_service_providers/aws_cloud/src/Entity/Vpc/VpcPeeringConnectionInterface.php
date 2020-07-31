<?php

namespace Drupal\aws_cloud\Entity\Vpc;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a VPC Peering Connection entity.
 *
 * @ingroup aws_cloud
 */
interface VpcPeeringConnectionInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public function getCloudContext();

  /**
   * Get the VPC Peering Connection ID.
   */
  public function getVpcPeeringConnectionId();

  /**
   * Set the VPC Peering Connection ID.
   *
   * @param string $vpc_peering_connection_id
   *   The VPC Peering Connection ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setVpcPeeringConnectionId($vpc_peering_connection_id);

  /**
   * Get the Requester Account ID.
   */
  public function getRequesterAccountId();

  /**
   * Set Requester Account ID.
   *
   * @param string $requester_account_id
   *   The Requester Account ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setRequesterAccountId($requester_account_id);

  /**
   * Get the Requester VPC ID.
   */
  public function getRequesterVpcId();

  /**
   * Set Requester VPC ID.
   *
   * @param string $requester_vpc_id
   *   The Requester VPC ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setRequesterVpcId($requester_vpc_id);

  /**
   * Get the Requester CIDR block.
   */
  public function getRequesterCidrBlock();

  /**
   * Set Requester CIDR block.
   *
   * @param string $requester_cidr_block
   *   The Requester CIDR Block.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setRequesterCidrBlock($requester_cidr_block);

  /**
   * Get the Requester Region.
   */
  public function getRequesterRegion();

  /**
   * Set Requester Region.
   *
   * @param string $requester_region
   *   The Requester Region.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setRequesterRegion($requester_region);

  /**
   * Get the Accepter Account ID.
   */
  public function getAccepterAccountId();

  /**
   * Set Accepter Account ID.
   *
   * @param string $accepter_account_id
   *   The Accepter Account ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setAccepterAccountId($accepter_account_id);

  /**
   * Get the Accepter VPC ID.
   */
  public function getAccepterVpcId();

  /**
   * Set Accepter VPC ID.
   *
   * @param string $accepter_vpc_id
   *   The Accepter VPC ID.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setAccepterVpcId($accepter_vpc_id);

  /**
   * Get the Accepter CIDR block.
   */
  public function getAccepterCidrBlock();

  /**
   * Set Accepter CIDR block.
   *
   * @param string $accepter_cidr_block
   *   The Accepter CIDR Block.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setAccepterCidrBlock($accepter_cidr_block);

  /**
   * Get the Accepter Region.
   */
  public function getAccepterRegion();

  /**
   * Set Accepter Region.
   *
   * @param string $accepter_region
   *   The Accepter Region.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setAccepterRegion($accepter_region);

  /**
   * Get the status code.
   */
  public function getStatusCode();

  /**
   * Set status code.
   *
   * @param string $status_code
   *   The status code.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setStatusCode($status_code);

  /**
   * Get the status message.
   */
  public function getStatusMessage();

  /**
   * Set status message.
   *
   * @param string $status_message
   *   The status message.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setStatusMessage($status_message);

  /**
   * Get the expiration time.
   */
  public function getExpirationTime();

  /**
   * Set expiration time.
   *
   * @param int $expiration_time
   *   The expiration time.
   *
   * @return \Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection
   *   The VPC Peering Connection entity.
   */
  public function setExpirationTime($expiration_time);

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
