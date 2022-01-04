<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Filters;

use CityOfHelsinki\WordPress\LinkedEvents\Api\FilterOptions;

class Languages extends FilterOptions {

	protected static $apiEndpoint = 'language';
	protected static $transientName = 'language_options';

}
