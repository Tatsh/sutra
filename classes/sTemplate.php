<?php
/**
 * Manages templating. Similar to fTemplating but different.
 *
 * @copyright Copyright (c) 2012 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.01
 */
class sTemplate {
  /**
   * The fCache instance.
   *
   * @var fCache
   */
  private static $cache = NULL;

  /**
   * The template name. Matches directory name in './template'. Defaults to
   *   'default'.
   *
   * @var string
   */
  private static $template_name = 'default';

  /**
   * The fallback template.
   *
   * @var string
   */
  private static $template_fallback = 'default';

  /**
   * The templates path without any ending directory separator (like /).
   *
   * @var string
   */
  private static $templates_path = './template';

  /**
   * The templates path used when production mode is enabled.
   *
   * @var string
   */
  private static $production_mode_template_path = './template';

  /**
   * The JavaScript files (which appear normally at the bottom of the page).
   *
   * @var array
   */
  private static $javascript_files = array(
    'head' => array(),
    'body' => array(),
  );

  /**
   * The minified/compiled JavaScript files used in production mode.
   *
   * @var array
   */
  private static $compiled_javascript_files = array(
    'head' => array(),
    'body' => array(),
  );

  /**
   * Whether or not the site is in production mode or not.
   *
   * @var boolean
   */
  private static $in_production_mode = FALSE;

  /**
   * Array of strings of class names to apply to the body element.
   *
   * @var array
   */
  private static $body_classes = array();

  /**
   * Array of CDN URL prefixes.
   *
   * @var array
   */
  private static $cdns = array();

  /**
   * If resources such as CSS and JavaScript while not in production mode
   *   should be printed with query strings added to prevent caching (in
   *   particular with IE).
   *
   * @var boolean
   */
  private static $query_strings_enabled = TRUE;

  /**
   * Registered callbacks.
   *
   * @var array
   */
  private static $registered_callbacks = array('*' => array());

  /**
   * CSS file paths. The keys are the media types.
   *
   * @var array
   */
  private static $css_files = array(
    'all' => array(),
    'screen' => array(),
    'print' => array(),
  );

  /**
   * CSS media order.
   *
   * @var array
   */
  private static $css_media_order = array('all', 'screen', 'print');

  /**
   * The language of the page.
   *
   * @var string
   */
  private static $language = 'en';

  /**
   * The text direction of the page.
   *
   * @var string
   */
  private static $text_direction = 'ltr';

  /**
   * The site name.
   *
   * @var string
   */
  private static $site_name = 'No Site Name';

  /**
   * The site slogan.
   *
   * @var string
   */
  private static $site_slogan = '';

  /**
   * Set the fCache instance sTemplate will use.
   *
   * @param fCache $cache The cache object.
   * @return void
   */
  public static function setCache(fCache $cache) {
    self::$cache = $cache;
  }

  /**
   * Gets the fCache instance this is using.
   *
   * @throws fProgrammerException If cache is NULL.
   *
   * @return fCache The fCache instance.
   * @see sTemplate::setCache()
   */
  public static function getCache() {
    if (!self::$cache) {
      throw new fProgrammerException('Cache must be set by calling %s.', __CLASS__.'::setCache()');
    }
    return self::$cache;
  }

  /**
   * Set the site name.
   *
   * @param string $name Name for the site.
   * @return void
   */
  public static function setSiteName($name) {
    self::$site_name = (string)$name;
  }

  /**
   * Set the current mode. In production mode, the site will use minified CSS
   *   and only minified JavaScript files which are added using
   *   sTemplate::addMinifiedJavaScriptFile().
   *
   * In development mode, the site will use the CSS and JavaScript files, and
   *   will append a query string to each resource to prevent caching by
   *   default. This can be disabled by calling:
   *   sTemplate::enableQueryStrings() with FALSE as the first argument.
   *
   * @param string $mode One of 'development' or 'production'.
   * @return void
   * @see sTemplate::addMinifiedJavaScriptFile()
   * @see sTemplate::enableQueryStrings()
   */
  public static function setMode($mode = 'development') {
    $valid_modes = array('development', 'production');
    $mode = strtolower($mode);

    if (!in_array($mode, $valid_modes)) {
      throw new fProgrammerException('Invalid mode, "%s", specified. Must be one of: %s.', implode(', ', $valid_modes));
    }

    self::$in_production_mode = $mode != 'development' ? TRUE : FALSE;
  }

  /**
   * Get the current working mode.
   *
   * @return string One of: 'development', 'production'.
   */
  public static function getMode() {
    return self::$in_production_mode ? 'production' : 'development';
  }

