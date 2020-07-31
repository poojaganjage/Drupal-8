<?php

namespace Drupal\openstack\Plugin\Derivative;

use Drupal\aws_cloud\Plugin\Derivative\AwsCloudMenuLinks;

/**
 * OpenStack menu link generation.
 */
class OpenStackMenuLinks extends AwsCloudMenuLinks {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $weight = 200;
    // Get all openstack entities.
    $entities = $this->cloudConfigPluginManager->loadConfigEntities('openstack');

    if (!empty($entities)) {
      // Add OpenStack Resources menu.
      $id = "openstack.local_tasks.instance.all";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => "OpenStack resources"]);
      $links[$id]['route_name'] = "view.openstack_instance.all";
      $links[$id]['menu_name'] = 'cloud.service_providers.menu.all';
      $links[$id]['parent'] = 'cloud.menu.cloud_links:cloud.service_providers.menu.all';
      $links[$id]['weight'] = $weight++;

      // Add dropdown menu for openstack.
      $title = "OpenStack";
      $id = "openstack.service_providers.menu";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $this->t('@title', ['@title' => $title]);
      $links[$id]['route_name'] = "view.cloud_config.list";
      $links[$id]['menu_name'] = 'openstack.service_providers.menu';
      $links[$id]['parent'] = 'cloud.service_providers.menu';
      $links[$id]['weight'] = $weight++;
      $links[$id]['expanded'] = TRUE;
    }

    foreach ($entities ?: [] as $entity) {
      /* @var \Drupal\cloud\Entity\CloudConfig $entity */
      $id = $entity->id() . '.local_tasks.' . $entity->getCloudContext();
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $entity->label();
      $links[$id]['route_name'] = 'view.openstack_instance.list';
      $links[$id]['base_route'] = 'openstack.service_providers.menu';
      $links[$id]['parent'] = 'openstack.menu.cloud_context:openstack.service_providers.menu';
      $links[$id]['route_parameters'] = ['cloud_context' => $entity->getCloudContext()];
    }

    // Add dropdown menus for cloud design from cloud_context.
    $weight = 200;
    foreach ($entities ?: [] as $entity) {
      // Add dropdown menus for server template for each openstack
      // cloud_context.
      $id = "server.{$entity->id()}.design.local_tasks.{$entity->getCloudContext()}";
      $links[$id] = $base_plugin_definition;
      $links[$id]['title'] = $entity->label();
      $links[$id]['parent'] = 'cloud.menu.design_links:cloud_server.template';
      $links[$id]['route_name'] = 'entity.cloud_server_template.collection';
      $links[$id]['route_parameters'] = ['cloud_context' => $entity->getCloudContext()];
      $links[$id]['weight'] = $weight++;
    }

    return $links;
  }

}
