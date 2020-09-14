<?php

namespace Drupal\smart_content\Controller;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Controller\ControllerBase;
use Drupal\smart_content\Cache\CacheableAjaxResponse;
use Drupal\smart_content\Decision\Storage\DecisionStorageInterface;

/**
 * Class ReactionController.
 */
class ReactionController extends ControllerBase {

  /**
   * Get the reaction response.
   *
   * @param \Drupal\smart_content\Decision\Storage\DecisionStorageInterface $decision_storage
   *   The decision storage plugin.
   * @param string $token
   *   The token of the decision instance.
   * @param string $reaction
   *   The reaction plugin id.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return the ajax response.
   */
  public function getReactionResponse(DecisionStorageInterface $decision_storage, $token, $reaction) {
    if (Uuid::isValid($token) && Uuid::isValid($reaction)) {
      $decision_storage->loadDecisionFromToken($token);
      if ($decision = $decision_storage->getDecision()) {
        // todo: Add access check.
        if ($decision->hasReaction($reaction)) {
          $reaction = $decision->getReaction($reaction);
          $response = $reaction->getResponse($decision);
          return $response;
        }
      }
    }
    return new CacheableAjaxResponse();
  }

}
