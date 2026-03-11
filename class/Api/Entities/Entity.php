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
	public function __construct( $entity_data )
	{
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
     */
	public function current_language(): string
	{
	    return (string) \apply_filters( 'helsinki_linkedevents_current_language', '' );
	}

    /**
     * Get default language
     */
	public function default_language(): string
	{
	    return (string) \apply_filters( 'helsinki_linkedevents_default_language', '' );
	}

    /**
     * Get key by language
     *
     * @param string      $key         Event object key.
     * @param bool|object $entity_data Entity data.
     *
     * @return string|null
     */
	protected function key_by_language( string $key, $entity_data = false )
	{
		$data = $entity_data
			? $this->key_value( $entity_data, $key )
			: $this->key_value( $this->entity_data, $key );

		if ( $data ) {
			return $this->key_value( $data, $this->current_language() )
				?: $this->key_value( $data, $this->default_language() )
				?: $this->key_value( $data, 'fi' );
		}
	}

	protected function key_value( $data, $key )
	{
		if ( is_object( $data ) ) {
			return $data->$key ?? null;
		}
		if ( is_array( $data ) ) {
			return $data[$key] ?? null;
		}
	}
}
