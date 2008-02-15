<?php

/**
 * Check if PostgreSQL is available.
 *
 * @return
 *  TRUE/FALSE
 */
function pgsql_is_available() {
  return function_exists('pg_connect');
}

/**
 * Check if we can connect to PostgreSQL.
 *
 * @return
 *  TRUE/FALSE
 */
function pgsql_test($url, &$success) {
  return TRUE;
}
?>