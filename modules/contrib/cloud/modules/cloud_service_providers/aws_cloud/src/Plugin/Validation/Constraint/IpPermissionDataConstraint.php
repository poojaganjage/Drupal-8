<?php

namespace Drupal\aws_cloud\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * IpPermission field validation.
 *
 * @Constraint(
 *   id = "ip_permission_data",
 *   label = @Translation("IP Permission", context = "Validation"),
 * )
 */
class IpPermissionDataConstraint extends Constraint {

  /**
   * A message: "The To Port is not numeric".
   *
   * @var string
   */
  public $toPortEmpty = 'The To Port is empty.';

  /**
   * A message: "The From Port is not numeric".
   *
   * @var string
   */
  public $fromPortEmpty = 'The From Port is empty.';

  /**
   * A message: "The To Port is not numeric".
   *
   * @var string
   */
  public $toPortNotNumeric = 'The To Port is not numeric.';

  /**
   * A message: "The From Port is not numeric".
   *
   * @var string
   */
  public $fromPortNotNumeric = 'The From Port is not numeric.';

  /**
   * A message: "The From Port needs to be -1 to support all ICMP types.".
   *
   * @var string
   */
  public $negativeFromPortICMP = 'The From Port needs to be -1 to support all ICMP types.';

  /**
   * A message: "The To Port is out of range.
   *
   * @var string
   */
  public $toPortOutOfRange = 'The To Port is out of range. For ICMP, the To Port must be less than 255.';

  /**
   * A message: "The From Port is out of range.
   *
   * @var string
   */
  public $fromPortOutOfRange = 'The From Port is out of range. For ICMP, the From Port must be less than 255.';

  /**
   * A message: "The To Port needs to be -1 to support all ICMP codes.".
   *
   * @var string
   */
  public $negativeToPortICMP = 'The To Port needs to be -1 to support all ICMP codes.';

  /**
   * A message: "CIDR IP is empty".
   *
   * @var string
   */
  public $ip4IsEmpty = 'CIDR IP is empty.';

  /**
   * A message: "CIDR IP is not valid".
   *
   * @var string
   */
  public $ip4Value = 'CIDR IP is not valid. Single IP addresses must be in x.x.x.x/32 notation.';

  /**
   * A message: "CIDR IPv6 is not valid.".
   *
   * @var string
   */
  public $ip6Value = 'CIDR IPv6 is not valid. Single IP addresses must be in x.x.x.x/32 notation.';

  /**
   * A message: "CIDR IPv6 is empty".
   *
   * @var string
   */
  public $ip6IsEmpty = 'CIDR IPv6 is empty.';

  /**
   * A message: "Group ID is empty".
   *
   * @var string
   */
  public $groupIdIsEmpty = 'Group ID is empty.';

  /**
   * A message: "Group ID belongs to a different VPC.".
   *
   * @var string
   */
  public $differentGroupVPC = 'Group @target_group - @target_group_id belongs to a different VPC than @source_group.';

  /**
   * A message: "No security group found".
   *
   * @var string
   */
  public $noSecurityGroup = 'No security group: @group_id found.';

  /**
   * A message: "From Port is greater than To Port".
   *
   * @var string
   */
  public $toPortGreater = 'From Port is greater than To Port.';

  /**
   * A message: "Duplicate IP rules found".
   *
   * @var string
   */
  public $duplicateIP4 = 'Duplicate IP rules found.';

  /**
   * A message: "Duplicate IPv6 rules found.".
   *
   * @var string
   */
  public $duplicateIP6 = 'Duplicate IPv6 rules found.';

  /**
   * A message: "Duplicate group rules found.".
   *
   * @var string
   */
  public $duplicateGroup = 'Duplicate group rules found.';

  /**
   * A message: "Duplicate prefix rules found.".
   *
   * @var string
   */
  public $duplicatePrefix = 'Duplicate prefix rules found.';

  /**
   * A message: "No Prefix List Id found.".
   *
   * @var string
   */
  public $noPrefixListId = 'No Prefix List Id found.';

}
