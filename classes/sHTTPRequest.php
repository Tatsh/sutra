<?php
/**
 * Interface to making HTTP requests.
 *
 * @copyright Copyright (c) 2012 bne1.
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.2
 */
class sHTTPRequest {
  /**
   * The URI to request from.
   *
   * @var string
   */
  private $url = NULL;

  /**
   * The data received.
   *
   * @var string
   */
  private $data = NULL;

  /**
   * HTTP timeout.
   *
   * @var integer
   */
  private $timeout = NULL;

  /**
   * The HTTP headers to use.
   *
   * @var array
   */
  private $headers = array('User-Agent' => 'Mozilla/5.0 Sutra-sHTTPRequest/1.2');

  /**
   * The HTTP method.
   *
   * @var string
   */
  private $method = 'GET';

  /**
   * The protocol. Either https or http.
   *
   * @var string
   */
  private $protocol = 'http';

  /**
   * The content to POST or PUT.
   *
   * @var string
   */
  private $content = NULL;

  /**
   * The proxy to use.
   *
   * @var string
   */
  private $proxy = NULL;

  /**
   * Constructor.
   *
   * @throws fProgrammerException If the URL is not valid.
   *
   * @param string $url Full URL to the resource.
   * @param string $method HTTP method. One of: 'GET', 'POST', 'PUT', 'DELETE'.
   * @param integer $timeout Timeout. Will default to default_socket_timeout
   *   in php.ini if not specified.
   * @return sHTTPRequest The object.
   */
  public function __construct($url, $method = 'GET', $timeout = NULL) {
    $matches = array();

    if (!preg_match('#^(?P<protocol>http(s)?)://#', $url, $matches)) {
      throw new fProgrammerException('The argument specified, "%s", is not a valid HTTP URL.', $url);
    }

    if ($timeout === NULL || !is_numeric($timeout)) {
      $timeout = ini_get('default_socket_timeout');
    }

    $this->url = $url;
    $this->timeout = (int)$timeout;
    $this->protocol = $matches['protocol'];

    $this->setMethod($method);
  }

  /**
   * Set HTTP headers in simple array key => value format.
   *
   * Example: array('User-Agent' => 'my custom user agent',
   *   'X-GData-Authorization-Key' => 'key');
   *
   * @param array $headers Header to set.
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function setHeaders(array $headers) {
    $this->headers = $headers;
    return $this;
  }

  /**
   * Set a specific header.
   *
   * @param string $name Name of the header.
   * @param string $value Value of the header.
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function setHeader($name, $value) {
    $this->headers = array_merge($this->headers, array($name => $value));
    return $this;
  }

  /**
   * Set the user agent header.
   *
   * @param string $agent User agent string.
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function setUserAgent($agent) {
    $this->headers = array_merge($this->headers, array('User-Agent' => $agent));
    return $this;
  }

  /**
   * Set the method.
   *
   * @throws fProgrammerException If the method is not a valid HTTP method.
   *
   * @param string $method HTTP method. One of: 'GET', 'POST', 'PUT', 'DELETE'.
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function setMethod($method = 'GET') {
    $valid = array('GET', 'POST', 'PUT', 'DELETE');
    $method_upper = strtoupper($method);

    if (!in_array($method_upper, $valid)) {
      throw new fProgrammerException('The method specified, "%s", is not a valid HTTP method.', $method);
    }

    $this->method = $method_upper;

    return $this;
  }

  /**
   * Get the HTTP headers.
   *
   * @param boolean $as_string If TRUE, return the headers as a string
   *   according to standards (\r\n at the end of each header value).
   * @return array|string Headers either as string or array.
   */
  public function getHeaders($as_string = FALSE) {
    if ($as_string) {
      $ret = '';
      foreach ($this->headers as $key => $value) {
        $ret .= "$key: $value\r\n";
      }
      return $ret;
    }
    return $this->headers;
  }

  /**
   * Gets the data from the source URI. Will call connect() if necessary.
   *
   * @return string The data retrieved.
   * @see sHTTPRequest::connect()
   */
  public function getData() {
    $this->connect();
    return $this->data;
  }

  /**
   * Set the content. This is mainly for use with POST and PUT requests.
   *
   * @param string $content Content to POST or PUT.
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function setContent($content) {
    $this->content = $content;
    return $this;
  }

  /**
   * Get the content set.
   *
   * @return string The content set. Will return empty string if no content has
   *   been set.
   */
  public function getContent() {
    return is_null($this->content) ? '' : $this->content;
  }

  /**
   * Set a proxy to use.
   *
   * @param string $proxy Proxy URI.
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function setProxy($proxy) {
    $this->proxy = $proxy;
    return $this;
  }

  /**
   * Gets the proxy in use.
   *
   * @return string The proxy in use. Will return empty string if no proxy
   *   has been set.
   */
  public function getProxy() {
    return is_null($this->proxy) ? '' : $this->proxy;
  }

  /**
   * Removes the proxy in use with this object.
   *
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function removeProxy() {
    $this->proxy = NULL;
    return $this;
  }

  /**
   * Connect to the URI and get the data.
   *
   * @throws fUnexpectedException If the connection fails.
   *
   * @return sHTTPRequest The object to allow method chaining.
   */
  public function connect($reconnect = FALSE) {
    if ($this->data === NULL || $reconnect) {
      // http://am.php.net/manual/en/context.http.php
      $opts = array(
        'method' => $this->method,
        'timeout' => $this->timeout,
        'user_agent' => $this->headers['User-Agent'],
        'content' => $this->content,
        'header' => $this->getHeaders(TRUE),
      );

      if ($this->content !== NULL) {
        $opts['content'] = $this->content;
      }
      if ($this->proxy !== NULL) {
        $opts['proxy'] = $this->proxy;
      }

      $context = stream_context_create(array(
        $this->protocol => $opts,
      ));
      fCore::startErrorCapture();
      $data = file_get_contents($this->url, FALSE, $context);
      fCore::stopErrorCapture();

      if ($data === FALSE) {
        throw new fUnexpectedException('The URI, "%s", could not be loaded.', $this->url);
      }

      $this->data = $data;
    }
    return $this;
  }

  /**
   * Alias for connect().
   *
   * @return sHTTPRequest The object to allow method chaining.
   * @see sHTTPRequest::connect()
   */
  public function send($reconnect = FALSE) {
    return $this->connect($reconnect);
  }
}
