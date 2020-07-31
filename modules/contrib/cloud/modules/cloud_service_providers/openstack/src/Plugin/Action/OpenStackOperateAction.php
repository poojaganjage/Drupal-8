<?php

namespace Drupal\openstack\Plugin\Action;

use Drupal\aws_cloud\Plugin\Action\OperateAction;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Operate selected Entity(s).
 */
abstract class OpenStackOperateAction extends OperateAction implements ContainerFactoryPluginInterface {

}
