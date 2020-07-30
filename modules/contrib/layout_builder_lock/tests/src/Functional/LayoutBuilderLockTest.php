<?php

namespace Drupal\Tests\layout_builder_lock\Functional;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\layout_builder_lock\LayoutBuilderLock;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\WebAssert;

/**
 * Tests Layout Builder Lock.
 *
 * @group layout_builder_lock
 */
class LayoutBuilderLockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'layout_builder_lock',
    'node',
    'user',
  ];

  /**
   * The body field uuid.
   *
   * @var string
   */
  protected $body_field_block_uuid;

  /**
   * The default theme to use.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with all permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A user with editor permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $editor;

  /**
   * The editor permissions.
   *
   * @var array
   */
  protected $editorPermissions = [
    'bypass node access',
    'configure any layout',
    'create and edit custom blocks',
    'access contextual links',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable Layout Builder for landing page.
    $this->createContentType(['type' => 'landing_page']);
    LayoutBuilderEntityViewDisplay::load('node.landing_page.default')
      ->enableLayoutBuilder()
      ->setOverridable()
      ->save();

    try {
      $this->adminUser = $this->createUser([], 'administrator', TRUE);
    } catch (EntityStorageException $ignored) {}
    try {
      $this->editor = $this->createUser($this->editorPermissions, 'editor');
    } catch (EntityStorageException $ignored) {}
  }

  /**
   * Tests locking features on sections.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testLock() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Create a node.
    $node = $this->drupalCreateNode(['type' => 'landing_page', 'title' => 'Homepage']);

    // Check as editor.
    $this->drupalLogin($this->editor);
    $this->drupalGet('node/' . $node->id() . '/layout');

    // Get the block uuid from the body field.
    $id = $assert_session->elementExists('css', '.layout-builder__region > div:nth-child(3)');
    $this->body_field_block_uuid = $id->getAttribute('data-layout-block-uuid');

    $assert_session->linkExists('Add block');
    $assert_session->linkExists('Add section');
    $assert_session->linkExists('Remove Section 1');
    $assert_session->linkExists('Configure Section 1');
    $assert_session->responseContains('js-layout-builder-block');
    $assert_session->responseContains('js-layout-builder-region');
    $this->checkContextualLinks($assert_session);
    $this->checkRouteAccess($assert_session, $node);

    $this->drupalLogin($this->adminUser);

    // Try to add a new section.
    $this->drupalGet('layout_builder/configure/section/defaults/node.landing_page.default/1/layout_onecol');
    $assert_session->statusCodeEquals(200);
    $assert_session->responseContains('Locks can be configured when the section has been added.');
    $this->drupalPostForm(NULL, [], 'Add section');
    $assert_session->statusCodeEquals(200);

    // Configure the section locks.
    $this->drupalGet('/layout_builder/configure/section/defaults/node.landing_page.default/0');

    $edit = [];
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_BLOCK_ADD . ']'] = TRUE;
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_BLOCK_MOVE . ']'] = TRUE;
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_BLOCK_UPDATE . ']'] = TRUE;
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_BLOCK_DELETE . ']'] = TRUE;
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_SECTION_BEFORE . ']'] = TRUE;
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_SECTION_BLOCK_MOVE . ']'] = TRUE;
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_SECTION_AFTER . ']'] = TRUE;
    $edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_SECTION_CONFIGURE . ']'] = TRUE;
    $this->drupalPostForm(NULL, $edit, 'Update');
    $page->pressButton('Save layout');

    // Check as editor.
    $this->drupalLogin($this->editor);
    $this->drupalGet('node/' . $node->id() . '/layout');

    $assert_session->linkNotExists('Add block');
    $assert_session->linkNotExists('Add section');
    $assert_session->linkNotExists('Remove Section 1');
    $assert_session->linkNotExists('Configure Section 1');
    $assert_session->responseNotContains('js-layout-builder-block');
    $assert_session->responseContains('layout-builder-block-locked');
    $assert_session->responseNotContains('js-layout-builder-region');
    $this->checkContextualLinks($assert_session, TRUE);
    $this->checkRouteAccess($assert_session, $node, 403);
  }

  /**
   * Tests with at least 3 sections.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMultipleSections() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Add two more sections.
    $this->drupalLogin($this->adminUser);

    $lock_edit = [];
    $lock_edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_SECTION_BEFORE . ']'] = TRUE;
    $lock_edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_SECTION_AFTER . ']'] = TRUE;
    $lock_edit['layout_builder_lock[' . LayoutBuilderLock::LOCKED_SECTION_CONFIGURE . ']'] = TRUE;

    $this->drupalGet('layout_builder/configure/section/defaults/node.landing_page.default/0/layout_onecol');
    $this->drupalPostForm(NULL, $lock_edit, 'Add section');
    $this->drupalGet('layout_builder/configure/section/defaults/node.landing_page.default/0/layout_onecol');
    $this->drupalPostForm(NULL, $lock_edit, 'Add section');
    $this->drupalGet('layout_builder/configure/section/defaults/node.landing_page.default/0');
    $this->drupalPostForm(NULL, $lock_edit, 'Update');

    $page->pressButton('Save layout');

    foreach ([0, 1, 2] as $delta) {
      $this->drupalGet('layout_builder/configure/section/defaults/node.landing_page.default/' . $delta);
      $assert_session->checkboxChecked('layout_builder_lock[5]');
      $assert_session->checkboxChecked('layout_builder_lock[6]');
      $assert_session->checkboxChecked('layout_builder_lock[7]');
    }

    // Create a node.
    $node = $this->drupalCreateNode(['type' => 'landing_page', 'title' => 'Homepage']);

    // Simply login as an editor. Should not throw any PHP error or show
    // add section or configure sections links.
    // @see https://www.drupal.org/project/layout_builder_lock/issues/3121250

    $this->drupalLogout();
    $this->drupalLogin($this->editor);
    $this->drupalGet('node/' . $node->id() . '/layout');

    $assert_session->linkNotExists('Add section');
    $assert_session->linkNotExists('Configure Section 3');
  }

  /**
   * Checks access to routes related to layout builder.
   *
   * @param \Drupal\Tests\WebAssert $assert_session
   * @param \Drupal\node\NodeInterface $node
   * @param int $code
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function checkRouteAccess(WebAssert $assert_session, NodeInterface $node, $code = 200) {

    $paths = [
      'layout_builder/configure/section/overrides/node.' . $node->id() . '/0',
      'layout_builder/remove/section/overrides/node.' . $node->id() . '/0',
      'layout_builder/choose/section/overrides/node.' . $node->id() . '/0',
      'layout_builder/choose/section/overrides/node.' . $node->id() . '/1',
      'layout_builder/choose/block/overrides/node.' . $node->id() . '/0/content',
      'layout_builder/update/block/overrides/node.' . $node->id() . '/0/content/' . $this->body_field_block_uuid,
      'layout_builder/move/block/overrides/node.' . $node->id() . '/0/content/' . $this->body_field_block_uuid,
      'layout_builder/remove/block/overrides/node.' . $node->id() . '/0/content/' . $this->body_field_block_uuid,
    ];
    foreach ($paths as $path) {
      $this->drupalGet($path);
      $assert_session->statusCodeEquals($code);
    }
  }

  /**
   * Check contextual links.
   *
   * @param \Drupal\Tests\WebAssert $assert_session
   * @param bool $locked
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function checkContextualLinks(WebAssert $assert_session, $locked = FALSE) {
    // Parse contextual links - target body field.
    $id = $assert_session->elementExists('css', '.layout-builder__region > div:nth-child(3) > div');
    $value = $id->getAttribute('data-contextual-id');
    $has_layout_builder_lock_element = FALSE;
    $layout_builder_lock_elements = $layout_builder_block_elements = [];

    $elements = explode('&', $value);
    foreach ($elements as $element) {

      // Layout Builder Lock element.
      if (strpos($element, 'layout_builder_lock') !== FALSE) {
        $has_layout_builder_lock_element = TRUE;
        $layout_builder_lock_elements = explode(':', str_replace(['%3A', 'layout_builder_lock='], [':', ''], $element));
      }

      // Layout Builder Block elements.
      if (strpos($element, 'operations') !== FALSE) {
        $ex = explode(':', $element, 2);
        $layout_builder_block_elements = explode(':', str_replace(['%3A', 'operations='], [':', ''], $ex[1]));
      }
    }

    if ($locked) {
      if ($has_layout_builder_lock_element) {
        self::assertTrue(in_array('layout_builder_block_move', $layout_builder_lock_elements));
        self::assertTrue(!in_array('move', $layout_builder_block_elements));
        self::assertTrue(in_array('layout_builder_block_update', $layout_builder_lock_elements));
        self::assertTrue(!in_array('update', $layout_builder_block_elements));
        self::assertTrue(in_array('layout_builder_block_remove', $layout_builder_lock_elements));
        self::assertTrue(!in_array('remove', $layout_builder_block_elements));
      }
      else {
        // Trigger an explicit fail.
        self::assertTrue($has_layout_builder_lock_element);
      }
    }
    else {
      self::assertTrue(empty($has_layout_builder_lock_element));
      self::assertTrue(in_array('move', $layout_builder_block_elements));
      self::assertTrue(in_array('update', $layout_builder_block_elements));
      self::assertTrue(in_array('remove', $layout_builder_block_elements));
    }
  }

}
