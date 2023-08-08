<?php

namespace CityOfHelsinki\WordPress\LinkedEvents\Blocks;

use CityOfHelsinki\WordPress\LinkedEvents as Plugin;
use CityOfHelsinki\WordPress\LinkedEvents\Api\Events;

use WP_Block_Editor_Context;

/**
  * Config
  */
function blocks() {
	return array(
		'grid' => array(
			'title' => __( 'Helsinki - Events', 'helsinki-linkedevents' ),
			'category' => 'helsinki-linkedevents',
			'dependencies' => array(
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-components',
				'wp-editor',
				'wp-compose',
				'wp-data',
				'wp-server-side-render',
			),
			'render_callback' => __NAMESPACE__ . '\\render_events_grid',
			'attributes' => array(
				'configID' => array(
					'type' => 'string',
					'default' => 0,
				),
				'title' => array(
					'type' => 'string',
					'default' => '',
				),
				'contentText' => array(
					'type' => 'string',
					'default' => '',
				),
				'anchor' => array(
					'type'    => 'string',
					'default' => '',
				),
				'isEditRender' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}

function events_per_page() {
	//get posts per page setting
	$posts_per_page = get_option( 'posts_per_page' );
	return $posts_per_page;
}

/**
  * Register
  */
add_action( 'init', __NAMESPACE__ . '\\register' );
function register() {
	foreach ( blocks() as $block => $config ) {
		register_block_type( "helsinki-linkedevents/{$block}", $config );
	}
}

add_filter( 'block_categories_all', __NAMESPACE__ . '\\category', 10, 2 );
function category( $categories, $context ) {
	if ( ! ( $context instanceof WP_Block_Editor_Context ) ) {
		return $categories;
	}

	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'helsinki-linkedevents',
				'title' => __( 'Helsinki', 'helsinki-linkedevents' ),
				'icon'  => 'calendar-alt',
			),
		)
	);
}

/**
  * Assets
  */
function block_dependencies() {
	$dependencies = array();
	foreach ( blocks() as $block => $config ) {
		$dependencies = array_merge(
			$dependencies,
			$config['dependencies']
		);
	}
	return array_unique( $dependencies, SORT_STRING );
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_assets', 10 );
function admin_assets( string $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }

	$base = Plugin\plugin_url();
	$debug = Plugin\debug_enabled();
	$version = $debug ? time() : Plugin\PLUGIN_VERSION;

	wp_enqueue_script(
		'helsinki-linkedevents-scripts',
		$debug ? $base . 'assets/admin/js/scripts.js' : $base . 'assets/admin/js/scripts.min.js',
		block_dependencies(),
		$version,
		true
	);

	wp_set_script_translations(
        'helsinki-linkedevents-scripts',
        'helsinki-linkedevents',
        Plugin\plugin_path() . 'languages'
    );

	wp_enqueue_style(
		'helsinki-linkedevents-tyles',
		$debug ? $base . 'assets/admin/css/styles.css' : $base . 'assets/admin/css/styles.min.css',
		array(),
		$version,
		'all'
	);
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\public_assets', 1 );
function public_assets() {
	$base = Plugin\plugin_url();
	$debug = Plugin\debug_enabled();
	$version = $debug ? time() : Plugin\PLUGIN_VERSION;

	wp_enqueue_script(
		'helsinki-linkedevents-scripts',
		$debug ? $base . 'assets/public/js/scripts.js' : $base . 'assets/public/js/scripts.min.js',
		array(),
		$version,
		true
	);

	wp_localize_script(
		'helsinki-linkedevents-scripts',
		'helsinkiLinkedEvents',
        array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		)
	);

	wp_enqueue_style(
		'helsinki-linkedevents-styles',
		$debug ? $base . 'assets/public/css/styles.css' : $base . 'assets/public/css/styles.min.css',
		array( 'wp-block-library' ),
		$version,
		'all'
	);
}

/**
  * Rendering
  */
