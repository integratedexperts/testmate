<?php

declare(strict_types=1);

namespace Drupal\testmode;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;

/**
 * Class Testmode.
 *
 * Class to handle all module operations.
 *
 * @package Drupal\testmode
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Testmode {

  /**
   * Defines cache key for views.
   */
  const CACHE_VIEWS = 'testmode:views';

  /**
   * The Testmode singleton.
   *
   * @var \Drupal\testmode\Testmode
   */
  protected static ?Testmode $instance = NULL;

  /**
   * Drupal config instance.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Drupal state instance.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Testmode constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory, StateInterface $state) {
    $this->config = $configFactory->getEditable('testmode.settings');
    $this->state = $state;
  }

  /**
   * Get the Testmode class instance.
   *
   * @return \Drupal\testmode\Testmode
   *   Instance of the Testmode class.
   */
  public static function getInstance(): Testmode {
    if (!self::$instance) {
      self::$instance = new self(\Drupal::configFactory(), \Drupal::state());
    }

    return self::$instance;
  }

  /**
   * Enable test mode.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function enableTestMode(): Testmode {
    if (!$this->isTestMode()) {
      $this->setMode(TRUE);
      $this->invalidateCahes();
    }
    return $this;
  }

  /**
   * Disable test mode.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function disableTestMode(): Testmode {
    if ($this->isTestMode()) {
      $this->setMode(FALSE);
      $this->invalidateCahes();
    }
    return $this;
  }

  /**
   * Toggle test mode.
   *
   * @param bool $enable
   *   Flag to enable test mode.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function toggleTestMode(bool $enable): Testmode {
    $enable ? $this->enableTestMode() : $this->disableTestMode();

    return $this;
  }

  /**
   * Invalidate caches.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function invalidateCahes(): Testmode {
    Cache::invalidateTags([self::CACHE_VIEWS]);
    return $this;
  }

  /**
   * Check if test mode is enabled.
   *
   * @return bool
   *   TRUE if test mode is enabled, FALSE otherwise.
   */
  public function isTestMode(): bool {
    return (bool) $this->state->get('testmode.enabled');
  }

  /**
   * Set test mode.
   *
   * @param bool $value
   *   FLag to set test mode.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  protected function setMode(bool $value): Testmode {
    $value ? $this->state->set('testmode.enabled', TRUE) : $this->state->delete('testmode.enabled');

    return $this;
  }

  /**
   * Get node views machine names.
   *
   * @return string[]
   *   Array of node views machine names.
   */
  public function getNodeViews(): array {
    $value = $this->config->get('views_node');
    return is_array($value) ? $value : [];
  }

  /**
   * Set node views machine names.
   *
   * @param string[]|string $value
   *   String delimited by a new line or an array.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function setNodeViews(array|string $value): Testmode {
    $this->config->set('views_node', self::multilineToArray($value))->save();
    return $this;
  }

  /**
   * Get term views machine names.
   *
   * @return string[]
   *   Array of term views machine names.
   */
  public function getTermViews(): array {
    $value = $this->config->get('views_term');
    return is_array($value) ? $value : [];
  }

  /**
   * Set term views machine names.
   *
   * @param string[]|string $value
   *   String delimited by a new line or an array.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function setTermViews(array|string $value): Testmode {
    $this->config->set('views_term', self::multilineToArray($value))->save();
    return $this;
  }

  /**
   * Get list term flag.
   *
   * @return bool
   *   TRUE if the flag is set, FALSE otherwise.
   */
  public function getListTerm(): bool {
    return (bool) $this->config->get('list_term');
  }

  /**
   * Set list term flag.
   *
   * @param bool $value
   *   Flag to set the value.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function setTermsList(bool $value): Testmode {
    $this->config->set('list_term', $value)->save();
    return $this;
  }

  /**
   * Get user views machine names.
   *
   * @return string[]
   *   Array of user views machine names.
   */
  public function getUserViews(): array {
    $value = $this->config->get('views_user');
    return is_array($value) ? $value : [];
  }

  /**
   * Set user views machine names.
   *
   * @param string[]|string $value
   *   String delimited by a new line or an array.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function setUserViews(array|string $value): Testmode {
    $this->config->set('views_user', self::multilineToArray($value))->save();
    return $this;
  }

  /**
   * Get node patterns.
   *
   * @return string[]
   *   Array of node patterns.
   */
  public function getNodePatterns(): array {
    $value = $this->config->get('pattern_node');
    return is_array($value) ? $value : [];
  }

  /**
   * Set node patterns.
   *
   * @param string[]|string $value
   *   String delimited by a new line or an array.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function setNodePatterns(array|string $value): Testmode {
    $this->config->set('pattern_node', self::multilineToArray($value))->save();
    return $this;
  }

  /**
   * Get term patterns.
   *
   * @return string[]
   *   Array of term patterns.
   */
  public function getTermPatterns(): array {
    $value = $this->config->get('pattern_term');
    return is_array($value) ? $value : [];
  }

  /**
   * Set term patterns.
   *
   * @param string[]|string $value
   *   String delimited by a new line or an array.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function setTermPatterns(array|string $value): Testmode {
    $this->config->set('pattern_term', self::multilineToArray($value))->save();
    return $this;
  }

  /**
   * Get user patterns.
   *
   * @return string[]
   *   Array of user patterns.
   */
  public function getUserPatterns(): array {
    $value = $this->config->get('pattern_user');
    return is_array($value) ? $value : [];
  }

  /**
   * Set user patterns.
   *
   * @param string[]|string $value
   *   String delimited by a new line or an array.
   *
   * @return \Drupal\testmode\Testmode
   *   Current class instance.
   */
  public function setUserPatterns(array|string $value): Testmode {
    $this->config->set('pattern_user', self::multilineToArray($value))->save();
    return $this;
  }

  /**
   * Match subject to an MySQL LIKE pattern.
   *
   * Not used in this module, but can be used from the alter hook of other
   * modules.
   *
   * @param string $like_pattern
   *   Patter in MySQL LIKE format.
   * @param string $subject
   *   String subject to match.
   *
   * @return bool
   *   TRUE if subject matches pattern, FALSE otherwise.
   */
  public static function matchLike(string $like_pattern, string $subject): bool {
    $like_pattern = str_replace('\%', 'LIKE_PERCENT_CHARACTER_PLACEHOLDER', $like_pattern);
    $like_pattern = str_replace('\_', 'LIKE_UNDERSCORE_CHARACTER_PLACEHOLDER', $like_pattern);
    $like_pattern = preg_quote($like_pattern);
    $like_pattern = str_replace('LIKE_PERCENT_CHARACTER_PLACEHOLDER', '\%', $like_pattern);
    $like_pattern = str_replace('LIKE_UNDERSCORE_CHARACTER_PLACEHOLDER', '\_', $like_pattern);
    $like_pattern = preg_replace('/(?<!\\\\)\%/i', '.*', $like_pattern);
    $like_pattern = preg_replace('/(?<!\\\\)\_/', '.', $like_pattern);
    $pattern = '/' . $like_pattern . '/';

    return (bool) preg_match($pattern, $subject);
  }

  /**
   * Helper to convert multi-line strings into an array.
   *
   * @param string|array<string> $string
   *   String value to convert.
   *
   * @return string[]
   *   Array of values.
   */
  public static function multilineToArray(array|string $string): array {
    $lines = is_array($string) ? $string : explode("\n", str_replace("\r\n", "\n", $string));
    return array_values(array_filter(array_map('trim', $lines)));
  }

  /**
   * Helper to convert an array to multi-line string value.
   *
   * @param string[]|string $array
   *   Array to convert.
   *
   * @return string
   *   String value of the array.
   */
  public static function arrayToMultiline(array|string $array): string {
    $array = is_array($array) ? $array : [$array];
    return implode(PHP_EOL, array_filter($array));
  }

}
