<?php

namespace Drupal\terraform\Entity;

use Drupal\cloud\Entity\CloudViewBuilder;

/**
 * Provides the Workspace view builders.
 */
class TerraformWorkspaceViewBuilder extends CloudViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsetDefs() {
    return [
      [
        'name' => 'workspace',
        'title' => $this->t('Workspace'),
        'open' => TRUE,
        'fields' => [
          'name',
          'workspace_id',
          'auto_apply',
          'terraform_version',
          'working_directory',
          'locked',
          'aws_cloud',
          'created',
        ],
      ],
      [
        'name' => 'vcs',
        'title' => $this->t('VCS'),
        'open' => TRUE,
        'fields' => [
          'vcs_repo_identifier',
          'vcs_repo_branch',
          'oauth_token_id',
        ],
      ],
      [
        'name' => 'run',
        'title' => $this->t('Run'),
        'open' => TRUE,
        'fields' => [
          'current_run_id',
          'current_run_status',
        ],
      ],
      [
        'name' => 'others',
        'title' => $this->t('Others'),
        'open' => FALSE,
        'fields' => [
          'cloud_context',
          'uid',
        ],
      ],
    ];
  }

}
