<?php

namespace Drupal\smart_content_a_b\Plugin\smart_content\SegmentSetStorage\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\smart_content_a_b\Entity\SegmentSetAB;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides segment set plugin definitions for a/b segment sets.
 *
 * @see Drupal\smart_content_demandbase\Plugin\smart_content\Condition\DemandbaseCondition
 */
class GlobalSegmentSetABDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * DemandbaseConditionDeriver constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];
    $entities = SegmentSetAB::loadMultiple();
    foreach ($entities as $entity) {
      $definition = [
        'label' => $entity->label(),
        'group' => $base_plugin_definition['label'],
      ];
      $this->derivatives[$entity->id()] = $definition + $base_plugin_definition;
    }
    return $this->derivatives;
  }

}
