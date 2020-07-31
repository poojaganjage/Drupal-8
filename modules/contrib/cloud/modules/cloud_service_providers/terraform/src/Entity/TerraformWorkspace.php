<?php

namespace Drupal\terraform\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Workspace entity.
 *
 * @ingroup terraform
 *
 * @ContentEntityType(
 *   id = "terraform_workspace",
 *   id_plural = "terraform_workspaces",
 *   label = @Translation("Workspace"),
 *   label_collection = @Translation("Workspaces"),
 *   label_singular = @Translation("Workspace"),
 *   label_plural = @Translation("Workspaces"),
 *   handlers = {
 *     "view_builder" = "Drupal\terraform\Entity\TerraformWorkspaceViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\terraform\Entity\TerraformWorkspaceViewsData",
 *     "access"       = "Drupal\terraform\Controller\TerraformWorkspaceAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\terraform\Form\TerraformWorkspaceCreateForm",
 *       "edit"       = "Drupal\terraform\Form\TerraformWorkspaceEditForm",
 *       "delete"     = "Drupal\terraform\Form\TerraformDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\terraform\Form\TerraformDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "terraform_workspace",
 *   admin_permission = "administer terraform workspace",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}",
 *     "collection"           = "/clouds/terraform/{cloud_context}/workspace",
 *     "add-form"             = "/clouds/terraform/{cloud_context}/workspace/add",
 *     "edit-form"            = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/edit",
 *     "delete-form"          = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/delete",
 *     "delete-multiple-form" = "/clouds/terraform/{cloud_context}/workspace/delete_multiple",
 *   },
 *   field_ui_base_route = "terraform_workspace.settings"
 * )
 */
class TerraformWorkspace extends TerraformEntityBase implements TerraformWorkspaceInterface {

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceId() {
    return $this->get('workspace_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkspaceId($workspace_id) {
    return $this->set('workspace_id', $workspace_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getAutoApply() {
    return $this->get('auto_apply')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAutoApply($auto_apply) {
    return $this->set('auto_apply', $auto_apply);
  }

  /**
   * {@inheritdoc}
   */
  public function getTerraformVersion() {
    return $this->get('terraform_version')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTerraformVersion($terraform_version) {
    return $this->set('terraform_version', $terraform_version);
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkingDirectory() {
    return $this->get('working_directory')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkingDirectory($working_directory) {
    return $this->set('working_directory', $working_directory);
  }

  /**
   * {@inheritdoc}
   */
  public function getLocked() {
    return $this->get('locked')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLocked($locked) {
    return $this->set('locked', $locked);
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentRunId() {
    return $this->get('current_run_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentRunId($current_run_id) {
    return $this->set('current_run_id', $current_run_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentRunStatus() {
    return $this->get('current_run_status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentRunStatus($current_run_status) {
    return $this->set('current_run_status', $current_run_status);
  }

  /**
   * {@inheritdoc}
   */
  public function getVcsRepoIdentifier() {
    return $this->get('vcs_repo_identifier')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVcsRepoIdentifier($vcs_repo_identifier) {
    return $this->set('vcs_repo_identifier', $vcs_repo_identifier);
  }

  /**
   * {@inheritdoc}
   */
  public function getOauthTokenId() {
    return $this->get('oauth_token_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setOauthTokenId($oauth_token_id) {
    return $this->set('oauth_token_id', $oauth_token_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getVcsRepoBranch() {
    return $this->get('vcs_repo_branch')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVcsRepoBranch($vcs_repo_branch) {
    return $this->set('vcs_repo_branch', $vcs_repo_branch);
  }

  /**
   * {@inheritdoc}
   */
  public function getAwsCloud() {
    return $this->get('aws_cloud')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAwsCloud($aws_cloud) {
    return $this->set('aws_cloud', $aws_cloud);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = TerraformEntityBase::baseFieldDefinitions($entity_type);

    $fields['workspace_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Workspace ID'))
      ->setDescription(t('The Workspace ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['auto_apply'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Auto Apply'))
      ->setDescription(t('Automatically apply changes when a Terraform plan is successful.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDefaultValue(FALSE)
      ->setReadOnly(TRUE);

    $fields['terraform_version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Terraform Version'))
      ->setDescription(t('The version of Terraform to use for this workspace.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['working_directory'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Terraform Working Directory'))
      ->setDescription(t('The directory to execute Terraform commands in.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['locked'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Locked'))
      ->setDescription(t('If the workspace is not locked, all operations can proceed normally.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDefaultValue(FALSE)
      ->setReadOnly(TRUE);

    $fields['current_run_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Current Run ID'))
      ->setDescription(t('The current run ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['current_run_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Current Run Status'))
      ->setDescription(t('The current run status.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['vcs_repo_identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VCS Repository Identifier'))
      ->setDescription(t('The VCS Repository Identifier.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['vcs_repo_branch'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VCS Repository Branch'))
      ->setDescription(t('The VCS Repository Branch.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['oauth_token_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('OAuth Token ID'))
      ->setDescription(t('The OAuth Token ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['aws_cloud'] = BaseFieldDefinition::create('string')
      ->setLabel(t('AWS Cloud'))
      ->setDescription(t('The AWS Cloud.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    return $fields;
  }

}
