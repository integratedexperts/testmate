<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

use Drupal\testmode\Testmode;
use Drupal\views\Views;

/**
 * Tests the user views.
 *
 * @group Testmode
 */
class UserViewsTest extends TestmodeTestBase {

  /**
   * Modules to enable.
   *
   * @var array<string>
   */
  protected static $modules = ['user', 'views'];

  /**
   * Views used by this test.
   *
   * @var array<string>
   */
  public static array $testViews = ['test_testmode_user'];

  /**
   * Test user view without caching.
   */
  public function testUserViewNoCache(): void {
    $this->createUsers(50);

    $this->testmode->setUserPatterns(Testmode::arrayToMultiline([
      '%example%',
      '%otherexample%',
    ]));

    // Login to bypass page caching.
    $user_with_access_user_profiles_permission = $this->drupalCreateUser(['access user profiles']);
    if ($user_with_access_user_profiles_permission) {
      $this->drupalLogin($user_with_access_user_profiles_permission);
    }

    // Add test view to a list of views.
    $this->testmode->setUserViews('test_testmode_user');

    $this->drupalGet('/test-testmode-user');
    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->responseContains('User 1');
    $this->assertSession()->responseContains('User 2');
    $this->assertSession()->responseContains('[TEST] User 3');
    $this->assertSession()->responseContains('[TEST] User 4');
    $this->assertSession()->responseContains('[OTHERTEST] User 5');
    $this->assertSession()->responseContains('[OTHERTEST] User 6');

    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->testmode->enableTestMode();

    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->responseNotContains('User 1');
    $this->assertSession()->responseNotContains('User 2');
    $this->assertSession()->responseContains('[TEST] User 3');
    $this->assertSession()->responseContains('[TEST] User 4');
    $this->assertSession()->responseContains('[OTHERTEST] User 5');
    $this->assertSession()->responseContains('[OTHERTEST] User 6');

    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');
  }

  /**
   * Test user view with tag-based caching.
   */
  public function testUserViewCacheTag():void {
    $this->createUsers(50);

    $this->testmode->setUserPatterns(Testmode::arrayToMultiline([
      '%example%',
      '%otherexample%',
    ]));

    // Login to bypass page caching.
    $user_with_access_user_profiles_permission = $this->drupalCreateUser(['access user profiles']);
    if ($user_with_access_user_profiles_permission) {
      $this->drupalLogin($user_with_access_user_profiles_permission);
    }

    // Add test view to a list of views.
    $this->testmode->setUserViews('test_testmode_user');

    // Enable Tag caching for this view.
    $view = Views::getView('test_testmode_user');
    $view->setDisplay('page_1');
    $view->display_handler->overrideOption('cache', [
      'type' => 'tag',
    ]);
    $view->save();

    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'MISS');

    $this->assertSession()->responseContains('User 1');
    $this->assertSession()->responseContains('User 2');
    $this->assertSession()->responseContains('[TEST] User 3');
    $this->assertSession()->responseContains('[TEST] User 4');
    $this->assertSession()->responseContains('[OTHERTEST] User 5');
    $this->assertSession()->responseContains('[OTHERTEST] User 6');

    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'HIT');

    $this->testmode->enableTestMode();

    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'MISS');

    $this->assertSession()->responseNotContains('User 1');
    $this->assertSession()->responseNotContains('User 2');
    $this->assertSession()->responseContains('[TEST] User 3');
    $this->assertSession()->responseContains('[TEST] User 4');
    $this->assertSession()->responseContains('[OTHERTEST] User 5');
    $this->assertSession()->responseContains('[OTHERTEST] User 6');

    $this->drupalGet('/test-testmode-user');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'HIT');
  }

  /**
   * Test user view for default User page with tag-based caching.
   */
  public function testUserViewContentNoCache(): void {
    $this->createUsers(50);

    $this->testmode->setUserPatterns(Testmode::arrayToMultiline([
      '%example%',
      '%otherexample%',
    ]));

    // Disable Tag caching for this view.
    $view = Views::getView('user_admin_people');
    $view->setDisplay('page_1');
    $view->display_handler->overrideOption('cache', [
      'type' => 'none',
    ]);
    $view->save();

    // Login to bypass page caching.
    $this->drupalLoginAdmin();

    $this->drupalGet('/admin/people');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->responseContains('User 1');
    $this->assertSession()->responseContains('User 2');
    $this->assertSession()->responseContains('[TEST] User 3');
    $this->assertSession()->responseContains('[TEST] User 4');
    $this->assertSession()->responseContains('[OTHERTEST] User 5');
    $this->assertSession()->responseContains('[OTHERTEST] User 6');

    $this->drupalGet('/admin/people');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->testmode->enableTestMode();

    $this->drupalGet('/admin/people');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');

    $this->assertSession()->responseNotContains('User 1');
    $this->assertSession()->responseNotContains('User 2');
    $this->assertSession()->responseContains('[TEST] User 3');
    $this->assertSession()->responseContains('[TEST] User 4');
    $this->assertSession()->responseContains('[OTHERTEST] User 5');
    $this->assertSession()->responseContains('[OTHERTEST] User 6');

    $this->drupalGet('/admin/people');
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'UNCACHEABLE');
  }

  /**
   * Helper to create users.
   */
  protected function createUsers(int $count = 0): void {
    for ($i = 0; $i < $count + 2; $i++) {
      $name = sprintf('User %s %s', $i + 1, $this->randomMachineName());
      $email = str_replace(' ', '_', $name) . '@somedomain.com';
      $this->drupalCreateUser([], $name, FALSE, [
        'mail' => $email,
      ]);
    }

    $name = sprintf('[TEST] User %s %s', $i - $count + 1, $this->randomMachineName());
    $email = str_replace(' ', '_', $name) . '@example.com';
    $this->drupalCreateUser([], $name, FALSE, [
      'mail' => $email,
    ]);
    $name = sprintf('[TEST] User %s %s', $i - $count + 2, $this->randomMachineName());
    $email = str_replace(' ', '_', $name) . '@example.com';
    $this->drupalCreateUser([], $name, FALSE, [
      'mail' => $email,
    ]);
    $name = sprintf('[OTHERTEST] User %s %s', $i - $count + 3, $this->randomMachineName());
    $email = str_replace(' ', '_', $name) . '@otherexample.com';
    $this->drupalCreateUser([], $name, FALSE, [
      'mail' => $email,
    ]);
    $name = sprintf('[OTHERTEST] User %s %s', $i - $count + 4, $this->randomMachineName());
    $email = str_replace(' ', '_', $name) . '@otherexample.com';
    $this->drupalCreateUser([], $name, FALSE, [
      'mail' => $email,
    ]);
  }

}
