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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'divi' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'w{Hv~mIk6-|dQ{%+5g{D L3|W g6ubs8v`As!-hrt:R]j Lba,yox<JA<-iHsD j' );
define( 'SECURE_AUTH_KEY',  'S S@Ao+((s<=a$.6^(+dP=}=51Mh(%lnwE5Ed6{M%-8L}/}ntxk-NXNH#FYVB<jV' );
define( 'LOGGED_IN_KEY',    'SyZ=7`-aP&c>oI4dP hvxVcpC%f_?NK!)#7:Ys5tHBM6L$7ALD)q}MJi*zo?Re<E' );
define( 'NONCE_KEY',        'x:$@B:^he+qs+j#DjfU;?.{$Eu5!M|42q9iCQ0ibP>x=z+.FeoHp.Y=JJ9sF(Ja1' );
define( 'AUTH_SALT',        't&N P`%qQ>dYRJ`iw)hcB7/9D]1P8<1s3^I}K`#f/2]kAJ1V9A=U+_KdRPfa!WH(' );
define( 'SECURE_AUTH_SALT', 'O^vq,`n@R9N .znD1:Wy)1lpY9h9Em(h^JngG6(&q&xh;JWJY6gr}@bZ*abw]99+' );
define( 'LOGGED_IN_SALT',   '[H=mR6O{y__FJhdtD=k7.tTcnbrh0rva>R3!T2[MiB#1)h+)zF5$;kf}e[y2:Nts' );
define( 'NONCE_SALT',       'faU[C$u7o|:/drxQ{l@y[7t)8fek&!<<Fu3/Qv<mitH{z<W/YNhqb(4nf+H{/-Q)' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
