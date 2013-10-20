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
        'stem' => array(),
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
        'de',  //  mainly for Spanish and French
        'el',  // Spanish
        'feat',
        'featuring',
        'for',
        'from',
        'il',  // Italian
        'in',
        'into',
        'la',  // Spanish/French/Italian
        'lo',  // Italian
        'of',
        'off',
        'on',
        'or',
        'per',
        //'so',
        'te',  // Spanish/French
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
     * Word replacements for singular numbers.
     *
     * @var array
     */
    protected $numberReplacements = array(
        0 => 'zero',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
    );

    /**
     * URL parser.
     *
     * @var URLParserInterface
     */
    protected $urlParser;

    /**
     * UTF-8 helper.
     *
     * @var Utf8HelperInterface
     */
    protected $utf8;

    /**
     * Constructor.
     *
     * @param UrlParserInterface $urlParser URL parser instance.
     */
    public function __construct(UrlParserInterface $urlParser)
    {
        $this->urlParser = $urlParser;
        $this->utf8 = new Utf8Helper();
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
     * {@inheritdoc}
     *
     * @link http://tartarus.org/~martin/PorterStemmer/
     */
    public function stem($word)
    {
        if (isset($this->cache['stem'][$word])) {
            return $this->cache['stem'][$word];
        }

        $original = $word;

        $sV  = '^([^aeiou][^aeiouy]*)?[aeiouy]';
        $mgr0 = $sV . '[aeiou]*[^aeiou][^aeiouy]*';

        $sVRegex  = '#' . $sV . '#';
        $mgr0Regex = '#' . $mgr0 . '#';
        $meq1Regex = '#' . $mgr0 . '([aeiouy][aeiou]*)?$#';
        $mgr1Regex = '#' . $mgr0 . '[aeiouy][aeiou]*[^aeiou][^aeiouy]*#';

        $word = $this->utf8->ascii($word);
        $word = strtolower($word);

        if (strlen($word) < 3) {
            return $word;
        }

        if ($word[0] == 'y') {
            $word = 'Y' . substr($word, 1);
        }

        // Step 1a
        $word = preg_replace('#^(.+?)(?:(ss|i)es|([^s])s)$#', '\1\2\3', $word);

        // Step 1b
        if (preg_match('#^(.+?)eed$#', $word, $match)) {
            if (preg_match($mgr0Regex, $match[1])) {
                $word = substr($word, 0, -1);
            }

        }
        else if (preg_match('#^(.+?)(ed|ing)$#', $word, $match)) {
            if (preg_match($sVRegex, $match[1])) {
                $word = $match[1];
                if (preg_match('#(at|bl|iz)$#', $word)) {
                    $word .= 'e';
                }
                else if (preg_match('#([^aeiouylsz])\1$#', $word)) {
                    $word = substr($word, 0, -1);
                }
                else if (preg_match('#^[^aeiou][^aeiouy]*[aeiouy][^aeiouwxy]$#', $word)) {
                    $word .= 'e';
                }
            }
        }

        // Step 1c
        if (substr($word, -1) == 'y') {
            $stem = substr($word, 0, -1);
            if (preg_match($sVRegex, $stem)) {
                $word = $stem . 'i';
            }
        }

        // Step 2
        if (preg_match('#^(.+?)(ational|tional|enci|anci|izer|bli|alli|entli|eli|ousli|ization|ation|ator|alism|iveness|fulness|ousness|aliti|iviti|biliti|logi)$#', $word, $match)) {
            if (preg_match($mgr0Regex, $match[1])) {
                $word = $match[1] . strtr(
                    $match[2],
                    array(
                        'ational' => 'ate',  'tional'  => 'tion', 'enci'    => 'ence',
                        'anci'    => 'ance', 'izer'    => 'ize',  'bli'     => 'ble',
                        'alli'    => 'al',   'entli'   => 'ent',  'eli'     => 'e',
                        'ousli'   => 'ous',  'ization' => 'ize',  'ation'   => 'ate',
                        'ator'    => 'ate',  'alism'   => 'al',   'iveness' => 'ive',
                        'fulness' => 'ful',  'ousness' => 'ous',  'aliti'   => 'al',
                        'iviti'   => 'ive',  'biliti'  => 'ble',  'logi'    => 'log'
                    )
                );
            }
        }

        // Step 3
        if (preg_match('#^(.+?)(icate|ative|alize|iciti|ical|ful|ness)$#', $word, $match)) {
            if (preg_match($mgr0Regex, $match[1])) {
                $word = $match[1] . strtr(
                    $match[2],
                    array(
                        'icate' => 'ic', 'ative' => '', 'alize' => 'al', 'iciti' => 'ic',
                        'ical'  => 'ic', 'ful'   => '', 'ness'  => ''
                    )
                );
            }
        }

        // Step 4
        if (preg_match('#^(.+?)(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ou|ism|ate|iti|ous|ive|ize|(?<=[st])ion)$#', $word, $match) && preg_match($mgr1Regex, $match[1])) {
            $word = $match[1];
        }

        // Step 5
        if (substr($word, -1) == 'e') {
            $stem = substr($word, 0, -1);
            if (preg_match($mgr1Regex, $stem)) {
                $word = $stem;
            }
            else if (preg_match($meq1Regex, $stem) && !preg_match('#^[^aeiou][^aeiouy]*[aeiouy][^aeiouwxy]$#', $stem)) {
                $word = $stem;
            }
        }

        if (preg_match('#ll$#', $word) && preg_match($mgr1Regex, $word)) {
            $word = substr($word, 0, -1);
        }

        if ($word[0] == 'Y') {
            $word = 'y' . substr($word, 1);
        }

        $this->cache['stem'][$original] = $word;

        return $word;
    }

    /**
     * {@inheritdoc}
     */
    public function inflectOnQuantity($quantity, $singular, $plural = null, $wordsForSingleDigits = false)
    {
        if (!$plural) {
            $plural = $this->pluralize($singular);
        }

        if (is_array($quantity)) {
            $quantity = sizeof($quantity);
        }

        if ($quantity == 1) {
            $output = $singular;
        }
        else {
            $output = $plural;

            // Handle placement of the quantity into the output
            if (strpos($output, '%d') !== false) {
                if ($wordsForSingleDigits && $quantity < 10) {
                    $quantity = $this->numberReplacements[$quantity];
                }

                $output = str_replace('%d', $quantity, $output);
            }
        }

        return $output;
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
