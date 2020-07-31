<?php

namespace Drupal\terraform\Traits;

use Drupal\cloud\Entity\CloudConfig;
use Drupal\terraform\Entity\TerraformVariable;
use Drupal\terraform\Entity\TerraformWorkspace;
use Drupal\terraform\Service\TerraformServiceException;

/**
 * The trait for Terraform Form.
 */
trait TerraformFormTrait {

  /**
   * Get select options of AWS Cloud.
   *
   * @param string $cloud_context
   *   The cloud context.
   *
   * @return array
   *   The select options.
   */
  protected function getAwsCloudOptions($cloud_context) {
    // Load the cloud config.
    $this->cloudConfigPluginManager->setCloudContext($cloud_context);
    $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();

    $options = [];
    $aws_clouds = $cloud_config->get('field_aws_cloud');
    if (empty($aws_clouds)) {
      return $options;
    }

    foreach ($aws_clouds as $aws_cloud) {
      $this->cloudConfigPluginManager->setCloudContext($aws_cloud->value);
      $cloud_config = $this->cloudConfigPluginManager->loadConfigEntity();
      $options[$aws_cloud->value] = $cloud_config->getName();
    }

    return $options;
  }

  /**
   * Update variables for AWS Cloud.
   *
   * @param \Drupal\terraform\Entity\TerraformWorkspace $workspace
   *   The terraform workspace entity.
   */
  protected function updateAwsCloudVariables(TerraformWorkspace $workspace) {
    $aws_cloud_variable_names = [
      'AWS_ACCESS_KEY_ID',
      'AWS_SECRET_ACCESS_KEY',
      'AWS_DEFAULT_REGION',
      'aws_assume_role_arn',
    ];

    try {
      $variable_entities = $this->entityTypeManager
        ->getStorage('terraform_variable')
        ->loadByProperties([
          'cloud_context' => $workspace->getCloudContext(),
          'terraform_workspace_id' => $workspace->id(),
        ]);

      if (empty($workspace->getAwsCloud())) {
        // Delete AWS Cloud variables.
        $this->terraformService->setCloudContext($workspace->getCloudContext());
        foreach ($variable_entities as $variable_entity) {
          if (!in_array($variable_entity->getAttributeKey(), $aws_cloud_variable_names)) {
            continue;
          }

          $this->deleteVariable($variable_entity);
        }

        $this->terraformService->updateVariables([
          'terraform_workspace' => $workspace,
        ]);
        return;
      }

      $this->cloudConfigPluginManager->setCloudContext($workspace->getAwsCloud());
      $aws_cloud_entity = $this->cloudConfigPluginManager->loadConfigEntity();
      $credentials = $this->cloudConfigPluginManager->loadCredentials();

      if (empty($credentials['ini_file'])) {
        return;
      }

      $data = parse_ini_file($credentials['ini_file']);

      $aws_cloud_variable_values = array_combine($aws_cloud_variable_names, [
        $data['aws_access_key_id'],
        $data['aws_secret_access_key'],
        $credentials['region'],
        $credentials['role_arn'],
      ]);

      if (!empty($credentials['use_assume_role'])) {
        $aws_cloud_variable_values['AWS_ACCESS_KEY_ID'] = '';
        $aws_cloud_variable_values['AWS_SECRET_ACCESS_KEY'] = '';
      }

      // Update or create variables.
      $aws_cloud_variable_entities = [];
      foreach ($variable_entities as $variable_entity) {
        if (!in_array($variable_entity->getAttributeKey(), $aws_cloud_variable_names)) {
          continue;
        }

        $aws_cloud_variable_entities[$variable_entity->getAttributeKey()] = $variable_entity;
      }

      foreach ($aws_cloud_variable_names as $aws_cloud_variable_name) {
        if (empty($aws_cloud_variable_entities[$aws_cloud_variable_name])) {
          if (!empty($aws_cloud_variable_values[$aws_cloud_variable_name])) {
            // Create a variable.
            $this->createVariable(
              $workspace,
              $aws_cloud_variable_name,
              $aws_cloud_variable_values[$aws_cloud_variable_name],
              $aws_cloud_entity
            );
          }
          continue;
        }

        // Delete or update a variable.
        if (empty($aws_cloud_variable_values[$aws_cloud_variable_name])) {
          $this->deleteVariable($aws_cloud_variable_entities[$aws_cloud_variable_name]);
          continue;
        }

        $this->updateVariable(
          $workspace,
          $aws_cloud_variable_entities[$aws_cloud_variable_name],
          $aws_cloud_variable_values[$aws_cloud_variable_name]
        );
      }

      $this->terraformService->updateVariables([
        'terraform_workspace' => $workspace,
      ]);
    }
    catch (TerraformServiceException $e) {
      $this->messenger->addError($this->t('Failed to update variables due to the error %message.', [
        '%message' => $e->getMessage(),
      ]));
    }
  }

