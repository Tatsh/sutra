<?php
/**
 * For adding variables to a specific template. Any classes that implement this
 *   interface must be included explicitly and cannot be reliably auto-loaded.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.example.com/
 *
 * @version 1.0
 */
interface sTemplateVariableSetter {
  /**
   * Get variable definitions for use with a template.
   *
   * @param string $template Template file (without .tpl.php) that is in use.
   * @return array Key => value pairs for use with template.
   */
  public static function getVariables($template);
}
