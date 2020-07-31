<?php

namespace Drupal\Tests\aws_cloud\Functional\cloud\server_template;

use Drupal\aws_cloud\Entity\Ec2\Image;
use Drupal\aws_cloud\Entity\Ec2\KeyPair;
use Drupal\aws_cloud\Entity\Ec2\SecurityGroup;
use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Tests\aws_cloud\Functional\AwsCloudTestBase;

/**
 * Tests cloud server templates (CloudServerTemplate).
 *
 * @group Cloud
 */
class CloudServerTemplateTest extends AwsCloudTestBase {

  public const CLOUD_SERVER_TEMPLATES_REPEAT_COUNT = 2;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'aws_cloud',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getPermissions(): array {
    return [
      'add cloud server templates',
      'list cloud server template',
      'view any published cloud server templates',
      'view any unpublished cloud server templates',
      'edit any cloud server templates',
      'delete any cloud server templates',
      'access cloud server template revisions',
      'revert all cloud server template revisions',
      'delete all cloud server template revisions',

      'add aws cloud image',
      'list aws cloud image',
      'view any aws cloud image',
      'edit any aws cloud image',
      'delete any aws cloud image',
    ];
  }

  /**
   * Create cloud context.
   *
   * @param string $bundle
   *   The cloud service provide bundle type ('aws_cloud').
   *
   * @return \Drupal\cloud\Entity\CloudConfig
   *   The cloud service provider (CloudConfig) entity.
   *
   * @throws \Exception
   */
  protected function createCloudContext($bundle = 'aws_cloud'): CloudContentEntityBase {
    $cloud_context = parent::createCloudContext($bundle);

    // Get AWS Cloud Instance Type Prices.
    aws_cloud_get_instance_types($this->cloudContext);

    return $cloud_context;
  }

