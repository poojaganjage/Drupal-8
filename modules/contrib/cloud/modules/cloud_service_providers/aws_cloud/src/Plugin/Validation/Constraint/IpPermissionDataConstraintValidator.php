<?php

namespace Drupal\aws_cloud\Plugin\Validation\Constraint;

use Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission;
use Drupal\cloud\Service\CloudService;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TypedData\Validation\TypedDataAwareValidatorTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates each permission field.
 */
class IpPermissionDataConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  use TypedDataAwareValidatorTrait;
  use CloudContentEntityTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cloud Service.
   *
   * @var \Drupal\cloud\Service\CloudService
   */
  protected $cloudService;

  /**
   * Current route.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRoute;

  /**
   * Constructs a new constraint validator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager instance.
   * @param \Drupal\cloud\Service\CloudService $cloud_service
   *   Cloud Service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route
   *   The current route.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CloudService $cloud_service, RouteMatchInterface $current_route) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cloudService = $cloud_service;
    $this->currentRoute = $current_route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cloud'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    /* @var \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $item */
    $source = $item->getSource();

    // Validate to and from ports.
    $this->validatePorts($item, $constraint);

    // Validate ip4/ip6 or group configurations.
    if ($source === 'ip4') {
      $this->validateCidrIp($item, $constraint);
    }
    elseif ($source === 'ip6') {
      $this->validateCidrIpv6($item, $constraint);
    }
    elseif ($source === 'prefix') {
      $this->validatePrefixId($item, $constraint);
    }
    elseif ($source === 'group') {
      $this->validateGroup($item, $constraint);
    }

    // Check for duplicates.
    $this->checkDuplicates($item, $constraint, $source);
  }

  /**
   * Check for empty Prefix Id.
   *
   * @param \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $ip_permission
   *   IP Permission object.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   Constraint object.
   */
  private function validatePrefixId(IpPermission $ip_permission, Constraint $constraint) {
    if (empty($ip_permission->getPrefixListId())) {
      $this->context->addViolation($constraint->noPrefixListId);
    }
  }

  /**
   * Check for duplicate rules.
   *
   * @param \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $ip_permission
   *   IP Permission object.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   Constraint object.
   * @param string $source
   *   Whether the permission is ip4, ipv6 or group.
   */
  private function checkDuplicates(IpPermission $ip_permission, Constraint $constraint, $source) {
    $entries = $ip_permission->getParent();
    foreach ($entries as $key => $entry) {
      // Don't validate itself.
      if ($ip_permission->getName() !== $entry->getName()) {
        // First check if there are duplicate to/from ports.
        $dup_ports = ($entry->getToPort() === $ip_permission->getToPort() &&
          $entry->getFromPort() === $ip_permission->getFromPort());

        // If there are dup_ports, check the corresponding ip4, ip6 or groupid.
        if ($dup_ports === TRUE) {
          if ($source === 'ip4' && $entry->getCidrIp() === $ip_permission->getCidrIp()) {
            $this->context->addViolation($constraint->duplicateIP4);
            break;
          }
          elseif ($source == 'ip6' && $entry->getCidrIpv6() === $ip_permission->getCidrIpv6()) {
            $this->context->addViolation($constraint->duplicateIP6);
            break;
          }
          elseif ($source == 'prefix' && $entry->getPrefixListId() === $ip_permission->getPrefixListId()) {
            $this->context->addViolation($constraint->duplicatePrefix);
            break;
          }
          elseif ($source == 'group' && $entry->getGroupId() === $ip_permission->getGroupId()) {
            $this->context->addViolation($constraint->duplicateGroup);
            break;
          }
        }
      }
    }
  }

  /**
   * Validate to and from port rules.
   *
   * @param \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $ip_permission
   *   IP Permission object.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   Constraint object.
   */
  private function validatePorts(IpPermission $ip_permission, Constraint $constraint) {
    $to_port = $ip_permission->getToPort();
    $from_port = $ip_permission->getFromPort();

    // Make sure the ports are not empty.
    // 0 is an allowed port, but empty(0) will evaluate it
    // as empty.
    if ($from_port !== '0' && empty($from_port)) {
      $this->context->addViolation($constraint->fromPortEmpty);
    }
    if ($to_port !== '0' && empty($to_port)) {
      $this->context->addViolation($constraint->toPortEmpty);
    }

    // Make sure the ports are numeric.
    if (!is_numeric($from_port)) {
      $this->context->addViolation($constraint->fromPortNotNumeric);
    }
    if (!is_numeric($to_port)) {
      $this->context->addViolation($constraint->toPortNotNumeric);
    }

    // Cast the ports to integers to do additional validations.
    $from_port = (int) $from_port;
    $to_port = (int) $to_port;

    if ($ip_permission->getIpProtocol() === 'icmp') {
      // For ICMP, the $to_port and $from_port
      // are used to hold ICMP code/type.
      // If -1 is passed, which means all code/type, both $to_port
      // and $from_port needs to be -1.
      if ($from_port === -1 && $to_port >= 0) {
        $this->context->addViolation($constraint->negativeToPortICMP);
      }
      elseif ($to_port === -1 && $from_port >= 0) {
        $this->context->addViolation($constraint->negativeFromPortICMP);
      }

      if ($from_port > 255) {
        // Code out of range.
        $this->context->addViolation($constraint->fromPortOutOfRange);
      }
      if ($to_port > 255) {
        // Type out of range.
        $this->context->addViolation($constraint->toPortOutOfRange);
      }
    }
    elseif ($ip_permission->getIpProtocol() === 'icmpv6') {
      // For ICMPv6, the ports need to be -1.
      if ($from_port !== -1) {
        $this->context->addViolation($constraint->negativeFromPortICMP);
      }
      if ($to_port !== -1) {
        $this->context->addViolation($constraint->negativeToPortICMP);
      }
    }
    elseif ($from_port > $to_port) {
      $this->context->addViolation($constraint->toPortGreater, [
        '%value' => $from_port,
        '@field_name' => 'from_port',
      ]);
      $this->context->addViolation($constraint->toPortGreater, [
        '%value' => $to_port,
        '@field_name' => 'to_port',
      ]);
    }
  }

  /**
   * Validate cidr_ipv6 addresses.
   *
   * @param \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $ip_permission
   *   IP Permission object.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   Constraint object.
   */
  private function validateCidrIpv6(IpPermission $ip_permission, Constraint $constraint) {
    $cidr_ipv6 = $ip_permission->getCidrIpv6();
    if (empty($cidr_ipv6)) {
      $this->context->addViolation($constraint->ip6IsEmpty, [
        '%value' => $cidr_ipv6,
        '@field_name' => 'cidr_ipv6',
      ]);
    }
    elseif (!$this->validateCidr($cidr_ipv6)) {
      $this->context->addViolation($constraint->ip6Value, [
        '%value' => $cidr_ipv6,
        '@field_name' => 'cidr_ipv6',
      ]);
    }

  }

  /**
   * Validate cidr_ip addresses.
   *
   * @param \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $ip_permission
   *   IP Permission object.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   Constraint object.
   */
  private function validateCidrIp(IpPermission $ip_permission, Constraint $constraint) {
    $cidr_ip = $ip_permission->getCidrIp();
    if (empty($cidr_ip)) {
      $this->context->addViolation($constraint->ip4IsEmpty, [
        '%value' => $cidr_ip,
        '@field_name' => 'cidr_ip',
      ]);
    }
    elseif (!$this->validateCidr($cidr_ip)) {
      $this->context->addViolation($constraint->ip4Value, [
        '%value' => $cidr_ip,
        '@field_name' => 'cidr_ip',
      ]);
    }
  }

  /**
   * Validate group id/name configuration.
   *
   * @param \Drupal\aws_cloud\Plugin\Field\FieldType\IpPermission $ip_permission
   *   IP Permission object.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   Constraint object.
   */
  private function validateGroup(IpPermission $ip_permission, Constraint $constraint) {
    // Group ID or name.
    $entity = $ip_permission->getEntity();
    $entity_type = $entity->getEntityTypeId();

    $security_group = $this->currentRoute
      ->getParameter($entity_type);

    if (!empty($security_group)) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $security_group */
      // Check that group_id is not empty.
      $group_id = $ip_permission->getGroupId();
      if (empty($group_id)) {
        $this->context->addViolation($constraint->groupIdIsEmpty, [
          '%value' => $group_id,
          '@field_name' => 'group_id',
        ]);
      }
      else {
        // If $group_id is passed, make sure the group vpc = the current
        // security group VPC.
        /* @var \Drupal\aws_cloud\Entity\Ec2\SecurityGroup $target_group */
        $target_group = $this->getSecurityGroupByGroupId($entity_type, $group_id, $security_group->getCloudContext());
        if ($target_group === FALSE) {
          $this->context->addViolation($constraint->noSecurityGroup, [
            '@group_id' => $group_id,
          ]);
        }
        elseif ($target_group->getVpcId() !== $security_group->getVpcId()) {
          $this->context->addViolation($constraint->differentGroupVPC, [
            '@target_group' => $target_group->getGroupName(),
            '@target_group_id' => $target_group->getGroupId(),
            '@source_group' => $security_group->getGroupName(),
          ]);
        }
      }
    }
    else {
      // Cannot load security group. Error out.
      $this->context->addViolation($constraint->noSecurityGroup, [
        '%value' => $ip_permission->getGroupName(),
        '@field_name' => 'group_name',
      ]);
    }
  }

  /**
   * Load a security group by the Security Group Id.
   *
   * @param string $entity_type
   *   The entity_type to lookup.
   * @param string $group_id
   *   The group id.
   * @param string $cloud_context
   *   The cloud context to load.
   *
   * @return \Drupal\aws_cloud\Entity\Ec2\SecurityGroup|false
   *   The loaded security group or FALSE.
   */
  private function getSecurityGroupByGroupId($entity_type, $group_id, $cloud_context) {
    $security_group = FALSE;
    try {
      $groups = $this->entityTypeManager
        ->getStorage($entity_type)
        ->loadByProperties(
          [
            'group_id' => $group_id,
            'cloud_context' => $cloud_context,
          ]
        );
      if (!empty($groups)) {
        $security_group = array_shift($groups);
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
    return $security_group;
  }

  /**
   * Validate CIDR IP addresses.
   *
   * This method works for cidr_ip and cidr_ipv6.
   *
   * @param string $cidr
   *   The CIDR string.
   *
   * @return bool
   *   TRUE or FALSE.
   */
  private function validateCidr($cidr) : bool {
    $parts = explode('/', $cidr);

    if (count($parts) !== 2) {
      return FALSE;
    }
    $ip = $parts[0];
    $netmask = (int) $parts[1];

    if ($netmask < 0) {
      return FALSE;
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      return $netmask <= 32;
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
      return $netmask <= 128;
    }
    return FALSE;
  }

}
