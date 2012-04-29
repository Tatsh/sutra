<?php
/**
 * Creates an HTML form based on an fActiveRecord class.
 *
 * @copyright Copyright (c) 2012 bne1.
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link https://github.com/tatsh/sutra
 *
 * @version 1.3
 */
class sCRUDForm {
  /**
   * Mappings.
   *
   * @var array
   * @see http://flourishlib.com/docs/FlourishSql#DataTypes
   */
  private static $column_to_form_mappings = array(
    'smallint' => 'number',
    'integer' => 'number',
    'bigint' => 'number',
    'float' => 'number',
    'real' => 'number',
    'decimal' => 'number',
    'char' => 'textfield',
    'varchar' => 'textfield',
    'text' => 'textarea',
    'blob' => 'file',
    'timestamp' => 'datetime',
    'date' => 'date',
    'time' => 'time',
    'boolean' => 'checkbox',
  );

  /**
   * Valid field types. Ones that have a value of TRUE are separate elements
   *   and are not part of the 'type' attribute of the input element.
   *
   * @var array
   */
  private static $valid_field_types = array(
    'text' => FALSE,
    'textfield' => FALSE,
    'textarea' => TRUE,
    'select' => TRUE,
    'tel' => FALSE,
    'url' => FALSE,
    'email' => FALSE,
    'password' => FALSE,
    'datetime' => FALSE,
    'date' => FALSE,
    'month' => FALSE,
    'week' => FALSE,
    'time' => FALSE,
    'datetime-local' => FALSE,
    'number' => FALSE,
    'range' => FALSE,
    'color' => FALSE,
    'checkbox' => FALSE,
    'file' => FALSE,
  );

  /**
   * Column names to never print fields for.
   *
   * @var array
   */
  private static $always_ignore = array();

  /**
   * Request method.
   *
   * @var string
   */
  private $request_method = 'post';

  /**
   * Action URL.
   *
   * @var string
   */
  private $action_url = NULL;

  /**
   * Form element attributes.
   *
   * @var string
   */
  private $form_attr = array();

  /**
   * The fields to build HTML from.
   *
   * @var array
   */
  private $fields = array();

  /**
   * If this form enables file uploads.
   *
   * @var boolean
   */
  private $file_uploads = FALSE;

  /**
   * The maximum size for a file upload. If not set, the hidden field will not
   *   be printed.
   *
   * @var integer
   */
  private $file_upload_max_size = NULL;

  /**
   * Class name passed in.
   *
   * @var string
   */
  private $class_name = NULL;

  /**
   * Buttons that will display.
   *
   * @var string
   */
  private $buttons = array();

  /**
   * Default action.
   *
   * @var string
   */
  private $action = NULL;

  /**
   * If a CSRF field should be printed.
   *
   * @var boolean
   */
  private $print_csrf = FALSE;

  /**
   * The CSRF field name.
   *
   * @var string
   */
  private $csrf_field_name = 'csrf';

  /**
   * The CSRF field URL.
   *
   * @var string
   */
  private $csrf_field_url = NULL;

  /**
   * Validate the field type.
   *
   * @throws fProgrammerException If the field type is invalid.
   *
   * @param string $type Field type.
   * @return void
   */
  private static function validateFieldType($type) {
    if (!isset(self::$valid_field_types[$type])) {
      throw new fProgrammerException('The field type specified, "%s", is not valid. Must be one of: %s',
        $type,
        implode(',', self::$valid_field_types)
      );
    }
  }

  /**
   * Configures the class to always ignore certain column names. This may be
   *   useful for fields that are managed by Flourish such as timestamp fields
   *   managed by fORMDate.
   *
   * @param array|string $field_name Field name or array or names to ignore.
   * @return void
   */
  public static function hideFieldNames($field_name) {
    if (!is_array($field_name)) {
      $field_name = func_get_args();
    }

    foreach ($field_name as $name) {
      self::$always_ignore[$name] = TRUE;
    }
  }

  /**
   * Makes an HTML form element wrapped in a div.
   *
   * @param string $type Type of the field.
   * @param string $name Name of the field.
   * @param string $label Label text of the field.
   * @param array $attr Array of fields.
   * @return string HTML of the field.
   */
  private static function makeElement($type, $name, $label, array $attr = array()) {
    if ($type == 'text') {
      $type = 'textfield';
    }

    $attr['label'] = $label;
    $class = 'form-'.$type.'-container';
    $container = '<div class="'.$class.'">';
    $container .= sHTML::makeFormElement($type, $name, $attr);
    $container .= '</div>';

    return $container;
  }

  /**
   * Valides the request method.
   *
   * @throws fProgrammerException If the request method is invalid.
   *
   * @param string $method Request method.
   * @return void
   */
  private static function validateRequestMethod($method) {
    $methods = array('get', 'post');
    if (!in_array($method, $methods)) {
      throw new fProgrammerException('Request method "%s" is invalid. Must be one of: %s',
        $method,
        implode(',', $methods)
      );
    }
  }

