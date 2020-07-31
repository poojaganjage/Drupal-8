<?php

namespace Drupal\k8s\Service;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Cost fields renderer service.
 */
class CostFieldsRenderer implements CostFieldsRendererInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new CostFieldsRenderer object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {

    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function render(
    $region,
    array $instance_types
  ) {
    $build = [];

    if (empty($instance_types)
    || empty($region)
    || !$this->moduleHandler->moduleExists('aws_cloud')) {
      return $build;
    }

    $price_date_provider = \Drupal::service('aws_cloud.instance_type_price_data_provider');
    $fields = $price_date_provider->getFields();
    $price_data = $price_date_provider->getDataByRegion($region);
    $price_info = [];
    foreach ($instance_types ?: [] as $instance_type) {
      foreach ($price_data ?: [] as $item) {
        if ($item['instance_type'] === $instance_type) {
          foreach ($item ?: [] as $name => $price) {
            if ($name === 'instance_type') {
              continue;
            }

            if (empty($price_info[$name])) {
              // Cast as float.
              // The else statement will case a php warning.
              $price_info[$name] = (float) $price;
            }
            else {
              $price_info[$name] += $price;
            }
          }
          break;
        }
      }
    }

    if (empty($price_info)) {
      return $build;
    }

    unset($fields['instance_type']);
    foreach ($fields ?: [] as $name => $label) {
      $label = str_replace('<br>', ' ', $label->render());
      $value = $price_info[$name];

      // Use html similar to a real field.
      $markup = <<<EOS
      <div class="field field--label-inline">
        <div class="field--label">$label</div>
        <div class="field--item">$value</div>
      </div>
EOS;

      $build[] = [
        '#markup' => $markup,
      ];
    }

    return $build;
  }

}
