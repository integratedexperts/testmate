<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

use Drupal\testmode\Testmode;

/**
 * Tests the settings form.
 *
 * @group Testmode
 */
class SettingsFormTest extends TestmodeFunctionalTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['testmode'];

  /**
   * Test that setting form works correctly.
   */
  public function testSettingsFormDefaults(): void {
    $this->drupalLoginAdmin();

    $this->drupalGet('admin/config/development/testmode');
    $this->assertSession()->fieldValueEquals('mode', '0');
    $this->assertSession()->fieldValueEquals('views_node', 'content');
    $this->assertSession()->fieldValueEquals('views_term', '');
    $this->assertSession()->fieldValueEquals('list_term', '1');
    $this->assertSession()->fieldValueEquals('views_user', 'user_admin_people');
    $this->assertSession()->fieldValueEquals('pattern_node', '[TEST%');
    $this->assertSession()->fieldValueEquals('pattern_term', '[TEST%');
    $this->assertSession()->fieldValueEquals('pattern_user', '%example%');
  }

  /**
   * Test that setting form can be submitted correctly.
   */
  public function testSettingsFormSubmit(): void {
    $this->drupalLoginAdmin();

    $this->drupalGet('admin/config/development/testmode');
    $this->assertSession()->fieldValueEquals('mode', '0');
    $this->assertSession()->fieldValueEquals('views_node', 'content');
    $this->assertSession()->fieldValueEquals('views_term', '');
    $this->assertSession()->fieldValueEquals('list_term', '1');
    $this->assertSession()->fieldValueEquals('views_user', 'user_admin_people');
    $this->assertSession()->fieldValueEquals('pattern_node', '[TEST%');
    $this->assertSession()->fieldValueEquals('pattern_term', '[TEST%');
    $this->assertSession()->fieldValueEquals('pattern_user', '%example%');
    $this->drupalGet('admin/config/development/testmode');

    $this->submitForm([
      'views_node' => Testmode::arrayToMultiline(['vn1', 'vn2']),
      'views_term' => Testmode::arrayToMultiline(['vt1', 'vt2']),
      'views_user' => Testmode::arrayToMultiline(['vu1', 'vu2']),
      'pattern_node' => Testmode::arrayToMultiline(['pn1', 'pn2']),
      'pattern_term' => Testmode::arrayToMultiline(['pt1', 'pt2']),
      'pattern_user' => Testmode::arrayToMultiline(['pu1', 'pu2']),
    ], 'Save configuration');

    $this->assertSession()->fieldValueEquals('views_node', Testmode::arrayToMultiline([
      'vn1',
      'vn2',
    ]));
    $this->assertSession()->fieldValueEquals('views_term', Testmode::arrayToMultiline([
      'vt1',
      'vt2',
    ]));
    $this->assertSession()->fieldValueEquals('views_user', Testmode::arrayToMultiline([
      'vu1',
      'vu2',
    ]));
    $this->assertSession()->fieldValueEquals('pattern_node', Testmode::arrayToMultiline([
      'pn1',
      'pn2',
    ]));
    $this->assertSession()->fieldValueEquals('pattern_term', Testmode::arrayToMultiline([
      'pt1',
      'pt2',
    ]));
    $this->assertSession()->fieldValueEquals('pattern_user', Testmode::arrayToMultiline([
      'pu1',
      'pu2',
    ]));
  }

  /**
   * Test that enabling/disabling test mode through the form shows message.
   */
  public function testModeSwitch(): void {
    $this->drupalLoginAdmin();

    $this->drupalGet('admin/config/development/testmode');
    $this->assertFalse($this->testmode->isTestMode(), 'Test mode is disabled by default');
    $this->assertSession()->pageTextNotContains('Test mode is enabled');
    $this->drupalGet('admin/config/development/testmode');

    $this->submitForm([
      'mode' => '1',
    ], 'Save configuration');
    $this->assertTrue($this->testmode->isTestMode());
    $this->assertSession()->pageTextContains('Test mode is enabled');
    $this->drupalGet('admin/config/development/testmode');

    $this->submitForm([
      'mode' => '0',
    ], 'Save configuration');
    $this->assertFalse($this->testmode->isTestMode(), 'Test mode is disabled by default');
    $this->assertSession()->pageTextNotContains('Test mode is enabled');
  }

}
