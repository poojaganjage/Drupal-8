<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Disassociate Elastic IP address form.
 */
class ElasticIpDisassociateForm extends AwsDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /* @var \Drupal\aws_cloud\Entity\Ec2\ElasticIp $entity */
    $entity = $this->entity;
    return $this->t('Are you sure you want to disassociate this Elastic IP address (@ip_address)', [
      '@ip_address' => $entity->getPublicIp(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $entity = $this->entity;
    $instance_id = $entity->getInstanceId();
    $network_interface_id = $entity->getNetworkInterfaceId();

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $msg = '<h2>Elastic IP Information:</h2>';
    $msg .= '<ul>';

    if (empty($instance_id) && empty($network_interface_id)) {
      $msg .= $this->t('<li>No information available for Instance ID and Network ID.</li>');
    }

    if (!empty($instance_id)) {
      $instance = $this->getInstanceById($instance_id, $module_name);
      $instance_link = $this->entityLinkRenderer->renderViewElement(
        $instance_id,
        "{$module_name}_instance",
        'instance_id',
        [],
        $instance->getName() !== $instance->getInstanceId() ? $this->t('@instance_name (@instance_id)', [
          '@instance_name' => $instance->getName(),
          '@instance_id' => $instance_id,
        ]) : $instance_id
      );

      $msg .= $this->t('<li>Instance ID: @instance_id</li>',
        [
          '@instance_id' => Markup::create($instance_link['#markup']),
        ]
      );
    }

    if (!empty($network_interface_id)) {
      $network_interface = $this->getNetworkInterfaceById($network_interface_id, $module_name);

      $network_interface_link = $this->entityLinkRenderer->renderViewElement(
        $network_interface_id,
        "{$module_name}_network_interface",
        'network_interface_id',
        [],
        $network_interface->getName() !== $network_interface->getNetworkInterfaceId() ? $this->t('@network_interface_name (@network_interface_id)', [
          '@network_interface_name' => $network_interface->getName(),
          '@network_interface_id' => $network_interface_id,
        ]) : $network_interface_id
      );

      $msg .= $this->t('<li>Network ID: @network_id</li>',
        [
          '@network_id' => Markup::create($network_interface_link['#markup']),
        ]
      );
    }

    $msg .= $this->t('</ul>');

    return $msg;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Disassociate Address');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    if ($this->entity->getAssociationId() === NULL) {
      $form['error'] = [
        '#markup' => '<div>' . $this->t('Elastic IP is already disassociated') . '</div>',
      ];
      unset($form['description']);
      unset($form['actions']['submit']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->ec2Service->setCloudContext($this->entity->getCloudContext());
    $entity = $this->entity;

    // Get module name.
    $module_name = $this->getModuleName($entity);

    $instance_id = $entity->getInstanceId();
    $network_interface_id = $entity->getNetworkInterfaceId();

    $result = $this->ec2Service->disassociateAddress([
      'AssociationId' => $this->entity->getAssociationId(),
    ]);
    if ($result !== NULL) {
      if (!empty($instance_id)) {
        $instance = $this->getInstanceById($instance_id, $module_name);
        $instance_link = $this->entityLinkRenderer->renderViewElement(
        $instance_id,
        "{$module_name}_instance",
        'instance_id',
        [],
        !empty($instance->getName()) !== $instance->getInstanceId()
          ? $this->t('@instance_name (@instance_id)', [
            '@instance_name' => $instance->getName(),
            '@instance_id' => $instance_id,
          ])
          : $instance_id
        );
      }
      if (!empty($network_interface_id)) {
        $network_interface = $this->getNetworkInterfaceById($network_interface_id, $module_name);
        $network_interface_link = $this->entityLinkRenderer->renderViewElement(
        $network_interface_id,
        "{$module_name}_network_interface",
        'network_interface_id',
        [],
        $network_interface->getName() !== $network_interface->getNetworkInterfaceId()
          ? $this->t('@network_interface_name (@network_interface_id)', [
            '@network_interface_name' => $network_interface->getName(),
            '@network_interface_id' => $network_interface_id,
          ])
          : $network_interface_id
        );
      }

      $this->messenger->addStatus($this->t('Elastic IP disassociated from: <ul><li>
        Instance: @instance_id </li> <li>Network: @network_id</li></ul>', [
          '@instance_id' => Markup::create($instance_link['#markup']),
          '@network_id' => Markup::create($network_interface_link['#markup']),
        ]));
      $this->ec2Service->updateElasticIp();
      $this->ec2Service->updateInstances();
      $this->ec2Service->updateNetworkInterfaces();

      $this->clearCacheValues();
    }
    else {
      $this->messenger->addError($this->t('Unable to disassociate Elastic IP.'));
    }
    $form_state->setRedirect("view.{$entity->getEntityTypeId()}.list", ['cloud_context' => $entity->getCloudContext()]);
  }

  /**
   * Helper method to load instance by ID.
   *
   * @param string $instance_id
   *   Instance ID to load.
   * @param string $module_name
   *   Module name.
   *
   * @return \Drupal\aws_cloud\Entity\Ec2\Instance
   *   The Instance entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getInstanceById($instance_id, $module_name) {
    $instances = $this->entityTypeManager
      ->getStorage("{$module_name}_instance")
      ->loadByProperties([
        'instance_id' => $instance_id,
      ]);
    return array_shift($instances);
  }

  /**
   * Helper method to load Network Interface by ID.
   *
   * @param string $network_interface_id
   *   Network Interface ID to load.
   * @param string $module_name
   *   Module name.
   *
   * @return \Drupal\aws_cloud\Entity\Ec2\NetworkInterface
   *   The NetworkInterface entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getNetworkInterfaceById($network_interface_id, $module_name) {
    $network_interface = $this->entityTypeManager
      ->getStorage("{$module_name}_network_interface")
      ->loadByProperties([
        'network_interface_id' => $network_interface_id,
      ]);
    return array_shift($network_interface);
  }

}
