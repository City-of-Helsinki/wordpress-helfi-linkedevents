<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Filters;

use CityOfHelsinki\WordPress\LinkedEvents\Api\FilterOptions;

class Keywords extends FilterOptions {

	protected static $apiEndpoint = 'keyword';
	protected static $transientName = 'keyword_options';

	protected static function before_store( array $items ) {
		$filtered = array();
		foreach ( $items as $key => $item ) {
			$filtered[$item->id] = array(
				'id' => $item->id,
				'name' => $item->name,
			);
		}
		return $filtered;
	}

	protected static function before_store_single( array $item ) {
		$filtered = array();

		$filtered['id'] = $item['id'];
		$filtered['name'] = $item['name'];
		
		return $filtered;
	}

}
