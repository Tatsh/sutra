<?php
namespace Sutra\Component\Url;

interface UrlParserInterface
{
    public function get($uri = null);
    public function getDomain($uri = null);
    public function getQueryString($uri = null);
    public function getWithQueryString($uri = null);
    public function makeFriendly($string, $maxLength = null, $delimiter = null);
    public function removeFromQueryString($parameter, $uri = null);
    public function replaceInQueryString($parameter, $value, $uri = null);
}
