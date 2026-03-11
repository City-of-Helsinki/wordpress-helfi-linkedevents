<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Integrations\Polylang;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

\add_action( 'helsinki_linkedevents_init', __NAMESPACE__ . '\\init' );
function init(): void {
	if ( is_polylang_active() ) {
		\add_filter( 'helsinki_linkedevents_polylang_active', '__return_true' );

		\add_filter(
			'helsinki_linkedevents_current_language',
			__NAMESPACE__ . '\\provide_current_language',
			15
		);

		\add_filter(
			'helsinki_linkedevents_default_language',
			__NAMESPACE__ . '\\provide_default_language',
			15
		);
	}
}

function is_polylang_active(): bool {
	return \did_action( 'pll_init' )
		&& function_exists( 'pll_current_language' )
		&& function_exists( 'pll_default_language' );
}

function provide_current_language( string $lang ): string {
	return \pll_current_language() ?: $lang;
}

function provide_default_language( string $lang ): string {
	return \pll_default_language() ?: $lang;
}
