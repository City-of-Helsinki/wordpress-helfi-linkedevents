<?php

/**
  * Plugin Name: Helsinki Linked Events
  * Description: Integration with the Helsinki Linked Events API.
  * Version: 2.0.0
  * License: GPLv3
  * Requires at least: 5.7
  * Requires PHP:      7.4
  * Author: ArtCloud
  * Author URI: https://www.artcloud.fi
  * Text Domain: helsinki-linkedevents
  * Domain Path: /languages
  */

namespace CityOfHelsinki\WordPress\LinkedEvents;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\init', 100 );
function init(): void {
	if ( ! function_exists('get_plugin_data') ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

    $plugin_data = \get_plugin_data( __FILE__, false, false );
	$dir = \plugin_dir_path( __FILE__ );

	/**
	  * Constants
	  */
	define( __NAMESPACE__ . '\\PLUGIN_VERSION', $plugin_data['Version'] );
	define( __NAMESPACE__ . '\\PLUGIN_PATH', $dir );
	define( __NAMESPACE__ . '\\PLUGIN_URL', \plugin_dir_url( __FILE__ ) );
	define( __NAMESPACE__ . '\\PLUGIN_BASENAME', \plugin_basename( __FILE__ ) );

	unset( $plugin_data );

	/**
	  * Plugin parts
	  */
	require_once $dir . 'functions.php';

	spl_autoload_register( __NAMESPACE__ . '\\autoloader' );

	/**
	  * Providers
	  */
	\add_filter(
		'helsinki_linkedevents_current_language',
		__NAMESPACE__ . '\\current_language',
	);

	\add_filter(
		'helsinki_linkedevents_default_language',
		__NAMESPACE__ . '\\current_language',
	);

	/**
	  * Features
	  */
	require_once $dir . 'features/blocks/register.php';
  	require_once $dir . 'features/cpt/linked-events-config.php';

	/**
	  * Integrations
	  */
	require_once $dir . 'integrations/polylang.php';

	/**
	  * Plugin ready
	  */
	\do_action( 'helsinki_linkedevents_init' );
}

\add_action( 'init', __NAMESPACE__ . '\\textdomain' );
