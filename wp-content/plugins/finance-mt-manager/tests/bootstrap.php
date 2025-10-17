<?php

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = sys_get_temp_dir() . '/wordpress-tests-lib';
}

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "Could not find WordPress test library.\n");
    exit(1);
}

require_once $_tests_dir . '/includes/functions.php';
require_once $_tests_dir . '/includes/bootstrap.php';
