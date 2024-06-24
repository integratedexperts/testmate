<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

use Drupal\testmode\Testmode;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Tests\ViewTestData;

/**
 * Base class for all Testmode Views tests.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
abstract class TestmodeTestBase extends ViewTestBase {

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
   * Setup for test.
   *
   * @param bool $import_test_views
   *   Import test view.
   * @param string[] $modules
   *   List module.
   *
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
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
    $this->drupalLogin($user);
  }

}
