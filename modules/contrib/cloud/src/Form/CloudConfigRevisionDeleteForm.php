<?php

namespace Drupal\cloud\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a cloud service provider revision.
 *
 * @ingroup cloud
 */
class CloudConfigRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The cloud service provider (CloudConfig) revision.
   *
   * @var \Drupal\cloud\Entity\CloudConfigInterface
   */
  protected $revision;

  /**
   * The cloud service provider (CloudConfig) storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $cloudConfigStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * The mocked date formatter class.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a new CloudConfigRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $entity_storage,
                              Connection $connection,
                              Messenger $messenger,
                              DateFormatterInterface $date_formatter) {
    $this->cloudConfigStorage = $entity_storage;
    $this->connection = $connection;
    $this->messenger = $messenger;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('cloud_config'),
      $container->get('database'),
      $container->get('messenger'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cloud_config_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.cloud_config.version_history', ['cloud_config' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_config_revision = NULL) {
    $this->revision = $this->cloudConfigStorage->loadRevision($cloud_config_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->cloudConfigStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('cloud_config')->notice($this->t('Cloud service provider: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]));
    $this->messenger->addStatus($this->t('Revision from %revision-date of cloud service provide %title has been deleted.', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.cloud_config.canonical',
       ['cloud_config' => $this->revision->id()]
    );
    try {
      if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {cloud_config_field_revision} WHERE id = :id', [':id' => $this->revision->id()])
        ->fetchField() > 1) {
        $form_state->setRedirect(
          'entity.cloud_config.version_history',
          ['cloud_config' => $this->revision->id()]
        );
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }
  }

}