  /**
   * Creates a form based on the schema of a table.
   *
   * @param fActiveRecord $class_or_schema Active record object, or class
   *   name.
   * @param string $action URL for the action attribute of the form element.
   * @param string $method Method type for the form element. One of: 'post',
   *   'get'.
   * @return sCRUDForm The form object.
   */
  public function __construct($class, $action, $method = 'post', array $attr = array()) {
    $method = strtolower($method);
    self::validateRequestMethod($method);
    $this->request_method = $method;

    if (!strlen($action)) {
      throw new fProgrammerException('Action URL is invalid. Must be at least 1 character long');
    }
    $this->action_url = (string)$action;

    $class = fORM::getClass($class);
    $this->class_name = $class;
    $table = fORM::tablize($class);
    $schema = fORMSchema::retrieve($class);
    $columns = $schema->getColumnInfo($table);
    $relationships = $schema->getRelationships($table);
    $keys = $schema->getKeys($table, 'primary');
    $pk_should_be_printed = count($keys) == 1 && $columns[$keys[0]]['type']['auto_increment'] != TRUE;
    $pk_field_name = count($keys) == 1 ? $keys[0] : NULL;
    $related_columns = array();

    foreach ($relationships['many-to-one'] as $info) {
      $related_columns[$info['column']] = array(
        'column' => $info['related_column'],
        'table' => $info['related_table'],
      );
    }

    foreach ($columns as $column_name => $info) {
      if ($pk_field_name == $column_name) {
        continue;
      }
      if (isset(self::$always_ignore[$column_name])) {
        continue;
      }
      if (isset($related_columns[$column_name])) {
        $this->fields[$column_name] = array(
          'type' => 'select',
          'name' => $column_name,
          'label' => fGrammar::humanize($column_name),
          'attributes' => array(),
          'required' => TRUE,
          'related' => TRUE,
          'related_column' => $related_columns[$column_name]['column'],
          'related_table' => $related_columns[$column_name]['table'],
        );
        continue;
      }

      $field_type = isset($info['valid_values']) ? 'select' : self::$column_to_form_mappings[$info['type']];
      if (strpos($column_name, 'password') !== FALSE) {
        $field_type = 'password';
      }
      $attr = array(
        'name' => $column_name,
        'required' => is_null($info['default']) && $info['type'] != 'boolean' ? TRUE : FALSE,
      );

      if (!is_null($info['default'])) {
        $attr['value'] = $info['default'];
      }

      switch ($field_type) {
        case 'textarea':
        case 'textfield':
          if (isset($info['max_length'])) {
            $attr['maxlength'] = $info['max_length'];
          }
          $attr['spellcheck'] = TRUE;
          break;

        case 'number':
          if (isset($info['min_value'])) {
            if ($info['min_value'] instanceof fNumber) {
              $attr['min'] = $info['min_value']->__toString();
            }
            else if (is_scalar($info['min'])) {
              $attr['min'] = $info['min_value'];
            }
          }
          if (isset($info['max_value'])) {
            if ($info['max_value'] instanceof fNumber) {
              $attr['max'] = $info['max_value']->__toString();
            }
            else if (is_scalar($info['min'])) {
              $attr['max'] = $info['max_value'];
            }
          }
      }

      $this->fields[$column_name] = array(
        'type' => $field_type,
        'label' => fGrammar::humanize($column_name),
        'attributes' => $attr,
        'related' => FALSE,
        'related_column' => NULL,
        'related_table' => NULL,
      );
    }

    if ($pk_should_be_printed) {
      array_unshift($this->fields, array(
        'type' => self::$column_to_form_mappings[$columns[$pk_field_name]['type']],
        'label' => fGrammar::humanize($pk_field_name),
        'attributes' => array(
          'required' => TRUE,
        ),
        'related' => FALSE,
        'related_column' => NULL,
        'related_table' => NULL,
      ));
    }
  }

  /**
   * Changes the form content type to allow file uploads, regardless if there
   *   are file (blob) fields.
   *
   * @param boolean $bool TRUE or FALSE.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function enableFileUpload($bool) {
    $this->file_uploads = $bool ? TRUE : FALSE;
    return $this;
  }

  /**
   * Set the maximum file upload size. This affects all file upload fields.
   *
   * @param integer $size Size to allow.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function setMaxFileUploadSize($size) {
    $this->file_upload_max_size = (int)$size;
    return $this;
  }

  /**
   * Validates if a field name exists in this class.
   *
   * @throws fProgrammerException If the field name is invalid.
   *
   * @param string $name Name of the field.
   * @return sCRUDForm The object to allow method chaining.
   */
  private function validateFieldName($name) {
    if (!isset($this->fields[$name])) {
      throw new fProgrammerException('The field name specified, "%s", does not exist. Must be one of: %s',
        $name,
        implode(',', $this->fields)
      );
    }
    return $this;
  }

