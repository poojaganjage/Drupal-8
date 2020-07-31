<?php

namespace Drupal\terraform\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Variable entity.
 *
 * @ingroup terraform
 *
 * @ContentEntityType(
 *   id = "terraform_variable",
 *   id_plural = "terraform_variables",
 *   label = @Translation("Variable"),
 *   label_collection = @Translation("Variables"),
 *   label_singular = @Translation("Variable"),
 *   label_plural = @Translation("Variables"),
 *   handlers = {
 *     "view_builder" = "Drupal\terraform\Entity\TerraformVariableViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\terraform\Entity\TerraformVariableViewsData",
 *     "access"       = "Drupal\terraform\Controller\TerraformVariableAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\terraform\Form\TerraformVariableCreateForm",
 *       "edit"       = "Drupal\terraform\Form\TerraformVariableEditForm",
 *       "delete"       = "Drupal\terraform\Form\TerraformDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\terraform\Form\TerraformVariableDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "terraform_variable",
 *   admin_permission = "administer terraform variable",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"  = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/variable/{terraform_variable}",
 *     "collection" = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/variable",
 *     "add-form"   = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/variable/add",
 *     "edit-form"  = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/variable/{terraform_variable}/edit",
 *     "delete-form"  = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/variable/{terraform_variable}/delete",
 *     "delete-multiple-form" = "/clouds/terraform/{cloud_context}/workspace/{terraform_workspace}/variable/delete_multiple",
 *   },
 *   field_ui_base_route = "terraform_variable.settings"
 * )
 */
class TerraformVariable extends TerraformEntityBase implements TerraformVariableInterface {

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
  public function getVariableId() {
    return $this->get('variable_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariableId($variable_id) {
    return $this->set('variable_id', $variable_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    return $this->set('description', $description);
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeKey() {
    return $this->get('attribute_key')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttributeKey($attribute_key) {
    return $this->set('attribute_key', $attribute_key);
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeValue() {
    return $this->get('attribute_value')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttributeValue($attribute_value) {
    return $this->set('attribute_value', $attribute_value);
  }

  /**
   * {@inheritdoc}
   */
  public function getCategory() {
    return $this->get('category')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCategory($category) {
    return $this->set('category', $category);
  }

  /**
   * {@inheritdoc}
   */
  public function getSensitive() {
    return $this->get('sensitive')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSensitive($sensitive) {
    return $this->set('sensitive', $sensitive);
  }

  /**
   * {@inheritdoc}
   */
  public function getHcl() {
    return $this->get('hcl')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setHcl($hcl) {
    return $this->set('hcl', $hcl);
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

    $fields['variable_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Variable ID'))
      ->setDescription(t('The variable ID.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('The description.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['attribute_key'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Key'))
      ->setDescription(t('The key.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['attribute_value'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Value'))
      ->setDescription(t('The value.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'aws_cloud_secret_formatter',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['category'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Category'))
      ->setDescription(t('The category.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['hcl'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('HCL'))
      ->setDescription(t('Parse this field as HashiCorp Configuration Language (HCL).'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['sensitive'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sensitive'))
      ->setDescription(t('Sensitive variables are never shown in the UI or API.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    return $fields;
  }

}
