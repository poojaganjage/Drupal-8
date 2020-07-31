<?php

namespace Drupal\terraform\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\terraform\Entity\TerraformWorkspace;
use Drupal\terraform\Entity\TerraformRun;
use Drupal\terraform\Entity\TerraformState;
use Drupal\terraform\Entity\TerraformVariable;

/**
 * Entity update methods for Batch API processing.
 */
class TerraformBatchOperations {

  use StringTranslationTrait;

  /**
   * The finish callback function.
   *
   * Deletes stale entities from the database.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $stale
   *   The stale entities to delete.
   * @param bool $clear
   *   TRUE to clear entities, FALSE keep them.
   */
  public static function finished($entity_type, array $stale, $clear = TRUE) {
    $entity_type_manager = \Drupal::entityTypeManager();
    if (count($stale) && $clear === TRUE) {
      $entity_type_manager->getStorage($entity_type)->delete($stale);
    }
  }

  /**
   * Update or create a terraform workspace entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $workspace
   *   The workspace array.
   *
   * @throws \Drupal\terraform\Service\TerraformServiceException
   *   Thrown when unable to get workspaces.
   */
  public static function updateWorkspace($cloud_context, array $workspace) {
    $terraform_service = \Drupal::service('terraform');
    $terraform_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $workspace['attributes']['name'];
    $entity_id = $terraform_service->getEntityId('terraform_workspace', 'name', $name);

    if (!empty($entity_id)) {
      $entity = TerraformWorkspace::load($entity_id);
    }
    else {
      $entity = TerraformWorkspace::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'created' => self::getCreationTimestamp($workspace, $timestamp),
        'changed' => self::getChangedTimestamp($workspace, $timestamp),
      ]);
    }

    $entity->setWorkspaceId($workspace['id']);
    $entity->setAutoApply($workspace['attributes']['auto-apply']);
    $entity->setTerraformVersion($workspace['attributes']['terraform-version']);
    $entity->setWorkingDirectory($workspace['attributes']['working-directory']);
    $entity->setLocked($workspace['attributes']['locked']);
    $entity->setRefreshed(self::getChangedTimestamp($workspace, $timestamp));

    if (!empty($workspace['attributes']['vcs-repo'])) {
      $entity->setVcsRepoIdentifier($workspace['attributes']['vcs-repo']['identifier'] ?? '');
      $entity->setOauthTokenId($workspace['attributes']['vcs-repo']['oauth-token-id'] ?? '');
      $entity->setVcsRepoBranch($workspace['attributes']['vcs-repo']['branch'] ?? '');
    }

    // Run Status and ID.
    if (!empty($workspace['relationships']['current-run'])
    && !empty($workspace['relationships']['current-run']['data'])
    && !empty($workspace['relationships']['current-run']['data']['id'])) {
      $entity->setCurrentRunId($workspace['relationships']['current-run']['data']['id']);

      $runs = $terraform_service->describeRuns([
        'terraform_workspace' => $entity,
      ]);
      foreach ($runs ?: [] as $run) {
        if ($run['id'] === $entity->getCurrentRunId()) {
          $entity->setCurrentRunStatus($run['attributes']['status']);
        }
      }
    }

    $entity->save();
  }

  /**
   * Update or create a terraform run entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $run
   *   The run array.
   * @param int $terraform_workspace_id
   *   The ID of terraform workspace entity.
   *
   * @throws \Drupal\terraform\Service\TerraformServiceException
   *   Thrown when unable to get runs.
   */
  public static function updateRun($cloud_context, array $run, $terraform_workspace_id) {
    $terraform_service = \Drupal::service('terraform');
    $terraform_service->setCloudContext($cloud_context);

    $timestamp = time();
    $name = $run['id'];
    $entity_id = $terraform_service->getEntityId('terraform_run', 'name', $name);

    if (!empty($entity_id)) {
      $entity = TerraformRun::load($entity_id);
    }
    else {
      $entity = TerraformRun::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'terraform_workspace_id' => $terraform_workspace_id,
        'created' => self::getCreationTimestamp($run, $timestamp),
        'changed' => self::getChangedTimestamp($run, $timestamp),
      ]);
    }

    $entity->setRunId($run['id']);
    $entity->setStatus($run['attributes']['status']);
    $entity->setMessage($run['attributes']['message']);
    $entity->setSource($run['attributes']['source']);
    $entity->setTriggerReason($run['attributes']['trigger-reason']);
    $entity->setRefreshed(self::getChangedTimestamp($run, $timestamp));

    // Plan.
    if (!empty($run['relationships']['plan'])) {
      $plan_id = $run['relationships']['plan']['data']['id'];
      $entity->setPlanId($plan_id);

      $plan = $terraform_service->showPlan($plan_id);
      if (!empty($plan['attributes']['log-read-url'])) {
        $entity->setPlanLog(file_get_contents($plan['attributes']['log-read-url']));
      }
    }

    // Apply.
    if (!empty($run['relationships']['apply'])) {
      $apply_id = $run['relationships']['apply']['data']['id'];
      $entity->setApplyId($apply_id);

      $apply = $terraform_service->showApply($apply_id);
      if (!empty($apply['attributes']['log-read-url'])) {
        $entity->setApplyLog(file_get_contents($apply['attributes']['log-read-url']));
      }
    }

    $entity->save();
  }

  /**
   * Update or create a terraform state entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $state
   *   The state array.
   * @param int $terraform_workspace_id
   *   The ID of terraform workspace entity.
   *
   * @throws \Drupal\terraform\Service\TerraformServiceException
   *   Thrown when unable to get runs.
   */
  public static function updateState($cloud_context, array $state, $terraform_workspace_id) {
    $terraform_service = \Drupal::service('terraform');
    $terraform_service->setCloudContext($cloud_context);
    $timestamp = time();
    $name = $state['id'];
    $entity_id = $terraform_service->getEntityId('terraform_state', 'name', $name);

    if (!empty($entity_id)) {
      $entity = TerraformState::load($entity_id);
    }
    else {
      $entity = TerraformState::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'terraform_workspace_id' => $terraform_workspace_id,
        'created' => self::getCreationTimestamp($state, $timestamp),
        'changed' => self::getChangedTimestamp($state, $timestamp),
      ]);
    }

    $entity->setStateId($state['id']);
    $entity->setSerialNo($state['attributes']['serial']);

    if (!empty($state['relationships']['run'])) {
      $entity->setRunId($state['relationships']['run']['data']['id']);
    }

    if (!empty($state['attributes']['hosted-state-download-url'])) {
      $entity->setDetail(file_get_contents($state['attributes']['hosted-state-download-url']));
    }

    $entity->setRefreshed(self::getChangedTimestamp($state, $timestamp));

    $entity->save();
  }

  /**
   * Update or create a terraform variable entity.
   *
   * @param string $cloud_context
   *   The cloud context.
   * @param array $variable
   *   The variable array.
   * @param int $terraform_workspace_id
   *   The ID of terraform workspace entity.
   *
   * @throws \Drupal\terraform\Service\TerraformServiceException
   *   Thrown when unable to get runs.
   */
  public static function updateVariable($cloud_context, array $variable, $terraform_workspace_id) {
    $terraform_service = \Drupal::service('terraform');
    $terraform_service->setCloudContext($cloud_context);
    $timestamp = time();
    $name = $variable['id'];
    $entity_id = $terraform_service->getEntityId('terraform_variable', 'name', $name);

    if (!empty($entity_id)) {
      $entity = TerraformVariable::load($entity_id);
    }
    else {
      $entity = TerraformVariable::create([
        'cloud_context' => $cloud_context,
        'name' => $name,
        'terraform_workspace_id' => $terraform_workspace_id,
        'created' => self::getCreationTimestamp($variable, $timestamp),
        'changed' => self::getChangedTimestamp($variable, $timestamp),
      ]);
    }

    $entity->setVariableId($variable['id']);
    $entity->setAttributeKey($variable['attributes']['key']);
    $entity->setAttributeValue($variable['attributes']['value']);
    $entity->setDescription($variable['attributes']['description']);
    $entity->setCategory($variable['attributes']['category']);
    $entity->setSensitive($variable['attributes']['sensitive']);
    $entity->setHcl($variable['attributes']['hcl']);
    $entity->setRefreshed(self::getChangedTimestamp($variable, $timestamp));

    $entity->save();
  }

  /**
   * Get creation timestamp.
   *
   * @param array $data
   *   The data.
   * @param int $default_timestamp
   *   The default timestamp.
   *
   * @return int
   *   The creation timestamp.
   */
  private static function getCreationTimestamp(array $data, $default_timestamp) {
    if (empty($data['attributes']['created-at'])) {
      return $default_timestamp;
    }

    return strtotime($data['attributes']['created-at']);
  }

  /**
   * Get changed timestamp.
   *
   * @param array $data
   *   The data.
   * @param int $default_timestamp
   *   The default timestamp.
   *
   * @return int
   *   The changed timestamp.
   */
  private static function getChangedTimestamp(array $data, $default_timestamp) {
    if (empty($data['attributes']['latest-change-at'])) {
      return $default_timestamp;
    }

    return strtotime($data['attributes']['latest-change-at']);
  }

}
