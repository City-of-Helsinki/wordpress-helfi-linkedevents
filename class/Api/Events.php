<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api;

use CityOfHelsinki\WordPress\LinkedEvents\CacheManager;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Entities\Event;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Places;

class Events extends Client {

	public static function entities( int $config_post_id ) {
		$items = CacheManager::load( 'events-config-' . $config_post_id );
		if ( $items ) {
			return self::map_entities( Event::class, $items );
		}

		$params = self::event_params( $config_post_id );

		$response = self::get( 'event', $params );
		if ( empty( $response->data ) ) {
			return array();
		}

		CacheManager::store(
			'events-config-' . $config_post_id,
			self::add_locations_data( $response->data )
		);

		return self::map_entities( Event::class, $response->data );
	}

	public static function current_language_entities( int $config_post_id  ) {
		$events = self::entities( $config_post_id );
		if ( ! $events ) {
			return array();
		}

		return array_filter( $events, function( $event ){
			return ! empty( $event->name() );
		} );
	}

	protected static function add_locations_data( $items ) {
		$options = Places::cache_items();
		$out = array();
		foreach ( $items as $item ) {
			$location_id = self::event_item_location_id( $item );
			if ( $location_id && isset( $options[$location_id] ) ) {
				if ( is_string( $options[$location_id] ) ) {
					$options[$location_id] = json_decode( $options[$location_id], true );
				}

				if ( is_object( $item ) ) {
					$item->location = $options[$location_id];
				} else {
					$item['location'] = $options[$location_id];
				}
			}
			$out[] = $item;
		}
		return $out;
	}

	protected static function event_item_location_id( $item ) {
		$location = self::event_item_location_data( $item );
		return ! empty( $location['@id'] ) ? basename( $location['@id'] ) : '';
	}

	protected static function event_item_location_data( $item ) {
		if ( is_object( $item ) ) {
			return property_exists( $item, 'location' ) ? (array) $item->location : [];
		} else {
			return $item['location'] ?? [];
		}
	}

	protected static function event_params( int $post_id ) {
		$post = get_post( $post_id );
		$config = array();

		if ( $post ) {
			$post = maybe_unserialize( $post->post_content );

			if ( $post ) {
				foreach ( $post as $key => $value ) {
					$config[$key] = is_array( $value ) ? implode( ',', $value ) : $value;
				}
			}
		}

		return array_filter(
			shortcode_atts(
				array(
					'start' => 'today',
					'sort' => 'start_time',
					'keyword' => '',
					'language' => '',
					'publisher' => '',
					'location' => '',
					'is_free' => '',
				),
				$config
			)
		);
	}

}
