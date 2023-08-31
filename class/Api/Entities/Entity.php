<?php
/**
 * Entity
 */

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Entities;

/**
 * Class Entity
 */
class Entity {

    /**
     * Entity data
     *
     * @var mixed
     */
    protected $entity_data;

    /**
     * Entity constructor.
     *
     * @param mixed $entity_data Entity data.
     */
    public function __construct( $entity_data ) {
        if ( is_array( $entity_data ) ) {
            foreach ( $entity_data as $key => $value ) {
				if ( in_array( $key, ['images', 'offers', 'keywords' ] ) ) {
					continue;
				}
                if ( is_array( $value ) ) {
                    $entity_data[$key] = (object) $value;
                }
            }
        }
        $this->entity_data = (object) $entity_data;
    }

    /**
     * Get current language
     *
     * @return bool|\PLL_Language|string
     */
    public function current_language() {
        if ( function_exists( 'pll_current_language' ) ) {
            return \pll_current_language() ?? locale();
        }
        return locale();
    }

    /**
     * Get default language
     *
     * @return bool|\PLL_Language|string
     */
    public function default_language() {
        if ( function_exists( 'pll_default_language' ) ) {
            return \pll_default_language() ?? locale();
        }
        return locale();
    }

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
        $final_language_fallback = 'fi';

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

		$value = $this->key_value( $data, $default_language );
		if ( $value ) {
			return $value;
		}

        $value = $this->key_value( $data, $final_language_fallback );
        if ( $value ) {
            return $value;
        }
    }

	protected function key_value( $data, $key ) {
		if ( is_object( $data ) ) {
			return $data->$key ?? null;
		}
		if ( is_array( $data ) ) {
			return $data[$key] ?? null;
		}
	}
}
