<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

use Drupal\Tests\search_api\Functional\ExampleContentTrait;
use Drupal\search_api\Entity\Index;
use Drupal\testmode\Testmode;

/**
 * Tests the Search API views.
 *
 * @group Testmode
 */
class SearchApiViewsTest extends TestmodeFunctionalTestBase {

  use ExampleContentTrait;

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['node', 'views', 'search_api'];

  /**
   * Views used by this test.
   *
   * @var string[]
   */
  public static $testViews = ['test_testmode_searchapi'];

  /**
   * Test search view.
   */
  public function testSearchViewNoCache(): void {
    $this->createNodes(20);

    \Drupal::getContainer()
      ->get('search_api.index_task_manager')
      ->addItemsAll(Index::load('test_content_index'));
    $this->indexItems('test_content_index');

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
    $this->testmode->setNodeViews('test_testmode_searchapi');

    $this->drupalGet('/search');
    $this->submitForm([
      'search_api_fulltext' => 'Article',
    ], 'Apply');

    $this->assertSession()->pageTextContains('Article 1');
    $this->assertSession()->pageTextContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');

    $this->testmode->enableTestMode();

    $this->drupalGet('/search');
    $this->submitForm([
      'search_api_fulltext' => 'Article',
    ], 'Apply');

    $this->assertSession()->pageTextNotContains('Article 1');
    $this->assertSession()->pageTextNotContains('Article 2');
    $this->assertSession()->pageTextContains('[TEST] Article 3');
    $this->assertSession()->pageTextContains('[TEST] Article 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Article 6');
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
