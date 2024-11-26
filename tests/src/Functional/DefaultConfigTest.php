<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Functional;

/**
 * Tests the default configuration.
 *
 * @group Testmode
 */
class DefaultConfigTest extends TestmodeFunctionalTestBase {

  /**
   * Test that default configuration is correctly installed.
   */
  public function testDefaultConfig(): void {
    $this->assertEquals(['content'], $this->testmode->getNodeViews());
    $this->assertEquals([''], $this->testmode->getTermViews());
    $this->assertEquals(TRUE, $this->testmode->getListTerm());
    $this->assertEquals(['user_admin_people'], $this->testmode->getUserViews());
    $this->assertEquals(['[TEST%'], $this->testmode->getNodePatterns());
    $this->assertEquals(['[TEST%'], $this->testmode->getTermPatterns());
    $this->assertEquals(['%example%'], $this->testmode->getUserPatterns());
  }

}
