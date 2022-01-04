<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Filters;

use CityOfHelsinki\WordPress\LinkedEvents\Api\FilterOptions;

class Prices extends FilterOptions {

	public static function options() {
		return array(
			'' => __( 'All', 'helsinki-linkedevents' ),
			'true' => __( 'Free', 'helsinki-linkedevents' ),
			'false' => __( 'Paid', 'helsinki-linkedevents' ),
		);
	}

}
