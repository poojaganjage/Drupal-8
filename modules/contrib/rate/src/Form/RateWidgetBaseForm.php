<?php

namespace Drupal\rate\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\votingapi\VoteResultFunctionManager;

/**
 * Form controller for rate vote forms.
 */
class RateWidgetBaseForm extends ContentEntityForm {

  /**
   * The votingapi result manager.
   *
   * @var \Drupal\votingapi\VoteResultFunctionManager
   */
  protected $votingapiResult;

  /**
   * Class constructor.
   */
  public function __construct(VoteResultFunctionManager $votingapi_result, EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->votingapiResult = $votingapi_result;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.votingapi.resultfunction'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $entity = $this->getEntity();
    $voted_entity_type = $entity->getVotedEntityType();
    $voted_entity_id = $entity->getVotedEntityId();
    $voted_entity = $this->entityManager->getStorage($voted_entity_type)->load($voted_entity_id);

    $additional_form_id_parts = [];
    $additional_form_id_parts[] = $voted_entity->getEntityTypeId();
    $additional_form_id_parts[] = $voted_entity->bundle();
    $additional_form_id_parts[] = $voted_entity->id();
    $additional_form_id_parts[] = $entity->bundle();
    $additional_form_id_parts[] = $entity->rate_widget->value;
    $form_id = implode('_', $additional_form_id_parts);

    return $form_id;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->getEntity();
    $voted_entity_type = $entity->getVotedEntityType();
    $voted_entity_id = $entity->getVotedEntityId();
    $voted_entity = $this->entityManager->getStorage($voted_entity_type)->load($voted_entity_id);
    $result_function = $this->getResultFunction($form_state);
    $options = $form_state->get('options');
    $option_classes = $form_state->get('classes');
    $form_id = Html::getUniqueId('rate-widget-base-form');
    $plugin = $form_state->get('plugin');
    $settings = $form_state->get('settings');
    $display = $settings->get('display');
    $results = $settings->get('results');
    $template = $settings->get('template');
    $rate_widget = $form_state->get('rate_widget');

    $form['#cache']['contexts'][] = 'user.permissions';
    $form['#cache']['contexts'][] = 'user.roles:authenticated';

    $form['#attributes']['id'] = $form_id;

    $rate_options = [];

    // Remove the labels for any template except yesno.
    if ($template != 'yesno') {
      // Remove the labels.
      foreach ($options as $key => $value) {
        $rate_options[$key] = '';
      }
    }
    else {
      // Options with labels.
      $rate_options = $options;
    }

    $form['value'] = [
      '#prefix' => '<div class="' . $template . '-rating-wrapper">',
      '#suffix' => '</div>',
      '#type' => 'radios',
      '#options' => $rate_options,
      '#default_value' => (int) $entity->getValue(),
      '#attributes' => ['class' => [$template . '-rating-input']],
      '#theme_wrappers' => [],
      '#wrapped_label' => TRUE,
    ];

    // Save the results for each option separately and add classes.
    if (isset($template) && $template != 'fivestar') {
      $form['value']['#attributes']['class'][] = 'rating-input';
      $vote_type = $entity->bundle();
      $votes = [];
      $votes = $plugin->getVotes($form_state->get('entity_type'), $form_state->get('entity_bundle'), $form_state->get('entity_id'), $vote_type, $rate_widget);

      $all_votes = 0;
      foreach ($votes as $vote) {
        $all_votes += array_sum($vote);
      }
      foreach ($options as $key => $option) {
        $form['value'][$key]['#attributes']['twig-suggestion'] = 'rating-input';
        $form['value'][$key]['#attributes']['class'][] = 'rating-input';
        $form['value'][$key]['#attributes']['class'][] = $template . '-rating-input';
        if ($template == 'numberupdown') {
          if ($key > 0) {
            $form['value'][$key]['#option_result'] = (isset($all_votes)) ? $all_votes : 0;
          }
        }
        else {
          $vote_sum[$key] = isset($votes[$key]) ? array_sum($votes[$key]) : 0;
          $form['value'][$key]['#option_result'] = ($vote_sum[$key] < 0) ? ($vote_sum[$key]) * -1 : $vote_sum[$key];
        }

        if (isset($option_classes[$key]) && ($option_classes[$key] != NULL)) {
          $form['value'][$key]['#label_attributes']['class'][] = 'rating-label';
          $form['value'][$key]['#label_attributes']['class'][] = 'rating-label-' . $template;
          $form['value'][$key]['#label_attributes']['class'][] = 'rating-label-' . $template . '-' . strtolower($option);
          $form['value'][$key]['#label_attributes']['class'][] = $option_classes[$key];
        }
        else {
          $form['value'][$key]['#label_attributes']['class'][] = 'rating-label';
          $form['value'][$key]['#label_attributes']['class'][] = $template . '-rating-label';
          $form['value'][$key]['#label_attributes']['class'][] = $template . '-rating-label-' . strtolower($option);
        }
      }
    }
    // Handle fivestar classes.
    else {
      foreach ($options as $key => $option) {
        // Add attributes and classes to the inputs.
        $form['value'][$key]['#attributes']['twig-suggestion'] = 'rating-input';
        $form['value'][$key]['#attributes']['class'][] = 'rating-input';
        $form['value'][$key]['#attributes']['class'][] = $template . '-rating-input';
        $form['value'][$key]['#attributes']['class'][] = $template . '-rating-input-' . $key;

        // Add attributes and classes to the labels.
        $form['value'][$key]['#label_attributes']['class'][] = 'rating-label';
        $form['value'][$key]['#label_attributes']['class'][] = $template . '-rating-label';
        $form['value'][$key]['#label_attributes']['class'][] = $template . '-rating-label-' . $key;
        if (isset($option_classes[$key]) && ($option_classes[$key] != NULL)) {
          $form['value'][$key]['#label_attributes']['class'][] = $option_classes[$key];
        }
      }
    }
    // Set the rate widget to readonly, if the entity uses a vote deadline.
    $deadline_disabled = FALSE;
    $voting = $settings->get('voting');
    if (isset($voting['use_deadline']) && $voting['use_deadline'] == 1) {
      // Get the rate_vote_deadline field.
      if ($voted_entity->hasField('field_rate_vote_deadline')) {
        $deadline = $voted_entity->get('field_rate_vote_deadline')->getString();
        $current_time = \Drupal::time()->getRequestTime();
        // Disable the widget if deadline to vote was set and was passed.
        if (!empty($deadline) && (strtotime($deadline) <= $current_time)) {
          $deadline_disabled = TRUE;
        }
      }
    }
    if ((isset($display['readonly']) && $display['readonly'] === 1) || !$plugin->canVote($entity) || $deadline_disabled === TRUE) {
      $form['value']['#disabled'] = TRUE;
      $form['value']['#prefix'] = '<div class="' . $template . '-rating-wrapper" can-edit="false">';
    }
    else {
      $form['value']['#disabled'] = FALSE;
      $form['value']['#prefix'] = '<div class="' . $template . '-rating-wrapper" can-edit="true">';
    }
    if (!isset($results['result_type']) || $results['result_type'] == '0') {
      $form['value']['#attributes']['data-show-own-vote'] = 'false';
      $form['value']['#default_value'] = $this->getResults($result_function);
    }
    else {
      $form['value']['#attributes']['data-show-own-vote'] = 'true';
      $form['value']['#default_value'] = (int) $entity->getValue();
    }

    // Get the results container.
    if (isset($results['result_position']) && $results['result_position'] !== 'hidden') {
      $form['result'] = [
        '#theme' => 'container',
        '#attributes' => [
          'class' => ['vote-result'],
        ],
        '#children' => [],
        '#weight' => 100,
      ];
      $form['result']['#children']['result'] = $plugin->getVoteSummary($entity);
    }

    // The form submit button.
    $form['submit'] = $form['actions']['submit'];
    $form['actions']['#access'] = FALSE;

    $form['submit'] += [
      '#type' => 'button',
      '#attributes' => [
        'class' => [$template . '-rating-submit'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxSubmit'],
        'event' => 'click',
        'wrapper' => $form_id,
        'progress' => [
          'type' => NULL,
        ],
      ],
    ];

    // Base widget template. Can create additional twig templates.
    $form['#theme'] = 'rate_widget';
    $form['#rate_widget'] = $rate_widget;
    $form['#widget_template'] = $template;
    $form['#display_settings'] = $display;
    $form['#results_settings'] = $results;
    $form['#results'] = $plugin->getVoteSummary($entity);
    return $form;
  }

  /**
   * Get result function.
   */
  protected function getResultFunction(FormStateInterface $form_state) {
    $entity = $this->getEntity();
    return ($form_state->get('resultfunction')) ? $form_state->get('resultfunction') : 'rate_average:' . $entity->getVotedEntityType() . '.' . $entity->bundle() . '.' . $entity->rate_widget->value;
  }

  /**
   * Get results.
   */
  public function getResults($result_function = FALSE, $reset = FALSE) {
    $entity = $this->entity;
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
   * Ajax submit handler.
   */
  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    $this->save($form, $form_state);

    $entity = $this->getEntity();
    $settings = $form_state->get('settings');
    $options = $form_state->get('options');
    $display = $settings->get('display');
    $results = $settings->get('results');
    $plugin = $form_state->get('plugin');
    $result_function = $this->getResultFunction($form_state);
    $result_value = $this->getResults($result_function, TRUE);
    $template = $settings->get('template');
    $rate_widget = $form_state->get('rate_widget');

    $voted_entity_id = $entity->getVotedEntityId();
    $user_input = $form_state->getUserInput()['value'];

    if (isset($results['result_position']) && $results['result_position'] !== 'hidden') {
      $form['result']['#children']['result'] = $plugin->getVoteSummary($entity);
      $form['#results'] = $plugin->getVoteSummary($entity);
    }

    if (isset($template) && $template != 'fivestar') {
      $vote_type = $entity->bundle();
      $votes = [];
      $votes = $plugin->getVotes($form_state->get('entity_type'), $form_state->get('entity_bundle'), $form_state->get('entity_id'), $vote_type, $rate_widget);
      foreach ($options as $key => $option) {
        if ($template == 'numberupdown') {
          if ($key > 0) {
            $option_result_function = 'rate_sum:' . $form_state->get('entity_type') . '.' . $form_state->get('entity_bundle') . '.' . $entity->rate_widget->value;
            $option_result = $this->getResults($option_result_function);
            $form['value'][$key]['#option_result'] = (isset($option_result) && is_string($option_result)) ? $option_result : 0;
          }
        }
        else {
          $vote_sum[$key] = isset($votes[$key]) ? array_sum($votes[$key]) : 0;
          $form['value'][$key]['#option_result'] = ($vote_sum[$key] < 0) ? ($vote_sum[$key]) * -1 : $vote_sum[$key];
        }
      }
    }

    $message = 'Vote ' . $entity->id() . ' saved. Voted ' . $user_input . ' on ' . $voted_entity_id . '.';
    \Drupal::logger('rate')->notice($message);
    if (!$plugin->canVote($entity)) {
      $form['value']['#disabled'] = TRUE;
      $form['value']['#prefix'] = '<div class="' . $template . '-rating-wrapper" can-edit="false">';
    }
    $form_state->setRebuild(TRUE);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $plugin = $form_state->get('plugin');

    if ($plugin->canVote($entity)) {
      return parent::save($form, $form_state);
    }
    return FALSE;
  }

}
