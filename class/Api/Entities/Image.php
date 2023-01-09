<?php
/**
 * Image entity
 */

namespace CityOfHelsinki\WordPress\LinkedEvents\Api\Entities;

/**
 * Class Image
 */
class Image extends Entity {

    /**
     * Get photographer name
     *
     * @return mixed
     */
    public function photographer_name() {
        return $this->entity_data->photographer_name ?? null;
    }

    /**
     * Get url
     *
     * @return mixed
     */
    public function url() {
        return $this->entity_data->url ?? null;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function name() {
        return $this->entity_data->name ?? null;
    }

    /**
     * Get alt text
     *
     * @return mixed
     */
    public function alt_text() {
        return $this->entity_data->alt_text ?? null;
    }

	/**
     * Get img tag
     *
     * @return string
     */
	public function html_img() {
		if ( ! $this->url() ) {
			return '';
		}
		return sprintf(
			'<img src="%s" alt="%s" aria-hidden="true">',
			esc_url( $this->url() ),
			esc_attr( $this->alt_text() )
		);
	}
}
