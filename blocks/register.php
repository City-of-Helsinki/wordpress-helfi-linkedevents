<?php

declare(strict_types = 1);

namespace CityOfHelsinki\WordPress\LinkedEvents\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CityOfHelsinki\WordPress\LinkedEvents as Plugin;

\add_action( 'helsinki_linkedevents_init', __NAMESPACE__ . '\\init' );
function init(): void {
	\add_action( 'init', __NAMESPACE__ . '\\register_blocks', 10 );
	\add_filter( 'block_categories_all', __NAMESPACE__ . '\\register_categories', 10, 2 );

	\add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_assets', 10 );
	\add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\public_assets', 1 );
}

function register_blocks(): void {
	$common = common_block_args();
	$path = \plugin_dir_path( __FILE__ );

	foreach ( blocks_config() as $name => $args ) {
		if ( ! empty( $args['render_callback'] ) ) {
			$args['render_callback'] = __NAMESPACE__ . '\\' . $args['render_callback'];

			require_once path_to_block_render( $path, $name );
		}

		\register_block_type(
			path_to_block_json( $path, $name ),
			array_merge( $common, $args )
		);
	}
}

function path_to_block_render( string $base, string $name ): string {
	return $base . implode( DIRECTORY_SEPARATOR, array( $name, 'render.php' ) );
}

function path_to_block_json( string $base, string $name ): string {
	return $base . implode( DIRECTORY_SEPARATOR, array( $name, 'block.json' ) );
}

function blocks_config(): array {
	return array(
		'grid' => array( 'render_callback' => 'Grid\render_events_grid' ),
	);
}

function common_block_args(): array {
	return array(
		'version' => Plugin\PLUGIN_VERSION,
		'textdomain' => 'helsinki-linkedevents',
		'category' => 'helsinki-linkedevents',
	);
}

function register_categories( array $categories, $context ): array {
	if ( ! ( $context instanceof \WP_Block_Editor_Context ) ) {
		return $categories;
	}

	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'helsinki-linkedevents',
				'title' => __( 'Helsinki', 'helsinki-linkedevents' ),
				'icon'  => 'calendar-alt',
			),
		)
	);
}

/**
  * Assets
  */
function admin_assets( string $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }

	$base = Plugin\plugin_url();
	$debug = Plugin\debug_enabled();
	$version = $debug ? time() : Plugin\PLUGIN_VERSION;

	\wp_set_script_translations(
        'helsinki-linkedevents-scripts',
        'helsinki-linkedevents',
        Plugin\plugin_path() . 'languages'
    );

	\wp_register_style(
		'helsinki-linkedevents-editor-styles',
		$debug ? $base . 'assets/admin/css/styles.css' : $base . 'assets/admin/css/styles.min.css',
		array(),
		$version,
		'all'
	);

	\wp_register_style(
		'helsinki-linkedevents-styles',
		$debug ? $base . 'assets/public/css/styles.css' : $base . 'assets/public/css/styles.min.css',
		array( 'wp-block-library' ),
		$version,
		'all'
	);
}

function public_assets() {
	$base = Plugin\plugin_url();
	$debug = Plugin\debug_enabled();
	$version = $debug ? time() : Plugin\PLUGIN_VERSION;

	\wp_register_script(
		'helsinki-linkedevents-scripts',
		$debug ? $base . 'assets/public/js/scripts.js' : $base . 'assets/public/js/scripts.min.js',
		array(),
		$version,
		true
	);

	\wp_localize_script(
		'helsinki-linkedevents-scripts',
		'helsinkiLinkedEvents',
        array( 'ajaxUrl' => \admin_url( 'admin-ajax.php' ) )
	);

	\wp_register_style(
		'helsinki-linkedevents-styles',
		$debug ? $base . 'assets/public/css/styles.css' : $base . 'assets/public/css/styles.min.css',
		array( 'wp-block-library' ),
		$version,
		'all'
	);
}
