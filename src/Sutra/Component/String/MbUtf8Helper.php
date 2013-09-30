<?php
namespace Sutra\Component\String;

/**
 * Utf8Helper extension that uses the `mbstring` extension.
 *
 * @replaces fUTF8
 */
class MbUtf8Helper extends Utf8Helper
{
    /**
     * All lowercase UTF-8 characters not properly handled by `mb_strtoupper()`
     *   mapped to upper-case characters.
     *
     * @var array
     */
    protected static $mbLowerToUpperFix = array(
        'ɘ' => 'Ǝ', 'ǲ' => 'Ǳ', 'ა' => 'Ⴀ', 'ბ' => 'Ⴁ', 'გ' => 'Ⴂ', 'დ' => 'Ⴃ',
        'ე' => 'Ⴄ', 'ვ' => 'Ⴅ', 'ზ' => 'Ⴆ', 'თ' => 'Ⴇ', 'ი' => 'Ⴈ', 'კ' => 'Ⴉ',
        'ლ' => 'Ⴊ', 'მ' => 'Ⴋ', 'ნ' => 'Ⴌ', 'ო' => 'Ⴍ', 'პ' => 'Ⴎ', 'ჟ' => 'Ⴏ',
        'რ' => 'Ⴐ', 'ს' => 'Ⴑ', 'ტ' => 'Ⴒ', 'უ' => 'Ⴓ', 'ფ' => 'Ⴔ', 'ქ' => 'Ⴕ',
        'ღ' => 'Ⴖ', 'ყ' => 'Ⴗ', 'შ' => 'Ⴘ', 'ჩ' => 'Ⴙ', 'ც' => 'Ⴚ', 'ძ' => 'Ⴛ',
        'წ' => 'Ⴜ', 'ჭ' => 'Ⴝ', 'ხ' => 'Ⴞ', 'ჯ' => 'Ⴟ', 'ჰ' => 'Ⴠ', 'ჱ' => 'Ⴡ',
        'ჲ' => 'Ⴢ', 'ჳ' => 'Ⴣ', 'ჴ' => 'Ⴤ', 'ჵ' => 'Ⴥ', 'ⓐ' => 'Ⓐ', 'ⓑ' => 'Ⓑ',
        'ⓒ' => 'Ⓒ', 'ⓓ' => 'Ⓓ', 'ⓔ' => 'Ⓔ', 'ⓕ' => 'Ⓕ', 'ⓖ' => 'Ⓖ', 'ⓗ' => 'Ⓗ',
        'ⓘ' => 'Ⓘ', 'ⓙ' => 'Ⓙ', 'ⓚ' => 'Ⓚ', 'ⓛ' => 'Ⓛ', 'ⓜ' => 'Ⓜ', 'ⓝ' => 'Ⓝ',
        'ⓞ' => 'Ⓞ', 'ⓟ' => 'Ⓟ', 'ⓠ' => 'Ⓠ', 'ⓡ' => 'Ⓡ', 'ⓢ' => 'Ⓢ', 'ⓣ' => 'Ⓣ',
        'ⓤ' => 'Ⓤ', 'ⓥ' => 'Ⓥ', 'ⓦ' => 'Ⓦ', 'ⓧ' => 'Ⓧ', 'ⓨ' => 'Ⓨ', 'ⓩ' => 'Ⓩ'
    );

