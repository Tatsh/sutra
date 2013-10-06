<?php
/**
 * Get translated version of text.
 *
 * For this function to work with other languages besides the original, the
 *   locale must be set with <code>setlocale()</code> (sometimes using putenv() is
 *   necessary as well), translation table file directory must be bound with
 *   bindtextdomain(), and a text domain (string) must be set
 *   using textdomain().
 *
 * Example:
 * <code>
 * putenv('LC_ALL=en_GB');
 * setlocale(LC_ALL, 'en_GB');
 * bindtextdomain('special', './locales');
 * textdomain('special');
 * $color = __(fRequest::get('color', 'string', 'blue', TRUE));
 * print __('You indicated that your favorite color is !color.', array('!color' => $color));
 * </code>
 *
 * Output:
 * <code>
 * You indicated that your favourite colour is blue.
 * </code>
 *
 * @param string $text Text to get translation of.
 * @param array|string $vars Replacement key => value pairs or multiple string
 *   arguments.
 * @return string The text.
 *
 * @package Sutra
 * @subpackage Functions
 *
 * @version 1.3
 * @since 1.3
 */
function __($text, $vars = array()) {
  if (!is_array($vars)) {
    $vars = func_get_args();

    array_shift($vars);

    $original = $vars;
    $vars = array();
    $words = preg_split('/\s/', $text);
    $special = array(
      '!' => true,
      '@' => true,
      '%' => true
    );
    $length = count($words);
    $i = 0;

    foreach ($words as $word) {
      if (!isset($original[$i])) {
        break;
      }

      if (isset($special[$word[0]])) {
        $vars[$word] = $original[$i];
        $i++;
      }
    }
  }

  return gettext(strtr($text, $vars));
}

/**
 * Copyright (c) 2012 Andrew Udvare <audvare@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
