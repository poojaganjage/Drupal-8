<?php

namespace Drupal\aws_cloud\Service\Ec2;

/**
 * Interface Ec2ServiceInterface.
 */
interface Ec2ServiceInterface {

  /**
   * Set the cloud context.
   *
   * @param string $cloud_context
   *   Cloud context string.
   */
  public function setCloudContext($cloud_context);

  /**
   * Calls the Amazon EC2 API endpoint AssociateAddress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function associateAddress(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint AuthorizeSecurityGroupIngress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function authorizeSecurityGroupIngress(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint AuthorizeSecurityGroupEgress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results or NULL if there is an error.
   */
  public function authorizeSecurityGroupEgress(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint AllocateAddress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of ElasticIps or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function allocateAddress(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint AssociateIamInstanceProfile.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   An IamInstanceProfileAssociation or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function associateIamInstanceProfile(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DisassociateIamInstanceProfile.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   An IamInstanceProfileAssociation or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function disassociateIamInstanceProfile(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint ReplaceIamInstanceProfileAssociation.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   An IamInstanceProfileAssociation or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function replaceIamInstanceProfileAssociation(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeIamInstanceProfileAssociations.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of IamInstanceProfileAssociation or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeIamInstanceProfileAssociations(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint CreateImage.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Image or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createImage(array $params = []);

  /**
   * Modifies the specified attribute of a image.
   *
   * @param array $params
   *   Parameters array to send to API.
   */
  public function modifyImageAttribute(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Key Pair.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of KeyPair or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createKeyPair(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Network Interface.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of NetworkInterface or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createNetworkInterface(array $params = []);

  /**
   * Modifies the specified attribute of a network interface.
   *
   * @param array $params
   *   Parameters array to send to API.
   */
  public function modifyNetworkInterfaceAttribute(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Volume.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Volume or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createVolume(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Modify Volume.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Volume or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function modifyVolume(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Snapshot.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Snapshot or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createSnapshot(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Vpc.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VPC or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createVpc(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Flow logs.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of FlowLog or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createFlowLogs(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create VPC Peering Connection.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VPC or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createVpcPeeringConnection(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Accept VPC Peering Connection.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VPC or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function acceptVpcPeeringConnection(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Describe VPC Peering Connections.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VPC or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeVpcPeeringConnections(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Describe Flow Logs.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of FlowLog or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeFlowLogs(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Security Group.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of SecurityGroup or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createSecurityGroup(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Create Tags.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createTags(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Delete Tags.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteTags(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeregisterImage.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deregisterImage(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeInstances.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Instances or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeInstances(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeInstanceAttribute.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Instances or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeInstanceAttribute(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeImages.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Images or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeImages(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeImageAttribute.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Images or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeImageAttribute(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeSecurityGroups.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of SecurityGroups or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeSecurityGroups(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeNetworkInterfaces.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of NetworkInterfaceList or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeNetworkInterfaces(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeAccountAttributes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Addresses or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeAccountAttributes(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeAddresses.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Addresses or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeAddresses(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeSnapshots.
   *
   * Only snapshots restorable by the user are returned.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Snapshots or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeSnapshots(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeKeyPairs.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of KeyPairs or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeKeyPairs(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeVolumes.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Volumes or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeVolumes(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeAvailabilityZones.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of AvailabilityZones or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeAvailabilityZones(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeVpcs.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VPCs or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeVpcs(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint AssociateVpcCidrBlock.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VPCs or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function associateVpcCidrBlock(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DisassociateVpcCidrBlock.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VPCs or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function disassociateVpcCidrBlock(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DescribeSubnets.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Subnets or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function describeSubnets(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint CreateSubnet.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Subnets or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function createSubnet(array $params = []);

  /**
   * Get regions.
   *
   * @return array
   *   Array of regions.
   */
  public function getRegions();

  /**
   * Get endpoint URLs.
   *
   * @return array
   *   Array of region endpoint URLs.
   */
  public function getEndpointUrls();

  /**
   * Calls the Amazon EC2 API endpoint ImportKeyPair.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of KeyPair or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function importKeyPair(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint RebootInstances.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   This call does not return anything.
   */
  public function rebootInstances(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint TerminateInstances.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Instance or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function terminateInstance(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteSecurityGroup.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteSecurityGroup(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteNetworkInterface.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteNetworkInterface(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint ReleaseAddress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function releaseAddress(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteKeyPair.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteKeyPair(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteVolume.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteVolume(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteSnapshot.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteSnapshot(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteVpc.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteVpc(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteVpcPeeringConnection.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteVpcPeeringConnection(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint Delete Flow logs.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of FlowLog or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteFlowLogs(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DeleteSubnet.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function deleteSubnet(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint DisassociateAddress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function disassociateAddress(array $params = []);

  /**
   * Calls the Amazon EC2 API endpoint RunInstances.
   *
   * @param array $params
   *   Parameters array to send to API.
   * @param array $tags
   *   Optional tags to be sent during the runInstance call.
   *
   * @return array
   *   Array of Instances or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function runInstances(array $params = [], array $tags = []);

  /**
   * Calls the Amazon EC2 API RevokeSecurityGroupIngress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results.
   */
  public function revokeSecurityGroupIngress(array $params = []);

  /**
   * Calls the Amazon EC2 API RevokeSecurityGroupEgress.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return mixed
   *   An array of results.
   */
  public function revokeSecurityGroupEgress(array $params = []);

  /**
   * Update the EC2Instances.
   *
   * Delete old Instance entities, query the API for updated entities and store
   * them as Instance entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateInstances(array $params = [], $clear = TRUE);

  /**
   * Update the EC2Instances without batch.
   *
   * Delete old Instance entities, query the API for updated entities and store
   * them as Instance entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateInstancesWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Update the EC2Images.
   *
   * Delete old Images entities, query the API
   * for updated entities and store them as Images entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to delete images entities before importing.
   *
   * @return bool|int
   *   FALSE if nothing is updated.  Number of images imported returned as
   *   integer if successful.
   */
  public function updateImages(array $params = [], $clear = FALSE);

  /**
   * Update the EC2Images not using batch API.
   *
   * Delete old Images entities, query the API
   * for updated entities and store them as Images entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to delete images entities before importing.
   *
   * @return bool|int
   *   FALSE if nothing is updated.  Number of images imported returned as
   *   integer if successful.
   */
  public function updateImagesWithoutBatch(array $params = [], $clear = FALSE);

  /**
   * Update the EC2Security Groups.
   *
   * Delete old Security Groups entities, query the API for updated entities and
   * store them as Security Groups entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale security groups.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateSecurityGroups(array $params = [], $clear = TRUE);

  /**
   * Update the EC2Network Interfaces.
   *
   * Delete old Network Interfaces entities, query the API for updated entities
   * and store them as Network Interfaces entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale VPCs.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateNetworkInterfaces(array $params = [], $clear = TRUE);

  /**
   * Update the EC2Elastic IPs.
   *
   * Delete old Network Interfaces entities, query the API for updated entities
   * and store them as Network Interfaces entities.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateElasticIp();

  /**
   * Update the EC2Key Pairs.
   *
   * Delete old Key Pairs entities,
   * query the API for updated entities and store them as Key Pairs entities.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateKeyPairs();

  /**
   * Update the EC2Volumes.
   *
   * Delete old Volumes entities,
   * query the API for updated entities and store them as Volumes entities.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateVolumes();

  /**
   * Update the EC2snapshots.
   *
   * Delete old snapshots entities,
   * query the API for updated entities and store them as snapshots entities.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateSnapshots();

  /**
   * Update the EC2VPCs.
   *
   * Delete old VPCs entities,
   * query the API for updated entities and store them as VPCs entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale VPCs.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateVpcs(array $params = [], $clear = TRUE);

  /**
   * Update the EC2VPCs without batch.
   *
   * Delete old VPCs entities,
   * query the API for updated entities and store them as VPCs entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale VPCs.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateVpcsWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Update the VPC Peering Connections.
   *
   * Delete old VPC Peering Connections entities,
   * query the API for updated entities and store them as
   * VPC Peering Connections entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale VPC Peering Connections.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateVpcPeeringConnections(array $params = [], $clear = TRUE);

  /**
   * Update the VPC Peering Connections without batch.
   *
   * Delete old VPC Peering Connections entities,
   * query the API for updated entities and store them as
   * VPC Peering Connections entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale VPC Peering Connections.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateVpcPeeringConnectionsWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Update the EC2subnets.
   *
   * Delete old subnets entities,
   * query the API for updated entities and store them as subnets entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale subnets.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateSubnets(array $params = [], $clear = TRUE);

  /**
   * Update the EC2subnets.
   *
   * Delete old subnets entities,
   * query the API for updated entities and store them as subnets entities.
   *
   * @param array $params
   *   Optional parameters array.
   * @param bool $clear
   *   TRUE to clear stale subnets.
   *
   * @return bool
   *   Indicates success so failure.
   */
  public function updateSubnetsWithoutBatch(array $params = [], $clear = TRUE);

  /**
   * Update cloud server templates.
   *
   * Delete old cloud server template entities,
   * query the API for updated entities and store them as
   * cloud server template entities.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateCloudServerTemplates();

  /**
   * Update cloud server templates.
   *
   * Delete old cloud server template entities,
   * query the API for updated entities and store them as
   * cloud server template entities.
   *
   * @return bool
   *   indicates success so failure.
   */
  public function updateCloudServerTemplatesWithoutBatch();

  /**
   * Method gets all the availability zones in a particular cloud context.
   *
   * @return array
   *   Array of availability zones.
   */
  public function getAvailabilityZones();

  /**
   * Method gets all the VPCs in a particular cloud context.
   *
   * @return array
   *   Array of VPCs.
   */
  public function getVpcs();

  /**
   * Method to clear all entities out of the system.
   */
  public function clearAllEntities();

  /**
   * Stops ec2 instance given an instance array.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Instances or NULL if there is an error.
   */
  public function stopInstances(array $params = []);

  /**
   * Start ec2 instance given an instance array.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of Instances or NULL if there is an error.
   */
  public function startInstances(array $params = []);

  /**
   * Modifies the specified attribute of a instance.
   *
   * @param array $params
   *   Parameters array to send to API.
   */
  public function modifyInstanceAttribute(array $params = []);

  /**
   * Attaches an EBS volume.
   *
   * Attaches an EBS Volume to a running or stopped
   * instance and exposes it to the instance with the
   * specified device name.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VolumeAttachment or NULL if there is an error.
   */
  public function attachVolume(array $params = []);

  /**
   * Detaches an EBS volume.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of VolumeAttachment or NULL if there is an error.
   */
  public function detachVolume(array $params = []);

  /**
   * Create a launch template.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of LaunchTemplate or NULL if there is an error.
   */
  public function createLaunchTemplate(array $params = []);

  /**
   * Delete a launch template.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of LaunchTemplate or NULL if there is an error.
   */
  public function deleteLaunchTemplate(array $params = []);

  /**
   * Modify a launch template.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of LaunchTemplate or NULL if there is an error.
   */
  public function modifyLaunchTemplate(array $params = []);

  /**
   * Describe launch templates.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of LaunchTemplate or NULL if there is an error.
   */
  public function describeLaunchTemplates(array $params = []);

  /**
   * Create a launch template version.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of LaunchTemplate or NULL if there is an error.
   */
  public function createLaunchTemplateVersion(array $params = []);

  /**
   * Delete launch template versions.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of LaunchTemplate or NULL if there is an error.
   */
  public function deleteLaunchTemplateVersions(array $params = []);

  /**
   * Describe launch template versions.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of LaunchTemplate or NULL if there is an error.
   */
  public function describeLaunchTemplateVersions(array $params = []);

  /**
   * Retrieves the supported platforms supported by a particular ec2 account.
   *
   * @return array
   *   An array of supported accounts.
   */
  public function getSupportedPlatforms();

  /**
   * Calls the Amazon EC2 API endpoint GetConsoleOutput.
   *
   * @param array $params
   *   Parameters array to send to API.
   *
   * @return array
   *   Array of output or NULL if there is an error.
   *
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   *   If the $params is empty or $ec2_client (Ec2Client) is NULL.
   */
  public function getConsoleOutput(array $params = []);

  /**
   * Helper method to get the name of AWS object.
   *
   * @param array $aws_obj
   *   Array of aws pbject.
   * @param string $default_value
   *   Default value of tag name.
   *
   * @return string
   *   Tag name.
   */
  public function getTagName(array $aws_obj, $default_value);

  /**
   * Helper method to get the map of snapshot ID and name.
   *
   * @param array $volumes
   *   Array of volumes.
   *
   * @return array
   *   Map of snapshots.
   */
  public function getSnapshotIdNameMap(array $volumes);

  /**
   * Helper function to parse drupal uid value out of the tags array.
   *
   * @param array $tags_array
   *   The tags array.
   * @param string $key
   *   The uid key.
   *
   * @return int
   *   Drupal uid.
   */
  public function getUidTagValue(array $tags_array, $key);

  /**
   * Helper function to get an instance's uid.
   *
   * @param string $instance_id
   *   The instance_id to load.
   *
   * @return int
   *   The uid of the instance.
   */
  public function getInstanceUid($instance_id);

  /**
   * Helper function to loop the network interfaces.
   *
   * Also creates a comma delimited string of private IPs. Function returns
   * false if no private IPs found.
   *
   * @param array $network_interfaces
   *   Array of network interfaces from the EC2 DescribeInstance API.
   *
   * @return string|false
   *   Imploded string or FALSE if no private IPs found.
   */
  public function getPrivateIps(array $network_interfaces);

  /**
   * Setup the ip_permission field given the inbound security group array.
   *
   * The array comes from DescribeSecurityGroup Amazon EC2 API call.
   *
   * @param array $ec2_permission
   *   An array object of EC2permission.
   *
   * @return array
   *   An array of \Drupal\Core\Field\FieldItemInterface.
   */
  public function setupIpPermissionObject(array $ec2_permission);

  /**
   * Setup IP Permissions.
   *
   * @param object $security_group
   *   The security group entity.
   * @param string $field
   *   Field to used for lookup.
   * @param array $ec2_permissions
   *   Permissions array from Ec2.
   */
  public function setupIpPermissions(&$security_group, $field, array $ec2_permissions);

  /**
   * Calculate the cost of a instance.
   *
   * @param array $instance
   *   The instance.
   * @param array $instance_types
   *   All instance types.
   *
   * @return float
   *   Cost of the instance.
   */
  public function calculateInstanceCost(array $instance, array $instance_types);

  /**
   * Clear plugin cache.
   */
  public function clearPluginCache();

  /**
   * Ping the metadata server for security credentials URL.
   *
   * @return bool
   *   TRUE if either 169.254.169.254 (EC2) or 169.254.170.2 (ECS) is
   *   accessible.
   */
  public function pingMetadataSecurityServer(): bool;

  /**
   * Terminate expired instances.
   */
  public function terminateExpiredInstances();

  /**
   * Update pending images.
   */
  public function updatePendingImages();

  /**
   * Create queue items for update resources queue.
   */
  public function createResourceQueueItems();

}
