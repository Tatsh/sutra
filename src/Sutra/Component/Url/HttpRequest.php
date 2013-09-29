<?php
namespace Sutra\Component\Url;

use Sutra\Component\Url\Exception\ProgrammerException;
use Sutra\Component\Url\Exception\UnexpectedException;

/**
 * Interface to making HTTP requests.
 *
 * @author Andrew Udvare <audvare@gmail.com>
 */
class HttpRequest
{
    /**
     * The URI to request from.
     *
     * @var string
     */
    protected $url = null;

    /**
     * The data received.
     *
     * @var string
     */
    protected $data = null;

    /**
     * HTTP timeout.
     *
     * @var integer
     */
    protected $timeout = null;

    /**
     * The HTTP headers to use.
     *
     * @var array
     */
    protected $headers = array('User-Agent' => 'Mozilla/5.0 Sutra-HttpRequest/1.3');

    /**
     * The HTTP method.
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * The protocol. Either https or http.
     *
     * @var string
     */
    protected $protocol = 'http';

    /**
     * The content to POST or PUT.
     *
     * @var string
     */
    protected $content = null;

    /**
     * The proxy to use.
     *
     * @var string
     */
    protected $proxy = null;

    /**
     * POST data content type.
     *
     * @var string
     */
    protected $postDataContentType = 'application/x-www-form-urlencoded';

    /**
     * Authentication details.
     *
     * @var array
     */
    protected $authentication = array();

    /**
     * Last response headers.
     *
     * @var array
     */
    protected $responseHeader = null;

    /**
     * Last HTTP status.
     *
     * @var integer
     */
    protected $status = null;

    /**
     * Constructor.
     *
     * @param string  $url     Full URL to the resource.
     * @param string  $method  HTTP method.
     * @param integer $timeout Timeout. Will default to `default_socket_timeout`
     *     in `php.ini` if not specified.
     *
     * @throws ProgrammerException If the URL is not valid.
     */
    public function __construct($url, $method = 'GET', $timeout = null)
    {
        $matches = array();

        if (!preg_match('#^(?P<protocol>http(s)?)://#', $url, $matches)) {
            throw new ProgrammerException('The argument specified, "%s", is not a valid HTTP URL.', $url);
        }

        if ($timeout === null || !is_numeric($timeout)) {
            $timeout = ini_get('default_socket_timeout');
        }

        $this->url = $url;
        $this->timeout = (int) $timeout;
        $this->protocol = $matches['protocol'];

        $this->setMethod($method);
    }

    /**
     * Set HTTP headers in simple array key => value format.
     *
     * Example: array('User-Agent' => 'my custom user agent',
     *     'X-GData-Authorization-Key' => 'key');
     *
     * @param array $headers Header to set.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        unset($this->headers['Content-Length']);

        return $this;
    }

    /**
     * Set the Accept header. Some servers read this header and based upon its
     *     value, decide which content (and format) to return.
     *
     * Example:
     *     text/html,application/xhtml+xml
     *
     * @param string $contentTypes Comma-delimited list of content types.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function setAcceptHeader($contentTypes)
    {
        $this->headers['Accept'] = $contentTypes;

        return $this;
    }

    /**
     * Set authentication details.
     *
     * @param string $userName User name.
     * @param string $password Password.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function setCredentials($userName, $password)
    {
        $this->authentication = array($userName, $password);
    }

    /**
     * Set a specific header.
     *
     * @param string $name  Name of the header. Content-Length is not supported
     *     (it is added dynamically).
     * @param string $value Value of the header.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function setHeader($name, $value)
    {
        if (strtolower($name) === 'content-length') {
            return $this;
        }

        $this->headers = array_merge($this->headers, array($name => $value));

        return $this;
    }

    /**
     * Set the user agent header.
     *
     * @param string $agent User agent string.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function setUserAgent($agent)
    {
        $this->headers = array_merge($this->headers, array('User-Agent' => $agent));

        return $this;
    }

    /**
     * Set the method.
     *
     * @param string $method HTTP method.
     *
     * @return HttpRequest The object to allow method chaining.
     *
     * @throws ProgrammerException If the method is not a valid HTTP method.
     */
    public function setMethod($method = 'GET')
    {
        $valid = array('GET', 'POST', 'PUT', 'DELETE', 'HEAD');
        $methodUpper = strtoupper($method);

        if (!in_array($methodUpper, $valid)) {
            throw new ProgrammerException('The method specified, "%s", is not a valid HTTP method.', $method);
        }

        $this->method = $methodUpper;

        return $this;
    }

