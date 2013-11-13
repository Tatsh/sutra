<?php
namespace Sutra\Component\Html\Tests\Purifier;

use Sutra\Component\DataStructure\StrictArray;
use Sutra\Component\Html\Purifier\Purifier;
use Sutra\Component\Html\Tests\TestCase;

class PurifierTest extends TestCase
{
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Argument 2 must be an instance of Sutra\Component\Html\Purifier\ConfigurationInterface or null
     */
    public function testPurifyBadConfig()
    {
        $purifier = new Purifier();
        $purifier->purify('content', array(1));
    }

    public function testPurify()
    {
        $purifier = new Purifier();
        $this->assertNotEquals('content&', $purifier->purify('content&'));
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Argument 2 must be an instance of Sutra\Component\Html\Purifier\ConfigurationInterface or null
     */
    public function testPurifyArrayBadConfig()
    {
        $purifier = new Purifier();
        $purifier->purifyArray(array('1', '2'), array(1));
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Argument 1 must be an array of strings
     */
    public function testPurifyArrayBadArrayArg1()
    {
        $purifier = new Purifier();
        $purifier->purifyArray('non array');
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Argument 1 must be an array of strings
     */
    public function testPurifyArrayBadArrayArg2()
    {
        $purifier = new Purifier();
        $purifier->purifyArray($purifier);
    }

    public function testPurifyArrayGoodArrayObject()
    {
        $purifier = new Purifier();
        $new = $purifier->purifyArray(new StrictArray('1"', '2&'));

        $this->assertNotEquals($new, array('1"', '2&'));
    }
}
