<?php

namespace Drupal\terraform\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Run entity.
 *
 * @ingroup terraform
 *
 * @ContentEntityType(
 *   id = "terraform_run",
 *   id_plural = "terraform_runs",
 *   label = @Translation("Run"),
 *   label_collection = @Translation("Runs"),
 *   label_singular = @Translation("Run"),
 *   label_plural = @Translation("Runs"),
 *   handlers = {
 *     "view_builder" = "Drupal\terraform\Entity\TerraformRunViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\terraform\Entity\TerraformRunViewsData",
 *     "access"       = "Drupal\terraform\Controller\TerraformRunAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\terraform\Form\TerraformRunCreateForm",
 *       "apply"      = "Drupal\terraform\Form\TerraformRunApplyForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "terraform_run",
 *   admin_permission = "administer terraform run",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"  = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/run/{terraform_run}",
 *     "collection" = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/run",
 *     "add-form"   = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/run/add",
 *     "apply-form" = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/run/{terraform_run}/apply",
 *   },
 *   field_ui_base_route = "terraform_run.settings"
 * )
 */
class TerraformRun extends TerraformEntityBase implements TerraformRunInterface {

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
  public function getMessage() {
    return $this->get('message')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMessage($message) {
    return $this->set('message', $message);
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    return $this->set('status', $status);
  }

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    return $this->get('source')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSource($source) {
    return $this->set('source', $source);
  }

  /**
   * {@inheritdoc}
   */
  public function getTriggerReason() {
    return $this->get('trigger_reason')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTriggerReason($trigger_reason) {
    return $this->set('trigger_reason', $trigger_reason);
  }

  /**
   * {@inheritdoc}
   */
  public function getPlanId() {
    return $this->get('plan_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPlanId($plan_id) {
    return $this->set('plan_id', $plan_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getPlanLog() {
    return $this->get('plan_log')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPlanLog($plan_log) {
    return $this->set('plan_log', $plan_log);
  }

  /**
   * {@inheritdoc}
   */
  public function getApplyId() {
    return $this->get('apply_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setApplyId($apply_id) {
    return $this->set('apply_id', $apply_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getApplyLog() {
    return $this->get('apply_log')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setApplyLog($apply_log) {
    return $this->set('apply_log', $apply_log);
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

    $fields['run_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Run ID'))
      ->setDescription(t('The run ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['message'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Message'))
      ->setDescription(t('The message.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setDescription(t('The status.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['source'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Source'))
      ->setDescription(t('The source.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['trigger_reason'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Trigger Reason'))
      ->setDescription(t('The trigger reason.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['plan_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Plan ID'))
      ->setDescription(t('The plan ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['plan_log'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Plan Log'))
      ->setDescription(t('Plan log.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'ansi_string_formatter',
        'weight' => -5,
      ]);

    $fields['apply_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Apply ID'))
      ->setDescription(t('The apply ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['apply_log'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Apply Log'))
      ->setDescription(t('Apply log.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'ansi_string_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
