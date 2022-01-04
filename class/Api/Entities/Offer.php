<?php
/**
 * Offer entity
 */

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Entities;

/**
 * Class Offer
 */
class Offer extends Entity {

    /**
     * Is free
     *
     * @return bool|null
     */
    public function is_free() {
        return $this->entity_data->is_free ?? null;
    }

    /**
     * Get price
     *
     * @return string|null
     */
    public function price() {
        return $this->key_by_language( 'price' );
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
     * Get description
     *
     * @return string|null
     */
    public function description() {
        return $this->key_by_language( 'description' );
    }
}
