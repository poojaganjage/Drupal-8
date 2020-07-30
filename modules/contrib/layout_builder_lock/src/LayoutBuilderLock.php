<?php

namespace Drupal\layout_builder_lock;

use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\layout_builder\OverridesSectionStorageInterface;

class LayoutBuilderLock implements TrustedCallbackInterface {

  const NO_LOCK = [];
  const LOCKED_BLOCK_UPDATE = 1;
  const LOCKED_BLOCK_DELETE = 2;
  const LOCKED_BLOCK_MOVE = 3;
  const LOCKED_BLOCK_ADD = 4;
  const LOCKED_SECTION_CONFIGURE = 5;
  const LOCKED_SECTION_BEFORE = 6;
  const LOCKED_SECTION_AFTER = 7;
  const LOCKED_SECTION_BLOCK_MOVE = 8;

  /**
   * Returns whether the current user has manage lock settings permission.
   *
   * @return bool
   */
  protected static function hasManageLockPermission() {
    return \Drupal::currentUser()->hasPermission('manage lock settings on sections');
  }

  /**
   * Applies lock settings to the Layout Builder element.
   *
   * @param array $element
   *   The Layout Builder render element.
   *
   * @return array
   *   The modified Layout Builder render element.
   */
  public static function preRender(array $element) {

    // Users who can manage the lock sections can do everything.
    if (self::hasManageLockPermission()) {
      return $element;
    }

    $element['#attached']['library'][] = 'layout_builder_lock/edit';

    // Get the section storage. In case of overrides, get it from default.
    $overridden = FALSE;
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $element['#section_storage'];
    if ($section_storage instanceof OverridesSectionStorageInterface) {
      $overridden = TRUE;
      $section_storage = $section_storage->getDefaultSectionStorage();
    }

    $section_number = 0;
    for ($i = 0; $i < $section_storage->count(); $i++) {

      // Get settings, continue if empty.
      $settings = [];
      try {
        $settings = array_filter($section_storage->getSection($i)->getThirdPartySetting('layout_builder_lock', 'lock', self::NO_LOCK));
      }
      catch (\OutOfBoundsException $ignored) {}
      if (empty($settings)) {
        continue;
      }

      $default_components = [];
      if ($overridden) {
        try {
          $default_components = $section_storage->getSection($i)->getComponents();
        }
        catch (\OutOfBoundsException $ignored) {}
      }

      // The element key in the build starts at $i + 1, after that it's 2 on
      // because of the add section links.
      if ($i == 0) {
        $section_number = $i + 1;
      }
      elseif ($i == 1) {
        $section_number = $i + 2;
      }
      else {
        $section_number += 2;
      }

      // Ignore non existing section numbers.
      if (!isset($element['layout_builder'][$section_number])) {
        continue;
      }

      foreach ($element['layout_builder'][$section_number] as $name => $item) {

        // Add new section link before.
        if (isset($settings[self::LOCKED_SECTION_BEFORE])) {
          unset($element['layout_builder'][$section_number - 1]);
        }

        // Add new section link after.
        if (isset($settings[self::LOCKED_SECTION_AFTER])) {
          unset($element['layout_builder'][$section_number + 1]);
        }

        // Remove or configure section links.
        if (isset($item['#url'])) {

          // Sections can not be deleted at all.
          if ($name == 'remove') {
            unset($element['layout_builder'][$section_number][$name]);
          }

          // Configure section link.
          if ($name == 'configure' && isset($settings[self::LOCKED_SECTION_CONFIGURE])) {
            unset($element['layout_builder'][$section_number][$name]);
          }

        }

        // Layout configuration: this contains all regions with their blocks and
        // the link to add new blocks.
        elseif (isset($item['#layout'])) {
          foreach (Element::children($item) as $region_key) {

            // Track if we have non default blocks in this section.
            $has_custom_block = FALSE;

            foreach (Element::children($item[$region_key]) as $item_key) {
              if ($item_key == 'layout_builder_add_block') {
                if (isset($settings[self::LOCKED_BLOCK_ADD])) {
                  unset($element['layout_builder'][$section_number][$name][$region_key][$item_key]);
                }
              }
              elseif (isset($item[$region_key][$item_key]['#contextual_links'])) {

                // Do not apply the block operation lock settings to blocks that
                // were added in the override. Also set the custom block
                // variable to TRUE so we don't remove the region classes for
                // this section.
                if ($overridden && !isset($default_components[$item_key])) {
                  $has_custom_block = TRUE;
                  continue;
                }

                $allow_moving = TRUE;
                $layout_builder_remove_block_operations = [];
                $block_operations = ['move', 'update', 'remove'];

                // Do not allow moving blocks.
                if (isset($settings[self::LOCKED_BLOCK_MOVE])) {
                  $allow_moving = FALSE;
                  unset($block_operations[array_search('move', $block_operations)]);
                  $layout_builder_remove_block_operations[] = 'layout_builder_block_move';
                }

                // Do not allow updating blocks.
                if (isset($settings[self::LOCKED_BLOCK_UPDATE])) {
                  unset($block_operations[array_search('update', $block_operations)]);
                  $layout_builder_remove_block_operations[] = 'layout_builder_block_update';
                }

                // Do not allow deleting blocks.
                if (isset($settings[self::LOCKED_BLOCK_DELETE])) {
                  unset($block_operations[array_search('remove', $block_operations)]);
                  $layout_builder_remove_block_operations[] = 'layout_builder_block_remove';
                }

                // Alter the default operations in case the count of operations
                // is not equal to 3. Empty is fine too, which means the user
                // won't be able to do anything at all with this block.
                if (count($block_operations) != 3) {
                  $element['layout_builder'][$section_number][$name][$region_key][$item_key]['#contextual_links']['layout_builder_block']['metadata']['operations'] = implode(':', $block_operations);
                }

                // Add the operations to remove in a custom metadata element so
                // we can remove the contextual link later.
                // @see layout_builder_lock_contextual_links_view_alter().
                if (!empty($layout_builder_remove_block_operations)) {
                  $element['layout_builder'][$section_number][$name][$region_key][$item_key]['#contextual_links']['layout_builder_block']['metadata']['layout_builder_lock'] = implode(':', $layout_builder_remove_block_operations);
                }

                // If moving is not allowed, remove the default layout builder
                // classes on this block and add our own so we can reset the
                // pointer and padding.
                if (!$allow_moving) {
                  foreach (['layout-builder-block', 'js-layout-builder-block'] as $class) {
                    $key = array_search($class, $element['layout_builder'][$section_number][$name][$region_key][$item_key]['#attributes']['class']);
                    unset($element['layout_builder'][$section_number][$name][$region_key][$item_key]['#attributes']['class'][$key]);
                  }
                  $element['layout_builder'][$section_number][$name][$region_key][$item_key]['#attributes']['class'][] = 'layout-builder-block-locked';
                }
              }
            }

            // Do not allow to move blocks into this section.
            if (isset($settings[self::LOCKED_SECTION_BLOCK_MOVE]) && !$has_custom_block) {
              $key = array_search('js-layout-builder-region', $element['layout_builder'][$section_number][$name][$region_key]['#attributes']['class']);
              unset($element['layout_builder'][$section_number][$name][$region_key]['#attributes']['class'][$key]);
            }

          }
        }
      }
    }

    return $element;
  }

  /**
   * @inheritDoc
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

}
