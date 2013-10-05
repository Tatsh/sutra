<?php
namespace Sutra\Component\String;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

use Sutra\Component\Url\UrlParserInterface;

/**
 * {@inheritdoc}
 *
 * @author Andrew Udvare <audvare@gmail.com>
 */
class Grammar implements GrammarInterface
{
    /**
     * Generic regular expression used for extensions and abbreviations in
     *   `#humanize`.
     *
     * @var string
     */
    const HUMANIZE_REGEX = '/(\b(api|css|gif|html|id|jpg|js|mp3|pdf|php|png|sql|swf|url|xhtml|xml)\b|\b\w)/';

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
     * Various stop words (not just English).
     *
     * @var array
     */
    protected $stopWords = array(
        'a',
        'an',
        'and',
        'at',
        'by',
        'de',  # mainly for Spanish and French
        'el',  # Spanish
        'feat',
        'featuring',
        'for',
        'from',
        'il',  # Italian
        'in',
        'into',
        'la',  # Spanish/French/Italian
        'lo',  # Italian
        'of',
        'off',
        'on',
        'or',
        'per',
        //'so',
        'te',  # Spanish/French
        //'than',
        'the',
        //'then',
        //'this',
        'to',
        //'too',
        'van',
        'via',
        'von',
        'vs',
        'with',
        'within',
        'without',
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
    public function removeSingularizationRule($word)
    {
        unset($this->rules['singularize'][$word]);
    }

    /**
     * {@inheritdoc}
     */
    public function studlyize($str)
    {
        if (isset($this->rules['studlyize'][$str])) {
            return $this->rules['studlyize'][$str];
        }

        return DoctrineInflector::camelize($str);
    }

    /**
     * {@inheritdoc}
     */
    public function addStudlyizationRule($word, $replacement)
    {
        $this->rules['studlyize'][$word] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function removeStudlyizationRule($word)
    {
        unset($this->rules['studlyize'][$word]);
    }

    /**
     * {@inheritdoc}
     */
    public function underscorize($str)
    {
        if (isset($this->rules['underscorize'][$str])) {
            return $this->rules['underscorize'][$str];
        }

        return DoctrineInflector::tableize($str);
    }

    /**
     * {@inheritdoc}
     */
    public function addUnderscorizationRule($word, $replacement)
    {
        $this->rules['underscorize'][$word] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function removeUnderscorizationRule($word)
    {
        unset($this->rules['underscorize'][$word]);
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

        $this->cache['dashize'][$original] = $string;

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function addDashizationRule($original, $returnString)
    {
        $this->rules['dashize'][$original] = $returnString;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDashizationRule($original)
    {
        unset($this->rules['dashize'][$original]);
    }

    /**
     * {@inheritdoc}
     */
    public function humanize($string)
    {
        if (isset($this->cache['humanize'][$string])) {
            return $this->cache['humanize'][$string];
        }

        $original = $string;

        $string = str_replace(array_keys($this->rules['humanize']), $this->rules['humanize'], $string);
        $string = str_replace('_', ' ', $this->underscorize($string));
        $string = preg_replace('/\s+/', ' ', $string);
        $string = preg_replace_callback(static::HUMANIZE_REGEX, array($this, 'humanizeCallback'), $string);

        $this->cache['humanize'][$original] = $string;

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function addHumanizationRule($substr, $replacement)
    {
        $this->rules['humanize'][$substr] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function removeHumanizationRule($substr)
    {
        unset($this->rules['humanize'][$substr]);
        unset($this->cache['humanize'][$substr]);

        foreach ($this->cache['humanize'] as $key => $value) {
            if (stripos($key, $substr) !== false) {
                unset($this->cache['humanize'][$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function titleize($str)
    {
        if (isset($this->cache['titleize'][$str])) {
            return $this->cache['titleize'][$str];
        }

        $original = $str;

        // First use rules
        $str = str_replace(array_keys($this->rules['titleize']), $this->rules['titleize'], $str);
        // Humanize (hope for no rule conflicts)
        $str = $this->humanize($str);

        // Split into words and replace with lower-case versions
        $words = array_map('trim', preg_split('/\s+/', $str, null, PREG_SPLIT_NO_EMPTY));
        foreach ($words as $key => $word) {
            $find = strtolower($word);

            if (in_array($find, $this->stopWords)) {
                $words[$key] = $find;
            }
        }

        $str = join(' ', array_merge(array(ucfirst($words[0])), array_slice($words, 1)));

        $this->cache['titleize'][$original] = $str;

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function addTitleizationRule($substr, $replacement)
    {
        $this->rules['titleize'][$substr] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTitleizationRule($substr)
    {
        unset($this->rules['titleize'][$substr]);
        unset($this->cache['titleize'][$substr]);

        foreach ($this->cache['titleize'] as $key => $value) {
            if (stripos($key, $substr) !== false) {
                unset($this->cache['titleize'][$key]);
            }
        }
    }

    /**
     * Callback used in `#humanize()`.
     *
     * @param array $matches Regular expression matches.
     *
     * @return string String with first letter upper-cased.
     */
    private function humanizeCallback(array $matches)
    {
        return strtoupper($matches[1]);
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
