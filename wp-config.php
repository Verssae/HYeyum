<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/volume3/web/wordpress/wp-content/plugins/wp-super-cache/' );
define('DB_NAME','wordpress');

/** MySQL database username */
define('DB_USER','wordpress_user');

/** MySQL database password */
define('DB_PASSWORD','kamilake1234');

/** MySQL hostname */
define('DB_HOST', 'localhost:/run/mysqld/mysqld10.sock');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY','5PNg4hWTUQSLjLwSYeqvrHRcTfilK8tKD04bHAYX1/d8ySWyfEwGLGmywCaL6ExX');
define('SECURE_AUTH_KEY','jwa44YrTHG+0Q6RTPfP9oomorTJpig62tA4LQKCAxYIwlZVagngsWjIMsT5WN+00');
define('LOGGED_IN_KEY','jMyc6AGCPQRYc4RMPGVZ2qmU5luuEXm7BHel4j/x8BmN8CX2j+bMns3kRuzU6p0F');
define('NONCE_KEY','CU+WM0+n1LD04tcf/4v9e4Bbq9h9X1LPfi22SIoUwZVGkyWeFIw3NlmCxckhW0uY');
define('AUTH_SALT','n8ksvcfXvo3NzwZzrxfiIdbV5oLsvgp3j8C7Npf+1s3SAbzGmeo8l/C1MzBsSDea');
define('SECURE_AUTH_SALT','afW+L5dhAPcL+5+wrDyIBKNBg5InupWRyPZvp8/EoRytIrCapyDsliCJP/tc4tbA');
define('LOGGED_IN_SALT','3YHs3jJy6TquqWOIqn0r/o2Kf8ytEf2warmVkN/LH+ES9yxbFnPC2yQmI3QxnH2+');
define('NONCE_SALT','V3KqAPs2WLuStR7n/TMKJNcglfgL2Lbr/a/q02fqvri1YIKUQ50/a58f0Bqoe03j');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 */

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */
$pageURL = 'http';
if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
$pageURL .= "://";
if ($_SERVER["SERVER_PORT"] != "80" and $_SERVER["SERVER_PORT"] != "443") {
	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
} else {
	$pageURL .= $_SERVER["SERVER_NAME"];
}

if ($_SERVER["HOST"] != "") {
	define('WP_SITEURL', $pageURL);
} else {
        define('WP_SITEURL', $pageURL.'/wordpress');
}

if (!defined('SYNOWORDPRESS'))
	define('SYNOWORDPRESS', 'Synology Inc.');

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
require_once(ABSPATH . 'syno-misc.php');

define( 'AUTOMATIC_UPDATER_DISABLED', true );
add_filter('pre_site_transient_update_core','__return_null');