    /**
     * Get the HTTP headers.
     *
     * @param boolean $asString If true, return the headers as a string
     *     according to standards (`\r\n` at the end of each header value).
     *
     * @return array|string Headers either as string or array.
     */
    public function getHeaders($asString = false)
    {
        $headers = $this->headers;
        $headers['Content-Length'] = strlen($this->content);

        if (!empty($this->authentication)) {
            $auth = base64_encode($this->authentication[0].':'.$this->authentication[1]);
            $headers['Authorization'] = 'Basic '.$auth;
        }

        if ($asString) {
            $ret = '';
            foreach ($headers as $key => $value) {
                $ret .= "$key: $value\r\n";
            }

            return $ret;
        }

        return $headers;
    }

    /**
     * Sets the POST data content type.
     *
     * @param string $type A mimetype.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function setPostDataContentType($type)
    {
        $this->postDataContentType = $type;

        return $this;
    }

    /**
     * Gets the data from the source URI. Will call connect() if necessary.
     *
     * @return string The data retrieved.
     *
     * @see HttpRequest::connect()
     */
    public function getData()
    {
        $this->connect();

        return $this->data;
    }

    /**
     * Set the content. This is mainly for use with POST and PUT requests.
     *
     * @param string|array $content Content to POST or PUT.
     *
     * @return HttpRequest The object to allow method chaining.
     *
     * @see http_build_query()
     */
    public function setContent($content)
    {
        if (is_array($content)) {
            $content = http_build_query($content);
        }

        $this->content = $content;

        return $this;
    }

    /**
     * Get the content set.
     *
     * @return string The content set. Will return empty string if no content has
     *     been set.
     */
    public function getContent()
    {
        return !$this->content ? '' : $this->content;
    }

    /**
     * Set a proxy to use.
     *
     * @param string $proxy Proxy URI.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Gets the proxy in use.
     *
     * @return string The proxy in use. Will return empty string if no proxy
     *     has been set.
     */
    public function getProxy()
    {
        return !$this->proxy ? '' : $this->proxy;
    }

    /**
     * Removes the proxy in use with this object.
     *
     * @return HttpRequest The object to allow method chaining.
     */
    public function removeProxy()
    {
        $this->proxy = null;

        return $this;
    }

    /**
     * Connect to the URI and get the data.
     *
     * @param boolean $reconnect Force re-fetching of the data.
     *
     * @return HttpRequest The object to allow method chaining.
     *
     * @throws UnexpectedException If the connection fails.
     */
    public function connect($reconnect = false)
    {
        if ($this->data === null || $reconnect) {
            // http://am.php.net/manual/en/context.http.php
            $opts = array(
                'method' => $this->method,
                'timeout' => $this->timeout,
                'user_agent' => $this->headers['User-Agent'],
                'content' => $this->content,
                'header' => $this->getHeaders(true),
            );

            if ($this->content !== null) {
                $opts['content'] = $this->content;
            }
            if ($this->proxy !== null) {
                $opts['proxy'] = $this->proxy;
            }

            $context = stream_context_create(array(
                $this->protocol => $opts,
            ));

            $data = @file_get_contents($this->url, false, $context);

            $this->response_header = isset($http_response_header) ? $http_response_header : null; // PHP is so strange

            if ($data === false) {
                throw new UnexpectedException('The URI, "%s", could not be loaded.', $this->url);
            }

            $this->data = $data;
        }

        return $this;
    }

    /**
     * Get the last HTTP status code.
     *
     * @return integer The last status code.
     */
    public function getStatus()
    {
        if ($this->status === null && isset($this->responseHeader[0])) {
            $matches = array();
            $isMatch = preg_match('#HTTP/1.\d\s(\d{3})#', $this->responseHeader[0], $matches);

            if ($isMatch) {
                $this->status = (int) $matches[1];
            }
        }

        return $this->status;
    }

    /**
     * Get the last received response headers.
     *
     * @return array Array of response headers.
     */
    public function getResponseHeaders()
    {
        return $this->responseHeader;
    }

    /**
     * Alias for `#connect()`.
     *
     * @param boolean $reconnect Force re-fetching of the data.
     *
     * @return HttpRequest The object to allow method chaining.
     *
     * @see `#connect()`
     */
    public function send($reconnect = false)
    {
        return $this->connect($reconnect);
    }

    /**
     * Gets data from a JSON source.
     *
     * @param boolean $assoc   If the data should be returned as associative array.
     *
     * @return mixed JSON decoded value. Can return null.
     *
     * @see json_decode()
     *
     * @todo Handle if `json_decode()` has an error.
     */
    public function getJson($assoc = false)
    {
        $this->setAcceptHeader('application/json');
        $data = $this->getData();

        return json_decode($data, $assoc);
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
