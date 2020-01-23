<?php
/**
 * Batch Processing Gutenberg
 *
 * @package Responsive Addons
 * @since 2.2.1
 */

if ( ! class_exists( 'Responsive_Ready_Sites_Batch_Processing_Gutenberg' ) ) :

	/**
	 * Responsive Ready Sites Batch Processing Gutenberg
	 *
	 * @since 2.2.1
	 */
	class Responsive_Ready_Sites_Batch_Processing_Gutenberg {

		/**
		 * Instance
		 *
		 * @since 2.2.1
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 2.2.1
		 * @return object initialized object of class.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 2.2.1
		 */
		public function __construct() {}

		/**
		 * Allowed tags.
		 *
		 * @param  array        $allowedposttags   Array of default allowable HTML tags.
		 * @param  string|array $context    The context for which to retrieve tags.
		 * @return array Array of allowed HTML tags.
		 */
		public function allowed_tags_and_attributes( $allowedposttags, $context ) {

			// Keep only for 'post' context.
			if ( 'post' === $context ) {

				// <svg> tag and attributes.
				$allowedposttags['svg'] = array(
					'xmlns'   => true,
					'viewbox' => true,
				);

				// <path> tag and attributes.
				$allowedposttags['path'] = array(
					'd' => true,
				);
			}

			return $allowedposttags;
		}

		/**
		 * Import
		 *
		 * @since 2.2.1
		 * @return void
		 */
		public function import() {

			add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_tags_and_attributes' ), 10, 2 );

			Responsive_Ready_Sites_Importer_Log::add( '---- Processing WordPress Posts / Pages - for "Gutenberg" ----' );

			$post_types = array( 'page' );

			$post_ids = Responsive_Ready_Sites_Batch_Processing::get_pages( $post_types );
			if ( empty( $post_ids ) && ! is_array( $post_ids ) ) {
				return;
			}

			foreach ( $post_ids as $post_id ) {
				$this->import_single_post( $post_id );
			}
		}

		/**
		 * Update post meta.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		public function import_single_post( $post_id = 0 ) {

			$ids_mapping = get_option( 'responsive_sites_wpforms_ids_mapping', array() );

			// Post content.
			$content = get_post_field( 'post_content', $post_id );

			if ( ! empty( $ids_mapping ) ) {
				// Replace ID's.
				foreach ( $ids_mapping as $old_id => $new_id ) {
					$content = str_replace( '[wpforms id="' . $old_id, '[wpforms id="' . $new_id, $content );
				}
			}

			// Update content.
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $content,
				)
			);
		}

	}

	/**
	 * Initiating by calling 'get_instance()' method
	 */
	Responsive_Ready_Sites_Batch_Processing_Gutenberg::get_instance();

endif;
