<?php

/**
  * Plugin Name: Helsinki Linked Events
  * Description: Integration with the Helsinki Linked Events API.
  * Version: 1.16.0
  * License: GPLv3
  * Requires at least: 5.7
  * Requires PHP:      7.1
  * Author: ArtCloud
  * Author URI: https://www.artcloud.fi
  * Text Domain: helsinki-linkedevents
  * Domain Path: /languages
  */

namespace CityOfHelsinki\WordPress\LinkedEvents;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

textdomain();
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init', 100 );
function init(): void {
	if ( ! function_exists('get_plugin_data') ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

    $plugin_data = get_plugin_data( __FILE__, false, false );

	/**
	  * Constants
	  */
	define( __NAMESPACE__ . '\\PLUGIN_VERSION', $plugin_data['Version'] );
	define( __NAMESPACE__ . '\\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
	define( __NAMESPACE__ . '\\PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( __NAMESPACE__ . '\\PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

	/**
	  * Plugin parts
	  */
	require_once 'functions.php';

	spl_autoload_register( __NAMESPACE__ . '\\autoloader' );

	require_once 'blocks/register.php';
	require_once 'ajax/events.php';
  	require_once 'cpt/linked-events-config.php';

	/**
	  * Actions & filters
	  */
	//add_action( 'init', __NAMESPACE__ . '\\textdomain' );

	/**
	  * Plugin ready
	  */
	do_action( 'helsinki_linkedevents_init' );
}

function textdomain() {
	load_plugin_textdomain(
		'helsinki-linkedevents',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
