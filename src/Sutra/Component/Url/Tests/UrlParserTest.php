<?php
namespace Sutra\Component\Url\Tests;

use Sutra\Component\String\Utf8Helper;
use Sutra\Component\Url\UrlParser;

class UrlParserTest extends TestCase
{
    const URI = 'http://www.myurl.com/?query=1&a=bar&b=f#hash';
    const SERVER_NAME = 'www.myurl.com';

    protected static $instance;

    public static function setUpBeforeClass()
    {
        static::$instance = new UrlParser(new Utf8Helper());
    }

    public function testConstructor()
    {
        $parser = new UrlParser(new Utf8Helper());
        $reflection = new \ReflectionClass($parser);
        $prop = $reflection->getProperty('utf8Helper');
        $prop->setAccessible(true);
        $this->assertInstanceOf('Sutra\Component\String\Utf8HelperInterface', $prop->getValue($parser));
    }

    public function testGet()
    {
        $this->assertEquals('http://www.myurl.com/', static::$instance->get(static::URI));

        $_SERVER['REQUEST_URI'] = static::URI;
        $this->assertEquals('http://www.myurl.com/', static::$instance->get());
    }

    public function testGetDomain()
    {
        $this->assertEquals('http://www.myurl.com', static::$instance->getDomain(static::URI));

        $_SERVER['SERVER_NAME'] = static::SERVER_NAME;
        $_SERVER['REQUEST_URI'] = static::URI;
        $this->assertEquals('http://www.myurl.com', static::$instance->getDomain());
    }

    public function testGetDomainSslNonStandardPort()
    {
        $uri = str_replace('.com/', '.com:444/', static::URI);
        $uri = str_replace('http', 'https', $uri);
        $this->assertEquals('https://www.myurl.com:444', static::$instance->getDomain($uri));

        $_SERVER['SERVER_PORT'] = 444;
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['SERVER_NAME'] = static::SERVER_NAME;
        $this->assertEquals('https://www.myurl.com:444', static::$instance->getDomain());
    }

    public function testGetDomainNonStandardPort()
    {
        $uri = str_replace('.com/', '.com:81/', static::URI);
        $this->assertEquals('http://www.myurl.com:81', static::$instance->getDomain($uri));

        $_SERVER['SERVER_PORT'] = 81;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['SERVER_NAME'] = static::SERVER_NAME;
        $this->assertEquals('http://www.myurl.com:81', static::$instance->getDomain());
    }

    public function testGetDomainSsl()
    {
        $_SERVER['REQUEST_URI'] = 'https://www.google.com';
        $_SERVER['SERVER_NAME'] = 'www.google.com';
        $_SERVER['HTTPS'] = 'on';

        $this->assertEquals('https://www.google.com', static::$instance->getDomain());
    }

    public function testGetQueryString()
    {
        $this->assertEquals('query=1&a=bar&b=f', static::$instance->getQueryString(static::URI));

        $_SERVER['REQUEST_URI'] = static::URI;
        $this->assertEquals('query=1&a=bar&b=f', static::$instance->getQueryString());
    }

    public function testGetWithQueryString()
    {
        $this->assertEquals('/?query=1&a=bar&b=f', static::$instance->getWithQueryString(static::URI));

        $_SERVER['REQUEST_URI'] = static::URI;
        $this->assertEquals('/?query=1&a=bar&b=f', static::$instance->getWithQueryString());
    }

    public static function makeFriendlyProvider()
    {
        return array(
            array('ALL UPPERCASE WORDS', null, null, 'all-uppercase-words'),
            array('Ignore uppercase', null, null, 'ignore-uppercase'),
            array('stays-the-same', null, null, 'stays-the-same'),
            array('   spaces are bad ', null, null, 'spaces-are-bad'),
            array('doesn\'t care for -- apostrophes', null, null, 'doesnt-care-for-apostrophes'),
            array('limited to 10 chars', 10, null, 'limited-to'),
            array('delim is underscore', null, '_', 'delim_is_underscore'),
            array('call without max length', '+', null, 'call+without+max+length'),
            array('my-----string-', 3, '-', 'my'),
        );
    }

    /**
     * @dataProvider makeFriendlyProvider
     */
    public function testMakeFriendly($input, $maxLength, $delimiter, $output)
    {
        $this->assertEquals($output, static::$instance->makeFriendly($input, $maxLength, $delimiter));
    }

    /**
     * @expectedException Sutra\Component\Url\Exception\UrlParserException
     * @expectedExceptionMessage URI "x://::abc/?:" is invalid
     */
    public function testParseException()
    {
        static::$instance->getDomain('x://::abc/?:');
    }

    public function testRemoveFromQueryString()
    {
        $this->assertEquals('?a=bar&b=f', static::$instance->removeFromQueryString('query', static::URI));

        $_SERVER['REQUEST_URI'] = static::URI;
        $this->assertEquals('?query=1&b=f', static::$instance->removeFromQueryString('a'));
    }

    public static function replaceInQueryStringProvider()
    {
        return array(
            array('query', 2, '?query=2&a=bar&b=f'),
            array(array('query'), array(2), '?query=2&a=bar&b=f'),
            array(array('query', 'c'), array(2, 3), '?query=2&a=bar&b=f&c=3'),
            array(array('query', 'b'), array('foo', 'bar'), '?query=foo&a=bar&b=bar'),
        );
    }

    /**
     * @dataProvider replaceInQueryStringProvider
     */
    public function testReplaceInQueryString($param, $value, $output)
    {
        $this->assertEquals($output, static::$instance->replaceInQueryString($param, $value, static::URI));
    }

    /**
     * @expectedException Sutra\Component\Url\Exception\ProgrammerException
     * @expectedExceptionMessage There are a different number of parameters and values
     */
    public function testReplaceInQueryStringDifferentNumberOfParameters()
    {
        static::$instance->replaceInQueryString(array('a', 'b'), array(1, 2, 3), static::URI);
    }

    /**
     * @expectedException Sutra\Component\Url\Exception\ProgrammerException
     * @expectedExceptionMessage There are a different number of parameters and values
     */
    public function testReplaceInQueryStringDifferentNumberOfParametersNoUri()
    {
        $_SERVER['REQUEST_URI'] = static::URI;
        static::$instance->replaceInQueryString(array('a', 'b'), array(1, 2, 3));
    }

    /**
     * @dataProvider replaceInQueryStringProvider
     */
    public function testReplaceInQueryStringNoUriArgument($param, $value, $output)
    {
        $_SERVER['REQUEST_URI'] = static::URI;
        $this->assertEquals($output, static::$instance->replaceInQueryString($param, $value));
    }

    public function testIsUri() {
        $this->assertTrue(static::$instance->isUri('http://www.google.com'));
        $this->assertTrue(static::$instance->isUri('https://www.amazon.com'));
        $this->assertFalse(static::$instance->isUri('garbage string'));
        $this->assertFalse(static::$instance->isUri('rtmp://alternate-protocol'));
        $this->assertTrue(static::$instance->isUri('rtmp://alternate-protocol', array('rtmp')));
    }

    public function tearDown()
    {
        unset($_SERVER['REQUEST_URI'], $_SERVER['SERVER_NAME'], $_SERVER['HTTPS'], $_SERVER['SERVER_PORT']);
    }
}
