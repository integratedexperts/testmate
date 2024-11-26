<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\testmode\Testmode;
use Drupal\views\Tests\ViewTestData;

/**
 * Base class for all Testmode Views tests.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
abstract class TestmodeFunctionalTestBase extends ViewTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * @var string[]
   */
  protected static $modules = ['testmode', 'testmode_test'];

  /**
   * Instance of the Testmode class.
   *
   * @var \Drupal\testmode\Testmode
   */
  protected $testmode;

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE, $modules = ['views_test_config']): void {
    parent::setUp($import_test_views);

    if ($import_test_views) {
      $this->drupalCreateContentType(['type' => 'article']);

      ViewTestData::createTestViews(get_class($this), ['testmode_test']);
    }

    $this->testmode = Testmode::getInstance();
  }

  /**
   * Helper to login as Admin user.
   */
  protected function drupalLoginAdmin(): void {
    $user = $this->createUser([], NULL, TRUE);
    // @phpstan-ignore-next-line
    $this->drupalLogin($user);
  }

  /**
   * Checks that current response header contains a value.
   */
  public function responseHeaderContains(string $name, string $value): void {
    // @phpstan-ignore-next-line
    $actual = $this->session->getResponseHeader($name);
    $message = sprintf('Current response header "%s" contains "%s", but "%s" expected.', $name, $actual, $value);

    $this->assertContains($value, $actual, $message);
  }

}
