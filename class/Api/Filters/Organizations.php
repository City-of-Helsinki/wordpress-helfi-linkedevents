<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Filters;

use CityOfHelsinki\WordPress\LinkedEvents\Api\FilterOptions;

class Organizations extends FilterOptions {

	protected static $apiEndpoint = 'organization';
	protected static $transientName = 'organization_options';

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

}
