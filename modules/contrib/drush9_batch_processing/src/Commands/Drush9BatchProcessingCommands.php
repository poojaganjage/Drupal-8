<?php

namespace Drupal\drush9_batch_processing\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
// use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class Drush9BatchProcessingCommands extends DrushCommands {

  // use StringTranslationTrait;

  /**
   * Entity type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerChannelFactory;

  /**
   * Constructs a new UpdateVideosStatsController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory) {
    parent::__construct();
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Update Node.
   *
   * @param string $type
   *   Type of node to update
   *   Argument provided to the drush command.
   *
   * @command update:node
   * @aliases update-node
   *
   * @usage update:node foo
   *   foo is the type of node to update
   */
  public function updateNode($type = '') {
    // 1. Log the start of the script.
    $this->loggerChannelFactory->get('drush9_batch_processing')->info(t('Update nodes batch operations start'));
    // Check the type of node given as argument, if not, set article as default.
    if (strlen($type) == 0) {
      $type = 'article';
    }
    // 2. Retrieve all nodes of this type.
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery()
        ->condition('type', $type)
        ->condition('status', '1');
      $nids = $query->execute();
    }
    catch (\Exception $e) {
      $this->output()->writeln($e);
      $this->loggerChannelFactory->get('drush9_batch_processing')->warning(t('Error found @e', ['@e' => $e,]));
    }
    // 3. Create the operations array for the batch.
    $operations = [];
    $numOperations = 0;
    $batchId = 1;
    if (!empty($nids)) {
      foreach ($nids as $nid) {
        // Prepare the operation. Here we could do other operations on nodes.
        $this->output()->writeln(t('Preparing batch: ') . $batchId);
        $operations[] = [
          '\Drupal\drush9_batch_processing\BatchService::processMyNode', [
            $batchId,
            t('Updating node @nid', ['@nid' => $nid,]),
          ],
        ];
        $batchId++;
        $numOperations++;
      }
    }
    else {
      $this->logger()->warning(t('No nodes of this type @type', ['@type' => $type,]));
    }
    // 4. Create the batch.
    $batch = [
      'title' => t('Updating @num node(s)', ['@num' => $numOperations,]),
      'operations' => $operations,
      'finished' => '\Drupal\drush9_batch_processing\BatchService::processMyNodeFinished',
    ];
    // 5. Add batch operations as new batch sets.
    batch_set($batch);
    // 6. Process the batch sets.
    drush_backend_batch_process();
    // 6. Show some information.
    $this->logger()->notice(t('Batch operations end.'));
    // 7. Log some information.
    $this->loggerChannelFactory->get('drush9_batch_processing')->info(t('Update batch operations end.'));
  }

}
