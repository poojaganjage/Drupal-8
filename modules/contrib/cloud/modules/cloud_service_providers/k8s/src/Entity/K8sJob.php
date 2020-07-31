<?php

namespace Drupal\k8s\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Job entity.
 *
 * @ingroup k8s
 *
 * @ContentEntityType(
 *   id = "k8s_job",
 *   id_plural = "k8s_jobs",
 *   label = @Translation("Job"),
 *   label_collection = @Translation("Jobs"),
 *   label_singular = @Translation("Job"),
 *   label_plural = @Translation("Jobs"),
 *   handlers = {
 *     "view_builder" = "Drupal\k8s\Entity\K8sJobViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\k8s\Entity\K8sJobViewsData",
 *     "access"       = "Drupal\k8s\Controller\K8sJobAccessControlHandler",
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
 *   base_table = "k8s_job",
 *   admin_permission = "administer k8s job",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/k8s/{cloud_context}/job/{k8s_job}",
 *     "collection"           = "/clouds/k8s/{cloud_context}/job",
 *     "add-form"             = "/clouds/k8s/{cloud_context}/job/add",
 *     "edit-form"            = "/clouds/k8s/{cloud_context}/job/{k8s_job}/edit",
 *     "delete-form"          = "/clouds/k8s/{cloud_context}/job/{k8s_job}/delete",
 *     "delete-multiple-form" = "/clouds/k8s/{cloud_context}/job/delete_multiple",
 *   },
 *   field_ui_base_route = "k8s_job.settings"
 * )
 */
class K8sJob extends K8sEntityBase implements K8sJobInterface {

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
  public function getImage() {
    return $this->get('image');
  }

  /**
   * {@inheritdoc}
   */
  public function setImage($image) {
    return $this->set('image', $image);
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletions() {
    return $this->get('completions');
  }

  /**
   * {@inheritdoc}
   */
  public function setCompletions($completions) {
    return $this->set('completions', $completions);
  }

  /**
   * {@inheritdoc}
   */
  public function getParallelism() {
    return $this->get('parallelism');
  }

  /**
   * {@inheritdoc}
   */
  public function setParallelism($parallelism) {
    return $this->set('parallelism', $parallelism);
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
  public function getFailed() {
    return $this->get('failed');
  }

  /**
   * {@inheritdoc}
   */
  public function setFailed($failed) {
    return $this->set('failed', $failed);
  }

  /**
   * {@inheritdoc}
   */
  public function getSucceeded() {
    return $this->get('succeeded');
  }

  /**
   * {@inheritdoc}
   */
  public function setSucceeded($succeeded) {
    return $this->set('succeeded', $succeeded);
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
      ->setDescription(t('The namespace of job.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['image'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Image'))
      ->setDescription(t('Docker image name.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['completions'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Completions'))
      ->setDescription(t('Specifies the desired number of successfully finished pods the job should be run with.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['parallelism'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Parallelism'))
      ->setDescription(t('Specifies the maximum desired number of pods the job should run at any given time.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['active'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Active'))
      ->setDescription(t('The number of actively running pods.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['failed'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Failed'))
      ->setDescription(t('The number of pods which reached phase Failed.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['succeeded'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Succeeded'))
      ->setDescription(t('The number of pods which reached phase Succeeded.'))
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
