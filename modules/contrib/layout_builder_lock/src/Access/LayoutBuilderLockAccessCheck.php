<?php

namespace Drupal\layout_builder_lock\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout_builder\DefaultsSectionStorageInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder_lock\LayoutBuilderLock;
use Symfony\Component\Routing\Route;

/**
 * Layout Builder Lock access check.
 *
 * @ingroup layout_builder_access
 */
class LayoutBuilderLockAccessCheck implements AccessInterface {

  /**
   * Checks routing access to the layout using lock settings.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(SectionStorageInterface $section_storage, AccountInterface $account, Route $route) {
    $operation = $route->getRequirement('_layout_builder_lock_access');

    // Use default access check in case this is a default section or if the user
    // has permission to manage lock settings.
    if ($section_storage instanceof DefaultsSectionStorageInterface || $account->hasPermission('manage lock settings on sections')) {
      return new AccessResultAllowed();
    }

    // Get settings from the default section.
    /** @var \Drupal\layout_builder\SectionStorageInterface $sectionStorage */
    $sectionStorage = $section_storage->getDefaultSectionStorage();

    // Get delta from route params.
    $delta = \Drupal::routeMatch()->getRawParameter('delta');
    if (!isset($delta)) {
      // This shouldn't happen normally, but you never know.
      return new AccessResultAllowed();
    }

    // Default settings.
    $check_before_and_after = FALSE;
    $lock_settings = $lock_settings_before = $lock_settings_after = [];

    // The add section operation is a bit more complex when delta is not 0.
    // In case of a higher number, we need to get any lock settings from the
    // section before and after.
    if ($operation == 'section_add' && $delta > 0) {

      $check_before_and_after = TRUE;

      // Ignore in case the next section doesn't exist at all.
      try {
        $lock_settings_before = array_filter($sectionStorage
          ->getSection($delta - 1)
          ->getThirdPartySetting('layout_builder_lock', 'lock', LayoutBuilderLock::NO_LOCK));
      }
      catch (\OutOfBoundsException $ignored) {}

      // Ignore in case the next section doesn't exist at all.
      try {
        $lock_settings_after = array_filter($sectionStorage
          ->getSection($delta + 1)
          ->getThirdPartySetting('layout_builder_lock', 'lock', LayoutBuilderLock::NO_LOCK));
      }
      catch (\OutOfBoundsException $ignored) {}
    }
    else {

      try {
        $lock_settings = array_filter($sectionStorage
        ->getSection($delta)
        ->getThirdPartySetting('layout_builder_lock', 'lock', LayoutBuilderLock::NO_LOCK));
      }
      catch (\OutOfBoundsException $ignored) {}

      // Use default access in case the settings are empty.
      if (empty($lock_settings)) {
        return new AccessResultAllowed();
      }
    }

    // Get default components.
    $default_components = [];
    try {
      $default_components = $sectionStorage->getSection($delta)->getComponents();
    }
    catch (\OutOfBoundsException $ignored) {}

    // Section storage access.
    $access = $section_storage->access($operation, $account, TRUE);

    switch ($operation) {
      case 'block_add':
        if (isset($lock_settings[LayoutBuilderLock::LOCKED_BLOCK_ADD])) {
          return new AccessResultForbidden();
        }
        break;

      case 'block_config':
        if (isset($lock_settings[LayoutBuilderLock::LOCKED_BLOCK_UPDATE])) {
          $access = new AccessResultForbidden();
        }
        break;

      case 'block_remove':
        $uuid = \Drupal::routeMatch()->getRawParameter('uuid');
        if (isset($lock_settings[LayoutBuilderLock::LOCKED_BLOCK_DELETE]) && isset($default_components[$uuid])) {
          $access = new AccessResultForbidden();
        }
        break;

      case 'block_reorder':
        $uuid = \Drupal::routeMatch()->getRawParameter('uuid');
        if (isset($lock_settings[LayoutBuilderLock::LOCKED_BLOCK_MOVE]) && isset($default_components[$uuid])) {
          $access = new AccessResultForbidden();
        }

        if (isset($lock_settings[LayoutBuilderLock::LOCKED_SECTION_BLOCK_MOVE])) {
          try {
            if (count($sectionStorage->getSection($delta)->getComponents()) == count($default_components)) {
              $access = new AccessResultForbidden();
            }
          }
          catch (\OutOfBoundsException $ignored) {}
        }

        break;

      case 'section_add':
        if ($check_before_and_after) {
          if (isset($lock_settings_before[LayoutBuilderLock::LOCKED_SECTION_AFTER]) || isset($lock_settings_after[LayoutBuilderLock::LOCKED_SECTION_BEFORE]) || isset($lock_settings_after[LayoutBuilderLock::LOCKED_SECTION_BEFORE])) {
            $access = new AccessResultForbidden();
          }
        }
        else {
          // This only needs the before check since the delta is 0.
          if (isset($lock_settings[LayoutBuilderLock::LOCKED_SECTION_BEFORE])) {
            $access = new AccessResultForbidden();
          }
        }
        break;

      case 'section_edit':
        if (isset($lock_settings[LayoutBuilderLock::LOCKED_SECTION_CONFIGURE])) {
          $access = new AccessResultForbidden();
        }
        break;

      case 'section_remove':
        // There are settings, so removing is forbidden.
        $access = new AccessResultForbidden();
        break;
    }

    if ($access instanceof RefinableCacheableDependencyInterface) {
      $access->addCacheableDependency($section_storage);
    }

    return $access;
  }

}
