<?php

namespace Drupal\vmware\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the VMware VM entity.
 *
 * @ingroup vmware
 *
 * @ContentEntityType(
 *   id = "vmware_vm",
 *   id_plural = "vmware_vms",
 *   label = @Translation("VM"),
 *   label_collection = @Translation("VMs"),
 *   label_singular = @Translation("VM"),
 *   label_plural = @Translation("VMs"),
 *   handlers = {
 *     "view_builder" = "Drupal\vmware\Entity\VmwareVmViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\vmware\Entity\VmwareVmViewsData",
 *     "access"       = "Drupal\vmware\Controller\VmwareVmAccessControlHandler",
 *     "form" = {
 *       "start"      = "Drupal\vmware\Form\VmwareVmStartForm",
 *       "stop"       = "Drupal\vmware\Form\VmwareVmStopForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "vmware_vm",
 *   admin_permission = "administer vmware vm",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/vmware/{cloud_context}/vm/{vmware_vm}",
 *     "collection"           = "/clouds/vmware/{cloud_context}/vm",
 *     "start-form"           = "/clouds/vmware/{cloud_context}/vm/{vmware_vm}/start",
 *     "stop-form"            = "/clouds/vmware/{cloud_context}/vm/{vmware_vm}/stop",
 *   },
 *   field_ui_base_route = "vmware_vm.settings"
 * )
 */
class VmwareVm extends VmwareEntityBase implements VmwareVmInterface {

  /**
   * {@inheritdoc}
   */
  public function getVmId() {
    return $this->get('vm_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVmId($vm_id) {
    return $this->set('vm_id', $vm_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getPowerState() {
    return $this->get('power_state')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPowerState($power_state) {
    return $this->set('power_state', $power_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getCpuCount() {
    return $this->get('cpu_count')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCpuCount($cpu_count) {
    return $this->set('cpu_count', $cpu_count);
  }

  /**
   * {@inheritdoc}
   */
  public function getMemorySize() {
    return $this->get('memory_size')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMemorySize($memory_size) {
    return $this->set('memory_size', $memory_size);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = VmwareEntityBase::baseFieldDefinitions($entity_type);

    $fields['vm_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VM ID'))
      ->setDescription(t('Identifier of the virtual machine.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['power_state'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Power State'))
      ->setDescription(t('Power state of the virtual machine.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['cpu_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('CPU Count'))
      ->setDescription(t('Number of CPU cores.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['memory_size'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Memory Size(MiB)'))
      ->setDescription(t('Memory size in mebibytes.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    return $fields;
  }

}
