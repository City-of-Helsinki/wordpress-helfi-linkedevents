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

    //override key_by_language
    /**
     * Get key by language
     *
     * @param string      $key         Event object key.
     * @param bool|object $entity_data Entity data.
     *
     * @return string|null
     */
    protected function key_by_language( string $key, $entity_data = false ) {
        $current_language = $this->current_language();
        $default_language = $this->default_language();

        if ( ! $entity_data ) {
            $entity_data = $this->entity_data;
        }

		$data = $this->key_value( $entity_data, $key );
		if ( ! $data ) {
			return;
		}

		$value = $this->key_value( $data, $current_language );
		if ( $value ) {
			return $value;
		}

        //do not fallback to a default language
        return;
    }
}