function render_events_grid( $attributes ) {
	if ( empty( $attributes['configID'] ) ) {
		return;
	}

	$events = Events::current_language_entities( absint($attributes['configID']) );
	if ( ! $events ) {
		return;
	}

	$id = '';
	if (!empty($attributes['anchor'])) {
		$id = 'id="'.esc_attr($attributes['anchor']).'"';
	}	

	global $paged;
	if ( empty( $paged ) ) {
		$paged = 1;
	}	  
	$per_page = events_per_page();
	$offset = ( $paged - 1 ) * $per_page;

	$output = sprintf(
		'%s
		%s
		%s
		<div class="events__container events__grid">%s</div>
		%s
		',
		render_events_title( $attributes['title'] ?? '', $attributes['configID'], $attributes['isEditRender'] ),
		render_events_excerpt( $attributes['contentText'] ?? '', $attributes['configID'], $attributes['isEditRender']),
		render_events_count( count( $events ), $attributes['configID'] ),
		render_grid_events( array_slice(
			$events, $offset, $per_page, false
		) ),
		render_event_pagination(count( $events ), $per_page),
		//count( $events ) > $per_page ? render_load_more_events( $attributes['configID'] ) : ''
	);

	if ( !$attributes['isEditRender'] ) {
		$output = sprintf(
			'<div %s class="helsinki-events events">
				<div class="hds-container">
					%s
				</div>
			</div>',
			$id,
			$output
		);
	}

	return $output;
}

function render_events_title( string $title, int $configID, bool $isEditRender = false ) {
	if ( $isEditRender ) {
		return '';
	}

	return apply_filters(
		'helsinki_linkedevents_block_title',
		sprintf(
			'<h2 class="events__title">%s</h2>',
			esc_html( $title )
		),
		$title,
		$configID
	);
}

function render_events_excerpt( string $excerpt, int $configID, bool $isEditRender = false ) {
	if ( $isEditRender ) {
		return '';
	}

	return apply_filters(
		'helsinki_linkedevents_block_excerpt',
		sprintf(
			'<div class="events__excerpt">%s</div>',
			esc_html( $excerpt )
		),
		$excerpt,
		$configID
	);
}

function render_events_count( int $count, int $configID ) {
	return apply_filters(
		'helsinki_linkedevents_block_count',
		sprintf(
			'<div class="events__count">%s %s</div>',
			esc_html( $count ),
			esc_html_x( 'events', 'events text after count', 'helsinki-linkedevents' ),
		),
		$count,
		$configID
	);
}

function render_load_more_events( int $configID ) {
	return sprintf(
		'<p class="events__more">
			<button class="button hds-button" type="button" data-paged="2" data-config="%d" data-action="helsinki_more_events">%s</button>
		</p>',
		$configID,
		apply_filters(
			'helsinki_linkedevents_more_events_text',
			esc_html__( 'Show more events', 'helsinki-linkedevents' ),
			$configID
		)
	);
}

function render_event_pagination( $count, $per_page) {
	if ( function_exists('helsinki_loop_pagination') ) {
		$max_pages = ceil($count / $per_page);

		ob_start();
		helsinki_loop_pagination(array('max_num_pages' => $max_pages));
		$pagination = ob_get_clean();
		return $pagination;
	}
}

function render_grid_events( $events ) {
	return implode( '',
		apply_filters(
			'helsinki_linkedevents_grid_events',
			array_map( __NAMESPACE__ . '\\render_grid_event', $events ),
			$events
		)
	);
}

function render_grid_event( $event ) {
	return apply_filters(
		'helsinki_linkedevents_grid_item',
		sprintf(
			'<div class="events__grid__item">%s</div>',
			render_event_card( $event )
		),
		$event
	);
}

