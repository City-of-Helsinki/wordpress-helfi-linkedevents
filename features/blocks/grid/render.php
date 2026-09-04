<?php

declare(strict_types = 1);

namespace CityOfHelsinki\WordPress\LinkedEvents\Features\Blocks\Grid;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use CityOfHelsinki\WordPress\LinkedEvents\Api\Filters\Keywords;

function determine_events_grid_id( array $attributes ): string {
	if ( ! empty( $attributes['anchor'] ) ) {
		return $attributes['anchor'];
	}

	if ( ! empty( $attributes['blockId'] ) ) {
		return $attributes['blockId'];
	}

	return md5( $attributes['title'] . $attributes['contentText'] );
}

function render_events_grid( array $attributes ): string {
	if ( $attributes['isEditRender'] ) {
		return sprintf(
			'<p>%s</p>',
			\esc_html( __( 'The events list is only visible on the public view.' ) )
		);
	}

	$config = array(
		'path' => array(),
		'events' => array(
			'field_event_list_title' => $attributes['title'] ?? '',
			'field_event_count' => (int) $attributes['eventsCount'] ?? 10,
			'field_filter_keywords' => array(),
			'events_public_url' => $attributes['configURL'],
			'events_api_url' => add_query_arg(
				event_params( $attributes['configURL'] ),
				'https://api.hel.fi/linkedevents/v1/event'
			),
			'event_list_type' => 'events',
			'use_fixtures' => false,
			'places' => array(),
			'field_event_location' => false,
			'field_event_time' => false,
			'field_free_events' => false,
			'field_remote_events' => false,
			'field_language' => false,
			'imagePlaceholder' => '<div class="image-placeholder"><span class="mask-icon hel-icon hel-icon--calendar-clock icon--calendar-clock hds-icon--calendar-clock" role="img" aria-hidden="true"></span></div>',
			'baseUrl' => 'https://tapahtumat.hel.fi',
		),
	);

	$content = array(
		render_events_title(
			$attributes['title'] ?? '',
			(int) $attributes['configID']
		),
		render_events_excerpt(
			$attributes['contentText'] ?? '',
			(int) $attributes['configID']
		)
	);

	return sprintf(
		'<div id="%s" class="wp-block-helsinki-linkedevents-grid helsinki-events events">
			<div class="hds-container">
				%s
				<div class="events__container" data-config="%s"></div>
			</div>
		</div>',
		\esc_attr( determine_events_grid_id( $attributes ) ),
		implode( PHP_EOL, $content ),
		htmlspecialchars( json_encode( array( 'config' => $config ) ) )
	);
}

function parse_event_params( string $url ): array
{
	$query = parse_url( $url, PHP_URL_QUERY );

	parse_str( html_entity_decode( $query ), $params );

	return is_array( $params ) ? $params : array();
}

function event_params( string $url)
{
	$params = parse_event_params( $url );

	if (isset($params['places'])) {
		$params['location'] = $params['places'];
		unset($params['places']);
	}

	if (isset($params['isFree'])) {
		$params['is_free'] = $params['isFree'];
		unset($params['isFree']);
	}
	if (isset($params['is_free']) && empty($params['is_free'])) {
		unset($params['is_free']);
	}

	if (isset($params['onlyRemoteEvents'])) {
		$params['internet_based'] = $params['onlyRemoteEvents'];
		unset($params['onlyRemoteEvents']);
	}

	if (isset($params['onlyEveningEvents'])) {
		$params['starts_after'] = '16:00';
		unset($params['onlyEveningEvents']);
	}

	if (isset($params['categories'])) {
		$categories = explode(',', $params['categories']);
		$keywords = array();
		foreach ($categories as $category) {
			$keyword = Keywords::search_first($category);
			if ($keyword) {
				$keywords[] = $keyword;
			}
		}
		$keyword_ids = array();
		foreach ($keywords as $keyword) {
			$keyword_ids[] = $keyword['id'];
		}
		$params['keyword_OR_set1'] = implode(',', $keyword_ids);
		unset($params['categories']);
	}

	if (isset($params['onlyChildrenEvents'])) {
		$keywords = array();
		$keywords[] = Keywords::search_first('children');
		$keyword_ids = array();
		foreach ($keywords as $keyword) {
			$keyword_ids[] = $keyword['id'];
		}
		if (isset($params['keyword_OR_set1'])) {
			$params['keyword_OR_set2'] = implode(',', $keyword_ids);
		}
		else {
			$params['keyword_OR_set1'] = implode(',', $keyword_ids);
		}
		unset($params['onlyChildrenEvents']);
	}

	if (isset($params['dateTypes'])) {

		$today = date('Y-m-d');
		$tomorrow = date('Y-m-d', strtotime('+1 day'));
		$this_week = date('Y-m-d', strtotime('this monday'));
		$weekend = date('Y-m-d', strtotime('this friday'));
		$weekend_end = date('Y-m-d', strtotime('this sunday'));

		$start = null;
		$end = null;

		$dateTypes = explode(',', $params['dateTypes']);

		foreach ($dateTypes as $dateType) {
			switch ($dateType) {
				case 'today':
					$start = $today;
					$end = $today;
					break;
				case 'tomorrow':
					if ($tomorrow < $start) {
						$start = $tomorrow;
					}
					if ($tomorrow > $end) {
						$end = $tomorrow;
					}
					break;
				case 'this_week':
					if ($this_week < $start) {
						$start = $this_week;
					}
					if ($weekend_end > $end) {
						$end = $weekend_end;
					}
					break;
				case 'weekend':
					if ($weekend < $start) {
						$start = $weekend;
					}
					if ($weekend_end > $end) {
						$end = $weekend_end;
					}
					break;
			}
		}

		if ($start) {
			$params['start'] = $start;
		}
		if ($end) {
			$params['end'] = $end;
		}

		unset($params['dateTypes']);
	}

	if (!isset($params['start'])) {
		$params['start'] = 'today';
	}

	if (!isset($params['sort'])) {
		$params['sort'] = 'end_time';
	}

	if (!isset($params['page_size'])) {
		$params['page_size'] = 100;
	}

	$params['include'] = 'keywords,location';

	return $params;
}

function render_events_title( string $title, int $configID ): string {
	return \apply_filters(
		'helsinki_linkedevents_block_title',
		sprintf(
			'<h2 class="events__title">%s</h2>',
			\esc_html( $title )
		),
		$title,
		$configID
	);
}

function render_events_excerpt( string $excerpt, int $configID ): string {
	return \apply_filters(
		'helsinki_linkedevents_block_excerpt',
		sprintf(
			'<div class="events__excerpt">%s</div>',
			\esc_html( $excerpt )
		),
		$excerpt,
		$configID
	);
}

function event_get_random_id(): string {
	return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (int) ceil(20/strlen($x)) )),1,20);
}
