<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the K8s Event entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_event",
 *   id_plural = "k8s_events",
 *   label = @Translation("Event"),
 *   label_collection = @Translation("Events"),
 *   label_singular = @Translation("Event"),
 *   label_plural = @Translation("Events"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sEventViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sEventViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sEventAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_event",
 *   admin_permission = "administer k8s event",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/event/{k8s_event}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/event",
 *   },
 *   field_ui_base_route = "k8s_event.settings"
 * )
 */
class K8sEvent extends K8sEntityBase implements K8sEventInterface {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->get('type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setType($val) {
    return $this->set('type', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getReason() {
    return $this->get('reason')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setReason($val) {
    return $this->set('reason', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getObjectKind() {
    return $this->get('object_kind')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setObjectKind($val) {
    return $this->set('object_kind', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getObjectName() {
    return $this->get('object_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setObjectName($val) {
    return $this->set('object_name', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    return $this->get('message')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMessage($val) {
    return $this->set('message', $val);
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeStamp() {
    return $this->get('time_stamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTimeStamp($val) {
    return $this->set('time_stamp', $val);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of k8s event.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['reason'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Reason'))
      ->setDescription(t('The reason of k8s event.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['object_kind'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Object Kind'))
      ->setDescription(t('The involved object kind of k8s event.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['object_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Object Name'))
      ->setDescription(t('The involved object name of k8s event.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Message'))
      ->setDescription(t('The message of k8s event.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string_long',
        'weight' => -5,
      ]);

    $fields['time_stamp'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Last Time Stamp'))
      ->setDescription('The last time stamp of k8s event.')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => -5,
      ]);

    return $fields;
  }

}
