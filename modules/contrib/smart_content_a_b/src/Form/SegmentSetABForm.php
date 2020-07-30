<?php

namespace Drupal\smart_content_a_b\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_content\Segment;
use Drupal\smart_content\SegmentSet;

/**
 * Class SegmentSetABForm.
 */
class SegmentSetABForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $smart_content_a_b = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $smart_content_a_b->label(),
      '#description' => $this->t("Label for the Segment Set A/B."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $smart_content_a_b->id(),
      '#machine_name' => [
        'exists' => '\Drupal\smart_content_a_b\Entity\SegmentSetAB::load',
      ],
      '#disabled' => !$smart_content_a_b->isNew(),
    ];

    if($this->entity->isNew()) {
      $form['count'] = [
        '#title' => 'Number of variations',
        '#type' => 'number',
        '#min' => 2,
        '#max' => 100,
        '#default_value' => 2,
        '#required' => TRUE,
      ];
    }
    else {
      $count = count($this->entity->getSegmentSet()->getSegments());
      $first_letter = 'a';
      $last_letter = $first_letter;
      for ($i = 0; $i < (int) $count - 1; $i++) {
        $last_letter++;
      }
      $first_letter = strtoupper($first_letter);
      $last_letter = strtoupper($last_letter);
      $form['count'] = [
        '#type' => 'markup',
        '#markup' => "Number of variations: $count [$first_letter - $last_letter]",
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    if($this->entity->isNew()) {
      $letter = 'a';
      $segment_set = $this->entity->getSegmentSet();
      for ($i = 0; $i < (int) $values['count']; $i++) {
        $segment = Segment::fromArray();
        $condition_configuration = [
          'entity_id' => $values['id'],
          'letter' => $letter
        ];
        $condition = \Drupal::service('plugin.manager.smart_content.condition')
          ->createInstance('smart_content_a_b_value', $condition_configuration);
        $segment->appendCondition($condition);
        $segment_set->setSegment($segment);
        $letter++;
      }
    }

    $smart_content_a_b = $this->entity;
    $status = $smart_content_a_b->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Segment Set A/B.', [
          '%label' => $smart_content_a_b->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Segment Set A/B.', [
          '%label' => $smart_content_a_b->label(),
        ]));
    }
    $form_state->setRedirectUrl($smart_content_a_b->toUrl('collection'));
  }

}
