<?php
namespace Sutra\Component\Url\Tests;

use Sutra\Component\Url\HttpRequest;

class HttpRequestTest extends TestCase
{
    const JSON_SOURCE_URI = 'https://gdata.youtube.com/feeds/api/users/google/uploads?alt=json';

    /**
     * @expectedException Sutra\Component\Url\Exception\ProgrammerException
     * @expectedExceptionMessage The argument specified, "aaa", is not a valid HTTP URL.
     */
    public function testConstructorBadURL()
    {
        new HttpRequest('aaa');
    }

    /**
     * @expectedException Sutra\Component\Url\Exception\ProgrammerException
     * @expectedExceptionMessage The method specified, "bad", is not a valid HTTP method.
     */
    public function testConstructorBadMethod()
    {
        new HttpRequest('http://localhost', 'bad');
    }

    public function testGetHeadersAsString()
    {
        $headers = array(
            'User-Agent' => 'my user agent',
            'custom-header' => 'custom header',
        );
        $a = new HttpRequest('http://localhost');
        $a->setHeaders($headers);
        $str = $a->getHeaders(TRUE);
        $result = "User-Agent: my user agent\r\ncustom-header: custom header\r\n";
        $result .= "Content-Length: 0\r\n";
        $this->assertEquals($result, $str);
    }

    public function testSetHeaders()
    {
        $headers = array(
            'User-Agent' => 'my user agent',
            'custom-header' => 'custom header',
        );
        $a = new HttpRequest('http://localhost');
        $a->setHeaders($headers);
        $this->assertInternalType('array', $a->getHeaders());
    }

    public function testSetHeader()
    {
        $a = new HttpRequest('http://localhost');
        $a->setHeader('X-Requested-With', 'blah');
        $headers = $a->getHeaders();
        $this->assertArrayHasKey('X-Requested-With', $headers);

        $a = new HttpRequest('http://localhost');
        $a->setHeader('Content-Length', 'blah');
        $headers = $a->getHeaders();
        $this->assertNotEquals($headers['Content-Length'], 'blah');
    }

    public function testSetUserAgent()
    {
        $agent = 'aaaaaa';
        $a = new HttpRequest('http://localhost');
        $a->setUserAgent($agent);
        $headers = $a->getHeaders();
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertEquals($agent, $headers['User-Agent']);
    }

    public function testGetData()
    {
        $a = new HttpRequest('http://php.tatsh.net/manual/en/context.http.php');
        $data = $a->getData();
        $this->assertTag(array(
            'tag' => 'title',
            'content' => 'PHP: HTTP context options - Manual',
        ), $data);

        $a = new HttpRequest('http://php.tatsh.net/manual/en/context.http.php');
        $a->send();
        $data = $a->getData();
        $this->assertTag(array(
            'tag' => 'title',
            'content' => 'PHP: HTTP context options - Manual',
        ), $data);

        $this->assertSame(200, $a->getStatus());
    }

    /**
     * @expectedException Sutra\Component\Url\Exception\UnexpectedException
     * @expectedExceptionMessage The URI, "http://hope-it-doesnt-exist", could not be loaded.
     */
    public function testBadRequest()
    {
        $url = 'http://hope-it-doesnt-exist';
        $a = new HttpRequest($url);
        $a->getData();
    }

    public function testPOST()
    {
        $a = new HttpRequest('http://php.tatsh.net/manual/en/context.http.php', 'POST');

        $this->assertEquals('', $a->getContent());

        $a->setContent('my content');
        $this->assertEquals('my content', $a->getContent());

        $data = $a->getData();
        $this->assertTag(array(
            'tag' => 'title',
            'content' => 'PHP: HTTP context options - Manual',
        ), $data);
    }

    public function testSetProxy()
    {
        $url = 'http://hope-it-doesnt-exist';
        $a = new HttpRequest($url);
        $a->setProxy('tcp://proxy.example.com:5100');
        $this->assertEquals('tcp://proxy.example.com:5100', $a->getProxy());
        $a->removeProxy();
        $this->assertEquals('', $a->getProxy());
    }

    /**
     * @expectedException Sutra\Component\Url\Exception\UnexpectedException
     * @expectedExceptionMessage The URI, "http://www.google.com", could not be loaded.
     *
     * @todo Need test with working proxy.
     */
    public function testWithProxy()
    {
        $url = 'http://www.google.com';
        $a = new HttpRequest($url);
        $a->setProxy('tcp://proxy.example.com:5100');
        $data = $a->getData();
    }

    public function testGetJSON()
    {
        $a = new HttpRequest(self::JSON_SOURCE_URI);
        $data = $a->getJson();
        $this->assertInternalType('object', $data);

        $a = new HttpRequest(self::JSON_SOURCE_URI);
        $data = $a->getJson(true);
        $this->assertInternalType('array', $data);
    }
}
