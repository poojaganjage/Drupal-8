USAGE INSTRUCTIONS
==================

The following Cloud Formation templates are provided.

- cloud_orchestrator_full.yaml - Builds a fully distributed Cloud Orchestrator
distribution.  It uses RDS/Mysql for database, ElastiCache for memcache and EC2
with LAMP stack to run Cloud Orchestrator.
- cloud_orchestrator_full_manual_vpc.yaml - Builds a fully distributed Cloud
Orchestrator distribution.  It uses RDS/Mysql for database, ElastiCache for
memcache and EC2 with LAMP stack to run Cloud Orchestrator. This template lets
a user choose a VPC and Subnet for the EC2 instance.
- cloud_orchestrator_single.yaml - Builds a Cloud Orchestrator distribution
fully contained in a single EC2 instance.  This template lets a user choose a
VPC and Subnet for the EC2 instance.
- cloud_orchestrator_single_manual_vpc.yaml - Builds a Cloud Orchestrator
distribution fully contained in a single EC2 instance.
- cloud_orchestrator_docker.yaml - Build a Cloud Orchestrator distribution
running individual Docker containers in a single EC2 instance.
- cloud_orchestrator_docker_manual_vpc.yaml - Builds a Cloud Orchestrator
distribution running individual Docker containers in a single EC2 instance.
This template lets a user choose a VPC and Subnet for the EC2 instance.
cloud_orchestrator_docker_rds_manual_vpc.yaml - Builds a Cloud Orchestrator
distribution running individual Docker containers in a single EC2 instance. The
database is running on RDS.  This template lets a user choose a VPC and Subnet
for the EC2 instance.

The packaged version of these template are hosted in S3.
https://cloud-orchestrator.s3.amazonaws.com/cfn/cloud_orchestrator_full.yaml
https://cloud-orchestrator.s3.amazonaws.com/cfn/cloud_orchestrator_full_manual_vpc.yaml
https://cloud-orchestrator.s3.amazonaws.com/cfn/cloud_orchestrator_single.yaml
https://cloud-orchestrator.s3.amazonaws.com/cfn/cloud_orchestrator_single_manual_vpc.yaml
https://cloud-orchestrator.s3.amazonaws.com/cfn/cloud_orchestrator_docker.yml
https://cloud-orchestrator.s3.amazonaws.com/cfn/cloud_orchestrator_docker_manual_vpc.yaml
https://cloud-orchestrator.s3.amazonaws.com/cfn/cloud_orchestrator_docker_rds_manual_vpc.yaml

To package the templates for your own usage, follow these steps:

1.  Prep and upload the nested templates.  The command also replaces the
TemplateURLs with publicly accessible S3 urls.
 `aws cloudformation package --template-file <TEMPLATE_NAME.yaml>
 --output-template <output_template_name.yaml> --s3-bucket
 <Accessible S3 Bucket>`
2.  Log into AWS console and navigate to CloudFormation.  Make sure your account
has permissions for IAM, VPC and Networking, Security Groups, RDS, ElastiCache
and EC2.
3.  Use the `<output_template_name.yaml>` to launch the Cloud Orchestrator
stack.
4.  Wait for the stack to launch.  Then follow the instructions under
`Outputs > Instructions`.
