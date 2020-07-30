<?php

namespace Drupal\smart_content_a_b\Plugin\smart_content\Condition\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\smart_content_a_b\Entity\SegmentSetAB;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition plugin definitions for A/B Tests.
 */
class ABConditionDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;


  /**
   * DemandbaseConditionDeriver constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
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
        'options_callback' => [get_class($this), 'getSegmentOptions'],
      ];
      $this->derivatives[$entity->id()] = $definition + $base_plugin_definition;
    }
    return $this->derivatives;
  }

  public static function getSegmentOptions($plugin) {
    $options = ['a' => 'A'];
    list($plugin_id, $entity_id) = explode(':', $plugin->getPluginId());
    if ($entity_id) {
      if ($entity = SegmentSetAB::load($entity_id)) {
        $count = count($entity->getSegmentSet()->getSegments());
        $letter = 'a';
        for ($x = 0; $x < $count - 1; $x++) {
          $letter++;
          $options[$letter] = strtoupper($letter);
        }
      }
    }
    return $options;
  }


}
