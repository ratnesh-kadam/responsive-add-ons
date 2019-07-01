<?php
/**
 * Responsive Ready Sites Importer
 *
 * @since  1.0.0
 * @package Responsive Ready Sites
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Responsive_Ready_Sites_Importer' ) ) :

	/**
	 * Responsive Ready Sites Importer
	 */
	class Responsive_Ready_Sites_Importer {

		/**
		 * Instance
		 *
		 * @since  1.0.0
		 * @var (Object) Class object
		 */
		public static $instance = null;

		/**
		 * Set Instance
		 *
		 * @since  1.0.0
		 *
		 * @return object Class object.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since  1.0.0
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'load_importer' ) );

			$responsive_ready_sites_importers_dir = plugin_dir_path( __FILE__ );
			require_once $responsive_ready_sites_importers_dir . 'class-responsive-ready-sites-plugin-installer.php';
			require_once $responsive_ready_sites_importers_dir . 'class-responsive-ready-sites-widgets-importer.php';
			require_once $responsive_ready_sites_importers_dir . 'class-responsive-ready-sites-options-importer.php';

			// Import AJAX.
			add_action( 'wp_ajax_responsive-ready-sites-import-set-site-data', array( $this, 'import_start' ) );
			add_action( 'wp_ajax_responsive-ready-sites-install-required-plugins', array( $this, 'install_required_plugins' ) );
			add_action( 'wp_ajax_responsive-ready-sites-import-xml', array( $this, 'import_xml_data' ) );
			add_action( 'wp_ajax_responsive-ready-sites-import-wpforms', array( $this, 'import_wpforms' ) );
			add_action( 'wp_ajax_responsive-ready-sites-import-customizer-settings', array( $this, 'import_customizer_settings' ) );
			add_action( 'wp_ajax_responsive-ready-sites-import-widgets', array( $this, 'import_widgets' ) );
			add_action( 'wp_ajax_responsive-ready-sites-import-options', array( $this, 'import_options' ) );
			add_action( 'wp_ajax_responsive-ready-sites-import-end', array( $this, 'import_end' ) );

			// Reset Customizer Data.
			add_action( 'wp_ajax_responsive-ready-sites-reset-customizer-data', array( $this, 'reset_customizer_data' ) );
			add_action( 'wp_ajax_responsive-ready-sites-reset-site-options', array( $this, 'reset_site_options' ) );
			add_action( 'wp_ajax_responsive-ready-sites-reset-widgets-data', array( $this, 'reset_widgets_data' ) );

			// Reset Post & Terms.
			add_action( 'wp_ajax_responsive-ready-sites-delete-posts', array( $this, 'delete_imported_posts' ) );
			add_action( 'wp_ajax_responsive-ready-sites-delete-wp-forms', array( $this, 'delete_imported_wp_forms' ) );
			add_action( 'wp_ajax_responsive-ready-sites-delete-terms', array( $this, 'delete_imported_terms' ) );

		}

		/**
		 * Load WordPress Plugin Installer and WordPress Importer.
		 */
		public function load_importer() {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$responsive_ready_sites_importers_dir = plugin_dir_path( __FILE__ );

			require_once $responsive_ready_sites_importers_dir . 'wxr-importer/class-responsive-ready-sites-wxr-importer.php';
		}

		/**
		 * Start Site Import
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function import_start() {

            $demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : ''; //phpcs:ignore

			if ( ! empty( $demo_api_uri ) ) {

				$demo_data = self::get_responsive_single_demo( $demo_api_uri );

				update_option( 'responsive_ready_sites_import_data', $demo_data );

				if ( is_wp_error( $demo_data ) ) {
					wp_send_json_error( $demo_data->get_error_message() );
				} else {
					do_action( 'responsive_ready_sites_import_start', $demo_data, $demo_api_uri );
				}

				wp_send_json_success( $demo_data );

			} else {
				wp_send_json_error( __( 'Request site API URL is empty. Try again!', 'responsive-addons' ) );
			}

		}


		/**
		 * Import XML Data.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function import_xml_data() {

			if ( ! class_exists( 'XMLReader' ) ) {
				wp_send_json_error( __( 'If XMLReader is not available, it imports all other settings and only skips XML import. This creates an incomplete website. We should bail early and not import anything if this is not present.', 'responsive-addons' ) );
			}

            $wxr_url = ( isset( $_REQUEST['xml_path'] ) ) ? urldecode( $_REQUEST['xml_path'] ) : ''; //phpcs:ignore

			if ( isset( $wxr_url ) ) {

				// Download XML file.
				$xml_path = self::download_file( $wxr_url );

				if ( $xml_path['success'] ) {
					if ( isset( $xml_path['data']['file'] ) ) {
						$data        = Responsive_Ready_Sites_WXR_Importer::instance()->get_xml_data( $xml_path['data']['file'] );
						$data['xml'] = $xml_path['data'];
						wp_send_json_success( $data );
					} else {
						wp_send_json_error( __( 'There was an error downloading the XML file.', 'responsive-addons' ) );
					}
				} else {
					wp_send_json_error( $xml_path['data'] );
				}
			} else {
				wp_send_json_error( __( 'Invalid site XML file!', 'responsive-addons' ) );
			}

		}

		/**
		 * Import WP Forms
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function import_wpforms() {

            $site_wpforms_url = ( isset( $_REQUEST['wpforms_path'] ) ) ? urldecode( $_REQUEST['wpforms_path'] ) : ''; //phpcs:ignore
			$ids_mapping      = array();

			if ( ! empty( $site_wpforms_url ) && function_exists( 'wpforms_encode' ) ) {

				// Download XML file.
				$xml_path = self::download_file( $site_wpforms_url );

				if ( $xml_path['success'] ) {
					if ( isset( $xml_path['data']['file'] ) ) {

						$ext = strtolower( pathinfo( $xml_path['data']['file'], PATHINFO_EXTENSION ) );

						if ( 'json' === $ext ) {
                            $forms = json_decode( file_get_contents( $xml_path['data']['file'] ), true ); //phpcs:ignore

							if ( ! empty( $forms ) ) {

								foreach ( $forms as $form ) {
									$title = ! empty( $form['settings']['form_title'] ) ? $form['settings']['form_title'] : '';
									$desc  = ! empty( $form['settings']['form_desc'] ) ? $form['settings']['form_desc'] : '';

									$new_id = post_exists( $title );

									if ( ! $new_id ) {
										$new_id = wp_insert_post(
											array(
												'post_title'   => $title,
												'post_status'  => 'publish',
												'post_type'    => 'wpforms',
												'post_excerpt' => $desc,
											)
										);

										// Set meta for tracking the form imported from demo site.
										update_post_meta( $new_id, '_responsive_ready_sites_imported_wp_forms', true );
									}

									if ( $new_id ) {

										// ID mapping.
										$ids_mapping[ $form['id'] ] = $new_id;

										$form['id'] = $new_id;
										wp_update_post(
											array(
												'ID' => $new_id,
												'post_content' => wpforms_encode( $form ),
											)
										);
									}
								}
							}
						}
					}
				}
			}

			update_option( 'responsive_sites_wpforms_ids_mapping', $ids_mapping );

			wp_send_json_success( $ids_mapping );
		}

		/**
		 * Import Customizer Settings
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function import_customizer_settings() {

            $customizer_data = ( isset( $_POST['site_customizer_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['site_customizer_data'] ), 1 ) : array(); //phpcs:ignore

			if ( ! empty( $customizer_data ) ) {

				// Set meta for tracking the post.
				update_option( '_responsive_sites_old_customizer_data', $customizer_data );

				if ( isset( $customizer_data['responsive_settings'] ) ) {
					update_option( 'responsive-settings', $customizer_data['responsive_settings'] );
				}

				wp_send_json_success( $customizer_data );

			} else {
				wp_send_json_error( __( 'Customizer data is empty!', 'responsive-addons' ) );
			}
		}


		/**
		 * Import Widgets.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function import_widgets() {

            $widgets_data = ( isset( $_POST['widgets_data'] ) ) ? (object) json_decode( stripcslashes( $_POST['widgets_data'] ) ) : ''; //phpcs:ignore

			$widgets_data = '';
			if ( ! empty( $widgets_data ) ) {

				$widgets_importer = Responsive_Ready_Sites_Widgets_Importer::instance();
				$status           = $widgets_importer->import_widgets_data( $widgets_data );

				// Set meta for tracking the post.
				if ( is_object( $widgets_data ) ) {
					$widgets_data = (array) $widgets_data;
					update_option( '_responsive_sites_old_widgets_data', $widgets_data );
				}

				wp_send_json_success( $widgets_data );
			} else {
				wp_send_json_error( __( 'Widget data is empty!', 'responsive-addons' ) );
			}

		}

		/**
		 * Import Options.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function import_options() {

			$options_data = ( isset( $_POST['options_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['options_data'] ), 1 ) : '';

			if ( ! empty( $options_data ) ) {

				// Set meta for tracking the post.
				if ( is_array( $options_data ) ) {
					update_option( '_responsive_ready_sites_old_site_options', $options_data );
				}

				$options_importer = Responsive_Ready_Sites_Options_Importer::instance();
				$options_importer->import_options( $options_data );
				wp_send_json_success( $options_data );
			} else {
				wp_send_json_error( __( 'Site options are empty!', 'responsive-addons' ) );
			}

		}

		/**
		 * Install required plugins
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function install_required_plugins() {
            $required_plugins = ( isset( $_POST['required_plugins'] ) ) ? (array) json_decode( stripcslashes( $_POST['required_plugins'] ), 1 ) : array(); //phpcs:ignore
			if ( ! empty( $required_plugins ) ) {

				Responsive_Ready_Sites_Plugin_Installer::instance()->install_plugins( $required_plugins );

				wp_send_json_success( $required_plugins );
			} else {
				wp_send_json_error( $required_plugins );
			}
		}

		/**
		 * Download File Into Uploads Directory
		 *
		 * @param  string $file Download File URL.
		 * @return array        Downloaded file data.
		 */
		public static function download_file( $file = '' ) {

			// Gives us access to the download_url() and wp_handle_sideload() functions.
			require_once ABSPATH . 'wp-admin/includes/file.php';

			$timeout_seconds = 5;

			// Download file to temp dir.
			$temp_file = download_url( $file, $timeout_seconds );

			// WP Error.
			if ( is_wp_error( $temp_file ) ) {
				return array(
					'success' => false,
					'data'    => $temp_file->get_error_message(),
				);
			}

			// Array based on $_FILE as seen in PHP file uploads.
			$file_args = array(
				'name'     => basename( $file ),
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);

			$overrides = array(

				// Tells WordPress to not look for the POST form
				// fields that would normally be present as
				// we downloaded the file from a remote server, so there
				// will be no form fields
				// Default is true.
				'test_form'   => false,

				// Setting this to false lets WordPress allow empty files, not recommended.
				// Default is true.
				'test_size'   => true,

				// A properly uploaded file will pass this test. There should be no reason to override this one.
				'test_upload' => true,

				'mimes'       => array(
					'xml'  => 'text/xml',
					'json' => 'text/plain',
				),
			);

			// Move the temporary file into the uploads directory.
			$results = wp_handle_sideload( $file_args, $overrides );

			if ( isset( $results['error'] ) ) {
				return array(
					'success' => false,
					'data'    => $results,
				);
			}

			// Success.
			return array(
				'success' => true,
				'data'    => $results,
			);
		}

		/**
		 * Import End.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function import_end() {

            $current_active_site = isset( $_REQUEST['slug'] ) ? $_REQUEST['slug'] : '';
            update_option('responsive_current_active_site', $current_active_site );
		}


		/**
		 * Get single demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $demo_api_uri API URL of a demo.
		 *
		 * @return (Array) $responsive_demo_data demo data for the demo.
		 */
		public static function get_responsive_single_demo( $demo_api_uri ) {

			// default values.
			$remote_args = array();
			$defaults    = array(
				'id'                   => '',
				'xml_path'             => '',
				'wpforms_path'         => '',
				'site_customizer_data' => '',
				'required_plugins'     => '',
				'site_widgets_data'    => '',
                'slug'                 => '',
			);

			$api_args = apply_filters(
				'responsive_sites_api_args',
				array(
					'timeout' => 15,
				)
			);

			// Use this for premium demos.
			$request_params = apply_filters(
				'responsive_sites_api_params',
				array(
					'site_url' => '',
				)
			);

			$demo_api_uri = add_query_arg( $request_params, $demo_api_uri );

			// API Call.
			$response = wp_remote_get( $demo_api_uri, $api_args );

			if ( is_wp_error( $response ) || ( isset( $response->status ) && 0 === $response->status ) ) {
				if ( isset( $response->status ) ) {
					$data = json_decode( $response, true );
				} else {
					return new WP_Error( 'api_invalid_response_code', $response->get_error_message() );
				}
			} else {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
			}

			$data = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! isset( $data['code'] ) ) {
				$remote_args['id']                   = $data['id'];
				$remote_args['xml_path']             = $data['xml_path'];
				$remote_args['wpforms_path']         = $data['wpforms_path'];
				$remote_args['site_customizer_data'] = $data['site_customizer_data'];
				$remote_args['required_plugins']     = $data['required_plugins'];
				$remote_args['site_widgets_data']    = $data['site_widget_data'];
				$remote_args['slug']                 = $data['slug'];
			}

			// Merge remote demo and defaults.
			return wp_parse_args( $remote_args, $defaults );
		}

		/**
		 * Reset customizer data
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function reset_customizer_data() {

			delete_option( 'responsive-settings' );

			wp_send_json_success();
		}

		/**
		 * Reset site options
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function reset_site_options() {

			$options = get_option( '_responsive_ready_sites_old_site_options', array() );

			if ( $options ) {
				foreach ( $options as $option_key => $option_value ) {
					delete_option( $option_key );
				}
			}

			wp_send_json_success();
		}

		/**
		 * Reset widgets data
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function reset_widgets_data() {
			$old_widgets = get_option( '_responsive_ready_sites_old_widgets_data', array() );

			if ( $old_widgets ) {
				$sidebars_widgets = get_option( 'sidebars_widgets', array() );

				foreach ( $old_widgets as $sidebar_id => $widgets ) {

					if ( $widgets ) {
						foreach ( $widgets as $widget_key => $widget_data ) {

							if ( isset( $sidebars_widgets['wp_inactive_widgets'] ) ) {
								if ( ! in_array( $widget_key, $sidebars_widgets['wp_inactive_widgets'], true ) ) {
									$sidebars_widgets['wp_inactive_widgets'][] = $widget_key;
								}
							}
						}
					}
				}

				update_option( 'sidebars_widgets', $sidebars_widgets );
			}
			wp_send_json_success();
		}


		/**
		 * Delete imported posts
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function delete_imported_posts() {
			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : '';
			$message = 'Deleted - Post ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );

			wp_delete_post( $post_id, true );

			/* translators: %s is the post ID */
			wp_send_json_success( $message );
		}

		/**
		 * Delete imported WP forms
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function delete_imported_wp_forms() {
			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : '';

			$message = 'Deleted - Form ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );

			wp_delete_post( $post_id, true );

			/* translators: %s is the form ID */
			wp_send_json_success( $message );
		}

		/**
		 * Delete imported terms
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function delete_imported_terms() {

			$term_id = isset( $_REQUEST['term_id'] ) ? absint( $_REQUEST['term_id'] ) : '';

			$message = '';

			if ( $term_id ) {
				$term = get_term( $term_id );
				if ( $term ) {
					$message = 'Deleted - Term ' . $term_id . ' - ' . $term->name . ' ' . $term->taxonomy;
					wp_delete_term( $term_id, $term->taxonomy );
				}
			}

			/* translators: %s is the term ID */
			wp_send_json_success( $message );
		}
	}

	/**
	 * Initialized by calling 'get_instance()' method
	 */
	Responsive_Ready_Sites_Importer::get_instance();

endif;
