<?php

namespace Drupal\terraform\Form;

use Drupal\Core\Url;

/**
 * Provides an entities deletion confirmation form.
 */
class TerraformVariableDeleteMultipleForm extends TerraformDeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $route = \Drupal::routeMatch();
    return new Url(
      'entity.' . $this->entityTypeId . '.collection', [
        'cloud_context' => $route->getParameter('cloud_context'),
        'terraform_workspace' => $route->getParameter('terraform_workspace'),
      ]
    );
  }

}
