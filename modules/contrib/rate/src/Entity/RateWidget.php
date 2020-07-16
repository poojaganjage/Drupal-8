<?php

namespace Drupal\rate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\rate\RateWidgetInterface;

/**
 * Defines the Rate Widget configuration entity.
 *
 * @ConfigEntityType(
 *   id = "rate_widget",
 *   label = @Translation("Rate widget"),
 *   config_prefix = "rate_widget",
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\rate\RateWidgetForm",
 *       "edit" = "Drupal\rate\RateWidgetForm",
 *       "delete" = "Drupal\rate\Form\RateWidgetDeleteForm"
 *     },
 *     "list_builder" = "Drupal\rate\RateWidgetListBuilder",
 *   },
 *   admin_permission = "administer rate",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/rate/add",
 *     "edit-form" = "/admin/structure/rate/{rate_widget}/edit",
 *     "delete-form" = "/admin/structure/rate/{rate_widget}/delete",
 *     "collection" = "/admin/structure/rate_widgets"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "template",
 *     "entity_types",
 *     "comment_types",
 *     "options",
 *     "voting",
 *     "display",
 *     "results",
 *   }
 * )
 */
class RateWidget extends ConfigEntityBase implements RateWidgetInterface {

  /**
   * The machine name of this rate widget.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the rate widget.
   *
   * @var string
   */
  protected $label;

  /**
   * The template for the rate widget.
   *
   * @var string
   */
  protected $template;

  /**
   * The entities the rate widget is attached to.
   *
   * @var array
   */
  protected $entity_types = [];

  /**
   * The comments the rate widget is attached to.
   *
   * @var array
   */
  protected $comment_types = [];

  /**
   * The the options to vote on.
   *
   * @var array
   */
  protected $options = [];

  /**
   * The voting settings of the widget.
   *
   * @var array
   */
  protected $voting = [];

  /**
   * The display settings of the widget.
   *
   * @var array
   */
  protected $display = [];

  /**
   * The result settings of the widget.
   *
   * @var array
   */
  protected $results = [];

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    $this->options = $options;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function getOption($key, $default_value = NULL) {
    if (isset($this->options[$key])) {
      return $this->options[$key];
    }
    return $default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return !$this->isNew();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    // Clear the rate widget cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
  }

}
