<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api;

use CityOfHelsinki\WordPress\LinkedEvents\CacheManager;

class FilterOptions extends Client {

	protected static $apiEndpoint = '';
	protected static $transientName = '';
	protected static $transientExpiration = DAY_IN_SECONDS;

	public static function cache_items() {
		$cache = CacheManager::load( static::$transientName );

		if( empty( $cache ) ) {
			$cache = self::all(
				static::$apiEndpoint,
				array( 'sort' => 'name' )
			);

			if ( ! $cache ) {
				return array();
			}
	
			$filtered = static::before_store($cache);
			CacheManager::store(
				static::$transientName,
				$filtered,
				static::$transientExpiration
			);
			
			return $filtered;
		}

		return $cache;
	}

	public static function options() {
		if ( ! static::$apiEndpoint || ! static::$transientName ) {
			return array();
		}

		$items = self::cache_items();
		if ( $items ) {
			return static::map_options( $items );
		}

		$items = self::all(
			static::$apiEndpoint,
			array( 'sort' => 'name' )
		);

		if ( ! $items ) {
			return array();
		}

		$filtered = static::before_store($items);
		CacheManager::store(
			static::$transientName,
			$filtered,
			static::$transientExpiration
		);

		return self::map_options( $filtered );
	}

	public static function option( string $id ) {
		$items = (array) self::get(
			static::$apiEndpoint . '/' . $id,
			array()
		);
		
		if ( ! $items ) {
			return array();
		}
		$filtered = static::before_store_single($items);
		return $filtered;
	}

	public static function search_first( string $search ) {
		$items = (array) self::first(
			static::$apiEndpoint,
			array( 'text' => $search, 'page_size' => 1 )
		);
		
		if ( ! $items ) {
			return array();
		}
		$filtered = static::before_store_single($items);
		return $filtered;
	}

	protected static function before_store( array $items ) {
		return $items;
	}

	protected static function map_options( array $items ) {
		$options = array();
		foreach ( $items as $item ) {
			$id = $item->id ?? $item['id'] ?? null;
			if ( ! $id ) {
				continue;
			}

			$name = $item->name ?? $item['name'] ?? array();
			$options[$id] = $name;
		}
		return $options;
	}

}
