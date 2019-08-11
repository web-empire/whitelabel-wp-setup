<?php
/**
 * Plugin Name: Whitelabel WP Setup
 * Author: WebEmpire
 * Author URI: https://webempire.org.in/
 * Version: 1.0.0
 * Description: This plugin is useful for whitelabel anything from the wp-content area. This plugin gives you priviledge to whitelabel WP active theme and plugins.
 * Text Domain: whitelabel-wp-content
 *
 * @package WHITELABEL_WP_CONTENT
 */

define( 'WHITELABEL_WP_CONTENT_FILE', __FILE__ );
define( 'WHITELABEL_WP_CONTENT_VER', '1.0.0' );
define( 'WHITELABEL_WP_CONTENT_DIR', plugin_dir_path( __FILE__ ) );
define( 'WHITELABEL_WP_CONTENT_URL', plugins_url( '/', __FILE__ ) );
define( 'WHITELABEL_WP_CONTENT_ROOT', dirname( plugin_basename( __FILE__ ) ) );

require_once 'classes/class-whitelabel-wp-themes-plugins-loader.php';