function render_event_card( $event ) {
	$card = apply_filters(
		'helsinki_linkedevents_event_card_article',
		'<article id="%1$s" class="event">%2$s</article>',
		$event
	);

	$parts = apply_filters(
		'helsinki_linkedevents_event_card_elements',
		array(
			'link_open' => sprintf(
				'<a class="event__link" href="%s">',
				esc_url( $event->permalink() )
			),
			'image' => render_event_image( $event ),
			'wrap_open' => '<div class="event__content">',
			'title' => render_event_title( $event ),
			'date' => render_event_date( $event ),
			'venue' => render_event_venue( $event ),
			'price' => render_event_price( $event ),
			'tags' => render_event_tags( $event ),
			'more' => render_event_more( $event ),
			'wrap_close' => '</div>',
			'link_close' => '</a>',
		),
		$event
	);

	return sprintf(
		$card,
		esc_attr( $event->id() ),
		implode( '', $parts )
	);
}

function render_event_image( $event ) {
	$img = $event->primary_image();
	if ($img != false) {
		$html = $img->html_img();
	}
	else {
		$html = false;
	}

	return apply_filters(
		'helsinki_linkedevents_event_image',
		sprintf(
			'<div class="event__image">%s</div>',
			$html ? $html : render_event_image_placeholder( $event )
		),
		$event
	);
}

function render_event_image_placeholder( $event ) {
	return apply_filters(
		'helsinki_linkedevents_event_image_placeholder',
		sprintf(
			'<div class="placeholder">%s</div>',
			render_event_icon( 'calendar-clock' )
		),
		$event
	);
}

function render_event_title( $event ) {
	return apply_filters(
		'helsinki_linkedevents_event_title',
		sprintf(
			'<h3 class="event__title">%s</h3>',
			esc_html( $event->name() )
		),
		$event
	);
}

function render_event_date( $event ) {
	return apply_filters(
		'helsinki_linkedevents_event_date',
		sprintf(
			'<div class="event__detail event__date">%s<div>%s<span><time>%s</time></span></div></div>',
			render_event_icon( 'calendar-clock' ),
			render_event_section_label(__('Time:', 'helsinki-linkedevents')),
			$event->formatted_time_string()
		),
		$event
	);
}

function render_event_venue( $event ) {
	$location = $event->location_string();
	return apply_filters(
		'helsinki_linkedevents_event_location',
		$location ? sprintf(
			'<address class="event__detail event__venue">%s<div>%s<span>%s</span></div></address>',
			render_event_icon( 'location' ),
			render_event_section_label(__('Location:', 'helsinki-linkedevents')),
			$location
		) : '',
		$event
	);
}

function render_event_price( $event ) {
	$prices = array();
	foreach ( $event->offers() as $offer ) {
		if ( $offer->is_free() ) {
			$price = esc_html__( 'Free', 'helsinki-linkedevents' );
		} else {
			if ( ! $offer->price() ) {
				$price = $offer->description();
			} else {
				$price = is_numeric( $offer->price() ) ? $offer->price() . ' €' : $offer->price();
			}
		}

		$prices[] = sprintf(
			'<div>%s<span class="price">%s</span></div>',
			render_event_section_label(__('Price:', 'helsinki-linkedevents')),
			wp_kses_post( $price )
		);
	}

	return apply_filters(
		'helsinki_linkedevents_event_price',
		sprintf(
			'<div class="event__detail event__prices">
				%s<div class="prices">%s</div>
			</div>',
			render_event_icon( 'ticket' ),
			implode( '', $prices )
		),
		$event,
		$prices
	);
}

function render_event_more( $event ) {
	return apply_filters(
		'helsinki_linkedevents_event_more',
		sprintf(
			'<div class="event__more">%s</div>',
			render_event_icon( 'link-external' )
		),
		$event
	);
}

function render_event_icon( string $name ) {
	$path = icon_path( $name );
	return $path ? sprintf(
		'<svg class="event__icon icon icon--%s" viewBox="0 0 24 24" %s>
			<path d="%s"></path>
		</svg>',
		$name,
		$name == 'link-external' ? 'aria-label="'. __('(Link leads to external service)', 'helsinki-linkedevents') .'"' : 'aria-hidden="true"',
		$path
	) : '';
}

