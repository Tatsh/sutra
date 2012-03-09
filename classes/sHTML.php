<?php
/**
 * Library to help with rendering HTML tags cleanly and validly. Note that
 *   only some element attributes are checked for validity and fixed.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class sHTML extends fHTML {
  /**
   * Stores all form element IDs so that all generated via this class are unique.
   *
   * @var array
   */
  private static $form_element_ids = array();

  /**
   * ASCII safe values used by stripNonASCIIFromString.
   *
   * @var array
   */
  private static $safe_ascii = array(
    'A',
    'B',
    'C',
    'D',
    'E',
    'F',
    'G',
    'H',
    'I',
    'J',
    'K',
    'L',
    'M',
    'N',
    'O',
    'P',
    'Q',
    'R',
    'S',
    'T',
    'U',
    'V',
    'W',
    'X',
    'Y',
    'Z',
    'a',
    'b',
    'c',
    'd',
    'e',
    'f',
    'g',
    'h',
    'i',
    'j',
    'k',
    'l',
    'm',
    'n',
    'o',
    'p',
    'q',
    'r',
    's',
    't',
    'u',
    'v',
    'w',
    'x',
    'y',
    'z',
    '0',
    '1',
    '2',
    '3',
    '4',
    '5',
    '6',
    '7',
    '8',
    '9',
    '0',
    '-',
  );

  /**
   * Valid values for the element input type attribute.
   *
   * @link http://dev.w3.org/html5/spec/Overview.html#states-of-the-type-attribute
   *
   * @var array
   */
  private static $input_type_values = array(
    'hidden',
    'text',
    'textfield',
    'search',
    'tel',
    'url',
    'email',
    'password',
    'datetime',
    'date',
    'month',
    'week',
    'time',
    'datetime-local',
    'number',
    'range',
    'color',
    'checkbox',
    'radio',
    'file',
    'submit',
    'image',
    'reset',
    'button',
  );

  /**
   * Special attributes like spellcheck and their valid values for states on
   *   and off (enumerated attributes that only have on/off states).
   *
   * @var array
   */
  private static $special_enumerated_attributes = array(
    'spellcheck' => array(
      TRUE => 'true',
      FALSE => 'false',
    ),
    'autocomplete' => array(
      TRUE => 'on',
      FALSE => 'off',
    ),
  );

  /**
   * Boolean attributes that must be omitted if the state is to be off.
   *
   * @var array
   */
  private static $boolean_attributes = array(
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

  /**
   * Create a form element ID.
   *
   * @param string $name Name attribute of the element.
   * @return string Unique identifier for use with the id attribute.
   */
  private static function formElementIDWithName($name) {
    $id = 'edit-'.self::stripNonASCIIFromString($name);

    if (in_array($id, self::$form_element_ids)) {
      $in_array = TRUE;
      $i = 1;
      $original = $id;
      while ($in_array) {
        $id = $original.'-'.$i;

        if (!in_array($id, self::$form_element_ids)) {
          $in_array = FALSE;
          break;
        }

        $i++;
      }
    }

    self::$form_element_ids[] = $id;

    return $id;
  }

  /**
   * Handles creating textarea tags.
   *
   * @param string $name Name of the field.
   * @param string $label Label HTML.
   * @param array $attributes Attributes.
   */
  private static function makeTextarea($name, $label = '', array $attributes = array()) {
    $value = isset($attributes['value']) ? (string)$attributes['value'] : '';
    unset($attributes['value']);
    unset($attributes['type']);
    $ret = $label;
    $ret .= '<textarea '.self::attributesString($attributes).'>';
    $ret .= self::encode($value);
    $ret .= '</textarea>';
    return $ret;
  }

  /**
   * Handles creating select fields with options.
   *
   * @param string $name Name of the field.
   * @param string $label Label HTML.
   * @param array $attributes Attributes.
   */
  private static function makeSelectElement($name, $label = '', array $attributes = array()) {
    $options_html = $options = '';
    $attr = $attributes;

    if (isset($attr['options']) && is_array($attr['options'])) {
      $selected = isset($attributes['value']) ? $attributes['value'] : NULL;
      unset($attributes['value']);
      unset($attributes['type']);

      $is_2d = is_array(current($attr['options']));
      $options = '';

      if ($is_2d) {
        foreach ($attr['options'] as $group_name => $options) {
          $options_html .= '<optgroup label="'.self::encode($group_name).'">';
          foreach ($options as $key => $value) {
            if ($selected && $selected == $key) {
              $options_html .= '<option selected="selected" value="'.self::encode($key).'">'.self::encode($value).'</option>';
            }
            else {
              $options_html .= '<option value="'.self::encode($key).'">'.self::encode($value).'</option>';
            }
          }
          $options_html .= '</optgroup>';
        }
        $options = $options_html;
      }
      else {
        foreach ($attr['options'] as $key => $value) {
          if ($selected && $selected == $key) {
            $options .= '<option selected="selected" value="'.self::encode($key).'">'.self::encode($value).'</option>';
          }
          else {
            $options .= '<option value="'.self::encode($key).'">'.self::encode($value).'</option>';
          }
        }
      }
      unset($attributes['options']);
    }

    return $label.'<select '.self::attributesString($attributes).'>'.$options.'</select>';
  }

  /**
   * Create a form element.
   *
   * If specifying a select element, it may be desirable to have a default
   *   selected option. To do so, specify a 'value' key with a string value
   *   in the $attr argument. For optgroups, use a 2-dimensional array in
   *   the 'options' key in the $attr argument.
   *
   * @throws fProgrammerException If the type argument is invalid.
   *
   * @param string $type One of the HTML 5 element types for use with the
   *   input tags, 'select', and 'textarea'.
   * @param string $name Form element name, unique to the form.
   * @param array $attr Array of key => value attributes. Use 'label' to
   *   provide a string for use with a label element (which will have the
   *   for attribute set properly).
   * @return string HTML tags ready for use.
   */
  public static function makeFormElement($type, $name, array $attr = array()) {
    $id = self::formElementIDWithName($name);
    $type = strtolower($type);
    $allowed_types = array_merge(self::$input_type_values, array('textarea', 'select'));

    if (!in_array($type, $allowed_types)) {
      throw new fProgrammerException('Type \'%s\' is not a valid form element type.', $type);
    }

    if ($type == 'textfield') {
      $type = 'text';
    }

    $attributes = array_merge($attr, array(
      'name' => $name,
      'type' => $type,
      'id' => $id,
    ));

    $has_class = isset($attributes['class']);

    if ($has_class && !is_array($attributes['class'])) {
      $classes = explode(' ', trim($attributes['class']));
      foreach ($classes as $key => $class) {
        $classes[$key] = trim($class);
      }
      $attributes['class'] = array_merge($classes, array('form-'.$type));
    }
    else if (!$has_class) {
      $attributes['class'] = array('form-'.$type);
    }

    // Handle the boolean attributes
    foreach (self::$boolean_attributes as $b_attr) {
      if (isset($attributes[$b_attr]) && $attributes[$b_attr]) {
        $attributes[$b_attr] = $b_attr;
      }
      else {
        unset($attributes[$b_attr]);
      }
    }

    unset($attributes['label']);

    $label = '';
    if (isset($attr['label'])) {
      $label = '<label for="'.$id.'">'.self::encode($attr['label']);
      if (isset($attributes['required']) && $attributes['required'] == TRUE) {
        $label .= ' <span class="form-required-marker">*</span>';
      }
      $label .= '</label>';
    }
    unset($attr['label']);

    if ($type == 'textarea') {
      return self::makeTextarea($name, $label, $attributes);
    }
    else if ($type == 'select') {
      return self::makeSelectElement($name, $label, $attributes);
    }

    return $label.'<input '.self::attributesString($attributes).'>';
  }

  /**
   * Strip out non-ASCII characters and accents out of the string.
   *   Basically, this removes all characters NOT within the range of:
   *   ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890-
   *
   * @param string $str String to process.
   * @param boolean $lower Lower case if set to TRUE (default).
   * @return string String, processed.
   */
  public static function stripNonASCIIFromString($str, $lower = TRUE) {
    $safe = self::$safe_ascii;

    $str = str_replace(array(' ', '_'), '-', $str);
    $str = str_split($str);
    foreach ($str as $key => $char) {
      if (!in_array($char, $safe)) {
        unset($str[$key]);
      }
    }
    $str = implode($str);

    // Strip out on-going dashes
    $str = preg_replace('/\-+/', '-', $str);
    // Strip out the end and beginning dashes if present
    $str = preg_replace(array('/^\-+(.*)/', '/([A-Za-z])\-+$/'), '$1', $str);

    return $lower ? strtolower($str) : $str;
  }

  /**
   * Get a valid value for an enumerated (only ones that represent on or off)
   *   or boolean attribute.
   *
   * @param string $attribute_value Attribute value.
   * @param boolean $value Boolean value for the attribute.
   *
   * @return boolean If this function returns FALSE, the attribute must be
   *   omitted.
   */
  private static function getValidAttributeValue($attribute_name, $value) {
    // Assume that maybe this value is for a boolean attribute
    if (!array_key_exists($attribute_name, self::$special_enumerated_attributes)) {
      return FALSE;
    }

    return self::$special_enumerated_attributes[$attribute_name][$value];
  }

  /**
   * Convert an array of key => value attributes for an HTML tag to a
   *   string for use in HTML.
   *
   * @param array $attr Attributes array.
   * @return string String ready for use within HTML tag.
   */
  public static function attributesString(array $attr = array()) {
    if (empty($attr)) {
      return '';
    }

    $str = array();

    foreach ($attr as $key => $value) {
      $key = strtolower($key);
      if ($key == 'class' && is_array($value)) {
        if (!count($value)) {
          continue;
        }

        $value = implode(' ', $value);
      }

      if (is_bool($value)) {
        if (in_array($key, self::$boolean_attributes)) {
          if ($value === TRUE) {
            $value = $key;
          }
          else {
            continue;
          }
        }
        else if ($test = self::getValidAttributeValue($key, $value)) {
          $value = $test;
        }
        else if ($value) {
          $value = 'true';
        }
        else {
          $value = 'false';
        }
      }

      $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
      $str[] = $key.'="'.$value.'"';
    }

    sort($str, SORT_STRING);

    return implode(' ', $str);
  }

  /**
   * Some tags require end tags while some others do not. Example: <p> does
   *   not in HTML, but in XHTML like all tags requires an end tag. However,
   *   some browsers are not going to do the same thing when encountering
   *   several <p> tags with no endings, so it returns TRUE.
   *
   * @param string $tag Tag name.
   * @return bool TRUE if the end tag is required, FALSE otherwise.
   */
  protected static function tagRequiresEnd($tag) {
    switch ($tag) {
      case 'input':
      case 'meta':
      case 'link':
      case 'img':
        return FALSE;
    }

    return TRUE;
  }

  /**
   * Create a tag HTML string.
   *
   * @param string $tag Tag name.
   * @param array Attributes array.
   * @param string $content Content to place within HTML. If the tag never uses an end tag, this parameter will be ignored. In particular, <input>, <link> and similar tags.
   * @return string HTML tag, ready to be displayed.
   */
  public static function tag($tag, $attr = array(), $content = '') {
    $tag = strtolower($tag);

    if (!self::tagRequiresEnd($tag)) {
      if (!empty($attr)) {
        return '<'.$tag.' '.self::attributesString($attr).'>';
      }
      else {
        return '<'.$tag.'>';
      }
    }

    if (!empty($attr)) {
      return '<'.$tag.' '.self::attributesString($attr).'>'.self::prepare($content).'</'.$tag.'>';
    }

    return '<'.$tag.'>'.self::prepare($content).'</'.$tag.'>';
  }

  /**
   * Create a tag HTML string wrapped by a MSIE conditional comment.
   *
   * @param string $rule The IE rule. Examples: 'lt IE 9', 'IE 8', 'gt IE 6'.
   * @param string $tag Tag name.
   * @param string $content Content to place within the HTML.
   * @return string The HTML tag, ready to be displayed.
   */
  public static function conditionalTag($rule, $tag, array $attr = array(), $content = '') {
    $tag = self::tag($tag, $attr, $content);
    return '<!--[if '.$rule.']>'.$tag.'<![endif]-->';
  }

  /**
   * Check if a URL is a full URI or not.
   *
   * @param string URL to check.
   * @return bool TRUE if the path is full URI beginning with http or https, FALSE otherwise.
   */
  public static function linkIsURI($url) {
    return substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://' || $url[0] == '/';
  }

  /**
   * Create paragraphs from 2 or more new lines.
   *
   * @param string $html Unfiltered string to paragraphify.
   * @return string
   */
  public static function paragraphify($html) {
    $matches = array();
    $html .= "\n\n";
    preg_match_all("#(.*)\n#", $html, $matches);
    $str = '';

    foreach ($matches as $index => $potentials) {
      foreach ($potentials as $potential) {
        $potential = trim($potential);

        if (strlen($potential) != 0) {
          $str .= '<p>'.htmlspecialchars($potential, ENT_QUOTES, 'UTF-8').'</p>';
        }
      }
      break;
    }

    return $str;
  }

  /**
   * Create a list. 'first', 'last', 'odd', 'even', 'item-INDEX' classes
   *   will be automatically added to each li element.
   *
   * @param array $items Array of strings.
   * @param string $type 'ul' or 'ol', defaults to 'ul' even if an invalid
   *   value is passed.
   * @param array $attr Array of attributes for the parent element.
   *
   * @return string
   */
  public static function makeList($items, $type = 'ul', $attr = array()) {
    switch ($type) {
      case 'ul':
      case 'ol':
        break;

      default:
        $type = 'ul';
        break;
    }

    if (!isset($attr['class'])) {
      $attr['class'] = array();
    }

    $html = '<'.$type.' '.self::attributesString($attr).'>';
    $i = 1;
    $count = count($items);
    foreach ($items as $path => $title) {
      $classes = array();
      if ($i == 1) {
        $classes[] = 'first';
      }
      if ($i % 2 == 0) {
        $classes[] = 'even';
      }
      else {
        $classes[] = 'odd';
      }
      if ($i == $count) {
        $classes[]= 'last';
      }
      $classes[] = 'item-'.$i;

      $html .= self::tag('li', array('class' => $classes), $title);
      $i++;
    }
    $html .= '</'.$type.'>';

    return $html;
  }

  // @codeCoverageIgnoreStart
  /**
   * Private to force use as static class.
   *
   * @return sHTML
   */
  private function __construct() {}
  // @codeCoverageIgnoreEnd
}
