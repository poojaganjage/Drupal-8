<?php

namespace Drupal\terraform\Entity;

use Drupal\cloud\Service\Util\EntityLinkHtmlGenerator;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the State entity.
 *
 * @ingroup terraform
 *
 * @ContentEntityType(
 *   id = "terraform_state",
 *   id_plural = "terraform_states",
 *   label = @Translation("State"),
 *   label_collection = @Translation("States"),
 *   label_singular = @Translation("State"),
 *   label_plural = @Translation("States"),
 *   handlers = {
 *     "view_builder" = "Drupal\terraform\Entity\TerraformStateViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\terraform\Entity\TerraformStateViewsData",
 *     "access"       = "Drupal\terraform\Controller\TerraformStateAccessControlHandler",
 *     "form" = {
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "terraform_state",
 *   admin_permission = "administer terraform state",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"  = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/state/{terraform_state}",
 *     "collection" = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/state",
 *     "apply-form" = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/state/{terraform_state}/apply",
 *   },
 *   field_ui_base_route = "terraform_state.settings"
 * )
 */
class TerraformState extends TerraformEntityBase implements TerraformStateInterface {

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    // Add terraform workspace ID.
    $uri_route_parameters['terraform_workspace'] = $this->getTerraformWorkspaceId();

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getTerraformWorkspaceId() {
    return $this->get('terraform_workspace_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTerraformWorkspaceId($terraform_workspace_id) {
    return $this->set('terraform_workspace_id', $terraform_workspace_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getStateId() {
    return $this->get('state_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStateId($state_id) {
    return $this->set('state_id', $state_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getRunId() {
    return $this->get('run_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRunId($run_id) {
    return $this->set('run_id', $run_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getSerialNo() {
    return $this->get('serial_no')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSerialNo($serial_no) {
    return $this->set('serial_no', $serial_no);
  }

  /**
   * {@inheritdoc}
   */
  public function getDetail() {
    return $this->get('detail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDetail($detail) {
    return $this->set('detail', $detail);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = TerraformEntityBase::baseFieldDefinitions($entity_type);

    $fields['terraform_workspace_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Terraform Workspace ID'))
      ->setDescription(t('The Terraform Workspace ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'integer',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['state_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('State ID'))
      ->setDescription(t('The state ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['run_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Run ID'))
      ->setDescription(t('The ID of Run.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => -5,
        'type' => 'entity_link',
        'settings' => [
          'target_type' => 'terraform_run',
          'field_name' => 'run_id',
          'html_generator_class' => EntityLinkHtmlGenerator::class,
          'extra_route_parameter' => 'terraform_workspace',
          'extra_route_parameter_entity_method' => 'getTerraformWorkspaceId',
        ],
      ])
      ->setReadOnly(TRUE);

    $fields['serial_no'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Serial'))
      ->setDescription(t('Serial.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'integer',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['detail'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Detail'))
      ->setDescription(t('State detail.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
