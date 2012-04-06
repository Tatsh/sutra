<?php
require './00-global.php';

class sGrammarTest extends PHPUnit_Framework_TestCase {
  public function testDashize() {
    $this->assertEquals('a-b-c', sGrammar::dashize('a b c'));
    $this->assertEquals('a-b-c', sGrammar::dashize('a b c'));

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
}