    /**
     * All uppercase UTF-8 characters not properly handled by `mb_strtolower()`
     *   mapped to lowercase characters.
     *
     * @var array
     */
    protected static $mbUpperToLowerFix = array(
        'ǝ' => 'ɘ', 'ǅ' => 'ǆ', 'ǈ' => 'ǉ', 'ǋ' => 'ǌ', 'Ⴀ' => 'ა', 'Ⴁ' => 'ბ',
        'Ⴂ' => 'გ', 'Ⴃ' => 'დ', 'Ⴄ' => 'ე', 'Ⴅ' => 'ვ', 'Ⴆ' => 'ზ', 'Ⴇ' => 'თ',
        'Ⴈ' => 'ი', 'Ⴉ' => 'კ', 'Ⴊ' => 'ლ', 'Ⴋ' => 'მ', 'Ⴌ' => 'ნ', 'Ⴍ' => 'ო',
        'Ⴎ' => 'პ', 'Ⴏ' => 'ჟ', 'Ⴐ' => 'რ', 'Ⴑ' => 'ს', 'Ⴒ' => 'ტ', 'Ⴓ' => 'უ',
        'Ⴔ' => 'ფ', 'Ⴕ' => 'ქ', 'Ⴖ' => 'ღ', 'Ⴗ' => 'ყ', 'Ⴘ' => 'შ', 'Ⴙ' => 'ჩ',
        'Ⴚ' => 'ც', 'Ⴛ' => 'ძ', 'Ⴜ' => 'წ', 'Ⴝ' => 'ჭ', 'Ⴞ' => 'ხ', 'Ⴟ' => 'ჯ',
        'Ⴠ' => 'ჰ', 'Ⴡ' => 'ჱ', 'Ⴢ' => 'ჲ', 'Ⴣ' => 'ჳ', 'Ⴤ' => 'ჴ', 'Ⴥ' => 'ჵ',
        'ᾈ' => 'ᾀ', 'ᾉ' => 'ᾁ', 'ᾊ' => 'ᾂ', 'ᾋ' => 'ᾃ', 'ᾌ' => 'ᾄ', 'ᾍ' => 'ᾅ',
        'ᾎ' => 'ᾆ', 'ᾏ' => 'ᾇ', 'ᾘ' => 'ᾐ', 'ᾙ' => 'ᾑ', 'ᾚ' => 'ᾒ', 'ᾛ' => 'ᾓ',
        'ᾜ' => 'ᾔ', 'ᾝ' => 'ᾕ', 'ᾞ' => 'ᾖ', 'ᾟ' => 'ᾗ', 'ᾨ' => 'ᾠ', 'ᾩ' => 'ᾡ',
        'ᾪ' => 'ᾢ', 'ᾫ' => 'ᾣ', 'ᾬ' => 'ᾤ', 'ᾭ' => 'ᾥ', 'ᾮ' => 'ᾦ', 'ᾯ' => 'ᾧ',
        'Ⓐ' => 'ⓐ', 'Ⓑ' => 'ⓑ', 'Ⓒ' => 'ⓒ', 'Ⓓ' => 'ⓓ', 'Ⓔ' => 'ⓔ', 'Ⓕ' => 'ⓕ',
        'Ⓖ' => 'ⓖ', 'Ⓗ' => 'ⓗ', 'Ⓘ' => 'ⓘ', 'Ⓙ' => 'ⓙ', 'Ⓚ' => 'ⓚ', 'Ⓛ' => 'ⓛ',
        'Ⓜ' => 'ⓜ', 'Ⓝ' => 'ⓝ', 'Ⓞ' => 'ⓞ', 'Ⓟ' => 'ⓟ', 'Ⓠ' => 'ⓠ', 'Ⓡ' => 'ⓡ',
        'Ⓢ' => 'ⓢ', 'Ⓣ' => 'ⓣ', 'Ⓤ' => 'ⓤ', 'Ⓥ' => 'ⓥ', 'Ⓦ' => 'ⓦ', 'Ⓧ' => 'ⓧ',
        'Ⓨ' => 'ⓨ', 'Ⓩ' => 'ⓩ'
    );

    /**
     * {@inheritdoc}
     */
    public function length($string)
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function lower($string)
    {
        // We get better performance falling back for ASCII strings
        if ($this->isAscii($string)) {
            return strtolower($string);
        }

        $string = mb_strtolower($string, 'utf-8');

        // For some reason mb_strtolower misses some characters
        return strtr($string, static::$mbUpperToLowerFix);
    }

    /**
     * {@inheritdoc}
     */
    public function upper($string)
    {
        if ($this->isAscii($string)) {
            return strtoupper($string);
        }

        $string = mb_strtoupper($string, 'utf-8');

        return strtr($string, static::$mbLowerToUpperFix);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($string, $needle, $offset = 0)
    {
        return mb_strpos($string, $needle, $offset, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function clean($value)
    {
        if (!is_array($value)) {
            $oldSub = ini_get('mbstring.substitute_character');
            ini_set('mbstring.substitute_character', 'none');
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            ini_set('mbstring.substitute_character', $oldSub);

            return $value;
        }

        $keys = array_keys($value);
        $numKeys = sizeof($keys);

        for ($i = 0; $i < $numKeys; $i++) {
            $value[$keys[$i]] = $this->clean($value[$keys[$i]]);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function substr($string, $start, $length = null)
    {
        $strLen = $this->length($string);

        if (abs($start) > $strLen) {
            return false;
        }

        if (!$length) {
            if ($start >= 0) {
                $length = $strLen - $start;
            }
            else {
                $length = abs($start);
            }
        }

        return mb_substr($string, $start, $length, 'UTF-8');
    }
}
