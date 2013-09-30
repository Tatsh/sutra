<?php
namespace Sutra\Component\String;

/**
 * UTF-8 string helper.
 *
 * @replaces fUTF8
 */
interface Utf8HelperInterface
{
    /**
     * Determines length of a string.
     *
     * @param string $string String to check.
     *
     * @return integer Length.
     *
     * @replaces ::len
     */
    public function length($string);

    /**
     * Replaces in a string. Replaces all instances found.
     *
     * @param string $string  String.
     * @param string $find    Substring to find.
     * @param string $replace Replacement string.
     *
     * @return string Fixed string.
     *
     * @replaces ::replace
     */
    public function replace($string, $find, $replace);

    /**
     * Replaces in a string in a case-insensitive manner. Replaces all
     *   instances found.
     *
     * @param string $string  String.
     * @param string $find    Substring to find.
     * @param string $replace Replacement string.
     *
     * @return string Fixed string.
     *
     * @replaces ::ireplace
     */
    public function caseInsensitiveReplace($string, $find, $replace);

    /**
     * Splits a string by a delimiter or none (makes an array of characters).
     *
     * @param string      $string    String to split.
     * @param string|null $delimiter Delimiter to use. If no argument is
     *   passed, the string is split into an array.
     *
     * @return array Array of characters.
     *
     * @replaces ::explode
     * @replaces explode
     * @replaces str_split
     */
    public function split($string, $delimiter = null);

    /**
     * Lower-cases a string.
     *
     * @param string $string String to lower-case.
     *
     * @return string Lower-case form of string.
     *
     * @replaces ::lower
     * @replaces strtolower
     */
    public function lower($string);

    /**
     * Upper-cases a string.
     *
     * @param string $string String to upper-case.
     *
     * @return string Lower-case form of string.
     *
     * @replaces ::upper
     * @replaces strtoupper
     */
    public function upper($string);

    /**
     * Title-cases a string. Handles special words like 'of'.
     *
     * @param string $string String to change.
     *
     * @return string Title form of string.
     */
    public function title($string);

    /**
     * Changes first character to upper-case.
     *
     * @param string $string String to fix.
     *
     * @return string String with first character made to be upper-case.
     *
     * @replaces ::ucfirst
     * @replaces ucfirst
     */
    public function firstToUpper($string);

    /**
     * Trims a string on the left side.
     *
     * @param string $string   String.
     * @param string $charList String of characters to remove. Defaults to
     *   whitespace.
     *
     * @return string String.
     *
     * @replaces ::ltrim
     * @replaces ltrim
     */
    public function trimLeft($string, $charList = null);

    /**
     * Trims a string on the right side.
     *
     * @param string $string   String.
     * @param string $charList String of characters to remove. Defaults to
     *   whitespace.
     *
     * @return string String.
     *
     * @replaces ::rtrim
     * @replaces rtrim
     */
    public function trimRight($string, $charList = null);

    /**
     * Trims a string on both sides.
     *
     * @param string $string   String.
     * @param string $charList String of characters to remove. Defaults to
     *   whitespace.
     *
     * @return string String.
     *
     * @replaces ::trim
     * @replaces trim
     */
    public function trim($string, $charList = null);

    /**
     * Gets the first position of a string needle.
     *
     * @param string  $string String to check.
     * @param string  $needle Needle to find.
     * @param integer $offset Offset to start at.
     *
     * @return integer|boolean Returns `false` if the needle is not found, or
     *   an integer. Can return 0 so use of `===` is recommended.
     *
     * @replaces ::pos
     * @replaces strpos
     */
    public function indexOf($string, $needle, $offset = 0);

    /**
     * Gets the last position of a string needle.
     *
     * @param string  $string String to check.
     * @param string  $needle Needle to find.
     * @param integer $offset Offset to start at.
     *
     * @return integer|boolean Returns `false` if the needle is not found, or
     *   an integer. Can return 0 so use of `===` is recommended.
     *
     * @replaces ::rpos
     * @replaces strrpos
     */
    public function lastIndexOf($string, $needle, $offset = 0);

    /**
     * Returns reversed string.
     *
     * @param string $string String to reverse.
     *
     * @return string Reversed string.
     *
     * @replaces ::rev
     * @replaces strrev
     */
    public function reverse($string);

    /**
     * Word wraps a string.
     *
     * @param string  $string String to word-wrap.
     * @param integer $width  Width of each line.
     * @param string  $break  String to use as a break for each line.
     * @param boolean $cut    If words longer than the character width should
     *   be split to fit.
     *
     * @return string Word wrapped string.
     *
     * @replaces ::wordwrap
     * @replaces wordwrap
     */
    public function wordWrap($string, $width = 75, $break = "\n", $cut = false);

    /**
     * Pads string on the left.
     *
     * @param string  $string    String.
     * @param integer $padLength Pad length.
     * @param string  $padString Pad string.
     *
     * @return string Padded string.
     *
     * @replaces ::pad
     * @replaces str_pad
     */
    public function padLeft($string, $padLength, $padString = ' ');

    /**
     * Pads string on the right.
     *
     * @param string  $string    String.
     * @param integer $padLength Pad length.
     * @param string  $padString Pad string.
     *
     * @return string Padded string.
     *
     * @replaces ::pad
     * @replaces str_pad
     */
    public function padRight($string, $padLength, $padString = ' ');

    /**
     * Pads string on both sides.
     *
     * @param string  $string    String.
     * @param integer $padLength Pad length.
     * @param string  $padString Pad string.
     *
     * @return string Padded string.
     *
     * @replaces ::pad
     * @replaces str_pad
     */
    public function pad($string, $padLength, $padString = ' ');

    /**
     * Cleans a string of malformed UTF-8.
     *
     * @param string $string String to fix.
     *
     * @return Cleaned string.
     *
     * @replaces ::clean
     */
    public function clean($string);

    /**
     * Detects if a UTF-8 string contains only ASCII characters.
     *
     * @param string $string The string to check.
     *
     * @return boolean `true` if the string only contains ASCII characters.
     */
    public function isAscii($string);

    /**
     * Converts string to ascii form (replacing accented letters, etc).
     *
     * @param string $string String to convert.
     *
     * @return string Converted string.
     *
     * @replaces ::ascii
     */
    public function ascii($string);
}
