<?php
// @codeCoverageIgnoreStart
ob_start();
require './autoload.inc';
require './block-exit.inc';
require './stubs.inc';

$_SERVER['SERVER_NAME'] = 'example.com';
$_SERVER['REQUEST_URI'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PORT'] = 80;
// @codeCoverageIgnoreEnd
