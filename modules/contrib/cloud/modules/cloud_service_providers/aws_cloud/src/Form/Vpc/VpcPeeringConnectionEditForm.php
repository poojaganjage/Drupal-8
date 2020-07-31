<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Form\Ec2\AwsCloudContentForm;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the VPC Peering Connection entity edit forms.
 *
 * @ingroup aws_cloud
 */
class VpcPeeringConnectionEditForm extends AwsCloudContentForm {

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

    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    $weight = -50;

    $form['vpc_peering_connection'] = [
      '#type'          => 'details',
      '#title'         => $this->t('VPC Peering Connection'),
      '#open'          => TRUE,
      '#weight'        => $weight++,
    ];

    $form['vpc_peering_connection']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => TRUE,
      '#weight'        => $weight++,
    ];

    $form['vpc_peering_connection']['vpc_peering_connection_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('VPC Peering Connection ID')),
      '#markup'        => $entity->getVpcPeeringConnectionId(),
    ];

    $form['vpc_peering_connection']['status_code'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Status Code')),
      '#markup'        => $entity->getStatusCode(),
    ];

    $form['vpc_peering_connection']['status_message'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Status Message')),
      '#markup'        => $entity->getStatusMessage(),
    ];

    $form['vpc_peering_connection']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    $form['requester'] = [
      '#type'          => 'details',
      '#title'         => $this->t('Requester'),
      '#open'          => TRUE,
      '#weight'        => $weight++,
    ];

    $form['requester']['requester_vpc_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Requester VPC ID')),
      '#markup'        => $entity->getRequesterVpcId(),
    ];

    $form['requester']['requester_cidr_block'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Requester CIDR Block')),
      '#markup'        => $entity->getRequesterCidrBlock(),
    ];

    $form['requester']['requester_account_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Requester AWS Account ID')),
      '#markup'        => $entity->getRequesterAccountId(),
    ];

    $form['requester']['requester_region'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Requester Region')),
      '#markup'        => $entity->getRequesterRegion(),
    ];

    $form['accepter'] = [
      '#type'          => 'details',
      '#title'         => $this->t('Accepter'),
      '#open'          => TRUE,
      '#weight'        => $weight++,
    ];

    $form['accepter']['accepter_vpc_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Accepter VPC ID')),
      '#markup'        => $entity->getAccepterVpcId(),
    ];

    $form['accepter']['accepter_cidr_block'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Accepter CIDR Block')),
      '#markup'        => $entity->getAccepterCidrBlock(),
    ];

    $form['accepter']['accepter_account_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Accepter AWS Account ID')),
      '#markup'        => $entity->getAccepterAccountId(),
    ];

    $form['accepter']['accepter_region'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Accepter Region')),
      '#markup'        => $entity->getAccepterRegion(),
    ];

    $form['fieldset_tags'] = [
      '#type'          => 'details',
      '#title'         => $this->t('Tags'),
      '#open'          => TRUE,
      '#weight'        => $weight++,
    ];

    $form['fieldset_tags'][] = $form['tags'];
    unset($form['tags']);

    $this->addOthersFieldset($form, $weight++, $cloud_context);

    $form['actions'] = $this->actions($form, $form_state, $cloud_context);
    $form['actions']['#weight'] = $weight++;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Call copyFormItemValues() to ensure the form array is intact.
    $this->copyFormItemValues($form);

    $this->trimTextfields($form, $form_state);

    $entity = $this->entity;
    $old_vpc_peering_connection = $this->entityTypeManager
      ->getStorage('aws_cloud_vpc_peering_connection')
      ->load($entity->id());

    $this->ec2Service->setCloudContext($entity->getCloudContext());
    if (!empty($old_vpc_peering_connection) && $entity->save()) {

      // Update tags.
      $tag_map = [];
      foreach ($entity->getTags() as $tag) {
        $tag_map[$tag['item_key']] = $tag['item_value'];
      }

      $tag_map['Name'] = $entity->getName();

      $this->setTagsInAws($entity->getVpcPeeringConnectionId(), $tag_map);

      // Update the vpc peering connection.
      $this->ec2Service->updateVpcPeeringConnections([
        'VpcPeeringConnectionId' => $entity->getVpcPeeringConnectionId(),
      ], FALSE);

      $this->processOperationStatus($entity, 'updated');
      $this->clearCacheValues();
    }
    else {
      $this->messenger->addError($this->t('Unable to update @label.', [
        '@label' => $entity->getEntityType()->getSingularLabel(),
      ]));
    }

    $form_state->setRedirect('view.aws_cloud_vpc_peering_connection.list', [
      'cloud_context' => $entity->getCloudContext(),
    ]);
  }

}
