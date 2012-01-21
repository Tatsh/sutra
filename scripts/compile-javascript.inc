<?php
/**
 * Defines the compileJavaScript() function.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraScripts
 * @link http://www.example.com/
 *
 * @version 1.0
 */

$reflection = new ReflectionClass('fTemplating');
if (!$reflection->hasMethod('minifyJavascript')) {
  throw new fUnexpectedException('fTemplating.php does not have the required patch for this to work.');
}

$javascript_files = sTemplate::getJavaScriptFiles();
$google_analytics_ua = SiteVariable::getGoogleAnalyticsUA('string', '00-00000');

function compileJavaScript() {
  global $javascript_files, $google_analytics_ua;

  $build = '';
  foreach ($javascript_files as $file) {
    $build .= file_get_contents($file);
  }

  $build .= "var _gaq = window['_gaq'] || [];
  _gaq.push(['_setAccount', '${google_analytics_ua}']);
  _gaq.push(['_trackPageview']);
  window['_gaq'] = _gaq;

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();";

  $compiled = fTemplating::minifyJavascript($build);
  if (!$compiled) {
    throw new fUnexpectedException('No output from fTemplating::minifyJavascript()');
  }

  return $compiled;
}
