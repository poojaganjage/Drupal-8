<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\cloud\Entity\CloudContentEntityBase;

/**
 * Provides an entities start confirmation form.
 */
class InstanceStartMultipleForm extends AwsCloudProcessMultipleForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {

    return $this->formatPlural(count($this->selection),
      'Are you sure you want to start this @item?',
      'Are you sure you want to start these @items?', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {

    return $this->t('Start');
  }

  /**
   * {@inheritdoc}
   */
  protected function processCloudResource(CloudContentEntityBase $entity) {

    $this->ec2Service->setCloudContext($entity->getCloudContext());

    return $this->ec2Service->startInstances([
      'InstanceIds' => [$entity->getInstanceId()],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function processEntity(CloudContentEntityBase $entity) {}

  /**
   * Process an entity and related AWS resource.
   *
   * @param \Drupal\cloud\Entity\CloudContentEntityBase $entity
   *   An entity object.
   *
   * @return bool
   *   Succeeded or failed.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function process(CloudContentEntityBase $entity) {

    if (!empty($entity) && $this->processCloudResource($entity)) {

      $this->processEntity($entity);
      $this->processOperationStatus($entity, 'started');

      return TRUE;
    }

    $this->processOperationErrorStatus($entity, 'started');

    return FALSE;
  }

  /**
   * Returns the message to show the user after an item was processed.
   *
   * @param int $count
   *   Count of processed translations.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The item processed message.
   */
  protected function getProcessedMessage($count) {
    $this->ec2Service->updateInstances();
    return $this->formatPlural($count, 'Started @count item.', 'Started @count items.');
  }

}
