<?php

namespace Drupal\cloud\Controller;

/**
 * Defines a class to build a listing of cloud service providers.
 *
 * @ingroup cloud
 */
class CloudConfigListBuilder extends CloudContentListBuilder {

  // This class is replaced by views.view.cloud_config.yml. However, this is
  // necessary for the route "entity.cloud_config.collection".
  // Refer to the following line:
  // "list_builder" = "Drupal\cloud\Controller\CloudConfigListBuilder"
  // in the CloudConfig class.
}
