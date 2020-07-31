<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Form\Ec2\AwsCloudContentForm;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the VPC entity edit forms.
 *
 * @ingroup aws_cloud
 */
class VpcEditForm extends AwsCloudContentForm {

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

    $form['vpc'] = [
      '#type' => 'details',
      '#title' => $this->t('VPC'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['vpc']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => TRUE,
    ];

    $form['vpc']['vpc_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('VPC ID')),
      '#markup'        => $entity->getVpcId(),
    ];

    $form['vpc']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
    ];

    $form['vpc']['flow_log'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Flow Log'),
      '#description'   => $this->t('Enable create flow log automatically.'),
      '#default_value' => $this->hasFlowLog($entity->getVpcId()),
    ];

    $form['fieldset_tags'] = [
      '#type'          => 'details',
      '#title'         => $this->t('Tags'),
      '#open'          => TRUE,
      '#weight'        => $weight++,
    ];

    $form['fieldset_tags'][] = $form['tags'];
    unset($form['tags']);

    $form['fieldset_cidr_blocks'] = [
      '#type'          => 'details',
      '#title'         => $this->t('CIDR Blocks'),
      '#open'          => TRUE,
      '#weight'        => $weight++,
    ];

    $form['fieldset_cidr_blocks'][] = $form['cidr_blocks'];
    unset($form['cidr_blocks']);

    $form['fieldset_ipv6_cidr_blocks'] = [
      '#type'          => 'details',
      '#title'         => $this->t('IPv6 CIDR Blocks'),
      '#open'          => TRUE,
      '#weight'        => $weight++,
    ];
    $form['fieldset_ipv6_cidr_blocks'][] = $form['ipv6_cidr_blocks'];
    unset($form['ipv6_cidr_blocks']);

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
    $old_vpc = $this->entityTypeManager
      ->getStorage('aws_cloud_vpc')
      ->load($entity->id());

    $this->ec2Service->setCloudContext($entity->getCloudContext());
    if (!empty($old_vpc) && $entity->save()) {

      // Update tags.
      $tag_map = [];
      foreach ($entity->getTags() ?: [] as $tag) {
        $tag_map[$tag['item_key']] = $tag['item_value'];
      }

      $tag_map['Name'] = $entity->getName();

      $this->setTagsInAws($entity->getVpcId(), $tag_map);

      // Update IPv4 CIDRs.
      $original_cidrs = [];
      $original_cidr_association_id_map = [];
      foreach ($old_vpc->getCidrBlocks() ?: [] as $cidr_block) {
        if ($cidr_block) {
          $original_cidrs[] = $cidr_block['cidr'];
          $original_cidr_association_id_map[$cidr_block['cidr']] = $cidr_block['association_id'];
        }
      }

      $new_cidrs = [];
      foreach ($entity->getCidrBlocks() ?: [] as $cidr_block) {
        $new_cidrs[] = $cidr_block['cidr'];
      }

      $cidrs_to_create = array_diff($new_cidrs, $original_cidrs);
      $cidrs_to_delete = array_diff($original_cidrs, $new_cidrs);

      foreach ($cidrs_to_create ?: [] as $cidr) {
        $this->ec2Service->associateVpcCidrBlock([
          'VpcId' => $entity->getVpcId(),
          'CidrBlock' => $cidr,
        ]);
      }

      foreach ($cidrs_to_delete ?: [] as $cidr) {
        $this->ec2Service->disassociateVpcCidrBlock([
          'AssociationId' => $original_cidr_association_id_map[$cidr],
        ]);
      }

      // Update IPv6 CIDRs.
      $original_cidrs = [];
      $original_association_id = NULL;
      foreach ($old_vpc->getIpv6CidrBlocks() ?: [] as $cidr_block) {
        if ($cidr_block) {
          $original_cidrs[] = $cidr_block['cidr'];
          $original_association_id = $cidr_block['association_id'];
        }
      }

      if (empty($original_cidrs)) {
        if (!empty($entity->getIpv6CidrBlocks())) {
          $this->ec2Service->associateVpcCidrBlock([
            'VpcId' => $entity->getVpcId(),
            'AmazonProvidedIpv6CidrBlock' => TRUE,
          ]);
        }
      }
      else {
        if (empty($entity->getIpv6CidrBlocks())) {
          $this->ec2Service->disassociateVpcCidrBlock([
            'AssociationId' => $original_association_id,
          ]);
        }
      }

      // Update the vpc.
      $this->ec2Service->updateVpcs([
        'VpcId' => $entity->getVpcId(),
      ], FALSE);

      // Update the flow log.
      if ($form_state->getValue('flow_log')) {
        // Create flow log.
        aws_cloud_create_flow_log($entity->getCloudContext(), $entity->getVpcId());
      }
      else {
        // Delete flow log.
        aws_cloud_delete_flow_log($entity->getCloudContext(), $entity->getVpcId());
      }

      $this->processOperationStatus($entity, 'updated');
      $this->clearCacheValues();
    }
    else {
      $this->messenger->addError($this->t('Unable to update @label.', [
        '@label' => $entity->getEntityType()->getSingularLabel(),
      ]));
    }

    $form_state->setRedirect('view.aws_cloud_vpc.list', [
      'cloud_context' => $entity->getCloudContext(),
    ]);
  }

  /**
   * Check if the flow log is configured to the VPC.
   *
   * @param string $vpc_id
   *   The VPC ID.
   *
   * @return bool
   *   True if the VPC has been configured with flow log.
   */
  private function hasFlowLog($vpc_id) {
    $params['Filter'] = [
      [
        'Name' => 'resource-id',
        'Values' => [$vpc_id],
      ],
    ];

    $result = $this->ec2Service->describeFlowLogs($params);
    return !empty($result['FlowLogs']);
  }

}
