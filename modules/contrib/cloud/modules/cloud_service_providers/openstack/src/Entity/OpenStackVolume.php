<?php

namespace Drupal\openstack\Entity;

use Drupal\cloud\Service\Util\EntityLinkWithNameHtmlGenerator;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\aws_cloud\Entity\Ec2\Volume;
use Drupal\aws_cloud\Entity\Ec2\VolumeInterface;

/**
 * Defines the OpenStack Volume entity.
 *
 * @ingroup openstack
 *
 * @ContentEntityType(
 *   id = "openstack_volume",
 *   id_plural = "openstack_volumes",
 *   label = @Translation("Volume"),
 *   label_collection = @Translation("Volumes"),
 *   label_singular = @Translation("Volume"),
 *   label_plural = @Translation("Volumes"),
 *   handlers = {
 *     "view_builder" = "Drupal\openstack\Entity\OpenStackVolumeViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\aws_cloud\Entity\Ec2\VolumeViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\openstack\Form\OpenStackVolumeEditForm",
 *       "edit" = "Drupal\openstack\Form\OpenStackVolumeEditForm",
 *       "add"  = "Drupal\openstack\Form\OpenStackVolumeCreateForm",
 *       "delete" = "Drupal\openstack\Form\OpenStackVolumeDeleteForm",
 *       "attach" = "Drupal\openstack\Form\OpenStackVolumeAttachForm",
 *       "detach" = "Drupal\openstack\Form\OpenStackVolumeDetachForm",
 *       "delete-multiple-confirm" = "Drupal\openstack\Form\OpenStackVolumeDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\aws_cloud\Controller\Ec2\VolumeAccessControlHandler",
 *   },
 *   base_table = "openstack_volume",
 *   admin_permission = "administer openstack volume",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/clouds/openstack/{cloud_context}/volume/{openstack_volume}",
 *     "add-form" = "/clouds/openstack/{cloud_context}/volume/{openstack_volume}/add",
 *     "edit-form" = "/clouds/openstack/{cloud_context}/volume/{openstack_volume}/edit",
 *     "delete-form" = "/clouds/openstack/{cloud_context}/volume/{openstack_volume}/delete",
 *     "attach-form" = "/clouds/openstack/{cloud_context}/volume/{openstack_volume}/attach",
 *     "detach-form" = "/clouds/openstack/{cloud_context}/volume/{openstack_volume}/detach",
 *     "delete-multiple-form" = "/clouds/openstack/{cloud_context}/volume/delete_multiple",
 *     "collection" = "/clouds/openstack/{cloud_context}/volume",
 *   },
 *   field_ui_base_route = "openstack_volume.settings"
 * )
 */
class OpenStackVolume extends Volume implements VolumeInterface {

  /**
   * {@inheritdoc}
   */
  public function getIops() {
    // OpenStack doesn't support IOPS.
  }

  /**
   * {@inheritdoc}
   */
  public function setIops($iops) {
    // OpenStack doesn't support IOPS.
  }

  /**
   * {@inheritdoc}
   */
  public function getKmsKeyId() {
    // OpenStack doesn't support KMS Key ID.
  }

  /**
   * {@inheritdoc}
   */
  public function getEncrypted() {
    // OpenStack doesn't support Encrypted.
  }

  /**
   * {@inheritdoc}
   */
  public function isVolumeUnused() {
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->get('openstack.settings');
    $unused = FALSE;

    $unused_interval = time() - ($config->get('openstack_unused_volume_criteria') / (24 * 60 * 60));
    if ($this->getState() === 'available' && $this->created() < $unused_interval) {
      $unused = TRUE;
    }
    return $unused;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['attachment_information'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Instance ID'))
      ->setDescription(t('Provides the volume attachment details: the ID of the instance the volume is attached to (and its name in parentheses if applicable), the device name, and the status of the attachment, for example, attaching, attached, or detaching.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_link',
        'settings' => [
          'target_type' => 'openstack_instance',
          'field_name' => 'instance_id',
          'html_generator_class' => EntityLinkWithNameHtmlGenerator::class,
        ],
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['snapshot_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Snapshot ID'))
      ->setDescription(t("The ID of the snapshot that was used to create the volume, if applicable. A snapshot is a copy of an @label at a point in time.", ['@label' => $entity_type->getLabel()]))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_link',
        'settings' => [
          'target_type' => 'openstack_snapshot',
          'field_name' => 'snapshot_id',
        ],
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    unset($fields['iops']);
    unset($fields['kms_key_id']);
    unset($fields['encrypted']);

    return $fields;
  }

}