  /**
   * Generates the form HTML. Should be called last.
   *
   * @return string The form HTML.
   */
  public function make() {
    $fields = '';
    $db = fORMDatabase::retrieve($this->class_name);

    foreach ($this->fields as $column_name => $info) {
      if ($info['type'] == 'file') {
        $this->file_uploads = TRUE;
      }

      if ($info['related']) {
        $sql = 'SELECT %r FROM %r ORDER BY %r';
        $column = $info['related_column'];
        $options = array();
        $result = $db->translatedQuery($sql, $column, $info['related_table'], $column);

        foreach ($result as $result) {
          $options[] = $result[$column];
        }

        $info['attributes'] = array_merge($info['attributes'], array(
          'options' => $options,
          'label' => $info['label'],
        ));

        $html = '<div class="form-'.$info['type'].'-container">';
        $fields .= $html.sHTML::makeFormElement($info['type'], $column_name, $info['attributes']).'</div>';

        continue;
      }

      $fields .= self::makeElement($info['type'], $column_name, $info['label'], $info['attributes']);
    }

    if (isset($this->action)) {
      $fields .= sHTML::makeFormElement('hidden', 'action', array('name' => 'action', 'value' => $this->action));
    }

    if (count($this->buttons)) {
      $container = '<div class="form-ops-container">';
      foreach ($this->buttons as $button) {
        $action_name = $button[0];
        $label = $button[1];

        $container .= sHTML::makeFormElement('submit', 'action::'.$action_name, array('value' => $label));
      }
      $container .= '</div>';
      $fields .= $container;
    }

    if ($this->print_csrf) {
      $fields .= sHTML::makeFormElement('hidden', $this->csrf_field_name, array('value' => fRequest::generateCSRFToken($this->csrf_field_url)));
    }

    if ($this->file_uploads) {
      $this->form_attr['enctype'] = 'multipart/form-data';
      if ($this->file_upload_max_size) {
        $fields .= '<input name="MAX_FILE_SIZE" value="'.(int)$this->file_upload_max_size.'" type="hidden">';
      }
    }

    return sHTML::tag('form', $this->form_attr, $fields);
  }

  /**
   * Hides a field.
   *
   * @param string $name Name of the field.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function hideField($name) {
    $this->validateFieldName($name);
    unset($this->fields[$name]);
    return $this;
  }

  /**
   * Adds a button.
   *
   * @param string $action_name Action name. This is for use with fRequest
   *   during the request.
   * @param string $label Label of the button.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function addAction($action_name, $label) {
    if (!isset($this->action)) {
      $this->action = $action_name;
    }
    $this->buttons[] = array($action_name, $label);
    return $this;
  }

  /**
   * Enables adding a CSRF field.
   *
   * @param boolean $bool If the CSRF field should be added.
   * @param string $name Name of the field.
   * @param string $url URL for the CSRF.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function enableCSRFField($bool, $name = 'csrf', $url = NULL) {
    $this->print_csrf = $bool ? TRUE : FALSE;
    $this->csrf_field_name = $name;
    $this->csrf_field_url = $url;
    return $this;
  }

  /**
   * Override a field types attributes. This is mainly so that an e-mail or
   *   date field column will render a different field from the default.
   *
   * @param string $name Name of the field.
   * @param string $type Type of the field.
   * @param array $attr Array of other attributes.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function overrideFieldType($name, $type, array $attr = array()) {
    $type = strtolower($type);
    $this->validateFieldName($name);
    self::validateFieldType($type);

    // Ignore these from attributes
    $required = $this->fields[$name]['attributes']['required'];
    unset($attr['type']);
    unset($attr['required']);
    unset($attr['label']);

    $this->fields[$type] = $type;
    $this->fields[$name]['attributes'] = array_merge($this->fields[$name]['attributes'], $attr);
    $this->fields[$name]['attributes']['required'] = $required;

    return $this;
  }

  /**
   * Override the label name for a column.
   *
   * @param string $column_name Column name.
   * @param string $label Label to use.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function overrideLabel($column_name, $label) {
    $this->validateFieldName($column_name);
    $this->fields[$column_name]['label'] = $label;
    return $this;
  }

  /**
   * Override the related column for a related table.
   *
   * @param string $column_name Column name of this table.
   * @param string $related_table_column_name Column name that should be read
   *   from the related table.
   * @return sCRUDForm The object to allow method chaining.
   */
  public function overrideRelatedColumn($column_name, $related_table_column_name) {
    $this->validateFieldName($column_name);
    $this->fields[$column_name]['related_column'] = $related_table_column_name;
    return $this;
  }
}
