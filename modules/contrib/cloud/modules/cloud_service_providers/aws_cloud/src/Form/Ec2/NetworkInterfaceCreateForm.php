<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\aws_cloud\Entity\Ec2\NetworkInterface;

/**
 * Form controller for the NetworkInterface entity create form.
 *
 * @ingroup aws_cloud
 */
class NetworkInterfaceCreateForm extends AwsCloudContentForm {

  use CloudContentEntityTrait;

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
    $form = parent::buildForm($form, $form_state);

    $this->ec2Service->setCloudContext($cloud_context);

    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\NetworkInterface */
    $entity = $this->entity;

    $weight = -50;

    $form['network_interface'] = [
      '#type' => 'details',
      '#title' => $entity->getEntityType()->getSingularLabel(),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['network_interface']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => TRUE,
    ];

    $form['network_interface']['description'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Description'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->getDescription(),
      '#required'      => FALSE,
    ];

    $method = "{$this->getModuleName($entity)}_get_subnet_options_by_vpc_id";
    $form['network_interface']['subnet_id'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Subnet'),
      '#options'       => $method(NULL, $entity),
      '#ajax' => [
        'callback' => '::subnetAjaxCallback',
        'wrapper'  => 'security-groups-wrapper',
      ],
      '#required'      => TRUE,
    ];

    $form['network_interface']['security_groups_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'security-groups-wrapper',
      ],
    ];

    $form['network_interface']['security_groups_wrapper']['security_groups'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Security Groups'),
      '#size'          => 5,
      '#multiple'      => TRUE,
      '#options'       => $this->getSecurityGroupOptions($cloud_context),
      '#required'      => TRUE,
    ];

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    return $form;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * @param array $form
   *   Array of form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;
    $security_group_ids = array_values($form_state->getValue('security_groups'));
    $entity->setSecurityGroups(implode(',', $security_group_ids));

    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $this->ec2Service->setCloudContext($cloud_context);
    $result = $this->ec2Service->createNetworkInterface([
      'Description' => $entity->getDescription(),
      'SubnetId' => $entity->getSubnetId(),
      'Groups' => $security_group_ids,
    ]);

    if (isset($result['NetworkInterface'])
    && ($entity->setNetworkInterfaceId($result['NetworkInterface']['NetworkInterfaceId']))
    && ($entity->save())) {
      $this->setTagsInAws($entity->getNetworkInterfaceId(), [
        $entity->getEntityTypeId() . '_' . NetworkInterface::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
        'Name' => $entity->getName(),
      ]);

      // Update the vpc.
      $this->ec2Service->updateNetworkInterfaces([
        'NetworkInterfaceId' => $entity->getNetworkInterfaceId(),
      ]);

      $this->processOperationStatus($entity, 'created');

      $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
      $this->clearCacheValues();
    }
    else {

      $this->processOperationErrorStatus($entity, 'created');
    }
  }

  /**
   * Ajax callback for select form item subnet.
   *
   * @param array &$form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   Response.
   */
  public function subnetAjaxCallback(array &$form, FormStateInterface $form_state) {
    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    if ($subnet_id = $form_state->getValue('subnet_id')) {
      $form['network_interface']['security_groups_wrapper']['security_groups']['#options']
        = $this->getSecurityGroupOptions($cloud_context, $subnet_id);
    }
    else {
      $form['network_interface']['security_groups_wrapper']['security_groups']['#options'] = [];
    }

    return $form['network_interface']['security_groups_wrapper'];
  }

  /**
   * Get select options of security groups.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param string $subnet_id
   *   The subnet ID.
   *
   * @return array
   *   The options of security groups.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   */
  private function getSecurityGroupOptions($cloud_context, $subnet_id = NULL) {
    $vpc_id = NULL;
    if (!empty($subnet_id)) {
      $result = $this->ec2Service->describeSubnets([
        'SubnetIds' => [$subnet_id],
      ]);
      $vpc_id = $result['Subnets'][0]['VpcId'];
    }

    $entity = $this->entity;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $entity_storage = \Drupal::entityTypeManager()
      ->getStorage("{$module_name}_security_group");

    if ($vpc_id !== NULL) {
      $entity_ids = $entity_storage
        ->getQuery()
        ->condition('vpc_id', $vpc_id)
        ->condition('cloud_context', $cloud_context)
        ->execute();
    }
    else {
      $entity_ids = $entity_storage
        ->getQuery()
        ->condition('cloud_context', $cloud_context)
        ->execute();
    }

    $options = [];
    $security_groups = $entity_storage->loadMultiple($entity_ids);
    foreach ($security_groups ?: [] as $security_group) {
      $options[$security_group->getGroupId()] = $security_group->getGroupName();
    }
    return $options;
  }

}