  /**
   * Delete a variable.
   *
   * @param \Drupal\terraform\Entity\TerraformVariable $variable_entity
   *   The terraform variable entity.
   */
  private function deleteVariable(TerraformVariable $variable_entity) {
    $this->terraformService->deleteVariable($variable_entity->getName());
    $this->processOperationStatus($variable_entity, 'deleted');
  }

  /**
   * Create a variable.
   *
   * @param \Drupal\terraform\Entity\TerraformWorkspace $workspace
   *   The terraform workspace entity.
   * @param string $variable_name
   *   The variable name.
   * @param string $variable_value
   *   The variable value.
   * @param \Drupal\cloud\Entity\CloudConfig $aws_cloud_entity
   *   The AWS Cloud cloud config entity.
   */
  private function createVariable(TerraformWorkspace $workspace, $variable_name, $variable_value, CloudConfig $aws_cloud_entity) {
    $params = [
      'workspace_id' => $workspace->getWorkspaceId(),
      'key' => $variable_name,
      'value' => $variable_value,
      'description' => 'Created automatically in Cloud Orchestrator.',
      'category' => $variable_name === strtoupper($variable_name) ? 'env' : 'terraform',
      'hcl' => FALSE,
      'sensitive' => FALSE,
    ];
    $result = $this->terraformService->createVariable($params);
    $this->terraformService->updateVariables([
      'terraform_workspace' => $workspace,
      'name' => $result['id'],
    ], FALSE);

    $variable_entities = $this->entityTypeManager
      ->getStorage('terraform_variable')
      ->loadByProperties([
        'cloud_context' => $workspace->getCloudContext(),
        'terraform_workspace_id' => $workspace->id(),
        'name' => $result['id'],
      ]);
    if (empty($variable_entities)) {
      return;
    }
    $variable_entity = reset($variable_entities);
    $this->messenger()->addStatus($this->t('The Variable %label for AWS Cloud %aws_cloud has been created.', [
      '%label' => $variable_entity->toLink($variable_entity->label())->toString(),
      '%aws_cloud' => $aws_cloud_entity->toLink($aws_cloud_entity->label())->toString(),
    ]));
    $this->logOperationMessage($variable_entity, 'created');
  }

  /**
   * Update a variable.
   *
   * @param \Drupal\terraform\Entity\TerraformWorkspace $workspace
   *   The terraform workspace entity.
   * @param \Drupal\terraform\Entity\TerraformVariable $variable_entity
   *   The terraform variable entity.
   * @param string $variable_value
   *   The variable value.
   */
  private function updateVariable(TerraformWorkspace $workspace, TerraformVariable $variable_entity, $variable_value) {
    $params = [
      'workspace_id' => $workspace->getWorkspaceId(),
      'variable_id' => $variable_entity->getVariableId(),
      'value' => $variable_value,
    ];
    $result = $this->terraformService->patchVariable($params);
    $this->processOperationStatus($variable_entity, 'updated');
  }

}
