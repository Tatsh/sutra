<?php
namespace Sutra\Component\Url;

use Sutra\Component\String\Utf8HelperInterface;

class UrlParser implements UrlParserInterface
{
    protected $utf8Helper;

    public function __construct(Utf8HelperInterface $utf8Helper)
    {
        $this->utf8Helper = $utf8Helper;
    }

    public function get($uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        return preg_replace('#\?.*$#D', '', $uri);
    }

    public function getDomain($uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
            $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
            $format = 'http%s://%s';

            if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
                if ($port && $port != 443) {
                    return sprintf($format.':%d', 's', $_SERVER['SERVER_NAME'], $port);
                }

                return sprintf($format, 's', $_SERVER['SERVER_NAME']);
            }

            if ($port && $port != 80) {
                return sprintf($format.':%d', '', $_SERVER['SERVER_NAME'], $port);
            }

            return sprintf($format, '', $_SERVER['SERVER_NAME'], $port);
        }

        $parsed = $this->parse($uri);

        if (isset($parsed['port']) && ($parsed['port'] != 80 && $parsed['port'] != 443)) {
            return sprintf('%s://%s:%d', $parsed['scheme'], $parsed['host'], $parsed['port']);
        }

        return sprintf('%s://%s', $parsed['scheme'], $parsed['host']);
    }

    public function getQueryString($uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $parsed = $this->parse($uri);

        return $parsed['query'];
    }

    public function getWithQueryString($uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $parsed = $this->parse($uri);

        return sprintf('%s?%s', $parsed['path'], $parsed['query']);
    }

    public function makeFriendly($string, $maxLength = null, $delimiter = null)
    {
        // This allows omitting the max length, but including a delimiter
        if ($maxLength && !is_numeric($maxLength)) {
            $delimiter  = $maxLength;
            $maxLength = NULL;
        }

        $string = $this->htmlDecode($this->utf8Helper->ascii($string));
        $string = strtolower(trim($string));
        $string = str_replace("'", '', $string);

        if (!strlen($delimiter)) {
            $delimiter = '-';
        }

        $delimiterReplacement = strtr($delimiter, array('\\' => '\\\\', '$' => '\\$'));
        $delimiterRegex = preg_quote($delimiter, '#');

        $string = preg_replace('#[^a-z0-9\-_]+#', $delimiterReplacement, $string);
        $string = preg_replace('#' . $delimiterRegex . '{2,}#', $delimiterReplacement, $string);
        $string = preg_replace('#_-_#', '-', $string);
        $string = preg_replace('#(^' . $delimiterRegex . '+|' . $delimiterRegex . '+$)#D', '', $string);

        $length = strlen($string);

        if ($maxLength && $length > $maxLength) {
            $lastPos = strrpos($string, $delimiter, ($length - $maxLength - 1) * -1);
            if ($lastPos < ceil($maxLength / 2)) {
                $lastPos = $maxLength;
            }
            $string = substr($string, 0, $lastPos);
        }

        return $string;
    }

    public function removeFromQueryString($parameter, $uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $parameters = func_get_args();
        $qsArray = array();

        parse_str(self::getQueryString($uri), $qsArray);
        unset($qsArray[$parameter]);

        return '?'.http_build_query($qsArray, '', '&');
    }

    public function replaceInQueryString($parameter, $value, $uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $qsArray = array();

        parse_str(self::getQueryString($uri), $qsArray);

        settype($parameter, 'array');
        settype($value, 'array');

        if (sizeof($parameter) != sizeof($value)) {
            throw new ProgrammerException(
                "There are a different number of parameters and values.\nParameters:\n%1\$s\nValues\n%2\$s",
                $parameter,
                $value
            );
        }

        for ($i = 0; $i < sizeof($parameter); $i++) {
            $qsArray[$parameter[$i]] = $value[$i];
        }

        return '?'.http_build_query($qsArray, '', '&');
    }

    private function parse($uri)
    {
        $parsed = parse_url($uri);

        if ($parsed === false) {
            throw new URLParserException('URI "%s" is invalid', (string) $uri);
        }

        return $parsed;
    }

    private function htmlDecode($content)
    {
        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }
}

