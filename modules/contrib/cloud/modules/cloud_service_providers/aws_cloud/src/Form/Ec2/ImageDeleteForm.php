<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a Image entity.
 *
 * @ingroup aws_cloud
 */
class ImageDeleteForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $state = $this->entity->getStatus();
    if ($state === 'pending') {
      return $this->t("Cannot delete an image in @state state.", [
        '@state' => $state,
      ]);
    }
    return parent::getDescription();
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if ($this->entity->getStatus() === 'pending') {
      return '';
    }
    return parent::getQuestion();
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if ($this->entity->getStatus() === 'pending') {
      unset($actions['submit']);
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());
    $this->cloudConfigPluginManager->setCloudContext($entity->getCloudContext());
    $account_id = $this->cloudConfigPluginManager->loadConfigEntity()->get('field_account_id')->value;

    // If the image isn't owned by the aws user,
    // the calling for deregisterImage will be skipped for AWS Cloud
    // and it will be called for OpenStack.
    if (($entity->getEntityTypeId() === 'aws_cloud_image'
    && ($entity->getAccountId() !== $account_id))
    || $this->ec2Service->deregisterImage([
      'ImageId' => $entity->getImageId(),
    ]) !== NULL) {

      $entity->delete();

      // Don't change the following message since we cannot use
      // $entity->label(), which represents 'ami_name'.
      $this->messenger->addStatus($this->t('The @type @label has been deleted.', [
        '@type'  => $entity->getEntityType()->getSingularLabel(),
        '@label' => $entity->getName(),
      ]));
      $this->logDeletionMessage();
      $this->clearCacheValues();
    }
    else {

      // Don't change the following message since we cannot use
      // $entity->label(), which represents 'ami_name'.
      $this->messenger->addError($this->t('The @type @label could not be deleted.', [
        '@type'  => $entity->getEntityType()->getSingularLabel(),
        '@label' => $entity->getName(),
      ]));

      $this->logger($entity->getEntityType()->getProvider())->error('@type: @label could not be deleted.', [
        '@type' => $entity->getEntityType()->getLabel(),
        '@label' => $entity->label(),
        'link' => $entity->toLink($entity->t('View'))->toString(),
      ]);
    }

    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

}
