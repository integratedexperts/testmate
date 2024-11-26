<?php

declare(strict_types=1);

namespace Drupal\Tests\testmode\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\testmode\Testmode;

/**
 * Class TestmodeTest.
 *
 * Tests for Testmode module.
 *
 * @group Testmode
 *
 * @package Drupal\testmode\Tests
 */
class TestmodeTest extends UnitTestCase {

  /**
   * Test for Testmode::matchLike().
   *
   * Test for matchLike().
   *
   * @dataProvider providerMatchLike
   */
  public function testMatchLike(string $pattern, string $subject, bool $is_match): void {
    $actual = Testmode::matchLike($pattern, $subject);
    if ($is_match) {
      $this->assertTrue($actual, 'Expected match found');
    }
    else {
      $this->assertFalse($actual, 'Expected not match found');
    }
  }

  /**
   * Data provider for testMatchLike().
   *
   * @return array<array<mixed>>
   *   Data provider for test.
   */
  public static function providerMatchLike(): array {
    return [
      ['', '', TRUE],
      ['t', 't', TRUE],
      ['t', 'text', TRUE],
      ['text', 'text', TRUE],

      // One or more characters.
      ['text%', 'text', TRUE],
      ['text%', 'textmore', TRUE],
      ['text%suffix', 'textmoresuffix', TRUE],
      ['%text', 'text', TRUE],
      ['%text', 'moretext', TRUE],
      ['prefix%text', 'prefixmoretext', TRUE],
      ['%text%', 'text', TRUE],
      ['%text%', 'moretextmore', TRUE],
      ['prefix%text%suffix', 'prefixmoretextmoresuffix', TRUE],

      // Single character.
      ['text_', 'text', FALSE],
      ['_text', 'text', FALSE],
      ['_text_', 'text', FALSE],
      ['text_', 'textA', TRUE],
      ['text__', 'textAB', TRUE],
      ['_text', 'Atext', TRUE],
      ['__text', 'ABtext', TRUE],
      ['__text__', 'ABtextAB', TRUE],
      ['prefix_text_suffix', 'prefixAtextAsuffix', TRUE],
      ['prefix_text_suffix', 'prefixABtextABsuffix', FALSE],

      // Mix of single and one or more characters.
      ['text_%', 'text', FALSE],
      ['text_%', 'textA', TRUE],
      ['text_%', 'textAB', TRUE],

      // Regex escape characters.
      ['[text', '[text', TRUE],
      ['[text%', '[text', TRUE],
      ['[text%', '[textsuffix', TRUE],
      ['[text_', '[textA', TRUE],
      ['_[text%', 'A[text', TRUE],
      ['_[text%', 'A[textsuffix', TRUE],

      // LIKE escape characters.
      ['text\%', 'text%', TRUE],
      ['text\%suffix', 'text%suffix', TRUE],
      ['text\_', 'text_', TRUE],
      ['text\_', 'text_suffix', TRUE],

      // Mix of normal and escape LIKE characters.
      ['text\%text2%', 'text%text2suffix', TRUE],
    ];
  }

  /**
   * Test for Testmode::multilineToArray().
   *
   * @param string|array<string> $string
   *   String or array of string want to test.
   * @param array<string> $expected
   *   Expected array string.
   *
   * @dataProvider providerMultilineToArray
   */
  public function testMultilineToArray(string|array $string, array $expected): void {
    $actual = Testmode::multilineToArray($string);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for multilineToArray().
   *
   * @return array<array<mixed>>
   *   Provider data for test.
   */
  public static function providerMultilineToArray(): array {
    return [
      ['', []],
      [' ', []],
      ['a', ['a']],
      [
        'a
        b', ['a', 'b'],
      ],
      [
        'a aa
        b', ['a aa', 'b'],
      ],
      [
        'a aa
        b
        c', ['a aa', 'b', 'c'],
      ],
      [
        'a aa
        b

        c', ['a aa', 'b', 'c'],
      ],
      // Array as input.
      [
        ['a', 'b'], ['a', 'b'],
      ],
    ];
  }

  /**
   * Test for Testmode::arrayToTextarea().
   *
   * @param array<string>|string $array
   *   Array test.
   * @param string $expected
   *   String expected.
   *
   * @dataProvider providerArrayToTextarea
   */
  public function testArrayToTextarea(array|string $array, string $expected): void {
    $actual = Testmode::arrayToMultiline($array);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for arrayToTextarea().
   *
   * @return array<array<mixed>>
   *   Provider test data.
   */
  public static function providerArrayToTextarea(): array {
    return [
      [[], ''],
      [[''], ''],
      [['', ''], ''],
      [[' ', ''], ' '],
      [['a'], 'a'],
      [
        ['a', 'b'], 'a' . PHP_EOL . 'b',
      ],
      [[' a ', 'b'], ' a ' . PHP_EOL . 'b'],
      [[' a ', '', 'b'], ' a ' . PHP_EOL . 'b'],
      [[' a ', ' ', 'b'], ' a ' . PHP_EOL . ' ' . PHP_EOL . 'b'],
      // String as input.
      ['', ''],
      ['a', 'a'],
    ];
  }

}
