<?php
/**
 * Site options importer class.
 *
 * @since  1.0.0
 * @package  Responsive Addon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site options importer class.
 *
 * @since  1.0.8
 */
class Responsive_Ready_Sites_Options_Importer {

	/**
	 * Instance of Responsive_Ready_Sites_Options_Importer
	 *
	 * @since  1.0.8
	 * @var (Object) Responsive_Ready_Sites_Options_Importer
	 */
	private static $instance = null;

	/**
	 * Instanciate Responsive_Ready_Sites_Options_Importer
	 *
	 * @since  1.0.8
	 * @return (Object) Responsive_Ready_Sites_Options_Importer
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Site Options
	 *
	 * @since 1.0.8
	 *
	 * @return array    List of defined array.
	 */
	private static function site_options() {
		return array(
			'custom_logo',
			'nav_menu_locations',
			'show_on_front',
			'page_on_front',
			'page_for_posts',

			// Plugin: WPForms.
			'wpforms_settings',

		);
	}

	/**
	 * Import site options.
	 *
	 * @since  1.0.8
	 *
	 * @param  (Array) $options Array of site options to be imported from the demo.
	 */
	public function import_options( $options = array() ) {

		if ( ! isset( $options ) ) {
			return;
		}

		foreach ( $options as $option_name => $option_value ) {

			// Is option exist in defined array site_options()?
			if ( null !== $option_value ) {

				// Is option exist in defined array site_options()?
				if ( in_array( $option_name, self::site_options(), true ) ) {

					switch ( $option_name ) {

						case 'page_for_posts':
						case 'page_on_front':
							$this->update_page_id_by_option_value( $option_name, $option_value );
							break;

						// nav menu locations.
						case 'nav_menu_locations':
							$this->set_nav_menu_locations( $option_value );
							break;

						// insert logo.
						case 'custom_logo':
							$this->insert_logo( $option_value );
							break;

						default:
							update_option( $option_name, $option_value );
							break;
					}
				}
			}
		}
	}

	/**
	 * Update post option
	 *
	 * @since 1.0.8
	 *
	 * @param  string $option_name  Option name.
	 * @param  mixed  $option_value Option value.
	 * @return void
	 */
	private function update_page_id_by_option_value( $option_name, $option_value ) {
		$page = get_page_by_title( $option_value );
		if ( is_object( $page ) ) {
			update_option( $option_name, $page->ID );
		}
	}

	/**
	 * In WP nav menu is stored as ( 'menu_location' => 'menu_id' );
	 * In export we send 'menu_slug' like ( 'menu_location' => 'menu_slug' );
	 * In import we set 'menu_id' from menu slug like ( 'menu_location' => 'menu_id' );
	 *
	 * @since 1.0.8
	 * @param array $nav_menu_locations Array of nav menu locations.
	 */
	private function set_nav_menu_locations( $nav_menu_locations = array() ) {

		$menu_locations = array();

		// Update menu locations.
		if ( isset( $nav_menu_locations ) ) {

			foreach ( $nav_menu_locations as $menu => $value ) {

				$term = get_term_by( 'slug', $value, 'nav_menu' );

				if ( is_object( $term ) ) {
					$menu_locations[ $menu ] = $term->term_id;
				}
			}

			set_theme_mod( 'nav_menu_locations', $menu_locations );
		}
	}

	/**
	 * Insert Logo By URL
	 *
	 * @since 1.0.8
	 * @param  string $image_url Logo URL.
	 * @return void
	 */
	private function insert_logo( $image_url = '' ) {
		$attachment_id = $this->download_image( $image_url );
		if ( $attachment_id ) {
			set_theme_mod( 'custom_logo', $attachment_id );
		}
	}

	/**
	 * Download image by URL
	 *
	 * @since 1.0.8
	 *
	 * @param  string $image_url Logo URL.
	 * @return mixed false|Attachment ID
	 */
	private function download_image( $image_url = '' ) {
		$data = (object) self::sideload_image( $image_url );

		if ( ! is_wp_error( $data ) ) {
			if ( isset( $data->attachment_id ) && ! empty( $data->attachment_id ) ) {
				return $data->attachment_id;
			}
		}

		return false;
	}

	/**
	 *  Download the image by URL
	 *
	 * @since 1.0.8
	 * @param string $file Image URL.
	 * @return int|object|stdClass|string|WP_Error
	 */
	public static function sideload_image( $file ) {
		$data = new stdClass();

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		if ( ! empty( $file ) ) {

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|svg|gif|png)\b/i', $file, $matches );
			$file_array         = array();
			$file_array['name'] = basename( $matches[0] );

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $file );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $file_array, 0 );

			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) ) {
				unlink( $file_array['tmp_name'] );
				return $id;
			}

			// Build the object to return.
			$meta                = wp_get_attachment_metadata( $id );
			$data->attachment_id = $id;
			$data->url           = wp_get_attachment_url( $id );
			$data->thumbnail_url = wp_get_attachment_thumb_url( $id );
			$data->height        = $meta['height'];
			$data->width         = $meta['width'];
		}

		return $data;
	}

}
