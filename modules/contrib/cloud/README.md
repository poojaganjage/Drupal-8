INTRODUCTION
============
Cloud is a set of modules to realize Cloud management: Drupal-based Cloud
dashboard such as Amazon Management Console, RightScale, ElasticFox, etc.
The module aims to support not only public Cloud like Amazon EC2 but also
private Cloud like Kubernetes, VMware or OpenStack since the system is highly
modularized by Drupal architecture.

REQUIREMENTS
============
- `PHP 7.3` or Higher (`128 MB` Memory or higher)

- `MySQL 5.7` or higher _OR_
- `MariaDB 10.3` or higher

- `Drupal 8.x` (Latest version of Drupal 8)
- `Cloud 8.x-2.x`
  - This branch includes support for `aws_cloud`, `openstack`, `k8s`.
  - Future support includes `Terraform Cloud`, VMware`, GCP` and `Azure`.

Limitations
===========
- The aws_cloud module does **not** support *Classic EC2 instances*
  (`Non-VPC`).

  **Note:** Classic instances (`Non-VPC`) are available for AWS accounts
    created before *2013-12-03*.
  `aws_cloud` module is only tested for `EC2-VPC` instances.

  See also:
  - [Default VPC and Default Subnets](
      https://docs.aws.amazon.com/vpc/latest/userguide/default-vpc.html
    )
  - [Discussion Forums: Launch a NON-VPC EC2 instance?](
      https://forums.aws.amazon.com/thread.jspa?threadID=182773
    )

INSTALLATION
=============
1. Download `aws-sdk` from:
     https://docs.aws.amazon.com/aws-sdk-php/v3/download/aws.zip
   and unzip it into the `vendor` directory.
2. Download `cloud` module.
3. Enable the `aws_cloud module`.  This will also enable the required modules.

   _OR_ (using `composer`)

- `composer require drupal/cloud`

CONFIGURATION
=============

Basic Setup (AWS)
-----------------
1. Create a new `cloud service provider` based on your needs.
   Go to `Structure` > `Cloud service providers` and
   `+ Add cloud service provider`
2. Enter all required configuration parameters.  The system will automatically
   setup all regions from your AWS account.  There are three options for
   specifying AWS credentials:

   a. Instance credentials - If cloud module is running on an EC2 instance and
   the EC2 instance has an IAM role attached, you have the option to check "Use
   Instance Credentials".  Doing so is secure and does not require `Access Key
   ID` and `Secret Access Key` to be entered into Drupal.
   Please refer to this AWS tutorial about IAM role and EC2 Instance:

   https://aws.amazon.com/blogs/security/easily-replace-or-attach-an-iam-role-to-an-existing-ec2-instance-by-using-the-ec2-console/

   b. Simple access - Specify `Access Key ID` and `Secret Access Key` to access
      a particular account's EC2 instances.

   c. Assume role - Specify `Access Key ID`, `Secret Access Key` and the
      `Assume Role` section.  With this combination, the cloud module can
      assume the role of another AWS account and access their EC2 instances.
      To learn more about setting up assume role setup, please read this AWS
      tutorial:

      https://docs.aws.amazon.com/IAM/latest/UserGuide/id_roles_use_permissions-to-switch.html

3. Run cron to update your specific Cloud region.
4. Use the links under `Cloud service providers` > `[Cloud service provider]`
   to manage your Amazon EC2 entities.
5. Import Images using the tab:
   `Cloud service providers` > `[Cloud service provider]` | `Images`
   - Click on `+ Import AWS Cloud Image`
   - Search for images by AMI name.  For example, to import `Anaconda` images
   based on Ubuntu, type in `anaconda*ubuntu*`.
   Use the AWS Console on `aws.amazon.com` to search for images to import
6. `Import` or `Add AWS Cloud Key Pair`.  The key pair is used to log into any
   system you launch.  Use the links under the tab:
   `Cloud service providers` > `[Cloud service provider]` | `Key Pair`
   - Use the `+ Import AWS Cloud Key Pair` button to import an existing key
     pair.  You will be uploading your public key.
   - Use `+ Add AWS Cloud Key Pair` to have AWS generate a new private key.
     You will be prompted to download the key after it is created.
7. Setup `Security groups`, `Network Interfaces` as needed from AWS Management
   Console.

Launching Instance
------------------
1. Create a launch template under
   `Design` > `Launch template` > `[Cloud service provider]`
2. Once template is created, click the `Launch` tab to launch it.

Permissions
===========
- Configure permissions per your requirements.
  - [The detail about permissions is here.](
     https://www.drupal.org/docs/8/modules/cloud/configuration)

Directory Structure
===================
```
cloud (Cloud is a core module for Cloud package)
└── modules
    └── cloud_budget
    └── cloud_service_providers
        └── aws_cloud
        └── k8s
        └── docker
        └── openstack
        └── terraform
        └── vmware
    └── gapps
    └── tools
        └── k8s_to_s3
        └── s3_to_k8s
```
Known Issues
============

   When adding a Metrics Server enabled Kubernetes cluster, the metrics
   importing operation can potentially take a long time to complete.
   During this process,  there might be database corruption if the aws_cloud
   module is enabled.

   As a workaround, enable aws_cloud when the server is idle and not processing
   a `Add Kubernetes Cloud Service Provider` operation.

Active Maintainers
==================

- `yas` (https://drupal.org/u/yas)
- `baldwinlouie` (https://www.drupal.org/u/baldwinlouie)
- `jigish.addweb` (https://www.drupal.org/u/jigishaddweb)
- `Masami` (https://www.drupal.org/u/Masami)
- `MasatoTakada` (https://www.drupal.org/u/masatotakada)
- `Xiaohua Guan` (https://www.drupal.org/u/xiaohua-guan)
