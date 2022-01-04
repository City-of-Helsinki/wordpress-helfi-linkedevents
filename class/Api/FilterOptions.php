<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api;

use CityOfHelsinki\WordPress\LinkedEvents\CacheManager;
use CityOfHelsinki\WordPress\LinkedEvents\Api\FilterOptions;

class FilterOptions extends Client {

	protected static $apiEndpoint = '';
	protected static $transientName = '';
	protected static $transientExpiration = DAY_IN_SECONDS;

	public static function cache_items() {
		return CacheManager::load( static::$transientName );
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
