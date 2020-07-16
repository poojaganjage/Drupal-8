<?php

namespace Drupal\Tests\rate\Functional;

use Drupal\rate\Entity\RateWidget;
use Drupal\Tests\rate\Traits\RateWidgetCreateTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Holds set of tools for the rate widget testing.
 */
abstract class RateWidgetTestBase extends BrowserTestBase {

  use RateWidgetCreateTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'comment',
    'rate',
    'views',
    'datetime',
  ];

  /**
   * The node access controller.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $accessController;

  /**
   * Load a rate widget easily.
   *
   * @param string $id
   *   The id of the rate widget.
   *
   * @return \Drupal\rate\Entity\RateWidget
   *   The rate widget Object.
   */
  protected function loadRateWidget($id) {
    return RateWidget::load($id);
  }

}
