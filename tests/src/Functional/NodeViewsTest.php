<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

use Drupal\testmode\Testmode;
use Drupal\views\Views;

/**
 * Tests the node views.
 *
 * @group Testmode
 */
class NodeViewsTest extends TestmodeFunctionalTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['node', 'views'];

  /**
   * Views used by this test.
   *
   * @var string[]
   */
  public static $testViews = ['test_testmode_node'];

  /**
   * Test node view without caching.
   */
  public function testNodeViewNoCache(): void {
    $this->createNodes(20);

    $this->testmode->setNodePatterns(Testmode::arrayToMultiline([
      '[TEST%',
      '[OTHERTEST%',
    ]));

    // Login to bypass page caching.
    $account = $this->drupalCreateUser();
    if ($account) {
      $this->drupalLogin($account);
    }

    // Add test view to a list of views.
    $this->testmode->setNodeViews('test_testmode_node');

    $this->drupalGet('/test-testmode-node');
    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->pageTextContains('Article 1');
    $this->assertSession()->pageTextContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');

    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->testmode->enableTestMode();

    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->pageTextNotContains('Article 1');
    $this->assertSession()->pageTextNotContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');

    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');
  }

  /**
   * Test node view with tag-based caching.
   */
  public function testNodeViewCacheTag(): void {
    $this->createNodes(20);

    $this->testmode->setNodePatterns(Testmode::arrayToMultiline([
      '[TEST%',
      '[OTHERTEST%',
    ]));

    // Login to bypass page caching.
    $account = $this->drupalCreateUser();
    if ($account) {
      $this->drupalLogin($account);
    }

    // Add test view to a list of Testmode views.
    $this->testmode->setNodeViews('test_testmode_node');

    // Enable Tag caching for this view.
    $view = Views::getView('test_testmode_node');
    $view->setDisplay('page_1');
    $view->display_handler->overrideOption('cache', [
      'type' => 'tag',
    ]);
    $view->save();

    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'MISS');

    $this->assertSession()->pageTextContains('Article 1');
    $this->assertSession()->pageTextContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');

    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'HIT');

    $this->testmode->enableTestMode();

    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'MISS');

    $this->assertSession()->pageTextNotContains('Article 1');
    $this->assertSession()->pageTextNotContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');

    $this->drupalGet('/test-testmode-node');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'HIT');
  }

  /**
   * Test node view for default Content with tag-based caching.
   */
  public function testNodeViewContentNoCache(): void {
    $this->createNodes(20);

    $this->testmode->setNodePatterns(Testmode::arrayToMultiline([
      '[TEST%',
      '[OTHERTEST%',
    ]));

    // Disable Tag caching for this view.
    $view = Views::getView('content');
    $view->setDisplay('page_1');
    $view->display_handler->overrideOption('cache', [
      'type' => 'none',
    ]);
    $view->save();

    // Login to bypass page caching.
    $this->drupalLoginAdmin();

    $this->drupalGet('/admin/content');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->pageTextContains('Article 1');
    $this->assertSession()->pageTextContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');

    $this->drupalGet('/admin/content');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->testmode->enableTestMode();

    $this->drupalGet('/admin/content');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');
    $this->assertSession()->pageTextNotContains('Article 1');
    $this->assertSession()->pageTextNotContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');

    $this->drupalGet('/admin/content');
    $this->assertSession()->responseHeaderContains('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');
  }

  /**
   * Helper to create nodes.
   */
  protected function createNodes(int $count = 0): void {
    for ($i = 0; $i < $count + 2; $i++) {
      $this->drupalCreateNode([
        'type' => 'article',
        'title' => sprintf('Article %s %s', $i + 1, $this->randomMachineName()),
      ]);
    }

    $this->drupalCreateNode([
      'type' => 'article',
      'title' => sprintf('[TEST] Article %s %s', $i - $count + 1, $this->randomMachineName()),
    ]);
    $this->drupalCreateNode([
      'type' => 'article',
      'title' => sprintf('[TEST] Article %s %s', $i - $count + 2, $this->randomMachineName()),
    ]);
    $this->drupalCreateNode([
      'type' => 'article',
      'title' => sprintf('[OTHERTEST] Article %s %s', $i - $count + 3, $this->randomMachineName()),
    ]);
    $this->drupalCreateNode([
      'type' => 'article',
      'title' => sprintf('[OTHERTEST] Article %s %s', $i - $count + 4, $this->randomMachineName()),
    ]);
  }

}
