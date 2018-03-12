<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
require $_SERVER['DOCUMENT_ROOT'].'/../includes/globals.php';
define('DB_NAME', 'wp');

/** MySQL database username */
define('DB_USER', $global_db_user);

/** MySQL database password */
define('DB_PASSWORD', $global_db_pass);

/** MySQL hostname */
define('DB_HOST', $global_db_host);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'Dd]!k)pSgbhhTgNEZ@9=}4sA[(|NoMNLNlM{v6WfC_BL_s//CL]Xn+-+cV$jy XE');
define('SECURE_AUTH_KEY',  'QPQWDLIScNc,+V(XQ5Kr}5sLj|+-=g{Fol,VK;{oZ9wGt|Jyi+F5g~Hf[^Jko?NO');
define('LOGGED_IN_KEY',    'uDpdZ-h,0Fh,(w<.-an6`=P~.`h5@1H#6}_L08,e{FMy:-|`LBUFkHR+m--t*%WG');
define('NONCE_KEY',        'D^^_7sZJW_1|{hhp*Y@+[{Jsw:AB|<72EZYoUYQ_*HlRN@rS;FABf*Th`|v+yU_S');
define('AUTH_SALT',        'o+s?wO,q!B$KlCDydl+q#KoC+S|%.vjhC+]KmLttH]xXoO;Jd5,KCu1PXEa4bb;I');
define('SECURE_AUTH_SALT', 'eayPp mzZM,~0k$^?<:?!t_&yp:`dwrb$NtDv,-LmJnLoZ<+QMm|-#S.|)Q-q^VR');
define('LOGGED_IN_SALT',   'R+w`#P`_z%V&%Zd_ H}b&,(*!K@YF6 g}+oDL%%G%{+Da7X|.iPfK{t@G+R-^|+7');
define('NONCE_SALT',       '|qf+%|(t&_D-sy+fg.9-#s+hKPlW|3oXFgyHDk?{r/-Cc;]vY%5[@]qXR*DbW8&q');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);
/*
define('WP_ALLOW_MULTISITE', true);
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'mashpia.com');
define('PATH_CURRENT_SITE', '/blog/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
*/
define('FS_METHOD', 'ssh');
define('FTP_USER', 'mashpia');
define('FTP_PASS', 'Chayolei@Th5778');
define('FTP_HOST', 'mashpia.com');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