  /**
   * Tests CRUD for server_template information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testCloudServerTemplate(): void {

    $cloud_context = $this->cloudContext;

    $image = $this->createImageTestEntity(Image::class, 0, $image_id = '', $cloud_context);
    $image_id = $image->getImageId();

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());

    $this->createSecurityGroupTestEntity(SecurityGroup::class, 0, '', '', '', $this->cloudContext);
    $this->createKeyPairTestEntity(KeyPair::class, 0, '', '', $this->cloudContext);

    // List cloud server template for AWS.
    $this->drupalGet("/clouds/design/server_template/$cloud_context");
    $this->assertNoErrorMessage();

    $vpcs = $this->createVpcsRandomTestFormData();
    $subnets = $this->createSubnetsRandomTestFormData();
    $this->updateVpcsAndSubnetsMockData($vpcs, $subnets);

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add a new server_template information.
    $add = $this->createServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {

      $add[$i]['field_image_id'] = $image_id;

      $vpc_index = array_rand($vpcs);
      $add[$i]['field_vpc'] = $vpcs[$vpc_index]['VpcId'];
      $vpc_name = $this->getNameFromArray($vpcs, $vpc_index, 'VpcId');

      $subnet_index = array_rand($subnets);
      $add[$i]['field_subnet'] = $subnets[$subnet_index]['SubnetId'];
      $subnet_name = $this->getNameFromArray($subnets, $subnet_index, 'SubnetId');

      $iam_role_index = array_rand($iam_roles);
      $add[$i]['field_iam_role'] = $iam_roles[$iam_role_index]['Arn'];
      $iam_role_name = $iam_roles[$iam_role_index]['InstanceProfileName'];

      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/aws_cloud/add",
        $add[$i],
        $this->t('Save'));
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      $this->assertSession()->pageTextContains($add[$i]['name[0][value]']);
      $this->assertSession()->pageTextContains($vpc_name);
      $this->assertSession()->pageTextContains($subnet_name);
      $this->assertSession()->pageTextContains($iam_role_name);
      $this->assertSession()->pageTextContains($add[$i]['field_tags[0][item_key]']);
      $this->assertSession()->pageTextContains($add[$i]['field_tags[0][item_value]']);

      // Make sure listing.
      $this->drupalGet("/clouds/design/server_template/$cloud_context");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $i + 1; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name[0][value]']);
      }
    }

    // Edit case.
    $edit = $this->createServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++, $num++) {

      // Name can't be changed.
      $edit[$i]['name[0][value]'] = $add[$i]['name[0][value]'];

      $edit[$i]['field_image_id'] = $image_id;

      $vpc_index = array_rand($vpcs);
      $edit[$i]['field_vpc'] = $vpcs[$vpc_index]['VpcId'];
      $vpc_name = $this->getNameFromArray($vpcs, $vpc_index, 'VpcId');

      $subnet_index = array_rand($subnets);
      $edit[$i]['field_subnet'] = $subnets[$subnet_index]['SubnetId'];
      $subnet_name = $this->getNameFromArray($subnets, $subnet_index, 'SubnetId');

      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/$num/edit",
        $edit[$i],
        $this->t('Save'));
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $edit[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

      $this->assertSession()->pageTextContains($edit[$i]['name[0][value]']);
      $this->assertSession()->pageTextContains($vpc_name);
      $this->assertSession()->pageTextContains($subnet_name);

      // Make sure listing.
      $this->drupalGet("/clouds/design/server_template/$cloud_context");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextContains($edit[$j]['name[0][value]']);
      }
    }

    // Delete server_template Items.
    for ($i = 0, $num = 1; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++, $num++) {

      $this->drupalGet("/clouds/design/server_template/$cloud_context/$num/delete");
      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/$num/delete",
        [],
        $this->t('Delete'));
      $this->assertNoErrorMessage();

      // Make sure listing.
      $this->drupalGet("/clouds/design/server_template/$cloud_context");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $num; $j++) {
        $this->assertSession()->pageTextNotContains($edit[$j]['name[0][value]']);
      }
    }

    // Click 'Refresh'.
    // @TODO: Need tests for the entities from the mock objects.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Updated launch templates.'));
    $this->assertNoErrorMessage();
  }

  /**
   * Tests CRUD for server_template revision information.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testCloudServerTemplateRevision(): void {
    $cloud_context = $this->cloudContext;

    $image = $this->createImageTestEntity(Image::class, 0, $image_id = '', $cloud_context);
    $image_id = $image->getImageId();

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());

    $this->createSecurityGroupTestEntity(SecurityGroup::class, 0, '', '', '', $cloud_context);
    $this->createKeyPairTestEntity(KeyPair::class, 0, '', '', $cloud_context);

    $vpcs = $this->createVpcsRandomTestFormData();
    $subnets = $this->createSubnetsRandomTestFormData();
    $this->updateVpcsAndSubnetsMockData($vpcs, $subnets);

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Create a cloud server template.
    $add = $this->createServerTemplateTestFormData();

    $add[0]['field_image_id'] = $image_id;

    $vpc_index = array_rand($vpcs);
    $add[0]['field_vpc'] = $vpcs[$vpc_index]['VpcId'];

    $subnet_index = array_rand($subnets);
    $add[0]['field_subnet'] = $subnets[$subnet_index]['SubnetId'];

    $this->drupalPostForm("/clouds/design/server_template/$cloud_context/aws_cloud/add",
                          $add[0],
                          $this->t('Save'));
    $this->assertNoErrorMessage();
    $t_args = ['@type' => 'launch template', '%label' => $add[0]['name[0][value]']];
    $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

    // Make sure listing revisions.
    $this->drupalGet("/clouds/design/server_template/$cloud_context/1/revisions");
    $this->assertNoErrorMessage();

    // Create a new revision.
    $edit = $add[0];
    $old_description = $edit['field_description[0][value]'];
    $revision_desc = $this->random->name(32, TRUE);
    $edit['field_description[0][value]'] = $revision_desc;
    $this->drupalPostForm("/clouds/design/server_template/$cloud_context/1/edit",
                          $edit,
                          $this->t('Save'));
    $this->assertNoErrorMessage();
    $t_args = ['@type' => 'launch template', '%label' => $edit['name[0][value]']];
    $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been updated.', $t_args)));

    // Make sure listing revisions.
    $this->drupalGet("/clouds/design/server_template/$cloud_context/1/revisions");
    $this->assertNoErrorMessage();
    $this->assertSession()->linkByHrefExists("server_template/$cloud_context/1/revisions/1/view");
    $this->assertSession()->linkByHrefExists("server_template/$cloud_context/1/revisions/1/revert");
    $this->assertSession()->linkByHrefExists("server_template/$cloud_context/1/revisions/1/delete");

    // View the revision.
    $this->drupalGet("/clouds/design/server_template/$cloud_context/1/revisions/1/view");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($old_description);

    // Revert the revision.
    $this->drupalPostForm("/clouds/design/server_template/$cloud_context/1/revisions/1/revert",
                          [],
                          $this->t('Revert'));
    $this->assertNoErrorMessage();
    // Check the string: 'The launch template %title has been reverted to
    // the revision from %revision-date.'.
    $this->assertSession()->pageTextContains($this->t('The launch template @name has been reverted to the revision from', [
      '@name' => $edit['name[0][value]'],
    ]));
    // The new revision is created.
    $this->assertSession()->linkByHrefExists("server_template/$cloud_context/1/revisions/2/view");

    // Delete the revision.
    $this->drupalPostForm("/clouds/design/server_template/$cloud_context/1/revisions/1/delete",
                          [],
                          $this->t('Delete'));
    $this->assertNoErrorMessage();
    // Check the part of the string: 'Revision from %revision-date of launch
    // template @label has been deleted.'.
    $t_args = ['@type' => 'launch template', '@label' => $edit['name[0][value]']];
    $this->assertSession()->pageTextContains(strip_tags($this->t('of @type @label has been deleted.', $t_args)));

    // The revision is deleted.
    $this->assertSession()->linkByHrefNotExists("server_template/$cloud_context/1/revisions/1/view");

    // Test copy function.
    $this->drupalGet("/clouds/design/server_template/{$cloud_context}");
    $this->clickLink($add[0]['name[0][value]']);
    $this->clickLink('Copy');
    $copy_url = $this->getUrl();
    $this->drupalPostForm($copy_url, [], 'Copy');
    $t_args = ['@type' => 'launch template', '%label' => "copy_of_{$add[0]['name[0][value]']}"];
    $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));
    $this->assertSession()->pageTextContains("copy_of_{$add[0]['name[0][value]']}");
  }

  /**
   * Tests CRUD for server_template information.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Exception
   */
  public function testCloudServerTemplateCopy(): void {
    $cloud_context = $this->cloudContext;

    $image = $this->createImageTestEntity(Image::class, 0, $image_id = '', $cloud_context);
    $image_id = $image->getImageId();

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/aws_cloud/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());

