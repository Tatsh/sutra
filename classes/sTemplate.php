<?php
/**
 * Manages templating. Similar to fTemplating but different.
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
class sTemplate {
  /**
   * The template name. Matches directory name in './template'. Defaults to
   *   'default'.
   *
   * @var string
   */
  protected static $template_name = 'default';

  /**
   * The templates path without any ending directory separator (like /).
   *
   * @var string
   */
  private static $templates_path = './template';

  /**
   * The conditional JavaScript placed in the head element (IE only).
   *
   * @var string
   */
  protected static $conditional_head_js = '';

  /**
   * The JavaScript placed in the head element. Should be as few as possible.
   *
   * @var string
   */
  protected static $head_js = '';

  /**
   * The template JSON file decoded into an array.
   *
   * @var array
   */
  protected static $json = NULL;

  /**
   * The JavaScript files (which appear normally at the bottom of the page).
   *
   * @var array
   */
  protected static $javascript_files = array();

  /**
   * Whether or not the site is in production mode or not.
   *
   * @var boolean
   */
  protected static $in_production_mode = FALSE;

  /**
   * Array of strings of class names to apply to the body element.
   *
   * @var array
   */
  protected static $body_classes = array();

  /**
   * Array of classes that implement sTemplateVariableSetter.
   *
   * @var array
   */
  private static $variable_setter_classes = NULL;

  /**
   * Array of CDN URL prefixes.
   *
   * @var array
   */
  private static $cdns = array();

  /**
   * Paths to search for .js files.
   *
   * @var string
   */
  private static $javascript_paths = array();

  /**
   * Set the templates path.
   *
   * The path is run through fDirectory. If it is not useable, then
   *   fDirectory::__construct() will throw an fValidationException.
   *
   * @param string $path Path without ending separator, such as / or \\.
   * @return void
   *
   * @see fDirectory::__construct()
   */
  public static function setTemplatesPath($path) {
    $dir = new fDirectory($path);
    self::$templates_path = $path;
  }

  /**
   * Add a JavaScript file.
   *
   * @param string $file File name. Should be relative to site root.
   * @param boolean $prepend If this JavaScript file should become the first.
   */
  public static function addJavaScriptFile($file, $prepend = FALSE) {
    $file = str_replace('./', '/', $file);
    if ($file[0] === '/') {
      $file = substr($file, 1);
    }

    if (!$prepend) {
      self::$javascript_files[] = $file;
      return;
    }

    array_unshift(self::$javascript_files, $file);
  }

  /**
   * Load/get all JavaScript files in an array.
   *
   * @return array
   */
  public static function getJavaScriptFiles() {
    return self::$javascript_files;
  }

  /**
   * Load the template's JSON data into the class property $json.
   *
   * @return void
   */
  private static function initialize() {
    if (is_array(self::$json)) {
      return;
    }

    if (self::$template_name == 'default') {
      self::$json = array(
        'css_files' => array(),
      );
    }

    self::$json = fJSON::decode(file_get_contents(self::$templates_path.'/'.self::$template_name.'/'.self::$template_name.'.json'), TRUE);
    if (!self::$json || !is_array(self::$json)) {
      throw new fProgrammerException('Template JSON was invalid. Verify the template JSON file with a linter.');
    }

    $config = sConfiguration::getInstance();
    self::$in_production_mode = $config->getProductionModeOn(FALSE, 'bool');

    if (!self::$in_production_mode) {
      self::getJavaScriptFiles();
    }
    else {
      // Find latest compiled version; first cache then database
      $cache = sCache::getInstance();

      try {
        $cwd = getcwd();
        $cached = $cache->get(__CLASS__.'::'.$cwd.'::latest_compiled_js_filename');
        $should_delete = $cache->get(__CLASS__.'::'.$cwd.'::latest_compiled_js_filename_should_be_deleted');

        if (is_null($cached) || $should_delete) {
          $records = fRecordSet::build('CompiledJavascriptFile', array('completed=' => TRUE), array('date_completed' => 'desc'), 1);
          $records->tossIfEmpty();

          $record = $records->getRecord(0);
          $name = $record->getFilename();

          if ($should_delete && ($name != $cached)) {
            // The new file is complete
            $cache->delete(__CLASS__.'::'.$cwd.'::latest_compiled_js_filename_should_be_deleted');
          }

          if (!is_file('./'.$name)) {
            $record->delete();
            throw new fEmptySetException;
          }

          $cached = $name;

          $cache->set(__CLASS__.'::'.$cwd.'::latest_compiled_js_filename', $name, 86400 * 7);
        }

        self::$javascript_files = array($cached);
      }
      catch (fEmptySetException $e) {
        self::$javascript_files = array();

        if (!is_null($cached) && is_file('./'.$cached)) {
          self::$javascript_files[] = $cached;
        }

        // Queue to be compiled
        $new_compilation = new CompiledJavascriptFile;
        $new_compilation->setFilename('files/js-'.fCryptography::randomString(32).'.js');
        $new_compilation->store();
      }
    }
  }

  /**
   * Set the active template.
   *
   * @throws fProgrammerException If the template directory does not exist, or
   *   if the JSON file is not found or not readable.
   *
   * @param string $template_name String of template name.
   * @return void
   */
  public static function setActiveTemplate($template_name) {
    $dir = self::$templates_path.'/'.$template_name;
    $json = $dir.'/'.$template_name.'.json';
    if (is_dir($dir) && is_readable($json)) {
      self::$template_name = $template_name;
      self::initialize();
      return;
    }
    else {
      throw new fProgrammerException('No template named '.$template_name.'.');
    }

    self::$template_name = 'default';
  }

  /**
   * Get all classes that implement the TemplateInterface class.
   *
   * @return array Array of class names.
   */
  private static function getTemplateImplementationClassNames() {
    if (!is_array(self::$variable_setter_classes)) {
      $ret = array();

      foreach (get_declared_classes() as $class_name) {
        $reflect = new ReflectionClass($class_name);
        if ($reflect->implementsInterface('sTemplateVariableSetter')) {
          $ret[] = $class_name;
        }
      }

      self::$variable_setter_classes = $ret;
    }

    return self::$variable_setter_classes;
  }

  /**
   * Buffer a file in for content.
   *
   * @param string $filename File name to include without extension.
   * @param array $variables Array of key => value pairs, which will be turned into
   *   local variables before the template file is included.
   *
   * @throws fProgrammerException If the file cannot be found.
   *
   * @return string The captured content.
   */
  public static function buffer($filename, array $variables = array()) {
    foreach (self::getTemplateImplementationClassNames() as $class) {
      $callback = array($class, 'getVariables');
      $add = call_user_func_array($callback, array($filename));
      $variables = array_merge($variables, $add);
    }

    extract($variables);

    fBuffer::startCapture();

    $default = self::$templates_path.'/default/'.$filename.'.tpl.php';
    $template = self::$templates_path.'/'.self::$template_name.'/'.$filename.'.tpl.php';

    if (is_file($template)) {
      require $template;
    }
    else if (is_file($default)) {
      require $default;
    }
    else {
      fBuffer::stopCapture();
      throw new fProgrammerException('Invalid template file "%s" specified.', $filename);
    }

    return fBuffer::stopCapture();
  }

  /**
   * Get the list of stylesheets in order. The template's JSON file dictates this order.
   *
   * Currently 'default' template has no stylesheets.
   * If production mode is enabled, this will return an empty string because all
   *   CSS will come from cache.
   *
   * @return string String of link HTML tags.
   */
  public static function getStylesheetsFromJSONFile() {
    if (self::$template_name == 'default') {
      return '';
    }

    self::initialize();
    $css = array();
    $html = '';

    if (!isset(self::$json['css_files'])) {
      self::$json['css_files'] = array();
      return '';
    }

    if (self::$in_production_mode) {
      $cache = sCache::getInstance();
      $cwd = getcwd();
      $cached = $cache->get(__CLASS__.'::'.$cwd.'::last_combined_css');
      $cached_name = $cache->get(__CLASS__.'::'.$cwd.'::last_combined_css_name');

      //$cached = NULL;
      if (is_null($cached) || is_null($cached_name)) {
        foreach (self::$json['css_files'] as $media => $files) {
          if (!isset($css[$media])) {
            $css[$media] = '';
          }

          foreach ($files as $file) {
            $css[$media] .= file_get_contents('./'.self::$web_path_prefix.$file);
          }
        }

        // For CssMin
        $filters = array(
          //           'ConvertLevel3AtKeyframes' => TRUE,
          //           'ConvertLevel3Properties' => TRUE,
        );
        $plugins = array(
          'CompressColorValues' => TRUE,
        );
        $has_css_min = class_exists('CssMin');

        foreach ($css as $key => $text) {
          if (!$text) {
            unset($css[$key]);
            continue;
          }

          if ($has_css_min) {
            $css[$key] = CssMin::minify($text, $filters, $plugins);
          }
          else {
            // Simple, but CSS has to be near perfect (as it should always be)
            $css[$key] = str_replace("\n", '', $text);
          }
        }

        $cached = $css;
        $cached_name = fCryptography::randomString(32, 'alpha');
        $cache->set(__CLASS__.'::'.$cwd.'::last_combined_css_name', $cached_name, 86400 * 7);
        $cache->set(__CLASS__.'::'.$cwd.'::last_combined_css', $css, 86400 * 7);
      }

      foreach ($cached as $media => $css) {
        $href = '/media/css/c'.$cached_name.'/'.urlencode(base64_encode($media)).'.css';
        $html .= '<link rel="stylesheet" type="text/css" href="'.$href.'" media="'.$media.'">';
      }

      return $html;
    }

    $time = time();

    foreach (self::$json['css_files'] as $media => $files) {
      foreach ($files as $file) {
        $href = self::$templates_path.'/'.self::$template_name.'/'.$file.'?_='.$time;
        $html .= '<link rel="stylesheet" type="text/css" href="'.$href.'" media="'.$media.'">'."\n";
      }
    }

    return $html;
  }

  /**
   * Get string of HTML scripts for use in the head element.
   *
   * JavaScript here can only be dependent on scripts that are also
   *   in the head element.
   * Make this script optionally compile-able.
   *
   * @return string
   */
  public static function getHeadJavaScriptFromJSONFile() {
    if (self::$template_name == 'default') {
      return '';
    }

    self::initialize();

    if (!isset(self::$json['head_js_files'])) {
      self::$json['head_js_files'] = array();
      return '';
    }

    $html = '';
    $time = !self::$in_production_mode ? '?_='.time() : '';
    foreach (self::$json['head_js_files'] as $path) {
      $url = self::$templates_path.'/'.self::$template_name.'/'.$path.$time;
      if (sHTML::linkIsURI($path)) {
        $url = $path;
      }
      $html .= '<script type="text/javascript" src="'.$url.'"></script>';
    }

    return $html;
  }

  /**
   * Get string of HTML scripts conditionally for IE. All conditional comments
   *   are in the returned string.
   *
   * @return string HTML string of script tags wrapped with conditional
   *   comments as necessary.
   */
  public static function getConditionalHeadJavaScriptFromJSONFile() {
    if (self::$template_name == 'default' || self::$in_production_mode) {
      return '';
    }

    self::initialize();

    if (!isset(self::$json['conditional_head_js_files'])) {
      self::$json['conditional_head_js_files'] = array();
      return '';
    }

    $html = '';
    $time = time();
    foreach (self::$json['conditional_head_js_files'] as $rule => $files) {
      foreach ($files as $file) {
        $url = self::$web_path_prefix.self::$template_name.'/'.$file.'?_='.$time;
        if (sHTML::linkIsURI($file)) {
          $url = $file;
        }
        $html .= '<!--[if '.$rule.']>';
        $html .= '<script type="text/javascript" src="'.$url.'"></script>';
        $html .= '<![endif]-->';
      }
    }

    return $html;
  }

  /**
   * Check if a certain template exists.
   *
   * @param string $template_name Template name to check, without .tpl.php.
   * @return boolean TRUE if the template exists, otherwise FALSE.
   */
  public static function templateExists($template_name) {
    return file_exists(self::$templates_path.'/'.self::$template_name.'/'.$template_name.'.tpl.php');
  }

  /**
   * Add a CDN URL prefix WITHOUT including the final slash.
   *
   * @param string $url The prefix URL to use.
   * @return void
   */
  public static function addCDN($url) {
    self::$cdns[] = $url;
  }

  /**
   * Remove a specified CDN URL.
   *
   * @param string $url The URL to remove.
   * @return void
   */
  public static function removeCDN($url) {
    foreach (self::$cdns as $key => $value) {
      if ($value === $url) {
        unset(self::$cdns[$key]);
        return;
      }
    }
  }

  /**
   * Set the CDNs to use.
   *
   * @param array Array of URL prefixes WITHOUT the ending /.
   * @return void
   */
  public static function setCDNs(array $urls) {
    self::$cdns = $urls;
  }

  /**
   * Get the CDNs in currently in use.
   *
   * @returns array Array of string URLs.
   */
  public static function getCDNs() {
    return self::$cdns;
  }

  /**
   * Remove all CDNs.
   *
   * @return void
   */
  public static function removeCDNs() {
    self::$cdns = array();
  }

  /**
   * Get a CDN to use.
   *
   * @return string Empty string, or CDN URL prefix.
   */
  private static function getACDN() {
    if (empty(self::$cdns)) {
      return '';
    }

    $key = fCryptography::random(0, count(self::$cdns) - 1);
    return self::$cdns[$key];
  }

  /**
   * Get a correct URL to the resource named.
   *
   * If in production mode, a CDN added to the class will be randomly selected.
   *   As such, every CDN used have the same content.
   *
   * @param string $filename File name to find within
   *   self::$templates_path/$template_name.
   * @return string Path to the file.
   */
  public static function getResourcePath($filename) {
    $cdn = '';
    if (self::$in_production_mode) {
      $cdn = self::getACDN();
    }
    return $cdn.'/files/resources/'.$filename;
  }

  /**
   * Get the JavaScript files array as a set of script tags all in one string.
   *
   * @return string String of HTML script tags.
   */
  private static function getScriptTags() {
    self::initialize();

    $scripts = '';
    $time = time();

    foreach (self::$javascript_files as $filename) {
      $filename = self::$in_production_mode ? $filename : $filename.'?_='.$time;
      $scripts .= '<script type="text/javascript" src="/'.$filename.'"></script>'."\n";
    }

    return $scripts;
  }

  /**
   * Add a body class.
   *
   * @param string $class_name Class name to add.
   * @return void
   */
  public static function addBodyClass($class_name) {
    self::$body_classes[] = $class_name;
  }

  /**
   * Get the body classes.
   *
   * @return array Array of strings.
   */
  public static function getBodyClasses() {
    return self::$body_classes;
  }

  /**
   * Perform final rendering. Call this at the end of the router's main action
   *   method.
   *
   * @param array $variables Array of key => value pairs, which will be turned into
   *   local variables before the template file is included.
   *
   * @return void
   */
  public static function render(array $variables) {
    self::initialize();
    $config = sConfiguration::getInstance();
    $path = fURL::get();
    $class_path = implode(' ', self::$body_classes);

    if ($path != '/') {
      $class_path .= ' page-'.str_replace('/', '-', substr($path, 1));
    }
    else {
      $class_path = NULL;
    }

    $vars = array(
      'lang' => $config->getSiteLanguage(),
      'dir' => $config->getSiteTextDirection(),
      'head' => '',
      'is_front' => fURL::get() == $config->getBaseUrl(),
      'css' => self::getStylesheetsFromJSONFile(),
      'head_js' => self::getHeadJavaScriptFromJSONFile(),
      'conditional_head_js' => self::getConditionalHeadJavaScriptFromJSONFile(),
      'body_id' => '',
      'body_class' => $class_path,
      'site_name' => fHTML::encode($config->getSiteName()),
      'site_slogan' => fHTML::encode($config->getSiteSlogan()),
      'error_message' => fMessaging::retrieve('validation', $path),
      'message' => fMessaging::retrieve('success', $path),
      'body_js' => self::getScriptTags(),
      'logged_in' => (bool)fAuthorization::checkLoggedIn(),
      'user' => fAuthorization::getUserToken(),
      'production_mode' => $config->getProductionModeOn(FALSE, 'bool'),
      'logo_url' => fHTML::encode($config->getSiteLogoPath('string')),
    );

    foreach (self::getTemplateImplementationClassNames() as $class) {
      $vars = array_merge($vars, call_user_func_array(array($class, 'getVariables'), array('page')));

      if ($class_path) {
        $vars = array_merge($vars, call_user_func_array(array($class, 'getVariables'), array($class_path)));
      }
    }

    $title = isset($vars['title']) ? $vars['title'] : $variables['title'];
    $variables = array_merge($vars, $variables);
    $variables['title'] = $title;
    extract($variables);
    $content = isset($vars['content']) ? $vars['content'] : $variables['content'];

    fHTML::sendHeader();

    $route = str_replace('/', '-', substr($path, 1));
    $candidates = array(
      self::$templates_path.'/'.self::$template_name.'/page-'.$route.'.tpl.php',
      self::$templates_path.'/'.'default/page-'.$route.'.tpl.php',
      self::$templates_path.'/'.self::$template_name.'/page.tpl.php',
      self::$templates_path.'/'.'default'.'/page.tpl.php',
    );
    foreach ($candidates as $file) {
      if (is_readable($file)) {
        if ($vars['production_mode']) {
          fBuffer::startCapture();
          require $file;
          $output = str_replace("\n", '', fBuffer::stopCapture());
          $output = preg_replace('/\s\s+/', '', $output);
          print $output;
        }
        else {
          require $file;
        }
        return;
      }
      //fCore::debug('Did not find '.$file.'.');
    }

    throw new fUnexpectedException('Could not find a valid page template for this page.');
  }

  // @codeCoverageIgnoreStart
  /**
   * Forces use as a static class.
   *
   * @return sTemplate
   */
  private function __construct() {}
  // @codeCoverageIgnoreEnd
}
