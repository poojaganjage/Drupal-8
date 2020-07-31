<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;
use Drupal\aws_cloud\Entity\Ec2\Image;

/**
 * Form controller for the CloudScripting entity edit forms.
 *
 * @ingroup aws_cloud
 */
class ImageEditForm extends AwsCloudContentForm {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::buildForm().
   *
   * @param array $form
   *   Array of form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param string $cloud_context
   *   A cloud_context string value from URL "path".
   *
   * @return array
   *   Array of form object.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Image */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $this->cloudConfigPluginManager->setCloudContext($entity->getCloudContext());
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();

    $weight = -50;

    $form['image'] = [
      '#type' => 'details',
      '#title' => $this->t('Image'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['image']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->getName(),
      '#required'      => TRUE,
    ];

    // Make disabled when OwnerId isn't same as Account ID of Cloud Config.
    $form['image']['description'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Description'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->getDescription(),
      '#required'      => TRUE,
      '#disabled' => $cloud_config->get('field_account_id')->value !== $entity->getAccountId(),
    ];

    $form['image']['ami_name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('AMI Name')),
      '#markup'        => $entity->getAmiName(),
    ];

    $form['image']['image_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Image ID')),
      '#markup'        => $entity->getImageId(),
    ];

    $form['image']['account_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Owner')),
      '#markup'        => $entity->getAccountId(),
    ];

    $form['image']['source'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Source')),
      '#markup'        => $entity->getSource(),
    ];

    $form['image']['status'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Status')),
      '#markup'        => $entity->getStatus(),
    ];

    $form['image']['state_reason'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('State Reason')),
      '#markup'        => $entity->getStateReason(),
    ];

    $form['image']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    $form['launch_permission'] = [
      '#type' => 'details',
      '#title' => $this->getItemTitle($this->t('Launch Permission')),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['launch_permission']['visibility_title'] = [
      '#type'          => 'markup',
      '#markup'        => '<b>Visibility: </b>',
      '#prefix'        => '<div class="container-inline">',
    ];

    // Make disabled when OwnerId isn't same as Account ID of Cloud Config.
    $form['launch_permission']['visibility'] = [
      '#type' => 'radios',
      '#default_value' => $entity->getVisibility(),
      '#options' => [
        '0' => $this->t('Private'),
        '1' => $this->t('Public'),
      ],
      '#suffix' => '</div>',
      '#disabled' => $cloud_config->get('field_account_id')->value !== $entity->getAccountId(),
    ];

    $form['launch_permission']['launch_permission_account_ids_container'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          'input[name="visibility"]' => ['value' => 0],
        ],
      ],
    ];

    $form['launch_permission']['launch_permission_account_ids_container']['launch_permission_account_ids'] = $form['launch_permission_account_ids'];
    unset($form['launch_permission_account_ids']);

    $form['type'] = [
      '#type' => 'details',
      '#title' => $this->getItemTitle($this->t('Type')),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['type']['platform'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Platform')),
      '#markup'        => $entity->getPlatform(),
    ];

    $form['type']['architecture'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Architecture')),
      '#markup'        => $entity->getArchitecture(),
    ];

    $form['type']['virtualization_type'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Virtualization Type')),
      '#markup'        => $entity->getVirtualizationType(),
    ];

    $form['type']['product_code'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Product Code')),
      '#markup'        => $entity->getProductCode(),
    ];

    $form['type']['image_type'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Image Type')),
      '#markup'        => $entity->getImageType(),
    ];

    $form['device'] = [
      '#type' => 'details',
      '#title' => $this->getItemTitle($this->t('Device')),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['device']['root_device_name'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Root Device Name')),
      '#markup'        => $entity->getRootDeviceName(),
    ];

    $form['device']['root_device_type'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Root Device Type')),
      '#markup'        => $entity->getRootDeviceType(),
    ];

    $form['device']['kernel_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Kernel ID')),
      '#markup'        => $entity->getKernelId(),
    ];

    $form['device']['ramdisk_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Ramdisk ID')),
      '#markup'        => $entity->getRamdiskId(),
    ];

    // Just display the device block mappings in the form.  There is no
    // edit functionality.
    $viewBuilder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    $output = $viewBuilder->viewField($entity->getBlockDeviceMappings(), 'full');
    $form['device']['device_block_mappings'] = [
      '#type' => 'item',
      '#not_field' => TRUE,
      '#markup' => $this->renderer->render($output),
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    // Customize others fieldset.
    $old_others = $form['others'];
    unset($form['others']['langcode']);
    unset($form['others']['uid']);

    $form['others']['langcode'] = $old_others['langcode'];
    $form['others']['uid'] = $old_others['uid'];

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);
    $form['actions']['#weight'] = $weight++;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $original_entity = $this->entityTypeManager
      ->getStorage($entity->getEntityTypeId())
      ->load($entity->id());

    parent::save($form, $form_state);

    $this->setTagsInAws($entity->getImageId(), [
      $entity->getEntityTypeId() . '_' . Image::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
      'Name' => $entity->getName(),
    ]);

    $this->cloudConfigPluginManager->setCloudContext($entity->getCloudContext());
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();

    // Call 'modifyImageAttribute'
    // only when OwnerId is same as Account ID of Cloud Config.
    // It's to avoid AuthFailure error from AWS API.
    if ($cloud_config->get('field_account_id')->value !== $entity->getAccountId()) {
      return;
    }
    $form_values = $form_state->getValues();
    $visibility = $form_values['visibility'] ?? 0;

    $permission = [
      [
        'Group' => 'all',
        'UserId' => $entity->getAccountId(),
      ],
    ];

    // 1 => Public, 0 => Private.
    $launch_permission =
      $visibility
        ? ['Add' => $permission]
        : ['Remove' => $permission];

    // Update image.
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    // Update image description.
    $this->ec2Service->modifyImageAttribute([
      'ImageId' => $entity->getImageId(),
      'Description' => ['Value' => $entity->getDescription()],
    ]);

    // These fields (Description , LaunchPermission , or ProductCode) cannot
    // be specified at the same time.
    // See also:
    // https://docs.aws.amazon.com/cli/latest/reference/ec2/modify-image-attribute.html
    // Update image launch permission.
    $this->ec2Service->modifyImageAttribute([
      'ImageId' => $entity->getImageId(),
      'LaunchPermission' => $launch_permission,
    ]);

    $new_account_ids = [];
    foreach ($entity->getLaunchPermissionAccountIds() as $account_id) {
      if (empty($account_id->value)) {
        continue;
      }
      $new_account_ids[] = $account_id->value;
    }

    $old_account_ids = [];
    foreach ($original_entity->getLaunchPermissionAccountIds() as $account_id) {
      if (empty($account_id->value)) {
        continue;
      }
      $old_account_ids[] = $account_id->value;
    }

    $account_ids_to_remove = array_diff($old_account_ids, $new_account_ids);
    $account_ids_to_add = array_diff($new_account_ids, $old_account_ids);

    if (!empty($account_ids_to_remove)) {
      $this->ec2Service->modifyImageAttribute([
        'ImageId' => $entity->getImageId(),
        'LaunchPermission' => [
          'Remove' => array_map(function ($item) {
            return ['UserId' => $item];
          }, $account_ids_to_remove),
        ],
      ]);
    }

    if (!empty($account_ids_to_add)) {
      $this->ec2Service->modifyImageAttribute([
        'ImageId' => $entity->getImageId(),
        'LaunchPermission' => [
          'Add' => array_map(function ($item) {
            return ['UserId' => $item];
          }, $account_ids_to_add),
        ],
      ]);
    }

    $this->clearCacheValues();
  }

}
