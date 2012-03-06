<?php
function block_exit($a) {
  if (isset($a)) {
    print $a;
  }
  return FALSE;
}
// Requires https://github.com/sebastianbergmann/php-test-helpers
if (function_exists('set_exit_overload')) {
  set_exit_overload('block_exit');
}
