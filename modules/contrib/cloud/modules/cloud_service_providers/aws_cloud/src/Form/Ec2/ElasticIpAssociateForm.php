<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Associate Elastic IP address.
 */
class ElasticIpAssociateForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /* @var \Drupal\aws_cloud\Entity\Ec2\ElasticIp $entity */
    $entity = $this->entity;
    return $this->t('Select the instance OR network interface to which you want to associate this @label address (@ip_address).', [
      '@ip_address' => $entity->getPublicIp(),
      '@label' => $entity->getEntityType()->getSingularLabel(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Associate');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $form = parent::buildForm($form, $form_state);

    // Get module name.
    $module_name = $this->getModuleName($entity);

    if ($entity->getAssociationId() !== NULL) {
      $form['error'] = [
        '#markup' => '<div>'
        . $this->t('@label is already associated.', [
          '@label' => $entity->getEntityType()->getSingularLabel(),
        ])
        . '</div>',
      ];
      unset($form['actions']['submit']);
    }
    else {
      $form['resource_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Resource type'),
        '#options' => [
          'instance' => $this->t('Instance'),
          'network_interface' => $this->t('Network Interface'),
        ],
        '#description' => $this->t('Choose the type of resource to which to associate the @label address.', [
          '@label' => $entity->getEntityType()->getSingularLabel(),
        ]),
        '#default_value' => 'instance',
      ];

      $form['instance_ip_container'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'instance-ip-container',
        ],
      ];

      $form['instance_ip_container']['instance_id'] = [
        '#type' => 'select',
        '#title' => $this->t('Instance'),
        '#options' => $this->getUnassociatedInstances($module_name),
        '#ajax' => [
          'callback' => '::getPrivateIpsAjaxCallback',
          'event' => 'change',
          'wrapper' => 'instance-ip-container',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Retrieving...'),
          ],
        ],
        '#states' => [
          'visible' => [
            'select[name="resource_type"]' => ['value' => 'instance'],
          ],
        ],
      ];

      $form['instance_ip_container']['instance_private_ip'] = [
        '#type' => 'select',
        '#title' => $this->t('Private IP'),
        '#description' => $this->t('The private IP address to which to associate the @label address. Only private IP addresses that do not already have an @label associated with them are available.', [
          '@label' => $entity->getEntityType()->getSingularLabel(),
        ]),
        '#options' => [
          '-1' => $this->t('Select a private IP.'),
        ],
        '#states' => [
          'visible' => [
            'select[name="resource_type"]' => ['value' => 'instance'],
          ],
        ],
      ];

      $form['network_interface_ip_container'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'network-interface-ip-container',
        ],
      ];

      $form['network_interface_ip_container']['network_interface_id'] = [
        '#type' => 'select',
        '#title' => $this->t('Network interface'),
        '#options' => $this->getUnassociatedNetworkInterfaces($module_name),
        '#ajax' => [
          'callback' => '::getNetworkIpsAjaxCallback',
          'event' => 'change',
          'wrapper' => 'network-interface-ip-container',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Retrieving...'),
          ],
        ],
        '#states' => [
          'visible' => [
            'select[name="resource_type"]' => ['value' => 'network_interface'],
          ],
        ],
      ];
      $form['network_interface_ip_container']['network_private_ip'] = [
        '#type' => 'select',
        '#title' => $this->t('Private IP'),
        '#description' => $this->t('The private IP address to which to associate the @label address. Only private IP addresses that do not already have an @label associated with them are available.', [
          '@label' => $entity->getEntityType()->getSingularLabel(),
        ]),
        '#options' => [
          '-1' => $this->t('Select a private IP.'),
        ],
        '#states' => [
          'visible' => [
            'select[name="resource_type"]' => ['value' => 'network_interface'],
          ],
        ],
      ];

      // Ajax support: Look at the instance value, and rebuild the private_ip
      // options.
      $instance = $form_state->getValue('instance_id');
      if (isset($instance)) {
        if ($instance !== '-1') {
          $ips = $this->getPrivateIps($instance);
          $form['instance_ip_container']['instance_private_ip']['#options'] = $ips;
        }
        else {
          $form['instance_ip_container']['instance_private_ip']['#options'] = [
            '-1' => $this->t('Select a private IP.'),
          ];
        }
      }

      // Ajax support: Look at network interface value and rebuild the private
      // IP portion of the form.
      $network_interface = $form_state->getValue('network_interface_id');
      if (isset($network_interface)) {
        if ($network_interface !== '-1') {
          $ips = $this->getNetworkPrivateIps($network_interface);
          $form['network_interface_ip_container']['network_private_ip']['#options'] = $ips;
        }
        else {
          $form['network_interface_ip_container']['network_private_ip']['#options'] = [
            '-1' => $this->t('Select a private IP.'),
          ];
        }
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('resource_type') === 'instance') {
      if ($form_state->getValue('instance_id') === -1) {
        // Error out.
        $form_state->setErrorByName('instance_id', $this->t('Instance ID is empty.'));
      }
      if ($form_state->getValue('instance_private_ip') === -1) {
        // Error out.
        $form_state->setErrorByName('instance_private_ip', $this->t('Private IP is empty.'));
      }
    }
    else {
      if ($form_state->getValue('network_interface_id') === -1) {
        $form_state->setErrorByName('network_interface_id', $this->t('Network interface is empty.'));
      }
      if ($form_state->getValue('network_private_ip') === -1) {
        $form_state->setErrorByName('network_private_ip', $this->t('Private IP is empty.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $this->ec2Service->setCloudContext($entity->getCloudContext());

    // Get module name.
    $module_name = $this->getModuleName($entity);

    // Determine if an IP address is attaching to instance or network_interface.
    if ($form_state->getValue('resource_type') === 'instance') {

      $entity_id = $form_state->getValue('instance_id');
      $private_ip = $form_state->getValue('instance_private_ip');

      if ($entity_id !== -1) {

        $instance = $this->entityTypeManager
          ->getStorage("{$module_name}_instance")
          ->loadByProperties(['id' => $entity_id]);

        if (count($instance) === 1) {
          $instance = array_shift($instance);
        }

        $instance_id = $instance->getInstanceId();

        $instance_link = $this->entityLinkRenderer->renderViewElement(
        $instance_id,
        "{$module_name}_instance",
        'instance_id',
        [],
        $instance->getName() !== $instance->getInstanceId()
          ? $this->t('@instance_name (@instance_id)', [
            '@instance_name' => $instance->getName(),
            '@instance_id' => $instance_id,
          ])
          : $instance_id
        );

        // Refresh network interfaces.
        $this->ec2Service->updateNetworkInterfaces();

        $network_interface = $this->getNetworkInterfaceByPrivateIp($private_ip, $module_name);
        if (!empty($network_interface)) {
          $result = $this->ec2Service->associateAddress([
            'AllocationId' => $entity->getAllocationId(),
            'NetworkInterfaceId' => $network_interface->getNetworkInterfaceId(),
            'PrivateIpAddress' => $private_ip,
          ]);

          if (!empty($result)) {
            $message = $this->t("@label @ip_address associated with @private_ip for instance: @instance_id", [
              '@ip_address' => $entity->getPublicIp(),
              '@private_ip' => $private_ip,
              '@instance_id' => Markup::create($instance_link['#markup']),
              '@label' => $entity->getEntityType()->getSingularLabel(),
            ]);

            $this->updateElasticIpEntity($message);
            $this->clearCacheValues();
          }
          else {
            $this->messenger->addError($this->t('Unable to associate @label.', [
              '@label' => $entity->getEntityType()->getSingularLabel(),
            ]));
          }
        }
        else {
          $this->messenger->addError($this->t('Unable to load network interface by private IP.'));
        }
      }
      else {
        $this->messenger->addError($this->t('Unable to load instance ID. No association performed.'));
      }
    }
    else {
      $network_interface_id = $form_state->getValue('network_interface_id');
      $network_private_ip = $form_state->getValue('network_private_ip');

      if ($network_interface_id !== -1) {

        $network_interface = $this->entityTypeManager
          ->getStorage("{$module_name}_network_interface")
          ->loadByProperties(['id' => $network_interface_id]);

        if (count($network_interface) === 1) {
          $network_interface = array_shift($network_interface);
        }

        $result = $this->ec2Service->associateAddress([
          'AllocationId' => $entity->getAllocationId(),
          'NetworkInterfaceId' => $network_interface->getNetworkInterfaceId(),
          'PrivateIpAddress' => $network_private_ip,
        ]);

        if ($result !== NULL) {
          $message = $this->t('@label @ip_address associated with @private_ip for network interface: @network_interface_id', [
            '@ip_address' => $entity->getPublicIp(),
            '@network_interface_id' => $network_interface->getNetworkInterfaceId(),
            '@private_ip' => $network_private_ip,
            '@label' => $entity->getEntityType()->getSingularLabel(),
          ]);

          $this->updateElasticIpEntity($message);
          $this->clearCacheValues();
        }
        else {
          $this->messenger->addError($this->t("Unable to associate @label.", [
            '@label' => $entity->getEntityType()->getSingularLabel(),
          ]));
        }
      }
      else {
        $this->messenger->addError($this->t('Unable to load instance ID. No association performed.'));
      }
    }

    $form_state->setRedirect("entity.{$entity->getEntityTypeId()}.canonical", [
      'cloud_context' => $entity->getCloudContext(),
      "{$entity->getEntityTypeId()}" => $entity->id(),
    ]);
  }

  /**
   * Helper function to update the current aws_cloud_elastic_ip entity.
   *
   * @param string $message
   *   Message to display to use.
   */
  private function updateElasticIpEntity($message) {
    $this->messenger->addStatus($message);

    // Update the following entities from EC2.
    $this->ec2Service->updateElasticIp();
    $this->ec2Service->updateInstances();
    $this->ec2Service->updateNetworkInterfaces();
  }

  /**
   * Ajax callback when the instance dropdown changes.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface element.
   *
   * @return mixed
   *   Form element for instance_ip_container.
   */
  public function getPrivateIpsAjaxCallback(array $form, FormStateInterface $form_state) {
    return $form['instance_ip_container'];
  }

  /**
   * Ajax callback when the network interface dropdown changes.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface element.
   *
   * @return mixed
   *   Form element for network_interface_ip_container.
   */
  public function getNetworkIpsAjaxCallback(array $form, FormStateInterface $form_state) {
    return $form['network_interface_ip_container'];
  }

  /**
   * Helper function that loads all the private IPs for an instance.
   *
   * @param int $instance_id
   *   The instance ID.
   *
   * @return array
   *   An array of IP addresses.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getPrivateIps($instance_id) {
    $entity = $this->entity;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $instance = $this->entityTypeManager
      ->getStorage("{$module_name}_instance")
      ->loadByProperties(['id' => $instance_id]);

    if (count($instance) === 1) {
      $instance = array_shift($instance);
    }

    $ips = !empty($instance) ? explode(', ', $instance->getPrivateIps()) : [];
    $private_ips = [];
    foreach ($ips ?: [] as $ip) {

      // Check if the IP is in the Elastic IP table.
      $result = $this->entityTypeManager
        ->getStorage($entity->getEntityTypeId())
        ->loadByProperties(['private_ip_address' => $ip]);

      if (count($result) === 0) {
        $private_ips[$ip] = $ip;
      }
    }

    return $private_ips;
  }

  /**
   * Helper function to load primary and secondary private IPs.
   *
   * @param int $network_interface_id
   *   The network interface ID.
   *
   * @return array
   *   An array of IP addresses.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getNetworkPrivateIps($network_interface_id) {
    $entity = $this->entity;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    /* @var \Drupal\aws_cloud\Entity\Ec2\NetworkInterface $network_interface */
    $network_interface = $this->entityTypeManager
      ->getStorage("{$module_name}_network_interface")
      ->loadByProperties(['id' => $network_interface_id]);

    if (count($network_interface) === 1) {
      $network_interface = array_shift($network_interface);
    }

    $association_id = $network_interface->getAssociationId();
    $secondary_association_id = $network_interface->getSecondaryAssociationId();

    $ips = [];
    if (empty($association_id)) {
      $ips[$network_interface->getPrimaryPrivateIp()] = $network_interface->getPrimaryPrivateIp();
    }

    if (empty($secondary_association_id) && !empty($network_interface->getSecondaryPrivateIps())) {
      $ips[$network_interface->getSecondaryPrivateIps()] = $network_interface->getSecondaryPrivateIps();
    }

    return $ips;
  }

  /**
   * Query the database for instances that do not have Elastic IPs.
   *
   * @param string $module_name
   *   Module name.
   *
   * @return array
   *   An array of instances formatted for a dropdown.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getUnassociatedInstances($module_name) {
    $entity = $this->entity;
    $instances['-1'] = $this->t('Select an instance.');
    $account = \Drupal::currentUser();

    $query = $this->entityTypeManager
      ->getStorage("{$module_name}_instance")
      ->getQuery()
      ->condition('cloud_context', $entity->getCloudContext());

    // Get cloud service provider name.
    $cloud_name = str_replace('_', ' ', $module_name);

    if (!$account->hasPermission("view any {$cloud_name} instance")) {
      $query->condition('uid', $account->id());
    }

    $results = $query->execute();

    foreach ($results ?: [] as $result) {

      /* @var \Drupal\aws_cloud\Entity\Ec2\Instance $instance */
      $instance = $this->entityTypeManager
        ->getStorage("{$module_name}_instance")
        ->loadByProperties(['id' => $result]);

      if (count($instance) === 1) {
        $instance = array_shift($instance);
      }

      $private_ips = explode(', ', $instance->getPrivateIps());

      foreach ($private_ips ?: [] as $private_ip) {

        $elastic_ips = $this->entityTypeManager
          ->getStorage($entity->getEntityTypeId())
          ->loadbyProperties(['private_ip_address' => $private_ip]);

        if (count($elastic_ips) === 0) {
          /* @var \Drupal\aws_cloud\Entity\Ec2\Instance $result */
          $instances[$instance->id()] = $this->t('%name - %instance_id', [
            '%name' => $instance->getName(),
            '%instance_id' => $instance->getInstanceId(),
          ]);
        }
      }
    }

    return $instances;
  }

  /**
   * Get a network interface given a private_ip address.
   *
   * @param string $private_ip
   *   The private IP used to look up the network interface.
   * @param string $module_name
   *   Module name.
   *
   * @return bool|mixed
   *   False if no network interface found.  The network interface object if
   *   found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getNetworkInterfaceByPrivateIp($private_ip, $module_name) {
    $network_interface = FALSE;

    $results = $this->entityTypeManager
      ->getStorage("{$module_name}_network_interface")
      ->loadByProperties(['primary_private_ip' => $private_ip]);

    if (count($results) === 1) {
      $network_interface = array_shift($results);
    }

    return $network_interface;
  }

  /**
   * Query the database for unassociated network interfaces IPs.
   *
   * @param string $module_name
   *   Module name.
   *
   * @return array
   *   An array of instances formatted for a dropdown.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getUnassociatedNetworkInterfaces($module_name) {
    $entity = $this->entity;
    $interfaces['-1'] = $this->t('Select a network interface.');

    $results = $this->entityTypeManager
      ->getStorage("{$module_name}_network_interface")
      ->getQuery('OR')
      ->notExists('association_id')
      ->notExists('secondary_association_id')
      ->execute();

    foreach ($results ?: [] as $result) {
      /* @var \Drupal\aws_cloud\Entity\Ec2\NetworkInterface $interface */
      $interface = $this->entityTypeManager
        ->getStorage("{$module_name}_network_interface")
        ->loadByProperties(['id' => $result]);

      if (count($interface) === 1) {
        $interface = array_shift($interface);
      }

      if ($interface->getCloudContext() === $entity->getCloudContext()) {
        $interfaces[$interface->id()] = $this->t('%name - %interface_id', [
          '%name' => $interface->getName(),
          '%interface_id' => $interface->getNetworkInterfaceId(),
        ]);
      }
    }

    return $interfaces;
  }

}
