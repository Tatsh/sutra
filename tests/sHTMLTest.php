<?php
require './includes/global.inc';

class sHTMLTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   * @covers sHTML::makeFormElement
   * @covers sHTML::formElementIDWithName
   */
  public function testMakeFormElementBadType() {
    sHTML::makeFormElement('nogood', 'name');
  }

  /**
   * @covers sHTML::makeFormElement
   * @covers sHTML::formElementIDWithName
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
    $this->assertNotEquals($text, $text2);
  }

  /**
   * @covers sHTML::makeFormElement
   * @covers sHTML::formElementIDWithName
   * @covers sHTML::validAttributeValue
   * @covers sHTML::attributesString
   */
  public function testMakeFormElementSpecialAttributes() {
    // autocomplete and spellcheck
    $text = sHTML::makeFormElement('text', 'name', array('spellcheck' => TRUE));
    $text_no_spellcheck = sHTML::makeFormElement('text', 'name', array('spellcheck' => FALSE));

    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('spellcheck' => 'true'),
    ), $text, "Failed to assert tag: $text");
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
   * @covers sHTML::formElementIDWithName
   * @covers sHTML::validAttributeValue
   * @covers sHTML::attributesString
   */
  public function testMakeFormElementCustomAttributes() {
    $text = sHTML::makeFormElement('text', 'textfield1', array('data-has-name' => FALSE, 'data-2' => TRUE));
    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('data-has-name' => 'false', 'data-2' => 'true'),
    ), $text, "Returned tag: $text");
  }

  /**
   * @covers sHTML::makeFormElement
   * @covers sHTML::formElementIDWithName
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
   * @covers sHTML::makeTextarea
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
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementClassNotArray() {
    $field = sHTML::makeFormElement('textfield', 'name', array('class' => 'class-1 class-2'));
    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array(
        'class' => 'class-1 class-2',
      ),
    ), $field);
  }

  /**
   * @covers sHTML::makeFormElement
   */
  public function testMakeFormElementLabelAndRequired() {
    $field = sHTML::makeFormElement('textfield', 'name', array(
      'label' => 'My label',
      'required' => TRUE,
    ));
    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array('required' => 'required'),
    ), $field);
    $this->assertTag(array(
      'tag' => 'label',
    ), $field);
    $this->assertTag(array(
      'tag' => 'span',
      'attributes' => array('class' => 'form-required-marker'),
    ), $field);
  }

  /**
   * @covers sHTML::makeFormElement
   * @covers sHTML::makeSelectElement
   */
  public function testMakeFormElementSelectField1D() {
    // Test selected attribute
    $field = sHTML::makeFormElement('select', 'options', array(
      'options' => array(
        0 => 'Pick an option',
        1 => 'Option 1',
        2 => 'Option 2',
      ),
      'value' => 1,
    ));
    $this->assertTag(array(
      'tag' => 'select',
      'attributes' => array('class' => 'form-select', 'name' => 'options'),
    ), $field);
    $this->assertTag(array(
      'tag' => 'option',
    ), $field);
    $this->assertTag(array(
      'tag' => 'option',
      'attributes' => array('selected' => 'selected'),
    ), $field, 'Failed to find selected option.');
  }

  /**
   * @covers sHTML::makeFormElement
   * @covers sHTML::makeSelectElement
   * @depends testMakeFormElementSelectField1D
   */
  public function testMakeFormElementSelectField1DWithLabel() {
    // Test selected attribute
    $field = sHTML::makeFormElement('select', 'options', array(
      'options' => array(
        0 => 'Pick an option',
        1 => 'Option 1',
        2 => 'Option 2',
      ),
      'value' => 1,
      'label' => 'My label',
    ));
    $this->assertTag(array('tag' => 'label'), $field);
  }

  /**
   * @covers sHTML::makeFormElement
   * @covers sHTML::makeSelectElement
   */
  public function testMakeFormElementSelectField2D() {
    $field = sHTML::makeFormElement('select', 'options', array(
      'options' => array(
        'group 1' => array(
          1 => 'option 1',
          2 => 'option 2',
          3 => 'option 3',
        ),
        'group 2' => array(
          4 => 'option 4 (group 2)',
          5 => 'option 5 (group 2)',
          6 => 'option 6 (group 2)',
        ),
      ),
      'value' => 2,
    ));

    $this->assertTag(array(
      'tag' => 'select',
      'attributes' => array('class' => 'form-select', 'name' => 'options'),
    ), $field);
    $this->assertTag(array(
      'tag' => 'option',
    ), $field);
    $this->assertTag(array(
      'tag' => 'option',
      'attributes' => array('selected' => 'selected'),
      ), $field, 'Failed to find selected option: '.$field);
    $this->assertTag(array(
      'tag' => 'optgroup',
      'attributes' => array('label' => 'group 1'),
    ), $field);
    $this->assertTag(array(
      'tag' => 'optgroup',
      'attributes' => array('label' => 'group 2'),
    ), $field);
  }

  /**
   * @covers sHTML::makeFormElement
   * @covers sHTML::makeSelectElement
   * @depends testMakeFormElementSelectField2D
   */
  public function testMakeFormElementSelectField2DWithLabel() {
    $field = sHTML::makeFormElement('select', 'options', array(
      'options' => array(
        'group 1' => array(
          1 => 'option 1',
          2 => 'option 2',
          3 => 'option 3',
        ),
        'group 2' => array(
          4 => 'option 4 (group 2)',
          5 => 'option 5 (group 2)',
          6 => 'option 6 (group 2)',
        ),
      ),
      'value' => 2,
      'label' => 'My label',
    ));
    $this->assertTag(array('tag' => 'label'), $field, "No label: $field");
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
   * @covers sHTML::validAttributeValue
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
      'autocomplete' => FALSE, // can only be on or off or omitted
      'scoped' => FALSE,
    );
    $result = sHTML::attributesString($attr);
    $this->assertEquals('autocomplete="off" required="required" spellcheck="true"', $result);

    // Pass an empty array (technically)
    $this->assertEquals('', sHTML::attributesString());

    // Pass an empty class array
    $this->assertEquals('', sHTML::attributesString(array('class' => array())));
  }

  /**
   * @covers sHTML::tag
   * @covers sHTML::tagRequiresEnd
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

    // <input> doesn't require end tag in HTML
    $result = sHTML::tag('input');
    $this->assertEquals('<input>', $result);
    $result = sHTML::tag('input', array('value' => 1, 'type' => 'hidden'));
    $this->assertTag(array(
      'input',
      'attributes' => array('value' => 1, 'type' => 'hidden'),
    ), $result);
  }

  /**
   * @covers sHTML::linkIsURI
   */
  public function testLinkIsURI() {
    $this->assertTrue(sHTML::linkIsURI('http://www.google.com'));
    $this->assertTrue(sHTML::linkIsURI('https://www.amazon.com'));
    $this->assertFalse(sHTML::linkIsURI('garbage string'));
    $this->assertFalse(sHTML::linkIsURI('rtmp://alternate-protocol'));
    $this->assertTrue(sHTML::linkIsURI('rtmp://alternate-protocol', array('rtmp')));
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

    $result_auto_type_ul = sHTML::makeList($items, 'bad value');
    $result = sHTML::makeList($items, 'ul');
    $result_ol = sHTML::makeList($items, 'ol');
    $result_with_attr = sHTML::makeList($items, 'ul', array('class' => array('list-1')));

    $this->assertTag(array('tag' => 'ul'), $result_auto_type_ul);
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

  public function testConditionalTag() {
    $tag = sHTML::conditionalTag('lt IE 9', 'td', array('valign' => 'top'));
    $this->assertEquals('<!--[if lt IE 9]><td valign="top"></td><![endif]-->', $tag);
  }
}