  /**
   * Enable or disable query strings on resource URLs such as CSS while in
   *   development mode.
   *
   * @param boolean $bool Value to set. TRUE or FALSE.
   * @return void
   */
  public static function enableQueryStrings($bool = TRUE) {
    self::$query_strings_enabled = $bool ? TRUE : FALSE;
  }

  /**
   * Register a callback to be called when the template name specified is about
   *   to be rendered.
   *
   * @param callback $callback Callback. All callbacks must return an array of
   *   keys to string values. They must be registered before the template will
   *   be used with sTemplate::buffer().
   * @param string $template_name Template name (without .tpl.php) to listen
   *   for.
   * @return void
   * @see sTemplate::buffer()
   */
  public static function registerCallback($callback, $template_name = '*') {
    self::$registered_callbacks[$template_name][] = $callback;
  }

  /**
   * Calls all the registered callback for * and this template.
   *
   * @param string $template_name Template name.
   * @return array Array of key => value pairs for use in the template.
   */
  private static function callCallbacks($template_name) {
    $variables = array();

    foreach (self::$registered_callbacks['*'] as $callback) {
      $ret = $callback();
      if (!is_array($ret)) {
        throw new fProgrammerException('Callback "%s" did not return an array.', $callback);
      }
      $variables = array_merge($variables, $ret);
    }

    if (isset(self::$registered_callbacks[$template_name])) {
      foreach (self::$registered_callbacks[$template_name] as $callback) {
        $ret = $callback();
        if (!is_array($ret)) {
          throw new fProgrammerException('Callback "%s" for template "%s" did not return an array.', $callback, $template_name);
        }
        $variables = array_merge($variables, $ret);
      }
    }

    return $variables;
  }

  /**
   * Set the templates path.
   *
   * The path is run through fDirectory. If it is not useable, then
   *   fDirectory::__construct() will throw an fValidationException.
   *
   * @param string $path Path without ending separator, such as / or \\.
   * @return void
   * @see fDirectory::__construct()
   */
  public static function setTemplatesPath($path) {
    new fDirectory($path);
    self::$templates_path = str_replace('\\', '/', $path);
  }

  /**
   * Add a JavaScript file.
   *
   * @param string $file File name. Should be relative to site root or can be
   *   full URIs.
   * @param string $where Where the script should go. One of: 'head', 'body'.
   * @param boolean $prepend If this JavaScript file should become the first.
   * @return void
   */
  public static function addJavaScriptFile($filename, $where = 'body') {
    $valid_where = array('head', 'body');
    $where = strtolower($where);
    $filename = preg_replace('/\.?\//', '', $filename);

    if (!in_array($where, $valid_where)) {
      throw new fProgrammerException('The $where argument specified, "%s", is invalid. It must be one of: %s.', $where, implode(', ', $valid_where));
    }

    self::$javascript_files[$where][] = $filename;
  }

  /**
   * Add a minified JavaScript file. Should be relative to site path, or can be
   *   full URIs. These are only added during production mode.
   *
   * @param string $filename File name. Example: '/files/themin.min.js'
   * @return void
   */
  public static function addMinifiedJavaScriptFile($filename, $where = 'body') {
    $valid_where = array('head', 'body');
    $where = strtolower($where);
    $filename = preg_replace('/^\.?\//', '', $filename);

    if (!in_array($where, $valid_where)) {
      throw new fProgrammerException('The $where argument specified, "%s", is invalid. It must be one of: %s.', $where, implode(', ', $valid_where));
    }

    self::$compiled_javascript_files[$where][] = $filename;
  }

  /**
   * Load/get all JavaScript files in an array. If no argument is specified,
   *   then all JavaScript file paths will be in the array, with first level
   *   keys being 'head' and 'body'.
   *
   * @param string $where Which to get. One of: 'head', 'body'.
   * @return array
   */
  public static function getJavaScriptFiles($where = NULL) {
    $arr = self::$compiled_javascript_files;

    if (!self::$in_production_mode) {
      $arr = self::$javascript_files;
    }

    if ($where == 'head') {
      return $arr['head'];
    }
    else if ($where == 'body') {
      return $arr['body'];
    }

    return $arr;
  }

  /**
   * Gets the correct templates path.
   *
   * @return string The path.
   */
  private static function getTemplatesPath() {
    return self::$in_production_mode ? self::$production_mode_template_path : self::$templates_path;
  }

  /**
   * Add a CSS file path.
   *
   * @param string $path Path to the CSS file. Should be in the site root.
   * @param string $media Media type.
   * @param boolean $prepend If this CSS file sould be first.
   * @return void
   * @see sTemplate::setCSSMediaOrder()
   */
  public static function addCSSFile($path, $media = 'all', $prepend = FALSE) {
    $filename = preg_replace('/^\.?\//', '', $path);

    if (!isset(self::$css_files[$media])) {
      self::$css_files[$media] = array();
    }

    if (!$prepend) {
      self::$css_files[$media][] = $filename;
      return;
    }

    array_unshift(self::$css_files[$media], $filename);
  }

