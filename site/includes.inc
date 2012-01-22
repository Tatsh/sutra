<?php
/**
 * Use this file to add JavaScript paths, directories which contain .route
 *   files, setting globals, etc.
 *
 * @copyright Copyright (c) 2012 Your Company.
 * @author Your Name [yn] <youremail@email.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraSite
 * @link http://www.yourwebsite.com/
 *
 * @version 1.0
 */

// Defaults to make the template site work

// If CssMin.php is in your include path, uncomment
// require 'CssMin.php';

sTemplate::addJavaScriptPath('js');
sRouter::addRoutesFilePath('./routes');
