<?php

namespace Drupal\aws_cloud\Form\Vpc;

use Drupal\aws_cloud\Form\Ec2\AwsCloudContentForm;
use Drupal\cloud\Service\Util\EntityLinkWithNameHtmlGenerator;
use Drupal\cloud\Traits\CloudContentEntityTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Subnet entity edit forms.
 *
 * @ingroup aws_cloud
 */
class SubnetEditForm extends AwsCloudContentForm {

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

    $weight = -50;

    $form['subnet'] = [
      '#type' => 'details',
      '#title' => $this->t('VPC'),
      '#open' => TRUE,
      '#weight' => $weight++,
    ];

    $form['subnet']['name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Name'),
      '#maxlength'     => 255,
      '#size'          => 60,
      '#default_value' => $entity->label(),
      '#required'      => TRUE,
    ];

    $form['subnet']['subnet_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Subnet ID')),
      '#markup'        => $entity->getSubnetId(),
    ];

    $form['subnet']['vpc_id'] = $this->entityLinkRenderer->renderFormElements(
      $entity->getVpcId(),
      'aws_cloud_vpc',
      'vpc_id',
      ['#title' => $this->getItemTitle($this->t('VPC'))],
      '',
      EntityLinkWithNameHtmlGenerator::class
    );

    $form['subnet']['state'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('State')),
      '#markup'        => $entity->getState(),
    ];

    $form['subnet']['account_id'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('AWS Account ID')),
      '#markup'        => $entity->getAccountId(),
    ];

    $form['subnet']['created'] = [
      '#type'          => 'item',
      '#title'         => $this->getItemTitle($this->t('Created')),
      '#markup'        => $this->dateFormatter->format($entity->created(), 'short'),
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

    $this->ec2Service->setCloudContext($entity->getCloudContext());
    if ($entity->save()) {

      // Update tags.
      $tag_map = [];
      foreach ($entity->getTags() ?: [] as $tag) {
        $tag_map[$tag['item_key']] = $tag['item_value'];
      }

      $tag_map['Name'] = $entity->getName();

      $this->setTagsInAws($entity->getSubnetId(), $tag_map);

      // Update the vpc.
      $this->ec2Service->updateSubnets([
        'SubnetId' => $entity->getSubnetId(),
      ], FALSE);

      $this->processOperationStatus($entity, 'updated');
      $this->clearCacheValues();
    }
    else {
      $this->messenger->addError($this->t('Unable to update @label.', [
        '@label' => $entity->getEntityType()->getSingularLabel(),
      ]));
    }

    $form_state->setRedirect('view.aws_cloud_subnet.list', [
      'cloud_context' => $entity->getCloudContext(),
    ]);
  }

}
