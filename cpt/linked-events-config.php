<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Cpt;

use CityOfHelsinki\WordPress\LinkedEvents as Plugin;

use CityOfHelsinki\WordPress\LinkedEvents\CacheManager;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Events;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Dates;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Keywords;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Languages;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Organizations;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Places;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Prices;

add_action( 'init', __NAMESPACE__ . '\\register' );
function register() {
    register_post_type(
		'linked_events_config',
		array(
	        'labels'             => array(
		        'name'                  => _x( 'Helsinki Events', 'Post type general name', 'textdomain' ),
		        'singular_name'         => _x( 'Helsinki Event', 'Post type singular name', 'textdomain' ),
		    ),
	        'public'             => false,
	        'publicly_queryable' => false,
	        'show_ui'            => true,
	        'show_in_menu'       => true,
	        'capability_type'    => 'post',
	        'has_archive'        => false,
	        'hierarchical'       => false,
	        'show_in_rest'       => true,
	        'supports'           => array( 'title' ),
			'delete_with_user' => false,
			'can_export' => true,
	    )
	);
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_assets' );
function admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css' );
	wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery') );
	wp_enqueue_script(
		'helsinki-linkedevents-select2',
		Plugin\plugin_url() . 'assets/admin/js/select2.js',
		array( 'jquery', 'select2' ),
		Plugin\PLUGIN_VERSION,
		true
	);
}

add_filter( 'use_block_editor_for_post_type', __NAMESPACE__ . '\\disable_editor', 10, 2 );
function disable_editor( $current_status, $post_type ) {
	if ( 'linked_events_config' === $post_type ) {
		return false;
	}
    return $current_status;
}

add_action( 'add_meta_boxes_linked_events_config', __NAMESPACE__ . '\\metabox' );
function metabox( $post ) {
	add_meta_box(
        'linked-events-configuration',
        __( 'Configuration' ),
        __NAMESPACE__ . '\\render_metabox',
        'linked_events_config',
        'advanced',
        'high',
		array(
			'filters' => array(
				'keyword' => Keywords::options(),
				'language' => Languages::options(),
				'publisher' => Organizations::options(),
				'location' => Places::options(),
				'is_free' => Prices::options(),
			),
		)
    );
}

function render_metabox( $post, $metabox ) {
	$savedOptions = maybe_unserialize( $post->post_content );

	wp_nonce_field( 'helsinki-linked-events-nonce', 'helsinki-linked-events-nonce' );

	$filterConfig = array(
		'keyword' => array(
			'title' => __( 'Keywords', 'helsinki-linkedevents' ),
			'multiple' => true,
		),
		'language' => array(
			'title' => __( 'Languages', 'helsinki-linkedevents' ),
			'multiple' => true,
		),
		'publisher' => array(
			'title' => __( 'Publishers', 'helsinki-linkedevents' ),
			'multiple' => true,
		),
		'location' => array(
			'title' => __( 'Locations', 'helsinki-linkedevents' ),
			'multiple' => true,
		),
		'is_free' => array(
			'title' => __( 'Price', 'helsinki-linkedevents' ),
			'multiple' => false,
		),
	);

	$filters = array();
	foreach ( $metabox['args']['filters'] as $filter => $options ) {
		$current = array();
		if ( ! empty( $savedOptions[$filter] ) ) {
			$current = is_array( $savedOptions[$filter] ) ? array_flip( $savedOptions[$filter] ) : $savedOptions[$filter];
		}

		$filters[$filter] = array(
			'current' => $current,
			'options' => $options,
			'title' => $filterConfig[$filter]['title'] ?? ucfirst( $filter ),
			'multiple' => $filterConfig[$filter]['multiple'] ?? false,
		);
	}

	Plugin\metabox_view( 'linked-events-config', $filters );

	if ( 'publish' === $post->post_status ) {
		Plugin\metabox_view( 'linked-events-list', Events::entities( $post->ID ) );
	}
}

add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\\filter_post_data' , 99, 2 );
function filter_post_data( $data , $postarr ) {
	if (
		empty( $_POST['helsinki-linked-events-nonce'] ) ||
		! wp_verify_nonce( $_POST['helsinki-linked-events-nonce'], 'helsinki-linked-events-nonce' )
	) {
		return $data;
	}

	if (
		empty( $postarr['ID'] ) ||
		! current_user_can( 'edit_post', $postarr['ID'] )
	) {
		return $data;
	}

	$config = $_POST['linked_events_config'] ?? array();
	if ( $config ) {
		$data['post_content'] = maybe_serialize( $config );
		CacheManager::clear( 'events-config-' . $postarr['ID'] );
	}

    return $data;
}

add_action( 'delete_post', __NAMESPACE__ . '\\delete_event_config' , 10, 2  );
function delete_event_config( $postid, $post ) {
	if ( 'linked_events_config' === $post->post_type ) {
		CacheManager::clear( 'events-config-' . $postid );
	}
}
