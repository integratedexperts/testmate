<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

use Drupal\Core\Language\LanguageInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\views\Views;
use Drupal\testmode\Testmode;

/**
 * Tests the term views.
 *
 * @group Testmode
 */
class TermViewsTest extends TestmodeTestBase {

  /**
   * Vocabulary for tests.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $vocabulary;

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['taxonomy', 'views'];

  /**
   * Views used by this test.
   *
   * @var string[]
   */
  public static $testViews = ['test_testmode_term'];

  /**
   * Test term view without caching.
   */
  public function testTermViewNoCache(): void {
    $this->createVocabulary();
    $this->createTerms(50);

    $this->testmode->setTermPatterns(Testmode::arrayToMultiline([
      '[TEST%',
      '[OTHERTEST%',
    ]));

    // Login to bypass page caching.
    $account = $this->drupalCreateUser();
    if ($account) {
      $this->drupalLogin($account);
    }

    // Add test view to a list of views.
    $this->testmode->setTermViews('test_testmode_term');

    $this->drupalGet('/test-testmode-term');
    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->pageTextContains('Term 1');
    $this->assertSession()->pageTextContains('Term 2');
    $this->assertSession()->pageTextContains('[TEST] Term 3');
    $this->assertSession()->pageTextContains('[TEST] Term 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 6');

    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->testmode->enableTestMode();

    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->pageTextNotContains('Term 1');
    $this->assertSession()->pageTextNotContains('Term 2');
    $this->assertSession()->pageTextContains('[TEST] Term 3');
    $this->assertSession()->pageTextContains('[TEST] Term 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 6');

    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');
  }

  /**
   * Test term view with tag-based caching.
   */
  public function testTermViewCacheTag(): void {
    $this->createVocabulary();
    $this->createTerms(50);

    $this->testmode->setTermPatterns(Testmode::arrayToMultiline([
      '[TEST%',
      '[OTHERTEST%',
    ]));

    // Login to bypass page caching.
    $account = $this->drupalCreateUser();
    if ($account) {
      $this->drupalLogin($account);
    }

    // Add test view to a list of Testmode views.
    $this->testmode->setTermViews('test_testmode_term');

    // Enable Tag caching for this view.
    $view = Views::getView('test_testmode_term');
    $view->setDisplay('page_1');
    $view->display_handler->overrideOption('cache', [
      'type' => 'tag',
    ]);
    $view->save();

    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'MISS');

    $this->assertSession()->pageTextContains('Term 1');
    $this->assertSession()->pageTextContains('Term 2');
    $this->assertSession()->pageTextContains('[TEST] Term 3');
    $this->assertSession()->pageTextContains('[TEST] Term 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 6');

    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'HIT');

    $this->testmode->enableTestMode();

    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'MISS');

    $this->assertSession()->pageTextNotContains('Term 1');
    $this->assertSession()->pageTextNotContains('Term 2');
    $this->assertSession()->pageTextContains('[TEST] Term 3');
    $this->assertSession()->pageTextContains('[TEST] Term 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 6');

    $this->drupalGet('/test-testmode-term');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'HIT');
  }

  /**
   * Test default Term Overview page with tag-based caching.
   */
  public function testTermOverview(): void {
    $this->createVocabulary();
    // Overview page has a limit of 50 items per page, so creating more terms
    // than a single page to test that filtering correctly applies to a pager.
    $this->createTerms(50);

    $this->testmode->setTermPatterns(Testmode::arrayToMultiline([
      '[TEST%',
      '[OTHERTEST%',
    ]));
    $this->testmode->setTermsList(TRUE);

    $this->drupalLoginAdmin();

    $this->drupalGet('/admin/structure/taxonomy/manage/testmode_tags/overview');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->pageTextContains('Term 1');
    $this->assertSession()->pageTextContains('Term 2');
    $this->assertSession()->pageTextContains('[TEST] Term 3');
    $this->assertSession()->pageTextContains('[TEST] Term 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 6');

    $this->drupalGet('/admin/structure/taxonomy/manage/testmode_tags/overview');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->testmode->enableTestMode();

    $this->drupalGet('/admin/structure/taxonomy/manage/testmode_tags/overview');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->pageTextNotContains('Term 1');
    $this->assertSession()->pageTextNotContains('Term 2');
    $this->assertSession()->pageTextContains('[TEST] Term 3');
    $this->assertSession()->pageTextContains('[TEST] Term 4');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 5');
    $this->assertSession()->pageTextContains('[OTHERTEST] Term 6');

    $this->drupalGet('/admin/structure/taxonomy/manage/testmode_tags/overview');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');
  }

  /**
   * Helper to create vocabulary.
   */
  protected function createVocabulary(): void {
    // Create the vocabulary for the tag field.
    $this->vocabulary = Vocabulary::create([
      'name' => 'Testmode tags',
      'vid' => 'testmode_tags',
    ]);
    $this->vocabulary->save();
  }

  /**
   * Helper to create terms.
   */
  protected function createTerms(int $count = 0): void {
    for ($i = 0; $i < $count + 2; $i++) {
      $this->createTerm([
        'name' => sprintf('Term %s %s', $i + 1, $this->randomMachineName()),
      ]);
    }

    $this->createTerm([
      'name' => sprintf('[TEST] Term %s %s', $i - $count + 1, $this->randomMachineName()),
    ]);
    $this->createTerm([
      'name' => sprintf('[TEST] Term %s %s', $i - $count + 2, $this->randomMachineName()),
    ]);
    $this->createTerm([
      'name' => sprintf('[OTHERTEST] Term %s %s', $i - $count + 3, $this->randomMachineName()),
    ]);
    $this->createTerm([
      'name' => sprintf('[OTHERTEST] Term %s %s', $i - $count + 4, $this->randomMachineName()),
    ]);
  }

  /**
   * Creates and returns a taxonomy term.
   *
   * @param array{"name"?: string, "description"?: string, "format"?: string, "vid"?: string, "langcode"?: string} $settings
   *   (optional) An array of values to override the following default
   *   properties of the term:
   *   - name: A random string.
   *   - description: A random string.
   *   - format: First available text format.
   *   - vid: Vocabulary ID of self::$vocabulary object.
   *   - langcode: LANGCODE_NOT_SPECIFIED.
   *   Defaults to an empty array.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   The created taxonomy term.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createTerm(array $settings = []): Term {
    $filter_formats = filter_formats();
    $format = array_pop($filter_formats);
    $settings += [
      'name' => $this->randomMachineName(),
      'description' => $this->randomMachineName(),
      // Use the first available text format.
      'format' => $format->id(),
      'vid' => $this->vocabulary->id(),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ];
    $term = Term::create($settings);
    $term->save();
    return $term;
  }

}
