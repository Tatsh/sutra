<?php
/**
 * Sutra-specific site messaging.
 *
 * @package Sutra
 */
class sMessaging extends fMessaging {
  /**
   * Add a success message to a path to be delivered upon arrival.
   *
   * @param string $message Message text. Should not have HTML.
   * @param string $path Path to deliver to. If not passed, defaults to current
   *   page.
   * @return void
   */
  public static function add($message, $path = NULL) {
    if ($path === '<front>' || $path === '/' || is_null($path)) {
      $path = sConfiguration::getInstance()->getBaseUrl();
    }

    self::create('success', $path, $message);
  }

  /**
   * Add an error message to a path to be delivered upon arrival.
   *
   * @param string $message Message text. Should not have HTML.
   * @param string $path Path to deliver to. If not passed, defaults to current
   *   page.
   * @return void
   */
  public static function addError($message, $path = NULL) {
    if ($path === '<front>' || $path === '/' || is_null($path)) {
      $path = sConfiguration::getInstance()->getBaseUrl();
    }

    self::create('validation', $path, $message);
  }
}
