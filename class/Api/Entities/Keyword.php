<?php
/**
 * Keyword entity
 *
 * @link: https://api.hel.fi/linkedevents/v1/keyword/yso:p5121/
 * @link: https://dev.hel.fi/apis/linkedevents#documentation
 */

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Entities;

/**
 * Class Keyword
 */
class Keyword extends Entity {

    /**
     * Get id
     *
     * @return mixed
     */
    public function id() {
        return $this->entity_data->id;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function name() : ?string {
        return $this->key_by_language( 'name' );
    }
}
