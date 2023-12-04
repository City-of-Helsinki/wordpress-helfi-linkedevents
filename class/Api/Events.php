<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api;

use CityOfHelsinki\WordPress\LinkedEvents\CacheManager;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Entities\Event;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Places;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Keywords;

class Events extends Client {

	public static function deprecated_entities( int $config_post_id ) {
		$items = CacheManager::load( 'events-config-' . $config_post_id );
		if ( $items ) {
			return self::map_entities( Event::class, $items );
		}

		$params = self::deprecated_event_params( $config_post_id );

		$response = self::get( 'event', $params );
		if ( empty( $response->data ) ) {
			return array();
		}

		CacheManager::store(
			'events-config-' . $config_post_id,
			self::add_data( $response->data )
		);

		return self::map_entities( Event::class, $response->data );
	}

	public static function entities ( string $config_url ) {
		$hash = md5( $config_url );

		$items = CacheManager::load( 'events-config-' . $hash );
		if ( $items ) {
			return self::map_entities( Event::class, $items );
		}

		$params = self::event_params( $config_url );

		$response = self::get( 'event', $params );
		if ( empty( $response->data ) ) {
			return array();
		}

		CacheManager::store(
			'events-config-' . $hash,
			self::add_data( $response->data )
		);

		return self::map_entities( Event::class, $response->data );
	}

	public static function current_language_entities( $config ) {

		if ( is_numeric( $config ) ) {
			$events = self::deprecated_entities( $config );
		}
		else {
			$events = self::entities( $config );
		}

		if ( ! $events ) {
			return array();
		}

		return array_filter( $events, function( $event ){
			return ! empty( $event->name() );
		} );
	}

	protected static function add_data( $items ) {
		$items = self::add_locations_data( $items );
		$items = self::add_keyword_data( $items );
		return $items;
	}

	protected static function add_locations_data( $items ) {
		$location_ids = array_unique( array_map( function( $item ){
			return self::event_item_location_id( $item );
		}, $items ) );

		$options = array();
		foreach ( $location_ids as $location_id ) {
			$option = Places::option($location_id);
			if ($option) {
				$options[$location_id] = $option;
			}
		}

		$out = array();
		foreach ( $items as $item ) {
			$location_id = self::event_item_location_id( $item );
			if ($location_id) {
				$option = $options[$location_id] ?? false;
			}
			if ($option) {
				if ( is_string( $option ) ) {
					$option = json_decode( $option, true );
				}

				if ( is_object( $item ) ) {
					$item->location = $option;
				} else {
					$item['location'] = $option;
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

	protected static function add_keyword_data( $items ) {
		$keyword_ids = array();
		foreach ( $items as $item ) {
			$keyword_ids = array_merge( $keyword_ids, self::event_item_keyword_ids( $item ) );
		}

		$options = array();
		foreach ( $keyword_ids as $keyword_id ) {
			$option = Keywords::option($keyword_id);
			if ($option) {
				$options[$keyword_id] = $option;
			}
		}

		$out = array();
		foreach ( $items as $item ) {
			$keyword_ids = self::event_item_keyword_ids( $item );
			if ( $keyword_ids ) {
				$keywords = array();
				foreach ( $keyword_ids as $keyword_id ) {
					$option = $options[$keyword_id] ?? false;
					if ($option) {
						$keywords[] = $option;
					}
				}
				if ( $keywords ) {
					if ( is_object( $item ) ) {
						$item->keywords = $keywords;
					} else {
						$item['keywords'] = $keywords;
					}
				}
			}
			$out[] = $item;
		}
		return $out;
	}

	protected static function event_item_keyword_ids( $item ) {
		$keywords = self::event_item_keyword_data( $item );
		return ! empty( $keywords ) ? array_map( function( $keyword ) {
			if (is_object($keyword)) {
				return basename( $keyword->{'@id'} );
			}
			else {
				return basename( $keyword['@id'] );
			}
		}, $keywords ) : [];
	}

	protected static function event_item_keyword_data( $item ) {
		if ( is_object( $item ) ) {
			return property_exists( $item, 'keywords' ) ? (array) $item->keywords : [];
		} else {
			return $item['keywords'] ?? [];
		}
	}


	protected static function deprecated_event_params( int $post_id ) {
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

	protected static function event_params( string $url) {
		$queryString = parse_url($url, PHP_URL_QUERY);

		parse_str($queryString, $params);

		if (isset($params['places'])) {
			$params['location'] = $params['places'];
			unset($params['places']);
		}

		if (isset($params['isFree'])) {
			$params['is_free'] = $params['isFree'];
			unset($params['isFree']);
		}
		if (isset($params['is_free']) && empty($params['is_free'])) {
			unset($params['is_free']);
		}

		if (isset($params['onlyRemoteEvents'])) {
			$params['internet_based'] = $params['onlyRemoteEvents'];
			unset($params['onlyRemoteEvents']);
		}

		if (isset($params['onlyEveningEvents'])) {
			$params['starts_after'] = '16:00';
			unset($params['onlyEveningEvents']);
		}

		if (isset($params['categories'])) {
			$categories = explode(',', $params['categories']);
			$keywords = array();
			foreach ($categories as $category) {
				$keyword = Keywords::search_first($category);
				if ($keyword) {
					$keywords[] = $keyword;
				}
			}
			$keyword_ids = array();
			foreach ($keywords as $keyword) {
				$keyword_ids[] = $keyword['id'];
			}
			$params['keyword_OR_set1'] = implode(',', $keyword_ids);
			unset($params['categories']);
		}

		if (isset($params['onlyChildrenEvents'])) {
			$keywords = array();
			$keywords[] = Keywords::search_first('children');
			$keyword_ids = array();
			foreach ($keywords as $keyword) {
				$keyword_ids[] = $keyword['id'];
			}
			if (isset($params['keyword_OR_set1'])) {
				$params['keyword_OR_set2'] = implode(',', $keyword_ids);
			}
			else {
				$params['keyword_OR_set1'] = implode(',', $keyword_ids);
			}
			unset($params['onlyChildrenEvents']);
		}

		if (isset($params['dateTypes'])) {

			$today = date('Y-m-d');
			$tomorrow = date('Y-m-d', strtotime('+1 day'));
			$this_week = date('Y-m-d', strtotime('this monday'));
			$weekend = date('Y-m-d', strtotime('this friday'));
			$weekend_end = date('Y-m-d', strtotime('this sunday'));

			$start = null;
			$end = null;

			$dateTypes = explode(',', $params['dateTypes']);

			foreach ($dateTypes as $dateType) {
				switch ($dateType) {
					case 'today':
						$start = $today;
						$end = $today;
						break;
					case 'tomorrow':
						if ($tomorrow < $start) {
							$start = $tomorrow;
						}
						if ($tomorrow > $end) {
							$end = $tomorrow;
						}
						break;
					case 'this_week':
						if ($this_week < $start) {
							$start = $this_week;
						}
						if ($weekend_end > $end) {
							$end = $weekend_end;
						}
						break;
					case 'weekend':
						if ($weekend < $start) {
							$start = $weekend;
						}
						if ($weekend_end > $end) {
							$end = $weekend_end;
						}
						break;
				}
			}

			if ($start) {
				$params['start'] = $start;
			}
			if ($end) {
				$params['end'] = $end;
			}

			unset($params['dateTypes']);
		}

		if (!isset($params['start'])) {
			$params['start'] = 'today';
		}

		if (!isset($params['sort'])) {
			$params['sort'] = 'end_time';
		}

		return $params;
	}

}
