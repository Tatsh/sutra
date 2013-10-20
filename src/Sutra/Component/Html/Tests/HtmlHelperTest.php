<?php
namespace Sutra\Component\Html\Tests;

use Sutra\Component\Html\HtmlHelper;
use Sutra\Component\Html\Purifier\Configuration as PurifierConfiguration;
use Sutra\Component\Html\Purifier\CustomLinkifyConfigurationSchema;
use Sutra\Component\Html\Purifier\Purifier;
use Sutra\Component\String\Utf8Helper;
use Sutra\Component\Url\UrlParser;

class HtmlHelperTest extends TestCase
{
    protected static $html;

    public static function setUpBeforeClass()
    {
        $utf8 = new Utf8Helper();

        // Factory should do this part
        // Can't use new keyword here yet because 'Cannot retrieve value of undefined directive Core.LexerImpl invoked on ...'
        $schema = CustomLinkifyConfigurationSchema::instance();
        $config = new PurifierConfiguration($schema);
        $config->autoFinalize = false;

        static::$html = new HtmlHelper(new UrlParser($utf8), new Purifier($config));
    }

    /**
     * @expectedException Sutra\Component\Html\Exception\ProgrammerException
     * @covers Sutra\Component\Html\Html::makeFormElement
     * @covers Sutra\Component\Html\Html::formElementIDWithName
     */
    public function testMakeFormElementBadType() {
        static::$html->makeFormElement('nogood', 'name');
    }

