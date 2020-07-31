<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Entity\Vpc\VpcPeeringConnection;
use Drupal\aws_cloud\Form\Ec2\AwsCloudContentForm;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the VPC Peering Connection entity create form.
 *
 * @ingroup aws_cloud
 */
class VpcPeeringConnectionCreateForm extends AwsCloudContentForm {

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
    /* @var $entity \Drupal\aws_cloud\Entity\Ec2\Snapshot */
    $form = parent::buildForm($form, $form_state);
    $this->ec2Service->setCloudContext($cloud_context);

    $this->cloudConfigPluginManager->setCloudContext($cloud_context);
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();

    $weight = -50;

    $form['vpc_peering_connection'] = [
      '#type' => 'details',
      '#title' => $this->t('VPC Peering Connection'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['vpc_peering_connection']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#required'      => TRUE,
    ];

    $vpcs = $this->ec2Service->getVpcs();
    ksort($vpcs);
    $form['vpc_peering_connection']['requester_vpc_id'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Requester VPC ID'),
      '#description'   => $this->t('The Requester VPC ID.'),
      '#options'       => $vpcs,
      '#required'      => TRUE,
    ];

    $form['vpc_peering_connection']['accepter_account_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Accepter AWS Account ID'),
      '#description'   => $this->t('The Accepter AWS Account ID.'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $cloud_config->field_account_id->value,
      '#required'      => FALSE,
      '#ajax' => [
        'event'    => 'change',
        'callback' => '::accepterVpcIdSelectAjaxCallback',
        'wrapper'  => 'accepter-vpc-id-wrapper',
      ],
    ];

    $form['vpc_peering_connection']['accepter_region'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Accepter Region'),
      '#description'   => $this->t('The Accepter Region.'),
      '#options'       => $this->ec2Service->getRegions(),
      '#default_value' => $cloud_config->field_region->value,
      '#required'      => FALSE,
      '#ajax' => [
        'callback' => '::accepterVpcIdSelectAjaxCallback',
        'wrapper'  => 'accepter-vpc-id-wrapper',
      ],
    ];

    $form['vpc_peering_connection']['accepter_vpc_id_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'accepter-vpc-id-wrapper',
      ],
    ];

    $accepter_region = $cloud_config->field_region->value;
    if (!empty($form_state->getValue('accepter_region'))) {
      $accepter_region = $form_state->getValue('accepter_region');
    }
    $accepter_account_id = $cloud_config->field_account_id->value;
    if (!empty($form_state->getValue('accepter_account_id'))) {
      $accepter_account_id = $form_state->getValue('accepter_account_id');
    }
    if ($cloud_config->field_region->value === $accepter_region
      && $cloud_config->field_account_id->value === $accepter_account_id) {
      $form['vpc_peering_connection']['accepter_vpc_id_wrapper']['accepter_vpc_id'] = [
        '#type'          => 'select',
        '#title'         => $this->t('Accepter VPC ID'),
        '#description'   => $this->t('The Accepter VPC ID.'),
        '#options'       => $vpcs,
        '#name'          => 'accepter_vpc_id',
        '#required'      => TRUE,
      ];
    }
    else {
      $form['vpc_peering_connection']['accepter_vpc_id_wrapper']['accepter_vpc_id'] = [
        '#type'          => 'textfield',
        '#title'         => $this->t('Accepter VPC ID'),
        '#description'   => $this->t('The Accepter VPC ID.'),
        '#maxlength'     => 255,
        '#size'          => 60,
        '#name'          => 'accepter_vpc_id',
        '#required'      => TRUE,
      ];
    }

    unset($form['tags']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);

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
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\aws_cloud\Service\Ec2\Ec2ServiceException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->trimTextfields($form, $form_state);

    $cloud_context = $this->routeMatch->getParameter('cloud_context');

    $entity = $this->entity;
    $entity->setCloudContext($cloud_context);

    $params = [];
    $params['VpcId'] = $form_state->getValue('requester_vpc_id');
    $params['PeerVpcId'] = $form_state->getValue('accepter_vpc_id');

    if (!empty($form_state->getValue('accepter_account_id'))) {
      $params['PeerOwnId'] = $form_state->getValue('accepter_account_id');
    }

    if (!empty($form_state->getValue('accepter_region'))) {
      $params['PeerRegion'] = $form_state->getValue('accepter_region');
    }

    $this->ec2Service->setCloudContext($cloud_context);
    $result = $this->ec2Service->createVpcPeeringConnection($params);
    if (isset($result['VpcPeeringConnection'])) {
      $storage = $this->entityTypeManager->getStorage('aws_cloud_vpc_peering_connection');
      $vpc_peering_connection_id = $result['VpcPeeringConnection']['VpcPeeringConnectionId'];

      // Check if the VPC Peering Connection exists.
      $entities = $storage->loadByProperties([
        'cloud_context' => $cloud_context,
        'vpc_peering_connection_id' => $vpc_peering_connection_id,
      ]);

      if (empty($entities)) {
        $entity->setVpcPeeringConnectionId($result['VpcPeeringConnection']['VpcPeeringConnectionId']);
        $entity->save();
        $this->setTagsInAws($entity->getVpcPeeringConnectionId(), [
          VpcPeeringConnection::TAG_CREATED_BY_UID => $entity->getOwner()->id(),
          'Name' => $entity->getName(),
        ]);

        // Update the vpc peering connection.
        $this->ec2Service->updateVpcPeeringConnections([
          'VpcPeeringConnectionIds' => [$entity->getVpcPeeringConnectionId()],
        ], FALSE);

        $this->processOperationStatus($entity, 'created');
        $this->clearCacheValues();

        $form_state->setRedirect('view.aws_cloud_vpc_peering_connection.list', ['cloud_context' => $entity->getCloudContext()]);
      }
      else {
        $message = $this->t('The @type %vpc_peering_connection_id already exists.', [
          '@type' => $entity->getEntityType()->getSingularLabel(),
          '%vpc_peering_connection_id' => $vpc_peering_connection_id,
        ]);
        $this->messenger->addError($message);

        $this->processOperationErrorStatus($entity, 'created');
      }
    }
    else {

      $this->processOperationErrorStatus($entity, 'created');
    }
  }

  /**
   * Ajax callback for select form accepter vpc id.
   *
   * @param array &$form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   Response.
   */
  public function accepterVpcIdSelectAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['vpc_peering_connection']['accepter_vpc_id_wrapper'];
  }

}