function render_event_tags( $event ) {
	$tags = array();
	foreach ( $event->keywords() as $keyword ) {
		$tags[] = sprintf(
			'<li class="hds-tag hds-tag--rounded-corners"><span class="hds-tag__label">%s</span></li>',
			esc_html( $keyword->name() )
		);
	}

	return apply_filters(
		'helsinki_linkedevents_event_tags',
		$tags ? sprintf(
			'<ul class="event__tags">%s</ul>',
			implode( '', $tags )
		) : '',
		$event,
		$tags
	);
}

function render_event_section_label(string $name) {
	return apply_filters(
		'helsinki_linkedevents_event_section_label',
		sprintf(
			'<label class="event__section_label">
				%s
			</label>',
			$name
		),
		$name,
	);

}

function icon_path( string $name ) {
	$icons = array(
		'calendar-clock' => 'M17 12a6 6 0 110 12 6 6 0 010-12zm0 2a4 4 0 100 8 4 4 0 000-8zm0-12a1 1 0 011 1v1h4l.002 9.103A7.018 7.018 0 0020 11.674L20 11H4v8l6.071.001a6.95 6.95 0 00.603 2L2 21V4h4V3a1 1 0 112 0v1h8V3a1 1 0 011-1zm.5 13v2.94l1.53 1.53-1.06 1.06L16 18.56V15h1.5zM20 6H4v3h16V6z',

		'location' => 'M11.967 1.5c2.06 0 4.12.778 5.69 2.334 3.143 3.111 2.93 7.96 0 11.268l-.622.709c-2.612 2.991-4.066 4.96-5.068 6.937-1.073-2.13-2.682-4.249-5.689-7.646-2.93-3.308-3.143-8.157 0-11.268A8.06 8.06 0 0111.967 1.5zm.032 2a6.072 6.072 0 00-4.3 1.762A5.606 5.606 0 006.002 9.41c.02 1.573.648 3.134 1.766 4.398l.66.752c1.59 1.823 2.717 3.239 3.573 4.503.975-1.437 2.292-3.063 4.233-5.255 1.118-1.264 1.746-2.825 1.766-4.398a5.616 5.616 0 00-1.698-4.15A6.077 6.077 0 0011.999 3.5zM12 6a3.5 3.5 0 110 6.999A3.5 3.5 0 0112 6zm0 2c-.827 0-1.5.673-1.5 1.5S11.173 11 12 11s1.5-.673 1.5-1.5S12.827 8 12 8z',

		'ticket' => 'M14.5 2l3.125 3.125L17 5.75a.884.884 0 001.173 1.319L18.25 7l.625-.625L22 9.5 9.5 22l-3.125-3.125L7 18.25a.884.884 0 00-1.173-1.319L5.75 17l-.625.625L2 14.5 14.5 2zm0 2.5l-3 3a1 1 0 11-.991 1.127L10.5 8.5l-6 6 .731.731.169-.073.173-.06a2.656 2.656 0 012.26.312l.166.118.138.115.113.107.138.149c.613.714.785 1.676.515 2.53l-.065.18-.07.16.732.731 6.002-6a1 1 0 11.99-1.126l.008.128 3-3.002-.732-.732-.168.074-.173.06a2.656 2.656 0 01-2.26-.312L16 8.472l-.138-.115-.113-.107-.138-.149a2.652 2.652 0 01-.515-2.53l.065-.18.07-.16L14.5 4.5zm-1.707 5.293a1 1 0 111.414 1.414 1 1 0 01-1.414-1.414z',

		'link-external' => 'M5 3V19H21V21H3V3H5ZM21 3V12H19V6.413L9.91421 15.5L8.5 14.0858L17.585 5H12V3H21Z',
	);
	return $icons[$name] ?? '';
}
