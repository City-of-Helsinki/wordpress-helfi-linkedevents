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

	protected static function before_store_single( array $item ) {
		$filtered = array();

		$filtered['id'] = $item['id'];
		$filtered['name'] = $item['name'];
		$filtered['street_address'] = $item['street_address'];
		$filtered['address_locality'] = $item['address_locality'];
		$filtered['address_region'] = $item['address_region'];
		$filtered['postal_code'] = $item['postal_code'];
		$filtered['post_office_box_num'] = $item['post_office_box_num'];
		$filtered['address_country'] = $item['address_country'];

		return $filtered;
	}

}