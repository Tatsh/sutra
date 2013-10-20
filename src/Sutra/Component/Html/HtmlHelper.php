<?php
namespace Sutra\Component\Html;

use Sutra\Component\Html\Exception\ProgrammerException;
use Sutra\Component\Html\Purifier\ConfigurationInterface;
use Sutra\Component\Html\Purifier\ConfigurationSchemaInterface;
use Sutra\Component\Html\Purifier\PurifierInterface;
use Sutra\Component\Url\UrlParserInterface;

/**
 * Provides HTML-related methods.
 */
class HtmlHelper
{
    /**
     * Inline tags.
     *
     * @var array
     *
     * @todo Ensure this list is complete as of HTML 5 standard.
     */
    protected $inlineTags = array(
        'a',
        'abbr',
        'acronym',
        'b',
        'big',
        'br',
        'button',
        'cite',
        'code',
        'del',
        'dfn',
        'em',
        'font',
        'frame',
        'hr',
        'i',
        'img',
        'input',
        'ins',
        'kbd',
        'label',
        'q',
        's',
        'samp',
        'select',
        'small',
        'span',
        'strike',
        'strong',
        'sub',
        'sup',
        'textarea',
        'tt',
        'u',
        'var',
    );

    /**
     * In-line tags without `<br>`.
     *
     * @var array
     *
     * @see #convertNewLines()
     */
    protected $inlineTagsMinusBr;

