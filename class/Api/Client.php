<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api;

abstract class Client {

    /**
     * Get API base url
     *
     * @return string
     */
    protected static function get_base_url() : string {
		return 'https://api.hel.fi/linkedevents/v1';
	}

    /**
     * Create request url.
     *
     * @param string       $base_url Request base url.
     * @param string|array $path     Request path.
     * @param array        $params   Request parameters.
     *
     * @return string Request url
     */
    protected static function create_request_url( string $base_url, $path, array $params ) : string {
        if ( is_array( $path ) ) {
            $path = trailingslashit( implode( '/', $path ) );
        }

        $path = trailingslashit( $path );

        if ( empty( $params ) ) {
            $path = trailingslashit( $path );
        }

        return add_query_arg(
            $params,
            sprintf(
                '%s/%s?',
                $base_url,
                $path
            )
        );
    }

    /**
     * Do an API request
     *
     * @param string|array $path   Request path.
     * @param array        $params Request parameters.
     *
     * @return bool|mixed
     */
    public static function get( $path, array $params = [] ) {
        $base_url = self::get_base_url();

        if ( empty( $base_url ) ) {
            return false;
        }

        $request_url = self::create_request_url( $base_url, $path, $params );
        $response    = wp_remote_get( $request_url );

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }

        return false;
    }

	/**
     * Do request to 'next' url returned by the API.
     *
     * @param string $request_url Request url.
     *
     * @return false|mixed
     */
    protected static function next( string $request_url ) {
        $response = wp_remote_get( $request_url );

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }

        return false;
    }

	public static function all( $path, $params = array() ) {
		$items = array();
		$response = self::get( $path, $params );

		if ( ! empty( $response->data ) ) {
			$items = $response->data;

			$next = null;
			if ( ! empty( $response->meta->next ) ) {
				$next = $response->meta->next;
				while ( $next ) {
					$response = self::next( $next );
					if ( ! empty( $response->data ) ) {
						$items = array_merge( $items, $response->data );
					}
					$next = $response->meta->next ?? null;
				}
			}
		}

		return $items;
	}

    public static function first( $path, $params = array() ) {
        $response = self::get( $path, $params );
        if ( ! empty( $response->data ) ) {
            return $response->data[0];
        }
        return false;
    }

	protected static function map_entities( $type, $items ) {
		$entities = array();
		foreach ( $items as $item ) {
			$entities[] = new $type( $item );
		}
		return $entities;
	}
}
