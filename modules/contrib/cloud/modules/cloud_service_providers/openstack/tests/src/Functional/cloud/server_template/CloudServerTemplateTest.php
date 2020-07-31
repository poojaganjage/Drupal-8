<?php

namespace Drupal\Tests\openstack\Functional\cloud\server_template;

use Drupal\openstack\Entity\OpenStackImage;
use Drupal\openstack\Entity\OpenStackKeyPair;
use Drupal\openstack\Entity\OpenStackSecurityGroup;
use Drupal\Tests\openstack\Functional\OpenStackTestBase;

/**
 * Tests cloud server templates (OpenStackCloudServerTemplate).
 *
 * @group Cloud
 */
class CloudServerTemplateTest extends OpenStackTestBase {

  public const CLOUD_SERVER_TEMPLATES_REPEAT_COUNT = 2;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'cloud',
    'openstack',
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

      'add openstack image',
      'list openstack images',
      'view any openstack image',
      'edit any openstack image',
      'delete any openstack image',
    ];
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

    $image = $this->createImageTestEntity(OpenStackImage::class, 0, $image_id = '', $cloud_context);
    $image_id = $image->getImageId();

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/openstack/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());

    $this->createSecurityGroupTestEntity(OpenStackSecurityGroup::class, 0, '', '', '', $this->cloudContext);
    $this->createKeyPairTestEntity(OpenStackKeyPair::class, 0, '', '', $this->cloudContext);

    // List cloud server template for OpenStack.
    $this->drupalGet("/clouds/design/server_template/$cloud_context");
    $this->assertNoErrorMessage();

    $vpcs = $this->createVpcsRandomTestFormData();
    $subnets = $this->createSubnetsRandomTestFormData();
    $this->updateVpcsAndSubnetsMockData($vpcs, $subnets);

    // Add a new server_template information.
    $add = $this->createOpenStackServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {

      $add[$i]['field_openstack_image_id'] = $image_id;

      $vpc_index = array_rand($vpcs);
      $add[$i]['field_openstack_vpc'] = $vpcs[$vpc_index]['VpcId'];
      $vpc_name = $this->getNameFromArray($vpcs, $vpc_index, 'VpcId');

      $subnet_index = array_rand($subnets);
      $add[$i]['field_openstack_subnet'] = $subnets[$subnet_index]['SubnetId'];
      $subnet_name = $this->getNameFromArray($subnets, $subnet_index, 'SubnetId');

      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/openstack/add",
                            $add[$i],
                            $this->t('Save'));
      $this->assertNoErrorMessage();
      $t_args = ['@type' => 'launch template', '%label' => $add[$i]['name[0][value]']];
      $this->assertSession()->pageTextContains(strip_tags($this->t('The @type %label has been created.', $t_args)));

      $this->assertSession()->pageTextContains($add[$i]['name[0][value]']);
      $this->assertSession()->pageTextContains($vpc_name);
      $this->assertSession()->pageTextContains($subnet_name);
      $this->assertSession()->pageTextContains($add[$i]['field_tags[0][item_key]']);
      $this->assertSession()->pageTextContains($add[$i]['field_tags[0][item_value]']);

      // Make sure listing.
      $this->drupalGet("/clouds/design/server_template/$cloud_context");
      $this->assertNoErrorMessage();
      for ($j = 0; $j < $i + 1; $j++) {
        $this->assertSession()->pageTextContains($add[$j]['name[0][value]']);
      }
    }

    // Click 'Refresh'.
    // @TODO: Need tests for the entities from the mock objects.
    $this->clickLink($this->t('Refresh'));
    $this->assertSession()->pageTextContains($this->t('Unnecessary to update launch templates.'));
    $this->assertNoErrorMessage();

    // Edit case.
    $edit = $this->createOpenStackServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    for ($i = 0, $num = 1; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++, $num++) {

      // Name can't be changed.
      $edit[$i]['name[0][value]'] = $add[$i]['name[0][value]'];

      $edit[$i]['field_openstack_image_id'] = $image_id;

      $vpc_index = array_rand($vpcs);
      $edit[$i]['field_openstack_vpc'] = $vpcs[$vpc_index]['VpcId'];
      $vpc_name = $this->getNameFromArray($vpcs, $vpc_index, 'VpcId');

      $subnet_index = array_rand($subnets);
      $edit[$i]['field_openstack_subnet'] = $subnets[$subnet_index]['SubnetId'];
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

    // Delete server_template Items
    // 3 times.
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
  }

  /**
   * Tests CRUD for server_template revision information.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Exception
   */
  public function testCloudServerTemplateRevision(): void {
    $cloud_context = $this->cloudContext;

    $image = $this->createImageTestEntity(OpenStackImage::class, 0, $image_id = '', $cloud_context);
    $image_id = $image->getImageId();

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/openstack/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());

    $this->createSecurityGroupTestEntity(OpenStackSecurityGroup::class, 0, '', '', '', $cloud_context);
    $this->createKeyPairTestEntity(OpenStackKeyPair::class, 0, '', '', $cloud_context);

    $vpcs = $this->createVpcsRandomTestFormData();
    $subnets = $this->createSubnetsRandomTestFormData();
    $this->updateVpcsAndSubnetsMockData($vpcs, $subnets);

    // Create a cloud server template.
    $add = $this->createOpenStackServerTemplateTestFormData();

    $add[0]['field_openstack_image_id'] = $image_id;

    $vpc_index = array_rand($vpcs);
    $add[0]['field_openstack_vpc'] = $vpcs[$vpc_index]['VpcId'];

    $subnet_index = array_rand($subnets);
    $add[0]['field_openstack_subnet'] = $subnets[$subnet_index]['SubnetId'];

    $this->drupalPostForm("/clouds/design/server_template/$cloud_context/openstack/add",
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

    // View the revision.
    $this->drupalGet("/clouds/design/server_template/$cloud_context/1/revisions/1/view");
    $this->assertNoErrorMessage();

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

    $image = $this->createImageTestEntity(OpenStackImage::class, 0, $image_id = '', $cloud_context);
    $image_id = $image->getImageId();

    // Make sure if the image entity is created or not.
    $this->drupalGet("/clouds/openstack/${cloud_context}/image");
    $this->assertNoErrorMessage();
    $this->assertSession()->pageTextContains($image->getName());
    $this->assertSession()->pageTextContains($image->getImageId());

    $this->createSecurityGroupTestEntity(OpenStackSecurityGroup::class, 0, '', '', '', $cloud_context);
    $this->createKeyPairTestEntity(OpenStackKeyPair::class, 0, '', '', $cloud_context);

    $vpcs = $this->createVpcsRandomTestFormData();
    $subnets = $this->createSubnetsRandomTestFormData();
    $this->updateVpcsAndSubnetsMockData($vpcs, $subnets);

    // Add a new server_template information.
    $add = $this->createOpenStackServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);
    $copy = $this->createOpenStackServerTemplateTestFormData(self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT);

    $num = 1;
    for ($i = 0; $i < self::CLOUD_SERVER_TEMPLATES_REPEAT_COUNT; $i++) {

      $add[$i]['field_openstack_image_id'] = $image_id;

      $vpc_index = array_rand($vpcs);
      $add[$i]['field_openstack_vpc'] = $vpcs[$vpc_index]['VpcId'];

      $subnet_index = array_rand($subnets);
      $add[$i]['field_openstack_subnet'] = $subnets[$subnet_index]['SubnetId'];

      $this->drupalPostForm("/clouds/design/server_template/$cloud_context/openstack/add",
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
      $copy[$i]['field_openstack_image_id'] = $image_id;
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
