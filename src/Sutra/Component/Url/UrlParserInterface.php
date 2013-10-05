<?php
namespace Sutra\Component\Url;

/**
 * Parses URIs.
 *
 * @replaces fURL
 */
interface UrlParserInterface
{
    /**
     * Returns the requested URL without domain name or query string.
     *
     * @param string $uri URL to use instead of requested URL.
     *
     * @return string The URL without the query string.
     *
     * @replaces ::get
     */
    public function get($uri = null);

    /**
     * Returns the current domain name, with protocol prefix. Port will be
     *   included if not 80 for HTTP or 443 for HTTPS.
     *
     * @param string $uri URL to use instead of requested URL.
     *
     * @return string The current domain name, prefixed by `http://` or
     *   `https://`.
     *
     * @replaces ::getDomain
     */
    public function getDomain($uri = null);

    /**
     * Returns the current query string.
     *
     * @param string $uri URL to use instead of requested URL.
     *
     * @return string The query string.
     *
     * @replaces ::getQueryString
     */
    public function getQueryString($uri = null);

    /**
     * Returns the requested URL with query string (without domain).
     *
     * @param string $uri URL to use instead of requested URL.
     *
     * @return string The URL with the query string.
     *
     * @replaces ::getWithQueryString
     */
    public function getWithQueryString($uri = null);

    /**
     * Changes a string into a URL-friendly string (slug).
     *
     * @param string  $string    The string to convert.
     * @param integer $maxLength Maximum length.
     * @param string  $delimiter Delimiter to use instead of `-`.
     *
     * @return string URL friendly version of the string.
     *
     * @replaces ::makeFriendly
     */
    public function makeFriendly($string, $maxLength = null, $delimiter = '-');

    /**
     * Removes a parameter from the query string.
     *
     * @param string $parameter Parameter name to remove.
     * @param string $uri URL to use instead of requested URL.
     *
     * @return string Query string with parameter removed. First character is
     *   `?`.
     *
     * @replaces ::removeFromQueryString
     */
    public function removeFromQueryString($parameter, $uri = null);

    /**
     * Replaces a value in the query string.
     *
     * If arrays are used, they must be aligned.
     *
     * @param string|array $parameter Query string parameter.
     * @param string|array $value     Value to set parameter to.
     *
     * @return string Query string with parameter replaced. First character is
     *   `?`.
     *
     * @replaces ::replaceInQueryString
     */
    public function replaceInQueryString($parameter, $value, $uri = null);
}