    $this->createSecurityGroupTestEntity(SecurityGroup::class, 0, '', '', '', $cloud_context);
    $this->createKeyPairTestEntity(KeyPair::class, 0, '', '', $cloud_context);

    $vpcs = $this->createVpcsRandomTestFormData();
    $subnets = $this->createSubnetsRandomTestFormData();
    $this->updateVpcsAndSubnetsMockData($vpcs, $subnets);

    // IAM Roles.
    $iam_roles = $this->createIamRolesRandomTestFormData();
    $this->updateIamRolesMockData($iam_roles);

    // Add a new server_template information.
    $add = $this->createServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    $copy = $this->createServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);

    $num = 1;
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {

      $add[$i]['field_image_id'] = $image_id;

      $vpc_index = array_rand($vpcs);
      $add[$i]['field_vpc'] = $vpcs[$vpc_index]['VpcId'];

      $subnet_index = array_rand($subnets);
      $add[$i]['field_subnet'] = $subnets[$subnet_index]['SubnetId'];

      $iam_role_index = array_rand($iam_roles);
      $add[$i]['field_iam_role'] = $iam_roles[$iam_role_index]['Arn'];

      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/aws_cloud/add",
        $add[$i],
        $this->t('Save'));
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      // Access copy page.
      $this->drupalGet("/clouds/design/server_template/$cloud_context/$num/copy");
      $this->assertSession()->pageTextContains('Copy launch template');
      $this->assertSession()->fieldValueEquals('name[0][value]', "copy_of_{$add[$i]['name[0][value]']}");

      // Submit copy.
      $copy[$i]['field_image_id'] = $image_id;
      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/$num/copy",
        $copy[$i],
        $this->t('Copy'));
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $copy[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));
      $this->assertSession()->pageTextNotContains($add[$i]['name[0][value]']);

      // Access edit page.
      $num++;
      $this->drupalGet("/clouds/design/server_template/$cloud_context/$num/edit");

      foreach ($copy[$i] ?: [] as $key => $value) {
        if (strpos($key, 'field_tags') === FALSE) {
          $this->assertSession()->fieldValueEquals($key, $value);
        }
      }

      $num++;

    }
  }

}
