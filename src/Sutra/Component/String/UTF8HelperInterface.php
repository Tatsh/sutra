<?php
namespace Sutra\String;

interface UTF8HelperInterface
{
    public function length($string);
    public function replace($string, $find, $replace, $caseSensitive = true);
    public function split($string, $delimiter = null);
    public function lower($string);
    public function upper($string);
    public function title($string);
    public function firstToUpper($string);
    public function trimLeft($string, $charList = null);
    public function trimRight($string, $charList = null);
    public function trim($string, $charList = null);
    public function indexOf($string, $needle, $offset = 0);
    public function lastIndexOf($string, $needle, $offset = 0);
    public function reverse($string);
    public function wordWrap($string, $width = 75, $break = '', $cut = false);
    public function padLeft($string, $padLength, $padString);
    public function padRight($string, $padLength, $padString);
    public function pad($string, $padLength, $padString);
    public function clean($string);
    public function isAscii($string);
}
