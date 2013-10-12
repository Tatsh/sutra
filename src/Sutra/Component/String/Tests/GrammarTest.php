<?php
namespace Sutra\Component\String\Tests;

use Sutra\Component\String\Grammar;
use Sutra\Component\String\Utf8Helper;
use Sutra\Component\Url\UrlParser;

class GrammarTest extends TestCase
{
    protected $grammar;

    public function setUp()
    {
        $utf8Helper = new Utf8Helper();
        $this->grammar = new Grammar(new UrlParser($utf8Helper));
    }

    public function testCamelize()
    {
        $this->grammar->addCamelizationRule('MySPEcialCase', 'somethingDifferent');
        $ret = $this->grammar->camelize('MySPEcialCase');
        $this->assertEquals('somethingDifferent', $ret);

        $this->grammar->removeCamelizationRule('MySPEcialCase');
        $ret = $this->grammar->camelize('MySPEcialCase');
        $this->assertNotEquals('somethingDifferent', $ret);
    }

    public function testSingularize()
    {
        $this->grammar->addSingularizationRule('words', 'wor');
        $ret = $this->grammar->singularize('words');
        $this->assertEquals('wor', $ret);

        $this->grammar->removeSingularizationRule('words');
        $ret = $this->grammar->singularize('words');
        $this->assertNotEquals('wor', $ret);
    }

    public function testStudlyize()
    {
        $this->grammar->addStudlyizationRule('MySPEcialCase', 'somethingDifferent');
        $ret = $this->grammar->studlyize('MySPEcialCase');
        $this->assertEquals('somethingDifferent', $ret);

        $this->grammar->removeStudlyizationRule('MySPEcialCase');
        $ret = $this->grammar->studlyize('MySPEcialCase');
        $this->assertNotEquals('somethingDifferent', $ret);
    }

    public function testPluralize()
    {
        $this->grammar->addPluralizationRule('DLC', 'downloads');
        $ret = $this->grammar->pluralize('DLC');
        $this->assertEquals('downloads', $ret);

        $this->grammar->removePluralizationRule('DLC');
        $ret = $this->grammar->pluralize('DLC');
        $this->assertNotEquals('downloads', $ret);
    }

    public function testUnderscorize()
    {
        $this->grammar->addUnderscorizationRule('my string', 'my_strings');
        $ret = $this->grammar->underscorize('my string');
        $this->assertEquals('my_strings', $ret);

        $this->grammar->removeUnderscorizationRule('my string');
        $ret = $this->grammar->underscorize('my string');
        $this->assertNotEquals('my_strings', $ret);
    }

    public function testDashize()
    {
        $ret = $this->grammar->dashize('string with spaces');
        $this->assertEquals('string-with-spaces', $ret);

        $ret = $this->grammar->dashize('camelCaseString');
        $this->assertEquals('camel-case-string', $ret);

        // Hit cache
        $ret = $this->grammar->dashize('camelCaseString');
        $this->assertEquals('camel-case-string', $ret);
    }

    public function testDashizeRule()
    {
        $this->grammar->addDashizationRule('my string', 'my-strings');
        $ret = $this->grammar->dashize('my string');
        $this->assertEquals('my-strings', $ret);

        $this->grammar->removeDashizationRule('my string');
        $ret = $this->grammar->dashize('my string');
        $this->assertEquals('my-string', $ret);
    }

    public function testHumanize()
    {
        $ret = $this->grammar->humanize('camelCaseString');
        $this->assertEquals('Camel Case String', $ret);

        $ret = $this->grammar->humanize('A File.pdf');
        $this->assertEquals('A File.PDF', $ret);

        $ret = $this->grammar->humanize('and_an_stop_words_here_of_to');
        $this->assertEquals('And An Stop Words Here Of To', $ret);

        // Hit cache
        $ret = $this->grammar->humanize('and_an_stop_words_here_of_to');
        $this->assertEquals('And An Stop Words Here Of To', $ret);
    }

    public function testHumanizeRule()
    {
        $this->grammar->addHumanizationRule('camelCaseString', 'Something Different');
        $ret = $this->grammar->humanize('camelCaseString');
        $this->assertEquals('Something Different', $ret);

        $this->grammar->removeHumanizationRule('camelCaseString');
        $ret = $this->grammar->humanize('camelCaseString');
        $this->assertNotEquals('Something Different', $ret);
    }

    public function testHumanizeRuleSubstring()
    {
        $this->grammar->addHumanizationRule('an_stop_words', 'Replacement');
        $this->grammar->addHumanizationRule('of_to', 'Replacement2');
        $ret = $this->grammar->humanize('and_an_stop_words_here_of_to');
        $this->assertEquals('And Replacement Here Replacement2', $ret);

        $this->grammar->removeHumanizationRule('an_stop_words');
        $ret = $this->grammar->humanize('and_an_stop_words_here_of_to');
        $this->assertEquals('And An Stop Words Here Replacement2', $ret);
    }

    public function testTitleize()
    {
        $ret = $this->grammar->titleize('camelCaseString');
        $this->assertEquals('Camel Case String', $ret);

        $ret = $this->grammar->titleize('A File.pdf');
        $this->assertEquals('A File.PDF', $ret);

        $ret = $this->grammar->titleize('and_an_stop_words_here_of_to');
        $this->assertEquals('And an Stop Words Here of to', $ret);

        // Hit cache
        $ret = $this->grammar->titleize('and_an_stop_words_here_of_to');
        $this->assertEquals('And an Stop Words Here of to', $ret);
    }

    public function testTitleizeRule()
    {
        $this->grammar->addTitleizationRule('an_stop_words', 'Replacement');
        $ret = $this->grammar->titleize('and_an_stop_words_here_of_to');
        $this->assertEquals('And Replacement Here of to', $ret);

        $this->grammar->removeTitleizationRule('an_stop_words');
        $ret = $this->grammar->titleize('and_an_stop_words_here_of_to');
        $this->assertEquals('And an Stop Words Here of to', $ret);
    }
}
