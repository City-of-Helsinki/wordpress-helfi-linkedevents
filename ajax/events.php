<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Ajax;

use CityOfHelsinki\WordPress\LinkedEvents as Plugin;
use CityOfHelsinki\WordPress\LinkedEvents\Blocks as Blocks;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Events;

add_action( 'wp_ajax_helsinki_more_events', __NAMESPACE__ . '\\more_events' );
add_action( 'wp_ajax_nopriv_helsinki_more_events', __NAMESPACE__ . '\\more_events' );

function more_events() {
	$config_id = $_POST['config'] ?? 0;

	if ( ! $config_id || ! is_numeric( $config_id ) ) {
		wp_send_json_error( null, 404 );
	}

	$events = Events::current_language_entities( absint( $config_id ) );
	if ( ! $events ) {
		wp_send_json_error( null, 404 );
	}

	$per_page = Blocks\events_per_page();
	$event_count = count( $events );
	if ( isset($_POST['paged']) && is_numeric($_POST['paged']) && $_POST['paged'] > 1 ) {
		$paged = (int) $_POST['paged'];
	} else {
		$paged = 2;
	}

	$offset = $per_page * ( $paged - 1 );
	$events = array_slice( $events, $offset, $per_page, false );
	$has_more = $event_count > ($per_page * $paged);

	$type = $_POST['view'] ?? 'grid';
	switch ( $type ) {
		case 'grid':
			$html = Blocks\render_grid_events( $events );
			break;

		default:
			$html = '';
			break;
	}

	wp_send_json_success( array(
		'html' => $html,
		'more' => $has_more,
	) );
}
