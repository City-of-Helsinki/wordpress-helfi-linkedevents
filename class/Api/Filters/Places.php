<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Filters;

use CityOfHelsinki\WordPress\LinkedEvents\Api\FilterOptions;

class Places extends FilterOptions {

	protected static $apiEndpoint = 'place';
	protected static $transientName = 'place_options';

	protected static function before_store( array $items ) {
		$filtered = array();
		foreach ( $items as $key => $item ) {
			$filtered[$item->id] = array(
				'id' => $item->id,
				'name' => $item->name,
				'street_address' => $item->street_address,
				'address_locality' => $item->address_locality,
				'address_region' => $item->address_region,
				'postal_code' => $item->postal_code,
				'post_office_box_num' => $item->post_office_box_num,
				'address_country' => $item->address_country,
			);
		}
		return $filtered;
	}

}
