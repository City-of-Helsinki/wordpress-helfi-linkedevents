<?php

namespace CityOfHelsinki\WordPress\LinkedEvents;

abstract class CacheManager
{

	public static function clear( $key ) {
		$cache = self::cache_file( $key );
		if ( file_exists( $cache ) ) {
			@unlink( $cache );
		}
		delete_transient( 'helsinki_linked_events_' . $key );
	}

	public static function load( $key ) {
		$file = get_transient( 'helsinki_linked_events_cache_file_' . $key );
		$cache = self::cache_file( $key );
		if ( ! $file || ! file_exists( $cache ) ) {
			return array();
		}
		$cache = file_get_contents( $cache );
		return json_decode( $cache, true );
	}

	public static function store( $key, array $data, int $expiration = 0 ) {
		$cache = self::cache_file( $key );
		$written = file_put_contents( $cache, json_encode( $data ) );
		return set_transient(
			'helsinki_linked_events_cache_file_' . $key,
			$cache,
			$expiration ? $expiration : HOUR_IN_SECONDS
		);
	}

	public static function cache_file( $key ) {
		return trailingslashit( self::cache_dir() ) . "cache-{$key}.json";
	}

	public static function cache_dir() {
		$path = trailingslashit( wp_upload_dir()['basedir'] ) . 'helsinki-linkedevents';
		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
			file_put_contents( trailingslashit( $path ) . 'index.php', '<?php' );
		}
		return $path;
	}

	public static function delete_cache_dir() {
		$path = self::cache_dir();
		if ( file_exists( $path ) ) {
			rmdir( $path );
		}
	}

}
