<?php

namespace CityOfHelsinki\WordPress\LinkedEvents;

function debug_enabled() {
	return defined( 'WP_DEBUG' ) && WP_DEBUG;
}

function plugin_path() {
	return untrailingslashit( PLUGIN_PATH ) . DIRECTORY_SEPARATOR;
}

function views_path( string $dir = '' ) {
	$path = plugin_path() . 'views' . DIRECTORY_SEPARATOR;
	if ( $dir ) {
		$path .= $dir . DIRECTORY_SEPARATOR;
	}
	return $path;
}

function metabox_view( string $name, $data = null ) {
	require_once views_path( 'metabox' ) . $name . '.php';
}

function config_path() {
	return plugin_path() . 'config' . DIRECTORY_SEPARATOR;
}

function plugin_url() {
	return untrailingslashit( PLUGIN_URL ) . '/';
}

function path_to_file( string $name ) {
	return plugin_path() . trim( $name ) . '.php';
}

function autoloader( $class ) {
	if ( false === stripos( $class, __NAMESPACE__ ) ) {
		return;
	}

	$class = str_replace( __NAMESPACE__, '', $class );
	$file = str_replace( '\\', DIRECTORY_SEPARATOR, path_to_file( 'class' . $class ) );
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

function textdomain() {
	load_plugin_textdomain(
		'helsinki-linkedevents',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
