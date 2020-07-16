<?php

namespace Drupal\flood_unblock;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class FloodUnblockManager implements FloodUnblockManagerInterface {

  use StringTranslationTrait;

  /**
   * The Database Connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * FloodUnblockAdminForm constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(Connection $database, FloodInterface $flood, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger) {
    $this->database = $database;
    $this->flood = $flood;
    $this->entityTypeManager = $entityTypeManager;
    $this->config = $configFactory->get('user.flood');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntries() {
    $entries = [];

    if ($this->database->schema()->tableExists('flood')) {
      $query = $this->database->select('flood', 'f');
      $query->addField('f', 'identifier');
      $query->addField('f', 'event');
      $query->addExpression('count(*)', 'count');
      $query->groupBy('identifier');
      $query->groupBy('event');
      $results = $query->execute();

      foreach ($results as $result) {

        // Sets defaults for the entries. These will be overridden further down
        // the line.
        $ip = $result->identifier;
        $entries[$result->identifier] = [
          'event' => $result->event,
          'type' => NULL,
          'ip' => $ip,
          'count' => $result->count,
          'location' => NULL,
          'blocked' => FALSE,
          'uid' => NULL,
          'username' => NULL,
        ];

        // Extracts IP and user from the identifier.
        $parts = explode('-', $result->identifier);
        if (isset($parts[0]) && isset($parts[1])) {
          $result->uid = $parts[0];
          $ip = $parts[1] ?? NULL;
          $entries[$result->identifier]['ip'] = $ip;

          /** @var \Drupal\user\Entity\User $user */
          $user = $this->entityTypeManager->getStorage('user')
            ->load($result->uid);
          if (isset($user)) {
            $user_link = $user->toLink($user->getAccountName());
          }
          else {
            $user_link = $this->t('Deleted user: @user', ['@user' => $result->uid]);
          }
          $entries[$result->identifier]['username'] = $user_link ?? NULL;
        }

        // Fetches location string and assigns to entries.
        if (function_exists('smart_ip_get_location')) {
          $location = smart_ip_get_location($ip);
          $location_string = sprintf(" (%s %s %s)", $location['city'], $location['region'], $location['country_code']);
        }
        else {
          $location_string = '';
        }
        $entries[$result->identifier]['location'] = $location_string;

        // Fetches blocking status.
        switch ($result->event) {
          case 'user.failed_login_ip':
            $blocked = !$this->flood->isAllowed('user.failed_login_ip', $this->config->get('ip_limit'), $this->config->get('ip_window'), $ip);
            $entries[$result->identifier]['type'] = 'ip';
            break;

          case 'user.failed_login_user':
            $blocked = !$this->flood->isAllowed('user.failed_login_user', $this->config->get('user_limit'), $this->config->get('user_window'), $result->identifier);
            $entries[$result->identifier]['type'] = 'user';
            break;

          case 'user.http_login':
            $blocked = !$this->flood->isAllowed('user.http_login', $this->config->get('user_limit'), $this->config->get('user_window'), $result->identifier);
            $entries[$result->identifier]['type'] = 'user';
            break;
        }
        $entries[$result->identifier]['blocked'] = $blocked;
      }
    }

    return $entries;
  }

  /**
   * {@inheritdoc}
   */
  public function flood_unblock_clear_event($event, $identifier) {
    $txn = $this->database->startTransaction('flood_unblock_clear');
    try {
      $query = $this->database->delete('flood')
        ->condition('event', $event);
      if (isset($identifier)) {
        $query->condition('identifier', $identifier);
      }
      $success = $query->execute();
      if ($success) {
        \Drupal::messenger()->addMessage($this->t('Flood entries cleared.'), 'status', FALSE);
      }
    } catch (\Exception $e) {
      // Something went wrong somewhere, so roll back now.
      $txn->rollback();
      // Log the exception to watchdog.
      watchdog_exception('type', $e);
      \Drupal::messenger()->addMessage($this->t('Error: @error', ['@error' => (string) $e]), 'error');
    }
  }
}
