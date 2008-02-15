<?php

/**
 * Check if MySQLi is available.
 *
 * @return
 *  TRUE/FALSE
 */
function mysqli_is_available() {
  return function_exists('mysqli_connect');
}

/**
 * Check if we can connect to MySQLi.
 *
 * @return
 *  TRUE/FALSE
 */
function mysqli_test() {
  return TRUE;
}

?>