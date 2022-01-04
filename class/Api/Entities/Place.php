<?php
/**
 * Place entity
 * https://api.hel.fi/linkedevents/v1/place/tprek:2692/
 */

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Entities;

/**
 * Class Place
 */
class Place extends Entity {

    /**
     * Get name
     *
     * @return string|null
     */
    public function name() {
        return $this->key_by_language( 'name' );
    }

    /**
     * Get street address
     *
     * @return string|null
     */
    public function street_address() {
        return $this->key_by_language( 'street_address' );
    }

    /**
     * Get address locality
     *
     * @return string|null
     */
    public function address_locality() {
        return $this->key_by_language( 'address_locality' );
    }

    /**
     * Get postal code
     *
     * @return string|null
     */
    public function postal_code() {
        return $this->entity_data->postal_code ?? null;
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
     * Get telephone
     *
     * @return string|null
     */
    public function telephone() {
        return $this->key_by_language( 'telephone' );
    }

    /**
     * Get neighborhood
     *
     * @return false|string|null
     */
    public function neighborhood() {
        if ( empty( $this->entity_data->divisions ) ) {
            return false;
        }

        foreach ( $this->entity_data->divisions as $division ) {
            if ( 'neighborhood' === $division->type ) {
                return $this->key_by_language( 'name', $division );
            }
        }

        return false;
    }

    /**
     * Get coordinates
     *
     * @return array|null
     */
    public function coordinates() {
        return $this->entity_data->position->coordinates ?? null;
    }

    /**
     * Get Google Maps link
     *
     * @return false|string
     */
    public function google_maps_link() {
        return ServiceLinks::google_maps_link( [
            $this->street_address(),
            $this->neighborhood(),
            $this->address_locality(),
        ] );
    }

    /**
     * Get HSL directions link
     *
     * @return false|string
     */
    public function hsl_directions_link() {
        return ServiceLinks::hsl_directions_link(
            $this->street_address(),
            $this->address_locality()
        );
    }

    /**
     * Get Google directions link
     *
     * @return false|string
     */
    public function google_directions_link() {
        return ServiceLinks::google_directions_link(
            $this->street_address(),
            $this->address_locality()
        );
    }
}
