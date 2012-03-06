<?php
require './autoload.inc';

class sHTMLTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementBadType() {
    sHTML::makeFormElement('nogood', 'name');
  }

  /**
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementTextAndTextField() {
    $text = sHTML::makeFormElement('text', 'name');
    $text2 = sHTML::makeFormElement('textfield', 'name');

    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('type' => 'text'),
    ), $text);
    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('type' => 'text'),
    ), $text2);
  }

  /**
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementSpecialAttributes() {
    // autocomplete and spellcheck
    $text = sHTML::makeFormElement('text', 'name', array('spellcheck' => TRUE));
    $text_no_spellcheck = sHTML::makeFormElement('text', 'name', array('spellcheck' => FALSE));

    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('spellcheck' => 'true'),
    ), $text);
    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('spellcheck' => 'false'),
    ), $text_no_spellcheck);

    $text = sHTML::makeFormElement('text', 'name', array('autocomplete' => TRUE));
    $text_no_autocomplete = sHTML::makeFormElement('text', 'name', array('autocomplete' => FALSE));

    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('autocomplete' => 'on'),
    ), $text);
    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('autocomplete' => 'off'),
    ), $text_no_autocomplete);
  }

  /**
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementBooleanAttributes() {
    $boolean_attributes = array(
      'scoped',
      'reveresed',
      'ismap',
      'seamless',
      'typemustmatch',
      'loop',
      'autoplay',
      'controls',
      'muted',
      'checked',
      'readonly',
      'required',
      'multiple',
      'disabled',
      'selected',
      'autofocus',
      'open',
      'hidden',
      'truespeed',
    );

    foreach ($boolean_attributes as $attr) {
      $with_attr = sHTML::makeFormElement('text', 'name', array($attr => TRUE));
      $without_attr = sHTML::makeFormElement('text', 'name', array($attr => FALSE));

      $this->assertTag(array(
        'tag' => 'input',
        'attributes' => array($attr => $attr),
      ), $with_attr, "Failed to assert $attr=\"attr\" in tag: ".$with_attr);
      $this->assertNotTag(array(
        'attributes' => array($attr => $attr),
      ), $without_attr, "Failed to assert no $attr attribute in tag: ".$without_attr);
    }
  }

  /**
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementHasLabelTag() {
    $labeled = sHTML::makeFormElement('text', 'name', array('label' => 'My label'));

    $this->assertTag(array(
      'tag' => 'label',
    ), $labeled, "Failed to assert $labeled has a <label> tag.");
  }

  /**
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementNonInputElements() {
    $textarea = sHTML::makeFormElement('textarea', 'name');
    $select = sHTML::makeFormElement('select', 'name');

    $this->assertTag(array(
      'tag' => 'textarea',
    ), $textarea);
    $this->assertTag(array(
      'tag' => 'select',
    ), $select);
  }

  /**
   * @covers sHTML::stripNonASCIIFromString
   */
  public function testStripNonASCIIFromString() {
    $str = 'this string just needs dashes';
    $result = sHTML::stripNonASCIIFromString($str);
    $this->assertEquals('this-string-just-needs-dashes', $result);

    $str = 'This string will be lower-cased and have dashes';
    $result = sHTML::stripNonASCIIFromString($str, TRUE);
    $this->assertEquals('this-string-will-be-lower-cased-and-have-dashes', $result);

    $str = '---This string has garbage !@#$%^&*()___+----';
    $result = sHTML::stripNonASCIIFromString($str, FALSE);
    $this->assertEquals('This-string-has-garbage', $result);
  }

  /**
   * @covers sHTML::attributesString
   */
  public function testAttributesString() {
    // Not using array as class
    // All strings
    $attr = array(
      'name' => 'name',
      'value' => 'My value&',
      'class' => 'class-1 class-2',
    );
    $result = sHTML::attributesString($attr);
    $this->assertEquals('class="class-1 class-2" name="name" value="My value&amp;"', $result);

    // Using array as class
    // All strings
    $attr = array(
      'name' => 'name',
      'value' => 'My value&',
      'class' => array('class-1', 'class-2'),
    );
    $result = sHTML::attributesString($attr);
    $this->assertEquals('class="class-1 class-2" name="name" value="My value&amp;"', $result);

    // Test array order is respected
    $attr = array(
      'name' => 'name',
      'value' => 'My value&',
      'class' => array('class-2', 'class-1'),
    );
    $result = sHTML::attributesString($attr);
    $this->assertEquals('class="class-2 class-1" name="name" value="My value&amp;"', $result);

    // Boolean attributes
    $attr = array(
      'required' => TRUE,
      'spellcheck' => TRUE, // can only be true or false or omitted
      'autocomplete' => TRUE, // can only be on or off or omitted
      'scoped' => TRUE,
    );
    $result = sHTML::attributesString($attr);
    $this->assertEquals('autocomplete="on" required="required" scoped="scoped" spellcheck="true"', $result);
  }

  /**
   * @covers sHTML::tag
   */
  public function testTag() {
    // Simple <a> tag
    $result = sHTML::tag('a', array(), 'My link');
    $this->assertEquals('<a>My link</a>', $result);

    // More complicated <a> tag
    $result = sHTML::tag('a', array(
      'href' => 'http://www.google.com',
      'rel' => 'external',
      'class' => 'some-link',
    ), 'My link');
    $this->assertEquals('<a class="some-link" href="http://www.google.com" rel="external">My link</a>', $result);
  }

  /**
   * @covers sHTML::linkIsURI
   */
  public function testLinkIsURI() {
    $this->assertTrue(sHTML::linkIsURI('http://www.google.com'));
    $this->assertTrue(sHTML::linkIsURI('https://www.amazon.com'));
    $this->assertFalse(sHTML::linkIsURI('garbage string'));
  }

  /**
   * @covers sHTML::paragraphify
   */
  public function testParagraphify() {
    $str = "My special string

Two lines should make a new paragraph.

There should not be any sparse <p> tags.

Everything should be properly encoded.";

    $result = sHTML::paragraphify($str);
    $this->assertTag(array('tag' => 'p'), $result);
    $this->assertStringStartsWith('<p>', $result);
    $this->assertStringEndsWith('.</p>', $result);
    $this->assertRegExp('#\&lt;p\&gt;#', $result);
  }

  /**
   * @covers sHTML::makeList
   */
  public function testMakeList() {
    $items = array(
      'item 1',
      'item 2',
      'item 3',
    );

    $result = sHTML::makeList($items, 'ul');
    $result_ol = sHTML::makeList($items, 'ol');
    $result_with_attr = sHTML::makeList($items, 'ul', array('class' => array('list-1')));

    $this->assertTag(array('tag' => 'ul'), $result);
    $this->assertTag(array('tag' => 'li'), $result);
    $this->assertTag(array('tag' => 'li', 'attributes' => array('class' => 'first')), $result);
    $this->assertTag(array('tag' => 'li', 'attributes' => array('class' => 'item-1')), $result);
    $this->assertTag(array('tag' => 'li', 'attributes' => array('class' => 'item-2')), $result);
    $this->assertTag(array('tag' => 'li', 'attributes' => array('class' => 'item-3')), $result);
    $this->assertTag(array('tag' => 'li', 'attributes' => array('class' => 'odd')), $result);
    $this->assertTag(array('tag' => 'li', 'attributes' => array('class' => 'even')), $result);
    $this->assertTag(array('tag' => 'li', 'attributes' => array('class' => 'last')), $result);
    $this->assertTag(array('tag' => 'ol'), $result_ol);
    $this->assertTag(array(
      'tag' => 'ul',
      'attributes' => array('class' => 'list-1'),
    ), $result_with_attr);
  }
}
