<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'my-first' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'YpB>TwIv|4K8:xD6:<vQ1?650nz0*^qq+Zv}2MwjdP/0S~})v2W5mGivevS!GbyH' );
define( 'SECURE_AUTH_KEY',  '-Cjc7=d^yT[jZ;@3h<bo:Cb>r7o>p&K8m<SxN]j~US9va-_KZmg{Prt/68=Bsses' );
define( 'LOGGED_IN_KEY',    'tdO:s4JI2`fK8_<Z!7[|eDt%.m:DdG-C$30@a|Aj&#:TQK,O^(-kr*rhu|G7jL`9' );
define( 'NONCE_KEY',        'ehzT?@<Csd=ODK,zf0U~>;d/MW&~[4N^,h9Mun7Gs-qkrq2}JH;k5fD3bJQb}!a8' );
define( 'AUTH_SALT',        '8~h76m|_f8MKo!Vv`z}Y>P,4RjU,inNA)~DVfbJ~lQU`^xAgJJ,9l8*&JmNHOxd%' );
define( 'SECURE_AUTH_SALT', '1$B%eoFh:;RLzel+OK)D<@=Mm0Q<%(SzOEX]p%L6]sB,hy&2S%s`1oDQ0q</Jw8}' );
define( 'LOGGED_IN_SALT',   'G [GudW#WuMxrZ;C*uios>2#f7|n-}{]Jo:u;g)=YH*E(<>o)3#lJ1t4I:/D.vJ8' );
define( 'NONCE_SALT',       'Rc[q(MX:]0[Pz7(.P8E>j}*@r9jJqZTMk@Tl(-?<eCS7ZoGy^mo]V<~ *SV0|U>*' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'rao_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
define('FS_METHOD', 'direct');


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

