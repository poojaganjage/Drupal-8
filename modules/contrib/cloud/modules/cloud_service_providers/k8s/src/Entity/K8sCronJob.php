<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Cron Job entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_cron_job",
 *   id_plural = "k8s_cron_jobs",
 *   label = @Translation("Cron Job"),
 *   label_collection = @Translation("Cron Jobs"),
 *   label_singular = @Translation("Cron Job"),
 *   label_plural = @Translation("Cron Jobs"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sCronJobViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sCronJobViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sCronJobAccessControlHandler",
 *     "form" = {
 *       "add"        = "Drupal\k8s\Form\K8sCreateForm",
 *       "edit"       = "Drupal\k8s\Form\K8sEditForm",
 *       "delete"     = "Drupal\k8s\Form\K8sDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\k8s\Form\K8sDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "k8s_cron_job",
 *   admin_permission = "administer k8s cron job",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/cron_job/{k8s_cron_job}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/cron_job",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/cron_job/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/cron_job/{k8s_cron_job}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/cron_job/{k8s_cron_job}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/cron_job/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_cron_job.settings"
 * )
 */
class K8sCronJob extends K8sEntityBase implements K8sCronJobInterface {

  /**
   * {@inheritdoc}
   */
  public function getNamespace() {
    return $this->get('namespace')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNamespace($namespace) {
    return $this->set('namespace', $namespace);
  }

  /**
   * {@inheritdoc}
   */
  public function getSchedule() {
    return $this->get('schedule');
  }

  /**
   * {@inheritdoc}
   */
  public function setSchedule($schedule) {
    return $this->set('schedule', $schedule);
  }

  /**
   * {@inheritdoc}
   */
  public function getActive() {
    return $this->get('active');
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    return $this->set('active', $active);
  }

  /**
   * {@inheritdoc}
   */
  public function isSuspend() {
    return $this->get('suspend');
  }

  /**
   * {@inheritdoc}
   */
  public function setSuspend($suspend) {
    return $this->set('suspend', $suspend);
  }

  /**
   * {@inheritdoc}
   */
  public function getLastScheduleTime() {
    return $this->get('last_schedule_time');
  }

  /**
   * {@inheritdoc}
   */
  public function setLastScheduleTime($last_schedule_time) {
    return $this->set('last_schedule_time', $last_schedule_time);
  }

  /**
   * {@inheritdoc}
   */
  public function getConcurrencyPolicy() {
    return $this->get('concurrency_policy');
  }

  /**
   * {@inheritdoc}
   */
  public function setConcurrencyPolicy($concurrency_policy) {
    return $this->set('concurrency_policy', $concurrency_policy);
  }

  /**
   * {@inheritdoc}
   */
  public function getStartingDeadlineSeconds() {
    return $this->get('starting_deadline_seconds');
  }

  /**
   * {@inheritdoc}
   */
  public function setStartingDeadlineSeconds($starting_deadline_seconds) {
    return $this->set('starting_deadline_seconds', $starting_deadline_seconds);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreationYaml() {
    return $this->get('creation_yaml')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreationYaml($creation_yaml) {
    return $this->set('creation_yaml', $creation_yaml);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = K8sEntityBase::baseFieldDefinitions($entity_type);

    $fields['namespace'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Namespace'))
      ->setDescription(t('The namespace of cron job.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['schedule'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Schedule'))
      ->setDescription(t('The schedule in Cron format.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['active'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Active'))
      ->setDescription(t('The number of currently running jobs.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['suspend'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Suspend'))
      ->setDescription(t('This flag tells the controller to suspend subsequent executions, it does not apply to already started executions. Defaults to false.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean',
        'weight' => -5,
        'settings' => [
          'format' => 'true-false',
        ],
      ]);

    $fields['last_schedule_time'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Last Schedule Time'))
      ->setDescription('Information when was the last time the job was successfully scheduled.')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => -5,
      ]);

    $fields['concurrency_policy'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Concurrency Policy'))
      ->setDescription(t('Specifies how to treat concurrent executions of a Job.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['starting_deadline_seconds'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Starting Deadline Seconds'))
      ->setDescription(t('Optional deadline in seconds for starting the job if it misses scheduled time for any reason.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['creation_yaml'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Creation YAML'))
      ->setDescription(t('The YAML content was used to create the entity.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'pre_string_formatter',
        'weight' => -5,
      ]);

    return $fields;
  }

}