    /**
     * @covers Sutra\Component\Html\Html::makeFormElement
     * @covers Sutra\Component\Html\Html::formElementIDWithName
     */
    public function testMakeFormElementTextAndTextField() {
        $text = static::$html->makeFormElement('text', 'name');
        $text2 = static::$html->makeFormElement('textfield', 'name');

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
     * @covers Sutra\Component\Html\Html::makeFormElement
     * @covers Sutra\Component\Html\Html::formElementIDWithName
     * @covers Sutra\Component\Html\Html::validAttributeValue
     * @covers Sutra\Component\Html\Html::attributesString
     */
    public function testMakeFormElementSpecialAttributes() {
        // autocomplete and spellcheck
        $text = static::$html->makeFormElement('text', 'name', array('spellcheck' => TRUE));
        $text_no_spellcheck = static::$html->makeFormElement('text', 'name', array('spellcheck' => FALSE));

        $this->assertTag(array(
            'tag' => 'input',
            'attributes' => array('spellcheck' => 'true'),
        ), $text, "Failed to assert tag: $text");
        $this->assertTag(array(
            'tag' => 'input',
            'attributes' => array('spellcheck' => 'false'),
        ), $text_no_spellcheck);

        $text = static::$html->makeFormElement('text', 'name', array('autocomplete' => TRUE));
        $text_no_autocomplete = static::$html->makeFormElement('text', 'name', array('autocomplete' => FALSE));

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
     * @covers Sutra\Component\Html\Html::makeFormElement
     * @covers Sutra\Component\Html\Html::formElementIDWithName
     * @covers Sutra\Component\Html\Html::validAttributeValue
     * @covers Sutra\Component\Html\Html::attributesString
     */
    public function testMakeFormElementCustomAttributes() {
        $text = static::$html->makeFormElement('text', 'textfield1', array('data-has-name' => FALSE, 'data-2' => TRUE));
        $this->assertTag(array(
            'tag' => 'input',
            'attributes' => array('data-has-name' => 'false', 'data-2' => 'true'),
        ), $text, "Returned tag: $text");
    }

  /**
   * @covers Sutra\Component\Html\Html::makeFormElement
   * @covers Sutra\Component\Html\Html::formElementIDWithName
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
      $with_attr = static::$html->makeFormElement('text', 'name', array($attr => TRUE));
      $without_attr = static::$html->makeFormElement('text', 'name', array($attr => FALSE));

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
   * @covers Sutra\Component\Html\Html::makeFormElement
   */
  public function testMakeFormElementHasLabelTag() {
    $labeled = static::$html->makeFormElement('text', 'name', array('label' => 'My label'));

    $this->assertTag(array(
      'tag' => 'label',
    ), $labeled, "Failed to assert $labeled has a <label> tag.");
  }

  /**
   * @covers Sutra\Component\Html\Html::makeFormElement
   * @covers Sutra\Component\Html\Html::makeTextarea
   */
  public function testMakeFormElementNonInputElements() {
    $textarea = static::$html->makeFormElement('textarea', 'name');
    $select = static::$html->makeFormElement('select', 'name');

    $this->assertTag(array(
      'tag' => 'textarea',
    ), $textarea);
    $this->assertTag(array(
      'tag' => 'select',
    ), $select);
  }

  /**
   * @covers Sutra\Component\Html\Html::makeFormElement
   */
  public function testMakeFormElementClassNotArray() {
    $field = static::$html->makeFormElement('textfield', 'name', array('class' => 'class-1 class-2'));
    $this->assertTag(array(
      'tag' => 'input',
      'attributes' => array(
        'class' => 'class-1 class-2',
      ),
    ), $field);
  }

  /**
   * @covers Sutra\Component\Html\Html::makeFormElement
   */
  public function testMakeFormElementLabelAndRequired() {
    $field = static::$html->makeFormElement('textfield', 'name', array(
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
   * @covers Sutra\Component\Html\Html::makeFormElement
   * @covers Sutra\Component\Html\Html::makeSelectElement
   */
  public function testMakeFormElementSelectField1D() {
    // Test selected attribute
    $field = static::$html->makeFormElement('select', 'options', array(
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
   * @covers Sutra\Component\Html\Html::makeFormElement
   * @covers Sutra\Component\Html\Html::makeSelectElement
   * @depends testMakeFormElementSelectField1D
   */
  public function testMakeFormElementSelectField1DWithLabel() {
    // Test selected attribute
    $field = static::$html->makeFormElement('select', 'options', array(
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
   * @covers Sutra\Component\Html\Html::makeFormElement
   * @covers Sutra\Component\Html\Html::makeSelectElement
   */
  public function testMakeFormElementSelectField2D() {
    $field = static::$html->makeFormElement('select', 'options', array(
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
   * @covers Sutra\Component\Html\Html::makeFormElement
   * @covers Sutra\Component\Html\Html::makeSelectElement
   * @depends testMakeFormElementSelectField2D
   */
  public function testMakeFormElementSelectField2DWithLabel() {
    $field = static::$html->makeFormElement('select', 'options', array(
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
   * @covers Sutra\Component\Html\Html::attributesString
   * @covers Sutra\Component\Html\Html::validAttributeValue
   */
  public function testAttributesString() {
    // Not using array as class
    // All strings
    $attr = array(
      'name' => 'name',
      'value' => 'My value&',
      'class' => 'class-1 class-2',
    );
    $result = static::$html->attributesString($attr);
    $this->assertEquals('class="class-1 class-2" name="name" value="My value&amp;"', $result);

    // Using array as class
    // All strings
    $attr = array(
      'name' => 'name',
      'value' => 'My value&',
      'class' => array('class-1', 'class-2'),
    );
    $result = static::$html->attributesString($attr);
    $this->assertEquals('class="class-1 class-2" name="name" value="My value&amp;"', $result);

    // Test array order is respected
    $attr = array(
      'name' => 'name',
      'value' => 'My value&',
      'class' => array('class-2', 'class-1'),
    );
    $result = static::$html->attributesString($attr);
    $this->assertEquals('class="class-2 class-1" name="name" value="My value&amp;"', $result);

    // Boolean attributes
    $attr = array(
      'required' => TRUE,
      'spellcheck' => TRUE, // can only be true or false or omitted
      'autocomplete' => FALSE, // can only be on or off or omitted
      'scoped' => FALSE,
    );
    $result = static::$html->attributesString($attr);
    $this->assertEquals('autocomplete="off" required="required" spellcheck="true"', $result);

    // Pass an empty array (technically)
    $this->assertEquals('', static::$html->attributesString());

    // Pass an empty class array
    $this->assertEquals('', static::$html->attributesString(array('class' => array())));
  }

  /**
   * @covers Sutra\Component\Html\Html::tag
   * @covers Sutra\Component\Html\Html::tagRequiresEnd
   */
  public function testTag() {
    // Simple <a> tag
    $result = static::$html->tag('a', array(), 'My link');
    $this->assertEquals('<a>My link</a>', $result);

    // More complicated <a> tag
    $result = static::$html->tag('a', array(
      'href' => 'http://www.google.com',
      'rel' => 'external',
      'class' => 'some-link',
    ), 'My link');
    $this->assertEquals('<a class="some-link" href="http://www.google.com" rel="external">My link</a>', $result);

    // <input> doesn't require end tag in HTML
    $result = static::$html->tag('input');
    $this->assertEquals('<input>', $result);
    $result = static::$html->tag('input', array('value' => 1, 'type' => 'hidden'));
    $this->assertTag(array(
      'input',
      'attributes' => array('value' => 1, 'type' => 'hidden'),
    ), $result);
  }

  // TODO Move
//   /**
//    * @covers Sutra\Component\Html\Html::linkIsURI
//    */
//   public function testLinkIsURI() {
//     $this->assertTrue(static::$html->linkIsURI('http://www.google.com'));
//     $this->assertTrue(static::$html->linkIsURI('https://www.amazon.com'));
//     $this->assertFalse(static::$html->linkIsURI('garbage string'));
//     $this->assertFalse(static::$html->linkIsURI('rtmp://alternate-protocol'));
//     $this->assertTrue(static::$html->linkIsURI('rtmp://alternate-protocol', array('rtmp')));
//   }

  /**
   * @covers Sutra\Component\Html\Html::paragraphify
   */
  public function testParagraphify() {
    $str = "My special string

Two lines should make a new paragraph.

Everything should be properly encoded.";

    $result = static::$html->paragraphify($str);
    $this->assertTag(array('tag' => 'p'), $result);
    $this->assertStringStartsWith('<p>', $result);
    $this->assertStringEndsWith('.</p>', $result);
  }

  /**
   * @covers Sutra\Component\Html\Html::makeList
   */
  public function testMakeList() {
    $items = array(
      'item 1',
      'item 2',
      'item 3',
    );

    $result_auto_type_ul = static::$html->makeList($items, 'bad value');
    $result = static::$html->makeList($items, 'ul');
    $result_ol = static::$html->makeList($items, 'ol');
    $result_with_attr = static::$html->makeList($items, 'ul', array('class' => array('list-1')));

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
    $tag = static::$html->conditionalTag('lt IE 9', 'td', array('valign' => 'top'));
    $this->assertEquals('<!--[if lt IE 9]><td valign="top"></td><![endif]-->', $tag);
  }
}