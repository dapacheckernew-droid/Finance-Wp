<?php

define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'wordpress');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'wordpress');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY',         'change-me');
define('SECURE_AUTH_KEY',  'change-me');
define('LOGGED_IN_KEY',    'change-me');
define('NONCE_KEY',        'change-me');
define('AUTH_SALT',        'change-me');
define('SECURE_AUTH_SALT', 'change-me');
define('LOGGED_IN_SALT',   'change-me');
define('NONCE_SALT',       'change-me');

define('FS_METHOD', 'direct');

define('WP_DEBUG', true);

define('ABSPATH', __DIR__ . '/');

require_once ABSPATH . 'wp-settings.php';
