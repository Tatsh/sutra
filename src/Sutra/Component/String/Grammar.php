<?php
namespace Sutra\Component\String;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

/**
 * @copyright Copyright (c) 2013 Andrew Udvare
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.3
 */
class Grammar implements GrammarInterface
{
    /**
    * Cache of strings.
    *
    * @var array
    */
    protected $cache = array(
        'dashize' => array(),
        'humanize' => array(),
        'titleize' => array(),
    );

    /**
    * Exceptions.
    *
    * @var array
    */
    protected $rules = array(
        'camelize' => array(),
        'dashize' => array(),
        'humanize' => array(),
        'pluralize' => array(),
        'singularize' => array(),
        'studlyize' => array(),
        'underscorize' => array(),
        'titleize' => array(),
    );

    /**
     * URL parser.
     *
     * @var URLParserInterface
     */
    protected $urlParser;

    public function __construct(URLParserInterface $urlParser)
    {
        $this->urlParser = $urlParser;
    }

    public function camelize($str)
    {
        return DoctrineInflector::classify($str);
    }

    public function addPluralizationRule($word, $replacement)
    {
        DoctrineInflector::rules('plural', array('irregular' => array($word, $replacement))));
    }

    public function pluralize($str)
    {
        return DoctrineInflector::pluralize($str);
    }

    public function singularize($str)
    {
        return DoctrineInflector::singularize($str);
    }

    public function addSingularizationRule($word, $replacement)
    {
        DoctrineInflector::rules('singular', array('irregular' => array($word, $replacement))));
    }

    public function studlyize($str)
    {
        return DoctrineInflector::camelize($str);
    }

    public function underscorize($str)
    {
        return DoctrineInflector::tableize($str);
    }

  /**
   * Add an exception string for sGrammar::dashize().
   *
   * @param string $original Original string.
   * @param string $returnString The string to return in case this string is passed to
   *   sGrammar::dashize().
   * @return void
   * @see sGrammar::removeDashizeRule()
   */
  public static function addDashizeRule($original, $returnString) {
    if (!strlen($returnString) || !strlen($original)) {
      throw new fProgrammerException('An empty string was passed to %s', self::addDashizeRule);
    }

    self::$dashize_rules[$original] = $returnString;
  }

  /**
   * Removes a rule used by sGrammar::dashize().
   *
   * @param string $original Original string that would be processed.
   * @return void
   * @see sGrammar::addDashizeRule()
   */
  public static function removeDashizeRule($original) {
    if (!strlen($original)) {
      throw new fProgrammerException('An empty string was passed to %s', self::removeDashizeRule);
    }

    if (isset(self::$dashize_rules[$original])) {
      unset(self::$dashize_rules[$original]);
    }
  }

    /**
    * Converts an underscore_notation or camelCase notation to dash-notation.
    *
    * @param string $string String to convert.
    * @return string Converted string.
    * @see sGrammar::addDashizeRule()
    */
    public function dashize($string) {
        if (!strlen($string)) {
            throw new ProgrammerException('An empty string was passed to %s', self::dashize);
        }

        if (isset(self::$dashize_cache[$string])) {
            return self::$dashize_cache[$string];
        }

        $original = $string;
        $string = trim(strtolower($string[0]) . substr($string, 1));

        // Handle custom rules
        if (isset(self::$dashize_rules[$string])) {
            $string = self::$dashize_rules[$string];
        }
        else if (strpos($string, ' ') === FALSE) {
            // Handle camelCase
            $string = $this->underscorize($string);
            $string = str_replace('_', '-', $string);
        }
        else {
            $string = $this->urlParser->makeFriendly($string, NULL, '-');
            $string = str_replace('_', '-', $string);
        }

        self::$dashize_cache[$original] = $string;

        return $string;
    }
}

/**
 * Copyright (c) 2012 Andrew Udvare <andrew@bne1.com>
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
