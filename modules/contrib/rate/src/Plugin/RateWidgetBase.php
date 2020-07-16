<?php

namespace Drupal\rate\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\votingapi\VoteResultFunctionManager;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Base class for Rate widget plugins.
 */
class RateWidgetBase extends PluginBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The votingapi result manager.
   *
   * @var \Drupal\votingapi\VoteResultFunctionManager
   */
  protected $votingapiResult;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\votingapi\VoteResultFunctionManager $vote_result
   *   Vote result function service.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, VoteResultFunctionManager $vote_result, EntityFormBuilderInterface $form_builder, AccountInterface $account, RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->votingapiResult = $vote_result;
    $this->entityFormBuilder = $form_builder;
    $this->account = $account;
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.votingapi.resultfunction'),
      $container->get('entity.form_builder'),
      $container->get('current_user'),
      $container->get('request_stack'),
      $container->get('config.factory')
    );
  }

  /**
   * Return label.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Return minimal value.
   */
  public function getValues() {
    return $this->getPluginDefinition()['values'];
  }

  /**
   * Gets the widget form as configured for given parameters.
   *
   * @return \Drupal\Core\Form\FormInterface
   *   configured vote form
   */
  public function getForm($entity_type, $entity_bundle, $entity_id, $vote_type, $rate_widget, $settings) {
    $template = $settings->get('template');

    $vote = $this->getEntityForVoting($entity_type, $entity_bundle, $entity_id, $vote_type, $rate_widget, $settings);

    $options = $settings->get('options');
    $new_options = [];
    $option_classes = [];

    // For Fivestar we need only the values and labels, omit classes.
    foreach ($options as $id => $option) {
      $new_options[$option['value']] = isset($option['label']) ? $option['label'] : '';
      $option_classes[$option['value']] = isset($option['class']) ? $option['class'] : '';
    }

    /*
     * @TODO: remove custom entity_form_builder once
     *   https://www.drupal.org/node/766146 is fixed.
     */
    $form = $this->entityFormBuilder->getForm($vote, 'rate_vote', [
      'settings' => $settings,
      'plugin' => $this,
      'options' => $new_options,
      'classes' => $option_classes,
      'show_own_vote' => '1',
      'readonly' => FALSE,
      // @TODO: remove following keys when #766146 fixed (multiple form_ids).
      'entity_type' => $entity_type,
      'entity_bundle' => $entity_bundle,
      'entity_id' => $entity_id,
      'vote_type' => $vote_type,
      'rate_widget' => $rate_widget,
    ]);
    return $form;
  }

  /**
   * Checks whether currentUser is allowed to vote.
   *
   * @return bool
   *   True if user is allowed to vote
   */
  public function canVote($vote, $account = FALSE) {
    if (!$account) {
      $account = $this->account;
    }
    $entity = $this->entityTypeManager
      ->getStorage($vote->getVotedEntityType())
      ->load($vote->getVotedEntityId());

    if (!$entity) {
      return FALSE;
    }

    if ($vote->getVotedEntityType() == 'comment') {
      $perm = 'cast rate vote on ' . $entity->getFieldName() . ' on ' . $entity->getCommentedEntityTypeId() . ' of ' . $entity->getCommentedEntity()->bundle();
    }
    else {
      $perm = 'cast rate vote on ' . $vote->getVotedEntityType() . ' of ' . $entity->bundle();
    }
    return $account->hasPermission($perm);
  }

  /**
   * Returns a Vote entity.
   *
   * Checks whether a vote was already done and if this vote should be reused
   * instead of adding a new one.
   *
   * @return \Drupal\votingapi\Entity\Vote
   *   Vote entity
   */
  public function getEntityForVoting($entity_type, $entity_bundle, $entity_id, $vote_type, $rate_widget, $settings) {
    $storage = $this->entityTypeManager->getStorage('vote');
    $voteData = [
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'type'  => $vote_type,
      'user_id' => $this->account->id(),
      'rate_widget' => $rate_widget,
    ];
    $vote = $storage->create($voteData);
    $voting_settings = $settings->get('voting');
    $timestamp_offset = $this->getWindow('user_window', $entity_type, $entity_bundle, $rate_widget, $voting_settings);

    if ($this->account->isAnonymous()) {
      $voteData['vote_source'] = $this->requestStack->getCurrentRequest()->getClientIp();
      $timestamp_offset = $this->getWindow('anonymous_window', $entity_type, $entity_bundle, $rate_widget, $voting_settings);
    }

    $query = $this->entityTypeManager->getStorage('vote')->getQuery();

    foreach ($voteData as $key => $value) {
      $query->condition($key, $value);
    }

    // Check if rollover is 'Immediately' or value in seconds.
    if ($timestamp_offset >= 0) {
      $query->condition('timestamp', time() - $timestamp_offset, '>');
    }

    $votes = $query->execute();
    if ($votes && count($votes) > 0) {
      $vote = $storage->load(array_shift($votes));
    }
    return $vote;
  }

  /**
   * Get results.
   */
  public function getResults($entity, $result_function = FALSE, $reset = FALSE) {
    if ($reset) {
      drupal_static_reset(__FUNCTION__);
    }
    $resultCache = &drupal_static(__FUNCTION__);
    if (!$resultCache || !isset($resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()])) {
      $resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()] = $this->votingapiResult->getResults($entity->getVotedEntityType(), $entity->getVotedEntityId());
    }

    $result = isset($resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()]) ? $resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()] : [];
    $result = !empty($result) && array_key_exists($entity->bundle(), $result) ? $result[$entity->bundle()] : [];
    if ($result_function && array_key_exists($result_function, $result) && $result[$result_function]) {
      $result = $result[$result_function];
    }
    return $result;
  }

  /**
   * Get time window settings.
   */
  public function getWindow($window_type, $entity_type_id, $entity_bundle, $rate_widget, $voting_settings) {
    // Check for rollover window settings in widget or use votingapi setting.
    $window_field_setting = !empty($voting_settings[$window_type]) ? $voting_settings[$window_type] : -2;
    $use_site_default = FALSE;

    // Use votingapi site-wide setting if requested or window not set.
    if ($window_field_setting === NULL || $window_field_setting === -2) {
      $use_site_default = TRUE;
    }

    $window = $window_field_setting;
    if ($use_site_default) {
      /*
       * @var \Drupal\Core\Config\ImmutableConfig $voting_configuration
       */
      $voting_configuration = $this->configFactory->get('votingapi.settings');
      $window = $voting_configuration->get($window_type);
    }
    return $window;
  }

  /**
   * Generate the result summary.
   */
  public function getVoteSummary(ContentEntityInterface $vote) {
    $results = $this->getResults($vote);
    $widget_name = $vote->rate_widget->value;
    $widget = \Drupal::service('entity_type.manager')->getStorage('rate_widget')->load($widget_name);
    $fieldResults = [];

    foreach ($results as $key => $result) {
      if (strpos($key, '.') && strpos($key, ':')) {
        if ((substr($key, strrpos($key, '.') + 1) === $widget_name)) {
          $key = explode(':', $key);
          $fieldResults[$key[0]] = ($result != 0) ? ceil($result * 10) / 10 : 0;
        }
      }
    }
    return [
      '#theme' => 'rate_widgets_summary',
      '#vote' => $vote,
      '#results' => $fieldResults,
      '#rate_widget' => $widget_name,
      '#widget_template' => $widget->get('template'),
    ];
  }

  /**
   * Returns the votes for an entity.
   *
   * @return array
   *   Vote entity results.
   */
  public function getVotes($entity_type, $entity_bundle, $entity_id, $vote_type, $rate_widget) {
    $storage = $this->entityTypeManager->getStorage('vote');
    $voteData = [
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'type'  => $vote_type,
      'rate_widget' => $rate_widget,
    ];
    $query = $this->entityTypeManager->getStorage('vote')->getQuery();
    foreach ($voteData as $key => $value) {
      $query->condition($key, $value);
    }
    $votes = $query->execute();
    $vote_values = [];
    if ($votes && count($votes) > 0) {
      foreach ($votes as $id) {
        $vote = $storage->load($id);
        $vote_values[$vote->getValue()][] = $vote->getValue();
      }
    }
    return $vote_values;
  }

}
