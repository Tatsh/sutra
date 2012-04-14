<?php
require './includes/global.inc';

class sGrammarTest extends PHPUnit_Framework_TestCase {
  public function testDashize() {
    $this->assertEquals('a-b-c', sGrammar::dashize('a b c'));
    $this->assertEquals('a-b-c', sGrammar::dashize('a b c'));
    $this->assertEquals('a-b-c', sGrammar::dashize('a_b_c'));

    sGrammar::addDashizeRule('my special string', 'my-spec');
    $this->assertEquals('my-spec', sGrammar::dashize('my special string'));
    sGrammar::removeDashizeRule('my special string');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testDashizeException() {
    sGrammar::dashize('');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testAddDashizeRuleException() {
    sGrammar::addDashizeRule('', 'abc');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testRemoveDashizeRuleException() {
    sGrammar::removeDashizeRule('');
  }

  public function testDashizeRule() {
    sGrammar::addDashizeRule('this special string', 'a');
    $this->assertEquals('a', sGrammar::dashize('this special string'));
  }

  public function testDashizeCamelCase() {
    $this->assertEquals('a-b-c', sGrammar::dashize('ABC'));
    $this->assertEquals('my-item', sGrammar::dashize('MyItem'));
    $this->assertEquals('my-u-r-l', sGrammar::dashize('myURL'));
    $this->assertEquals('my-other-item', sGrammar::dashize('myOtherItem'));
  }
}