    /**
     * Valid values for the element input type attribute.
     *
     * @link http://dev.w3.org/html5/spec/Overview.html#states-of-the-type-attribute
     *
     * @var array
     */
    protected $inputTypeValues = array(
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
    protected $specialEnumeratedAttributes = array(
        'spellcheck' => array(
            true => 'true',
            false => 'false',
        ),
        'autocomplete' => array(
            true => 'on',
            false => 'off',
        ),
    );

    /**
    * Boolean attributes that must be omitted if the state is to be off.
    *
    * @var array
    */
    protected $booleanAttributes = array(
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
     * Form element IDs used.
     *
     * @var array
     */
    protected $formElementIds = array();

    /**
     * HTML Purifier instance.
     *
     * @var PurifierInterface
     */
    protected $purifier;

    /**
     * URL parser.
     *
     * @var UrlParserInterface
     */
    protected $urlParser;

    /**
     * Constructor.
     *
     * Note that for HTML Purifier, the Sutra `Purifier` class *must* be used
     *   instead of HTML Purifier's class.
     *
     * @param UrlParserInterface $urlParser URL parser instance.
     * @param PurifierInterface  $purifier  HTML Purifier instance.
     */
    public function __construct(UrlParserInterface $urlParser, PurifierInterface $purifier)
    {
        $this->urlParser = $urlParser;
        $this->purifier = $purifier;

        foreach ($this->inlineTags as $k => $tag) {
            $this->inlineTags[$k] = '<'.$tag.'>';
        }
        $this->inlineTagsMinusBr = array_filter($this->inlineTags, function ($a) {
            return $a !== '<br>';
        });
    }

    /**
     * Convert an array of key => value attributes for an HTML tag to a
     *   string for use in HTML.
     *
     * @param array $attr Attributes array.
     *
     * @return string String ready for use within HTML tag.
     */
    public function attributesString(array $attr = array())
    {
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
                if (in_array($key, $this->booleanAttributes)) {
                    if ($value === true) {
                        $value = $key;
                    }
                    else {
                        continue;
                    }
                }
                else if (($test = $this->validAttributeValue($key, $value))) {
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
     * Create a tag HTML string wrapped by a MSIE conditional comment.
     *
     * @param string $rule    The IE rule. Examples: 'lt IE 9', 'IE 8', etc.
     * @param string $tag     Tag name.
     * @param array  $attr    Attributes array.
     * @param string $content Content to place within the HTML.
     *
     * @return string The HTML tag, ready to be displayed.
     */
    public function conditionalTag($rule, $tag, array $attr = array(), $content = '')
    {
        $tag = $this->tag($tag, $attr, $content);

        return '<!--[if '.$rule.']>'.$tag.'<![endif]-->';
    }

    /**
     * Checks a string of HTML for block level elements.
     *
     * @param string $content The HTML content to check.
     *
     * @return boolean If the content contains a block level tag.
     */
    public function containsBlockLevelHtml($content)
    {
        return strip_tags($content, $this->inlineTags) != $content;
    }

    /**
     * Converts newlines into `<br>` tags as long as there are no
     *   block-level HTML tags present.
     *
     * @param string $content The content to display.
     *
     * @return string HTML content.
     */
    public function convertNewLines($content)
    {
        return strip_tags($content, $this->inlineTagsMinusBr) != $content ? $content : nl2br($content);
    }

    /**
     * Converts all HTML entities to normal characters, using UTF-8.
     *
     * @param string $content The content to decode.
     *
     * @return string The decoded content.
     */
    public function decode($content)
    {
        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Converts all special characters to entites, using UTF-8.
     *
     * @param string $content The content to encode
     *
     * @return string The encoded content
     *
     * @replaces ::encode Array argument no longer supported. Use `array_map()`
     *   or similar from calling side.
     */
    public function encode($content)
    {
        return htmlentities($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Create a form element.
     *
     * If specifying a select element, it may be desirable to have a default
     *   selected option. To do so, specify a 'value' key with a string value
     *   in the $attr argument. For optgroups, use a 2-dimensional array in
     *   the 'options' key in the $attr argument.
     *
     * @param string $type One of the HTML 5 element types for use with the
     *   input tags, 'select', and 'textarea'.
     * @param string $name Form element name, unique to the form.
     * @param array  $attr Array of key => value attributes. Use 'label' to
     *   provide a string for use with a label element (which will have the
     *   for attribute set properly).
     *
     * @throws ProgrammerException If the type argument is invalid.
     *
     * @return string HTML tags ready for use.
     */
    public function makeFormElement($type, $name, array $attr = array())
    {
        $id = $this->formElementIDWithName($name);
        $type = strtolower($type);
        $allowed_types = array_merge($this->inputTypeValues, array('textarea', 'select'));

        if (!in_array($type, $allowed_types)) {
            throw new ProgrammerException('Type \'%s\' is not a valid form element type.', $type);
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
            $attributes['class'] = array_merge($classes, array('form-field', 'form-'.$type));
        }
        else if (!$has_class) {
            $attributes['class'] = array('form-field', 'form-'.$type);
        }

        // Handle the boolean attributes
        foreach ($this->booleanAttributes as $b_attr) {
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
            $label = '<label class="form-label" for="'.$id.'">'.$this->encode($attr['label']);
            if (isset($attributes['required']) && $attributes['required'] == true) {
                $label .= ' <span class="form-required-marker">*</span>';
            }
            $label .= '</label>';
        }
        unset($attr['label']);

        if ($type == 'textarea') {
            return $this->makeTextarea($name, $label, $attributes);
        }
        else if ($type == 'select') {
            return $this->makeSelectElement($name, $label, $attributes);
        }

        if ($type == 'checkbox') {
            return '<input '.$this->attributesString($attributes).'>'.$label;
        }

        return $label.'<input '.$this->attributesString($attributes).'>';
    }

    /**
     * Create a list. 'first', 'last', 'odd', 'even', 'item-INDEX' classes
     *   will be automatically added to each li element.
     *
     * @param array  $items Array of strings.
     * @param string $type  'ul' or 'ol', defaults to 'ul' even if an invalid
     *   value is passed.
     * @param array  $attr  Array of attributes for the parent element.
     *
     * @return string
     */
    public function makeList($items, $type = 'ul', $attr = array())
    {
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

        $html = '<'.$type.' '.$this->attributesString($attr).'>';
        $i = 1;
        $count = count($items);
        foreach ($items as $title) {
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
                $classes[] = 'last';
            }

            $classes[] = 'item-'.$i;

            $html .= $this->tag('li', array('class' => $classes), $title);
            $i++;
        }
        $html .= '</'.$type.'>';

        return $html;
    }

    /**
     * Takes a block of text and converts all URLs into HTML links.
     *
     * @param string  $content        The content to parse for links.
     * @param integer $linkTextLength Link text length limit.
     *
     * @return string The content with all URLs converted to HTML link.
     *
     * @replaces ::makeLinks
     */
    public function makeLinks($content, $linkTextLength = null)
    {
        $originalLinkTextLength = $this->purifer->config->get('AutoFormat.LinkifyWithTextLengthLimit.Limit');
        $this->purifier->config->set('AutoFormat.Custom', array(
            'LinkifyWithTextLengthLimit',
        ));
        $this->purifier->config->set('AutoFormat.LinkifyWithTextLengthLimit.Limit', $linkTextLength);

        $ret =  $this->purifier->purify($content);

        $this->purifier->config->set('AutoFormat.LinkifyWithTextLengthLimit.Limit', $originalLinkTextLength ? $originalLinkTextLength : 0);

        return $ret;
    }

    /**
     * Create paragraphs from 2 or more new lines.
     *
     * @param string $content Unfiltered string to paragraphify.
     *
     * @return string HTML content.
     */
    public function paragraphify($content)
    {
        $this->purifier->config->set('AutoFormat.AutoParagraph', true);

        return $this->purifier->purify($content);
    }

    /**
     * Prepares content for display in UTF-8 encoded HTML - allows HTML tags
     *
     * @param string|array $content The content to prepare.
     *
     * @return string The encoded HTML.
     */
    public function prepare($content)
    {
        if (is_array($content)) {
            return $this->purifier->purifyArray($content);
        }

        return $this->purifier->purify($content);
    }

    /**
     * Create a tag HTML string.
     *
     * @param string $tag     Tag name.
     * @param array  $attr    Attributes array.
     * @param string $content Content to place within HTML. If the tag never
     *   uses an end tag, this parameter will be ignored. In particular,
     *   `<input>`, `<link>` and similar tags.
     *
     * @return string HTML tag, ready to be displayed.
     */
    public function tag($tag, $attr = array(), $content = '')
    {
        $tag = strtolower($tag);

        if (!$this->tagRequiresEnd($tag)) {
            if (!empty($attr)) {
                return '<'.$tag.' '.$this->attributesString($attr).'>';
            }
            else {
                return '<'.$tag.'>';
            }
        }

        if (!empty($attr)) {
            return '<'.$tag.' '.$this->attributesString($attr).'>'.$this->prepare($content).'</'.$tag.'>';
        }

        return '<'.$tag.'>'.$this->prepare($content).'</'.$tag.'>';
    }

    /**
     * Create a form element ID.
     *
     * @param string $name Name attribute of the element.
     *
     * @return string Unique identifier for use with the `id` attribute.
     */
    protected function formElementIDWithName($name)
    {
        $id = 'edit-'.$this->urlParser->makeFriendly($name, '-');

        if (in_array($id, $this->formElementIds)) {
            $in_array = true;
            $i = 1;
            $original = $id;
            while ($in_array) {
                $id = $original.'-'.$i;

                if (!in_array($id, $this->formElementIds)) {
                    $in_array = false;
                    break;
                }

                $i++;
            }
        }

        $this->formElementIds[] = $id;

        return $id;
    }

    /**
     * Handles creating select fields with options.
     *
     * @param string $name       Name of the field.
     * @param string $label      Label HTML.
     * @param array  $attributes Attributes.
     *
     * @return string HTML.
     */
    protected function makeSelectElement($name, $label = '', array $attributes = array())
    {
        $options_html = $options = '';
        $attr = $attributes;

        if (isset($attr['options']) && is_array($attr['options'])) {
            $selected = isset($attributes['value']) ? $attributes['value'] : null;
            unset($attributes['value']);
            unset($attributes['type']);

            $is2d = is_array(current($attr['options']));
            $options = '';

            if ($is2d) {
                foreach ($attr['options'] as $group_name => $options) {
                    $options_html .= '<optgroup label="'.$this->encode($group_name).'">';
                    foreach ($options as $key => $value) {
                        if ($selected && $selected == $key) {
                            $options_html .= '<option selected="selected" value="'.$this->encode($key).'">'.$this->encode($value).'</option>';
                        }
                        else {
                            $options_html .= '<option value="'.$this->encode($key).'">'.$this->encode($value).'</option>';
                        }
                    }
                    $options_html .= '</optgroup>';
                }
                $options = $options_html;
            }
            else {
                foreach ($attr['options'] as $key => $value) {
                    if ($selected && $selected == $key) {
                        $options .= '<option selected="selected" value="'.$this->encode($key).'">'.$this->encode($value).'</option>';
                    }
                    else {
                        $options .= '<option value="'.$this->encode($key).'">'.$this->encode($value).'</option>';
                    }
                }
            }
            unset($attributes['options']);
        }

        $attributes['name'] = $name;

        return $label.'<select '.$this->attributesString($attributes).'>'.$options.'</select>';
    }

    /**
     * Handles creating textarea tags.
     *
     * @param string $name       Name of the field.
     * @param string $label      Label HTML.
     * @param array  $attributes Attributes.
     *
     * @return string HTTML.
     */
    protected function makeTextarea($name, $label = '', array $attributes = array())
    {
        $value = isset($attributes['value']) ? (string) $attributes['value'] : '';
        $attributes['name'] = $name;

        unset($attributes['value']);
        unset($attributes['type']);

        $ret = $label;
        $ret .= '<textarea '.$this->attributesString($attributes).'>';
        $ret .= $this->encode($value);
        $ret .= '</textarea>';

        return $ret;
    }

    /**
     * Some tags require end tags while some others do not. Example: <p> does
     *   not in HTML, but in XHTML like all tags requires an end tag. However,
     *   some browsers are not going to do the same thing when encountering
     *   several <p> tags with no endings, so it returns `true`.
     *
     * @param string $tag Tag name.
     *
     * @return bool `true` if the end tag is required, `false` otherwise.
     */
    protected function tagRequiresEnd($tag)
    {
        switch ($tag) {
            case 'input':
            case 'meta':
            case 'link':
            case 'img':
                return false;
        }

        return true;
    }

    /**
     * Get a valid value for an enumerated (only ones that represent on or off)
     *   or boolean attribute.
     *
     * @param string  $attribute_name Attribute name.
     * @param boolean $value          Boolean value for the attribute.
     *
     * @return boolean If this function returns `false`, the attribute must be
     *   omitted.
     */
    protected function validAttributeValue($attribute_name, $value)
    {
        // Assume that maybe this value is for a boolean attribute
        if (!array_key_exists($attribute_name, $this->specialEnumeratedAttributes)) {
            return false;
        }

        return $this->specialEnumeratedAttributes[$attribute_name][$value];
    }
}
