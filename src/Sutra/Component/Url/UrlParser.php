<?php
namespace Sutra\Component\Url;

use Sutra\Component\String\Utf8HelperInterface;
use Sutra\Component\Url\Exception\ProgrammerException;
use Sutra\Component\Url\Exception\UrlParserException;

/**
 * {@inheritdoc}
 */
class UrlParser implements UrlParserInterface
{
    /**
     * UTF-8 helper.
     *
     * @var Utf8HelperInterface
     */
    protected $utf8Helper;

    /**
     * Constructor.
     *
     * @param Utf8HelperInterface $utf8Helper UTF-8 helper instance.
     */
    public function __construct(Utf8HelperInterface $utf8Helper)
    {
        $this->utf8Helper = $utf8Helper;
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        return preg_replace('#\?.*$#D', '', $uri);
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getQueryString($uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $parsed = $this->parse($uri);

        return $parsed['query'];
    }

    /**
     * {@inheritdoc}
     */
    public function getWithQueryString($uri = null)
    {
        if ($uri === null) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $parsed = $this->parse($uri);

        return sprintf('%s?%s', $parsed['path'], $parsed['query']);
    }

    /**
     * {@inheritdoc}
     */
    public function makeFriendly($string, $maxLength = null, $delimiter = '-')
    {
        // This allows omitting the max length, but including a delimiter
        if ($maxLength && !is_numeric($maxLength)) {
            $delimiter  = $maxLength;
            $maxLength = null;
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
            throw new ProgrammerException('There are a different number of parameters and values');
        }

        for ($i = 0; $i < sizeof($parameter); $i++) {
            $qsArray[$parameter[$i]] = $value[$i];
        }

        return '?'.http_build_query($qsArray, '', '&');
    }

    /**
     * For parsing URL but throwing a consistent exception.
     *
     * @param string $uri URI to parse.
     *
     * @return array Parsed URL parts.
     *
     * @throws UrlParserException If the URI is invalid.
     *
     * @see parse_url()
     */
    private function parse($uri)
    {
        $parsed = parse_url($uri);

        if ($parsed === false) {
            throw new UrlParserException('URI "%s" is invalid', (string) $uri);
        }

        return $parsed;
    }

    /**
     * For consistent HTML-encoded string decoding.
     *
     * @param string $content String to decode.
     *
     * @return string Decoded string.
     */
    private function htmlDecode($content)
    {
        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }
}
