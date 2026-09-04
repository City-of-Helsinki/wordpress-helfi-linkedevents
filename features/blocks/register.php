<?php

declare(strict_types = 1);

namespace CityOfHelsinki\WordPress\LinkedEvents\Features\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use function CityOfHelsinki\WordPress\LinkedEvents\plugin_path;
use function CityOfHelsinki\WordPress\LinkedEvents\plugin_url;
use function CityOfHelsinki\WordPress\LinkedEvents\plugin_version;

\add_action( 'helsinki_linkedevents_init', __NAMESPACE__ . '\\init' );
function init(): void {
	\add_action( 'init', __NAMESPACE__ . '\\register_blocks', 10 );
	\add_filter( 'block_categories_all', __NAMESPACE__ . '\\register_categories', 10, 2 );

	\add_filter( 'helsinki_wp_allowed_blocks', __NAMESPACE__ . '\\provide_allowed_blocks', 10 );

	\add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );

	\add_filter(
		'load_script_translation_file',
		__NAMESPACE__ . '\\translations_location',
		10, 3
	);
}

function enqueue_assets(): void {
	\wp_register_script(
		'helsinki-linkedevents-app',
		plugin_url() . 'assets/js/app.js',
		array(
			'react',
			'react-dom',
		),
		plugin_version(),
		true
	);
}

function translations_location( string $file, string $handle, string $domain ): string {
	if ( 'helsinki-linkedevents' === $domain ) {
		return str_replace( WP_LANG_DIR . '/plugins', plugin_path() . 'languages', $file );
	}

	return $file;
}

function provide_allowed_blocks( array $blocks ): array {
	if ( isset( $blocks['common'] ) ) {
		$blocks['common']['helsinki-linkedevents/grid'] = true;
	}

	return $blocks;
}

function register_blocks(): void {
	$path = \plugin_dir_path( __FILE__ );

	require_once $path . 'grid/render.php';

	\register_block_type(
		$path . 'grid/block.json',
		array( 'render_callback' => __NAMESPACE__ . '\\Grid\render_events_grid' )
	);
}

function register_categories( array $categories, $context ): array {
	if ( $context instanceof \WP_Block_Editor_Context ) {
		return array_merge( $categories, array(
			array(
				'slug' => 'helsinki-linkedevents',
				'title' => __( 'Helsinki', 'helsinki-linkedevents' ),
				'icon'  => 'calendar-alt',
			),
		) );
	}

	return $categories;
}