  /**
   * Set the CSS media type order.
   *
   * @param array $order Array of media query strings such as 'screen'.
   * @return void
   */
  public static function setCSSMediaOrder(array $order) {
    self::$css_media_order = $order;
  }

  /**
   * Set the active template.
   *
   * @param string $template_name String of template name.
   * @param string $fallback_template The fallback template.
   * @return void
   */
  public static function setActiveTemplate($template_name, $fallback_template = 'default') {
    $path = self::getTemplatesPath();
    self::$template_name = $template_name;
    self::$template_fallback = $fallback_template;
    new fDirectory($path.'/'.self::$template_name);
    new fDirectory($path.'/'.self::$template_fallback);
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
    $variables = array_merge($variables, self::callCallbacks($filename));
    $path = self::getTemplatesPath();
    $default = $path.'/'.self::$template_fallback.'/'.$filename.'.tpl.php';
    $template = $path.'/'.self::$template_name.'/'.$filename.'.tpl.php';

    extract($variables);
    fBuffer::startCapture();

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
   * Set the template path used when production mode is enabled. If this is not
   *   set, the default path will be used.
   *
   * @param string $path Path to use.
   * @return void
   */
  public static function setProductionModeTemplatesPath($path) {
    new fDirectory($path);
    self::$production_mode_template_path = $path;
  }

  private static function getStylesheetsHTMLProductionMode() {
    fCore::startErrorCapture(E_ALL);

    $html = '';
    $cache = self::getCache();
    $cwd = getcwd();
    $cached = $cache->get(__CLASS__.'::'.$cwd.'::last_combined_css');
    $cached_name = $cache->get(__CLASS__.'::'.$cwd.'::last_combined_css_name');
    $cdn = self::getACDN();
    $css = array();
    //$cached = NULL; // for debugging

    if (is_null($cached) || is_null($cached_name)) {
      foreach (self::$css_files as $media => $files) {
        if (!isset($css[$media])) {
          $css[$media] = '';
        }

        foreach ($files as $file) {
          $ret = file_get_contents(self::$production_mode_template_path.'/'.self::$template_name.'/'.$file);

          if ($ret === FALSE) {
            throw new fUnexpectedException('Unable to read file "%s"', $file);
          }

          $css[$media] .= $ret;
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
      $href = $cdn.'/media/css/c'.$cached_name.'/'.urlencode(base64_encode($media)).'.css';
      $html .= sHTML::tag('link', array(
        'rel' => 'stylesheet',
        'type' => 'text/css',
        'href' => $href,
        'media' => $media,
      ));
    }

    fCore::stopErrorCapture();

    return $html;
  }

  /**
   * Get the list of stylesheets in order.
   *
   * @throws fUnexpectedException If the CSS file cannot be read (production mode only).
   *
   * @return string String of link HTML tags.
   * @see sTemplate::setCSSMediaOrder()
   */
  private static function getStylesheetsHTML() {
    if (self::$template_name == 'default') {
      return '';
    }

    $html = '';
    $prefix = preg_replace('/^\./', '', self::getTemplatesPath());

    if (self::$in_production_mode) {
      return self::getStylesheetsHTMLProductionMode();
    }

    $qs = self::$query_strings_enabled ? '?_='.time() : '';
    $added = array();

    foreach (self::$css_media_order as $media) {
      $files = isset(self::$css_files[$media]) ? self::$css_files[$media] : array();

      foreach ($files as $file) {
        $href = $prefix.'/'.self::$template_name.'/'.$file.$qs;
        $added[$href] = TRUE;
        $html .= sHTML::tag('link', array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'href' => $href,
          'media' => $media,
        ))."\n";
      }
    }

    // Then just add the rest
    foreach (self::$css_files as $media => $files) {
      foreach ($files as $file) {
        $href = $prefix.'/'.self::$template_name.'/'.$file.$qs;
        if (!isset($added[$href])) {
          $html .= sHTML::tag('link', array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => $href,
            'media' => $media,
          ))."\n";
        }
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
  private static function getJavaScriptHTML($where) {
    $html = '';
    $qs = !self::$in_production_mode && self::$query_strings_enabled ? '?_='.time() : '';
    $cdn = '';

    if (self::$in_production_mode) {
      $cdn = self::getACDN();
    }

    foreach (self::$javascript_files[$where] as $path) {
      $url = $cdn.'/'.$path.$qs;
      if (sHTML::linkIsURI($path)) {
        $url = $path;
      }
      $html .= sHTML::tag('script', array(
        'type' => 'text/javascript',
        'src' => $url,
      ))."\n";
    }

    return $html;
  }

  /**
   * Get string of HTML scripts conditionally for IE. All conditional comments
   *   are in the returned string.
   *
   * @return string HTML string of script tags wrapped with conditional
   *   comments as necessary.
   * @todo Re-do
   */
  public static function getConditionalHeadJavaScriptFromJSONFile() {
    if (self::$template_name == 'default' || self::$in_production_mode) {
      return '';
    }

    $html = '';
    $time = !self::$in_production_mode ? '?_='.time() : '';
    $prefix = preg_replace('/^\./', '/', self::getTemplatesPath());

//     foreach (self::$json['conditional_head_js_files'] as $rule => $files) {
//       foreach ($files as $file) {
//         $url = self::$template_name.'/'.$file.'?_='.$time;
//         if (sHTML::linkIsURI($file)) {
//           $url = $file;
//         }
//         $html .= sHTML::conditionalTag($rule, 'script', array(
//           'type' => 'text/javascript',
//           'src' => $url,
//         ));
//       }
//     }

    return $html;
  }

  /**
   * Check if a certain template file exists.
   *
   * @param string $template_name Template name to check, without .tpl.php.
   * @return boolean TRUE if the template exists, otherwise FALSE.
   */
  public static function templateExists($template_name) {
    return file_exists(self::getTemplatesPath().'/'.self::$template_name.'/'.$template_name.'.tpl.php');
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
    if (!self::$in_production_mode || empty(self::$cdns)) {
      return '';
    }

    $key = fCryptography::random(0, count(self::$cdns) - 1);

    return self::$cdns[$key];
  }

  /**
   * Add a body class. This would normally be output in the class attribute of
   *   the &lt;body&gt; element.
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
   * @throws fUnexpectedException If the template cannot be found.
   * @throws fProgrammerException If the keys title or content are missing.
   *
   * @param array $variables Array of key => value pairs, which will be turned into
   *   local variables before the template file is included. Must have the keys content
   *   and title.
   * @return void
   */
  public static function render(array $variables) {
    if (!isset($variables['content'])) {
      throw new fProgrammerException('The content string is missing in the variables array.');
    }
    if (!isset($variables['title'])) {
      throw new fProgrammerException('The title string is missing in the variables array.');
    }

    $path = fURL::get();
    $classes = implode(' ', self::$body_classes);
    $cdn = self::getACDN();
    $route = str_replace('/', '-', substr($path, 1));
    $templates_path = self::getTemplatesPath();
    $candidates = array(
      $templates_path.'/'.self::$template_name.'/page-'.$route.'.tpl.php',
      $templates_path.'/'.self::$template_fallback.'/page-'.$route.'.tpl.php',
      $templates_path.'/'.self::$template_name.'/page.tpl.php',
      $templates_path.'/'.self::$template_fallback.'/page.tpl.php',
    );
    $error_message = fMessaging::retrieve('validation', $path);
    $message = fMessaging::retrieve('success', $path);
    $logged_in = fAuthorization::checkLoggedIn();

    if ($path != '/') {
      $classes .= ' page-'.str_replace('/', '-', substr($path, 1));
    }
    $classes .= $logged_in ? ' logged-in' : ' not-logged-in';

    $vars = array(
      'lang' => self::$language,
      'dir' => self::$text_direction,
      'is_front' => fURL::get() == '/',
      'css' => self::getStylesheetsHTML(),
      'head_js' => self::getJavaScriptHTML('head'),
      'conditional_head_js' => self::getConditionalHeadJavaScriptFromJSONFile(),
      'body_id' => '',
      'body_class' => $classes,
      'site_name' => fHTML::encode(self::$site_name),
      'site_slogan' => fHTML::encode(self::$site_slogan),
      'error_message' => $error_message ? $error_message : '',
      'message' => $message ? $message : '',
      'body_js' => self::getJavaScriptHTML('body'),
      'logged_in' => $logged_in,
      'user' => fAuthorization::getUserToken(),
      'production_mode' => self::$in_production_mode,
    );

    $vars = array_merge($vars, self::callCallbacks('page'));
    $vars = array_merge($vars, self::callCallbacks('page-'.$route));

    // Do not let a template override the title or content
    $vars['title'] = $variables['title'];
    $vars['content'] = $variables['content'];
    extract($vars);

    fHTML::sendHeader();

    foreach ($candidates as $file) {
      if (is_readable($file)) {
        if (self::$in_production_mode) {
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
