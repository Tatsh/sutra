<?php
namespace Sutra\Component\String;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

/**
 * {@inheritdoc}
 *
 * @author Andrew Udvare <audvare@gmail.com>
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

    /**
     * Constructor.
     *
     * @param UrlParserInterface $urlParser URL parser instance.
     */
    public function __construct(UrlParserInterface $urlParser)
    {
        $this->urlParser = $urlParser;
    }

    /**
     * {@inheritdoc}
     */
    public function camelize($str)
    {
        if (isset($this->rules['camelize'][$str])) {
            return $this->rules['camelize'][$str];
        }

        return DoctrineInflector::classify($str);
    }

    /**
     * {@inheritdoc}
     */
    public function addCamelizationRule($word, $replacement)
    {
        $this->rules['camelize'][$word] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCamelizationRule($word)
    {
        unset($this->rules['camelize'][$word]);
    }

    /**
     * {@inheritdoc}
     */
    public function pluralize($str)
    {
        if (isset($this->rules['pluralize'][$str])) {
            return $this->rules['pluralize'][$str];
        }

        return DoctrineInflector::pluralize($str);
    }

    /**
     * {@inheritdoc}
     */
    public function addPluralizationRule($word, $replacement)
    {
        $this->rules['pluralize'][$word] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function removePluralizationRule($word)
    {
        unset($this->rules['pluralize'][$word]);
    }

    /**
     * {@inheritdoc}
     */
    public function singularize($str)
    {
        if (isset($this->rules['singularize'][$str])) {
            return $this->rules['singularize'][$str];
        }

        return DoctrineInflector::singularize($str);
    }

    /**
     * {@inheritdoc}
     */
    public function addSingularizationRule($word, $replacement)
    {
        $this->rules['singularize'][$word] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function studlyize($str)
    {
        return DoctrineInflector::camelize($str);
    }

    /**
     * {@inheritdoc}
     */
    public function underscorize($str)
    {
        return DoctrineInflector::tableize($str);
    }

    /**
     * {@inheritdoc}
     */
    public function dashize($string)
    {
        if (isset($this->rules['dashize'][$string])) {
            return $this->rules['dashize'][$string];
        }

        if (isset($this->cache['dashize'][$string])) {
            return $this->cache['dashize'][$string];
        }

        $original = $string;
        $string = trim(strtolower($string[0]) . substr($string, 1));

        if (strpos($string, ' ') === false) {
            // Handle camelCase
            $string = $this->underscorize($string);
            $string = str_replace('_', '-', $string);
        }
        else {
            $string = $this->urlParser->makeFriendly($string);
        }

        $this->rules['dashize'][$original] = $string;

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function addDashizeRule($original, $returnString)
    {
        $this->rules['dashize'][$original] = $returnString;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDashizeRule($original)
    {
        unset($this->rules['dashize'][$original]);
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
