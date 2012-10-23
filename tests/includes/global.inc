<?php
// @codeCoverageIgnoreStart
set_include_path(get_include_path().PATH_SEPARATOR.getcwd().'/includes');
ob_start();
require 'autoload.inc';
require 'block-exit.inc';

$_SERVER['SERVER_NAME'] = 'example.com';
$_SERVER['REQUEST_URI'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PORT'] = 80;

date_default_timezone_set('America/Chicago');
// @codeCoverageIgnoreEnd
