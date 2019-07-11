<?php
/**
 * Batch Processing
 *
 * @package Responsive Addons
 * @since 1.0.14
 */

if ( ! class_exists( 'Responsive_Ready_Sites_Batch_Processing' ) ) :

	/**
	 * Responsive_Ready_Sites_Batch_Processing
	 *
	 * @since 2.0.0
	 */
	class Responsive_Ready_Sites_Batch_Processing {

		/**
		 * Instance
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Process All
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access public
		 */
		public static $process_all;

		/**
		 * Initiator
		 *
		 * @since 1.0.14
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.14
		 */
		public function __construct() {

			// Core Helpers - Image.
			// @todo 	This file is required for Elementor.
			// Once we implement our logic for updating elementor data then we'll delete this file.
			require_once ABSPATH . 'wp-admin/includes/image.php';

            $responsive_ready_sites_batch_processing = plugin_dir_path( __FILE__ );

			// Core Helpers - Batch Processing.
			require_once $responsive_ready_sites_batch_processing . 'helpers/class-wp-async-request.php';
			require_once $responsive_ready_sites_batch_processing . 'helpers/class-wp-background-process.php';
			require_once $responsive_ready_sites_batch_processing . 'helpers/class-wp-background-process-responsive.php';

			// Prepare Page Builders.
			require_once $responsive_ready_sites_batch_processing . 'class-responsive-ready-sites-batch-processing-elementor.php';

			self::$process_all = new WP_Background_Process_Responsive();

			// Start image importing after site import complete.
			add_action( 'responsive_ready_sites_import_complete', array( $this, 'start_process' ) );
		}

		/**
		 * Start Image Import
		 *
		 * @since 1.0.14
		 *
		 * @return void
		 */
		public function start_process() {

			// Add "elementor" in import [queue].
			// @todo Remove required `allow_url_fopen` support.
			if ( ini_get( 'allow_url_fopen' ) ) {
				if ( is_plugin_active( 'elementor/elementor.php' ) ) {
					$import = new \Elementor\TemplateLibrary\Responsive_Ready_Sites_Batch_Processing_Elementor();
					self::$process_all->push_to_queue( $import );
				}
			}

			// Dispatch Queue.
			self::$process_all->save()->dispatch();
		}

		/**
		 * Get all post id's
		 *
		 * @since 1.0.14
		 *
		 * @param  array $post_types Post types.
		 * @return array
		 */
		public static function get_pages( $post_types = array() ) {

			if ( $post_types ) {
				$args = array(
					'post_type'      => $post_types,

					// Query performance optimization.
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				);

				$query = new WP_Query( $args );

				// Have posts?
				if ( $query->have_posts() ) :

					return $query->posts;

				endif;
			}

			return null;
		}

		/**
		 * Get Supporting Post Types..
		 *
		 * @since 1.3.7
		 * @param  integer $feature Feature.
		 * @return array
		 */
		public static function get_post_types_supporting( $feature ) {
			global $_wp_post_type_features;

			$post_types = array_keys(
				wp_filter_object_list( $_wp_post_type_features, array( $feature => true ) )
			);

			return $post_types;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
    Responsive_Ready_Sites_Batch_Processing::get_instance();

endif;
