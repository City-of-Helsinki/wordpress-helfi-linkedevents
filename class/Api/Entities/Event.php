<?php
/**
 * Event entity
 *
 * @link: https://api.hel.fi/linkedevents/v1/event/helsinki:afxsfaqz44/?include=keywords,location
 * @link: https://dev.hel.fi/apis/linkedevents#documentation
 */

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Entities;

use DateTime;
use Exception;

/**
 * Class Event
 */
class Event extends Entity {

    /**
     * Event settings
     *
     * @var array
     */
    private array $settings;

    /**
     * Event constructor.
     *
     * @param mixed $entity_data Entity data.
     * @param array $settings    Event settings.
     */
    public function __construct( $entity_data, array $settings = [] ) {
        $this->settings = $settings;

        parent::__construct( $entity_data );
    }

    /**
     * Get event permalink
     *
     * @return string|false
     */
    public function permalink() {
        return 'https://tapahtumat.hel.fi/events/' . $this->id();
    }

    /**
     * Get Id
     *
     * @return mixed
     */
    public function id() {
        return $this->entity_data->id;
    }

    /**
     * Has super event
     *
     * @return bool
     */
    public function has_super_event() {
        return ! empty( $this->entity_data->super_event );
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function name() {
        return $this->key_by_language( 'name' );
    }

    /**
     * Get status
     *
     * @return mixed
     */
    public function status() {
        return $this->entity_data->event_status ?? null;
    }

    /**
     * Get short description
     *
     * @return string|null
     */
    public function short_description() {
        return $this->key_by_language( 'short_description' );
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function description() {
        return $this->key_by_language( 'description' );
    }

    /**
     * Get publisher
     *
     * @return mixed
     */
    public function publisher() {
        return $this->entity_data->publisher ?? null;
    }

    /**
     * Get start time as DateTime instance.
     *
     * @return DateTime|null
     */
    public function start_time() {
        if ( empty( $this->entity_data->start_time ) ) {
            return null;
        }

        try {
            return new DateTime( $this->entity_data->start_time );
        }
        catch ( Exception $e ) {
            error_log( $e->getMessage() );
        }

        return null;
    }

    /**
     * Get end time as DateTime instance.
     *
     * @return DateTime|null
     */
    public function end_time() {
        if ( empty( $this->entity_data->end_time ) ) {
            return null;
        }

        try {
            return new DateTime( $this->entity_data->end_time );
        }
        catch ( Exception $e ) {
            error_log( $e->getMessage() );
        }

        return null;
    }

    /**
     * Get event's formatted time string
     *
     * @return string|bool
     */
    public function formatted_time_string() {
        $start_time = $this->start_time();
        $end_time   = $this->end_time();

        if ( empty( $start_time ) ) {
            return false;
        }

        $dates = [
            1 => __( 'Mon', 'helsinki-linkedevents' ),
            2 => __( 'Tue', 'helsinki-linkedevents' ),
            3 => __( 'Wed', 'helsinki-linkedevents' ),
            4 => __( 'Thu', 'helsinki-linkedevents' ),
            5 => __( 'Fri', 'helsinki-linkedevents' ),
            6 => __( 'Sat', 'helsinki-linkedevents' ),
            7 => __( 'Sun', 'helsinki-linkedevents' ),
        ];

        $date_format = 'd.n.Y';
        $time_format = 'H:i';

        if ( $start_time && $end_time ) {
            // 13.12.2020 - 24.12.2020
            if ( $start_time->diff( $end_time )->days >= 1 ) {
                return sprintf(
                    '%s - %s',
                    $start_time->format( $date_format ),
                    $end_time->format( $date_format )
                );
            }

            // 13.12.2020 at 18:30 - 21:45
            return sprintf(
                '%s %s, %s %s - %s',
                $dates[ $start_time->format( 'N' ) ],
                $start_time->format( $date_format ),
                _x( 'at', 'time of clock', 'helsinki-linkedevents', ),
                $start_time->format( $time_format ),
                $end_time->format( $time_format )
            );
        }

        // 13.12.2020 at 18:30
        return sprintf(
            '%s %s, %s %s',
            $dates[ $start_time->format( 'N' ) ],
            $start_time->format( $date_format ),
            _x( 'at', 'time of clock', 'helsinki-linkedevents' ),
            $start_time->format( $time_format )
        );
    }

    /**
     * Get location
     *
     * @return Place
     */
    public function location() {
        return new Place( $this->entity_data->location ?? null );
    }

    /**
     * Get location string
     *
     * @return false|string
     */
    public function location_string() {
        $location = $this->location();

        if ( empty( $location ) ) {
            return false;
        }

        $location_string = [
            $location->name(),
            $location->street_address(),
            $location->address_locality(),
        ];

        $location_string = array_filter( $location_string );

        return implode( ', ', $location_string );
    }

    /**
     * Get offers
     *
     * @return array|Offer[]
     */
    public function offers() {
        if ( empty( $this->entity_data->offers ) ) {
            return array();
        }

        return array_map( fn( $offer ) => new Offer( $offer ), $this->entity_data->offers );
    }

    /**
     * Get single ticket url for offers
     *
     * @return bool|string
     */
    public function single_ticket_url() {
        $offers = $this->offers();

        if ( empty( $offers ) ) {
            return false;
        }

        $urls = array_filter( array_map( fn( $offer ) => $offer->info_url(), $offers ) );

        return 1 === count( array_unique( $urls ) ) ? $urls[0] : false;
    }

    /**
     * Get keywords
     *
     * @param int|bool $limit Limit keywords.
     *
     * @return array|Keyword[]
     */
    public function keywords( $limit = false ) {
        if ( empty( $this->entity_data->keywords ) ) {
            return [];
        }

        $keywords = array_map( fn( $keyword ) => new Keyword( $keyword ), $this->entity_data->keywords );

        if ( $limit && ! empty( $keywords ) ) {
            $keywords = array_slice( $keywords, 0, $limit );
        }

        return $keywords;
    }

    /**
     * Get info url
     *
     * @return string|null
     */
    public function info_url() {
        return $this->key_by_language( 'info_url' );
    }

    /**
     * Get images
     *
     * @return array|Image[]
     */
    public function images() {
        if ( empty( $this->entity_data->images ) ) {
            return [];
        }
        return array_map( fn( $image ) => new Image( $image ), $this->entity_data->images );
    }

    /**
     * Get primary image.
     *
     * @return false|Image|mixed
     */
    public function primary_image() {
        $images = $this->images();

        if ( ! empty( $images ) ) {
            return $images[0];
        }

        return false;
    }
}
