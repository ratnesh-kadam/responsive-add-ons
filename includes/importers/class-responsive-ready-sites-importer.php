<?php
/**
 * Responsive Ready Sites Importer
 *
 * @since   1.0.0
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
		 * @since 1.0.0
		 * @var   (Object) Class object
		 */
		public static $instance = null;

		/**
		 * Set Instance
		 *
		 * @since 1.0.0
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
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'load_importer' ) );

			$responsive_ready_sites_importers_dir = plugin_dir_path( __FILE__ );
			require_once $responsive_ready_sites_importers_dir . 'class-responsive-ready-sites-importer-log.php';
			include_once $responsive_ready_sites_importers_dir . 'class-responsive-ready-sites-widgets-importer.php';
			include_once $responsive_ready_sites_importers_dir . 'class-responsive-ready-sites-options-importer.php';

			if ( is_admin() ) {
				// Import AJAX.
				add_action( 'wp_ajax_responsive-ready-sites-import-set-site-data-free', array( $this, 'import_start' ) );
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

				// Import single page.
				add_action( 'wp_ajax_responsive-sites-create-page', array( $this, 'import_single_page' ) );

				if ( ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) ) {
					remove_filter( 'wp_import_post_meta', array( 'Elementor\Compatibility', 'on_wp_import_post_meta' ) );
					remove_filter( 'wxr_importer.pre_process.post_meta', array( 'Elementor\Compatibility', 'on_wxr_importer_pre_process_post_meta' ) );

					add_filter( 'wp_import_post_meta', array( $this, 'on_wp_import_post_meta' ) );
					add_filter( 'wxr_importer.pre_process.post_meta', array( $this, 'on_wxr_importer_pre_process_post_meta' ) );
				}
			}

			add_action( 'responsive_ready_sites_import_complete', array( $this, 'clear_cache' ) );

			include_once $responsive_ready_sites_importers_dir . 'batch-processing/class-responsive-ready-sites-batch-processing.php';

			if ( version_compare( get_bloginfo( 'version' ), '5.0.0', '>=' ) ) {
				add_filter( 'http_request_timeout', array( $this, 'set_timeout_for_images' ), 10, 2 );
			}
		}

		/**
		 * Clear Cache.
		 *
		 * @since  2.0.3
		 */
		public function clear_cache() {
			// Clear 'Elementor' file cache.
			if ( class_exists( '\Elementor\Plugin' ) ) {
				Elementor\Plugin::$instance->posts_css_manager->clear_cache();
			}
			Responsive_Ready_Sites_Importer_Log::add( 'Complete ' );
		}

		/**
		 * Set the timeout for the HTTP request by request URL.
		 *
		 * E.g. If URL is images (jpg|png|gif|jpeg) are from the domain `https://websitedemos.net` then we have set the timeout by 30 seconds. Default 5 seconds.
		 *
		 * @since 2.0.3
		 *
		 * @param int    $timeout_value Time in seconds until a request times out. Default 5.
		 * @param string $url           The request URL.
		 */
		public function set_timeout_for_images( $timeout_value, $url ) {

			// URL not contain `https://ccreadysites.cyberchimps.com` then return $timeout_value.
			if ( strpos( $url, 'ccreadysites.cyberchimps.com' ) === false ) {
				return $timeout_value;
			}

			// Check is image URL of type jpg|png|gif|jpeg.
			if ( self::is_image_url( $url ) ) {
				$timeout_value = 30;
			}
			return $timeout_value;
		}

		/**
		 * Is Image URL
		 *
		 * @since 2.0.3
		 *
		 * @param  string $url URL.
		 * @return boolean
		 */
		public function is_image_url( $url = '' ) {
			if ( empty( $url ) ) {
				return false;
			}

			if ( preg_match( '/^((http?:\/\/)|(https?:\/\/)|(www\.))([a-z0-9-].?)+(:[0-9]+)?\/[\w\-]+\.(jpg|png|svg|gif|jpeg)\/?$/i', $url ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Load WordPress Plugin Installer and WordPress Importer.
		 */
		public function load_importer() {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$responsive_ready_sites_importers_dir = plugin_dir_path( __FILE__ );

			include_once $responsive_ready_sites_importers_dir . 'wxr-importer/class-responsive-ready-sites-wxr-importer.php';
		}

		/**
		 * Start Site Import
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function import_start() {

			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'User does not have permission!', 'responsive-addons' ) );
			}

            $demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : ''; //phpcs:ignore

			if ( ! empty( $demo_api_uri ) ) {

				$demo_data = self::get_responsive_single_demo( $demo_api_uri );
				if ( ! $demo_data['success'] ) {
					wp_send_json( $demo_data );
				}

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
		 * @since  1.0.0
		 * @return void
		 */
		public function import_xml_data() {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			if ( ! class_exists( 'XMLReader' ) ) {
				wp_send_json_error( __( 'If XMLReader is not available, it imports all other settings and only skips XML import. This creates an incomplete website. We should bail early and not import anything if this is not present.', 'responsive-addons' ) );
			}

            $wxr_url = ( isset( $_REQUEST['xml_path'] ) ) ? urldecode( $_REQUEST['xml_path'] ) : ''; //phpcs:ignore

			if ( isset( $wxr_url ) ) {

				Responsive_Ready_Sites_Importer_Log::add( 'Importing from XML ' . $wxr_url );

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

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

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
											$title  = ! empty( $form['settings']['form_title'] ) ? $form['settings']['form_title'] : '';
											$desc   = ! empty( $form['settings']['form_desc'] ) ? $form['settings']['form_desc'] : '';
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

										Responsive_Ready_Sites_Importer_Log::add( 'Inserted WP Form ' . $new_id );
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

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

            $customizer_data = ( isset( $_POST['site_customizer_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['site_customizer_data'] ), 1 ) : array(); //phpcs:ignore

			if ( ! empty( $customizer_data ) ) {

				Responsive_Ready_Sites_Importer_Log::add( 'Imported Customizer Settings ' . wp_json_encode( $customizer_data ) );

				// Set meta for tracking the post.
				update_option( '_responsive_sites_old_customizer_data', $customizer_data );

				if ( isset( $customizer_data['responsive_settings'] ) ) {
					Responsive_Ready_Sites_Importer_Log::add( 'Old Responsive Theme Options ' . get_option( 'responsive_theme_options' ) );
					update_option( 'responsive_theme_options', $customizer_data['responsive_settings'] );
				}

				if ( isset( $customizer_data['theme_mods_responsive'] ) ) {
					$current_theme = wp_get_theme();
					if ( is_child_theme() && 'Responsive' === $current_theme->parent()->get( 'Name' ) ) {
						$current_theme = str_replace( ' ', '-', strtolower( $current_theme ) );
						Responsive_Ready_Sites_Importer_Log::add( 'Backup - Responsive Theme Mods ' . get_option( 'theme_mods_' . $current_theme, true ) );
						update_option( 'theme_mods_' . $current_theme, $customizer_data['theme_mods_responsive'] );
					} else {
						Responsive_Ready_Sites_Importer_Log::add( 'Backup - Responsive Theme Mods ' . get_option( 'theme_mods_responsive', true ) );
						update_option( 'theme_mods_responsive', $customizer_data['theme_mods_responsive'] );
					}
				}

				// Add Custom CSS.
				if ( isset( $options['custom_css'] ) ) {
					wp_update_custom_css_post( $customizer_data['custom_css'] );
				}

				wp_send_json_success( $customizer_data );

			} else {
				wp_send_json_error( __( 'Customizer data is empty!', 'responsive-addons' ) );
			}
		}


		/**
		 * Import Widgets.
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function import_widgets() {
			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

            $widgets_data = ( isset( $_POST['widgets_data'] ) ) ? (object) json_decode( stripcslashes( $_POST['widgets_data'] ) ) : ''; //phpcs:ignore

			if ( ! empty( $widgets_data ) ) {

				$widgets_importer = Responsive_Ready_Sites_Widgets_Importer::instance();
				$status           = $widgets_importer->import_widgets_data( $widgets_data );

				Responsive_Ready_Sites_Importer_Log::add( 'Imported - Widgets ' . wp_json_encode( $widgets_data ) );

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
		 * @since  1.0.0
		 * @return void
		 */
		public function import_options() {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			$options_data = ( isset( $_POST['options_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['options_data'] ), 1 ) : '';

			if ( ! empty( $options_data ) ) {

				// Set meta for tracking the post.
				if ( is_array( $options_data ) ) {
					Responsive_Ready_Sites_Importer_Log::add( 'Imported - Site Options ' . wp_json_encode( $options_data ) );
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
		 * Download File Into Uploads Directory
		 *
		 * @param  string $file Download File URL.
		 * @return array        Downloaded file data.
		 */
		public static function download_file( $file = '' ) {

			// Gives us access to the download_url() and wp_handle_sideload() functions.
			include_once ABSPATH . 'wp-admin/includes/file.php';

			$timeout_seconds = 20;

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
		 * @since  1.0.0
		 * @return void
		 */
		public function import_end() {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			$current_active_site_slug               = isset( $_REQUEST['slug'] ) ? $_REQUEST['slug'] : '';
			$current_active_site_title              = isset( $_REQUEST['title'] ) ? $_REQUEST['title'] : '';
			$current_active_site_featured_image_url = isset( $_REQUEST['featured_image_url'] ) ? $_REQUEST['featured_image_url'] : '';

			$current_active_site_data = array(
				'slug'               => $current_active_site_slug,
				'title'              => $current_active_site_title,
				'featured_image_url' => $current_active_site_featured_image_url,
			);

			update_option( 'responsive_current_active_site', $current_active_site_data );

			do_action( 'responsive_ready_sites_import_complete' );
		}


		/**
		 * Get single demo.
		 *
		 * @since 1.0.0
		 *
		 * @param (String) $demo_api_uri API URL of a demo.
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
				'site_options_data'    => '',
				'pages'                => '',
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
					'api_key'               => '',
					'site_url'              => site_url(),
					'responsive_addons_ver' => RESPONSIVE_ADDONS_VER,
				)
			);

			$demo_api_uri = add_query_arg( $request_params, $demo_api_uri );

			// API Call.
			$response = wp_remote_get( $demo_api_uri, $api_args );

			if ( is_wp_error( $response ) || ( isset( $response->status ) && 0 === $response->status ) ) {
				if ( isset( $response->status ) ) {
					$data = json_decode( $response, true );
				} else {
					return false;
				}
			} else {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( ! $data['success'] ) {
					return $data;
				}
			}

			if ( ! isset( $data['code'] ) ) {
				$remote_args['id']                   = $data['id'];
				$remote_args['xml_path']             = $data['xml_path'];
				$remote_args['wpforms_path']         = $data['wpforms_path'];
				$remote_args['site_customizer_data'] = $data['site_customizer_data'];
				$remote_args['required_plugins']     = $data['required_plugins'];
				$remote_args['pages']                = $data['pages'];
				$remote_args['site_widgets_data']    = json_decode( $data['site_widgets_data'] );
				$remote_args['site_options_data']    = $data['site_options_data'];
				$remote_args['slug']                 = $data['slug'];
				$remote_args['featured_image_url']   = $data['featured_image_url'];
				$remote_args['title']                = $data['title']['rendered'];
				$remote_args['success']              = true;
			}

			// Merge remote demo and defaults.
			return wp_parse_args( $remote_args, $defaults );
		}

		/**
		 * Reset customizer data
		 *
		 * @since  1.3.0
		 * @return void
		 */
		public function reset_customizer_data() {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			Responsive_Ready_Sites_Importer_Log::add( 'Deleted customizer Settings ' . wp_json_encode( get_option( 'responsive_theme_options', array() ) ) );

			delete_option( 'responsive_theme_options' );

			wp_send_json_success();
		}

		/**
		 * Reset site options
		 *
		 * @since  1.3.0
		 * @return void
		 */
		public function reset_site_options() {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			$options = get_option( '_responsive_ready_sites_old_site_options', array() );

			Responsive_Ready_Sites_Importer_Log::add( 'Deleted - Site Options ' . wp_json_encode( $options ) );

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
		 * @since  1.3.0
		 * @return void
		 */
		public function reset_widgets_data() {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

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
		 * @since  1.3.0
		 * @param int $post_id Post Id.
		 * @return void
		 */
		public function delete_imported_posts( $post_id = 0 ) {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}
			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : $post_id;

			$message = '';
			if ( $post_id ) {
				$message = 'Deleted - Post ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );

				Responsive_Ready_Sites_Importer_Log::add( $message );
				wp_delete_post( $post_id, true );
			}

			/* translators: %s is the post ID */
			wp_send_json_success( $message );
		}

		/**
		 * Delete imported WP forms
		 *
		 * @since  1.3.0
		 * @param int $post_id Post Id.
		 * @return void
		 */
		public function delete_imported_wp_forms( $post_id = 0 ) {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : $post_id;

			$message = '';
			if ( $post_id ) {
				$message = 'Deleted - Form ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );
				Responsive_Ready_Sites_Importer_Log::add( $message );
				wp_delete_post( $post_id, true );
			}
			/* translators: %s is the form ID */
			wp_send_json_success( $message );
		}

		/**
		 * Delete imported terms
		 *
		 * @since  1.3.0
		 * @param int $term_id Term Id.
		 * @return void
		 */
		public function delete_imported_terms( $term_id = 0 ) {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			$term_id = isset( $_REQUEST['term_id'] ) ? absint( $_REQUEST['term_id'] ) : $term_id;

			$message = '';

			if ( $term_id ) {
				$term = get_term( $term_id );
				if ( $term ) {
					$message = 'Deleted - Term ' . $term_id . ' - ' . $term->name . ' ' . $term->taxonomy;
					Responsive_Ready_Sites_Importer_Log::add( $message );
					wp_delete_term( $term_id, $term->taxonomy );
				}
			}

			/* translators: %s is the term ID */
			wp_send_json_success( $message );
		}

		/**
		 * Import Single Page.
		 *
		 * @since  2.3.0
		 */
		public function import_single_page() {

			// Verify Nonce.
			check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
			}

			$data = isset( $_POST['data'] ) ? $_POST['data'] : array();

			if ( empty( $data ) ) {
				wp_send_json_error( 'Page Data is empty.' );
			}

			$current_page_api = isset( $_POST['current_page_api'] ) ? $_POST['current_page_api'] : '';
			update_option( 'current_page_api', $current_page_api );

			$page_id = isset( $_POST['data']['id'] ) ? $_POST['data']['id'] : '';
			$title   = isset( $_POST['data']['title']['rendered'] ) ? $_POST['data']['title']['rendered'] : '';
			$excerpt = isset( $_POST['data']['excerpt']['rendered'] ) ? $_POST['data']['excerpt']['rendered'] : '';
			$content = isset( $_POST['data']['original_content'] ) ? $_POST['data']['original_content'] : ( isset( $_POST['data']['content']['rendered'] ) ? $_POST['data']['content']['rendered'] : '' );

			$post_args = array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => $title,
				'post_content' => $content,
				'post_excerpt' => $excerpt,
			);

			$new_page_id = wp_insert_post( $post_args );
			$post_meta   = isset( $_POST['data']['post-meta'] ) ? $_POST['data']['post-meta'] : array();

			if ( ! empty( $post_meta ) ) {
				$this->import_post_meta( $new_page_id, $post_meta );
			}

			do_action( 'responsive_ready_sites_process_template', $new_page_id );

			wp_send_json_success(
				array(
					'remove-page-id' => $page_id,
					'id'             => $new_page_id,
					'link'           => get_permalink( $new_page_id ),
				)
			);
		}

		/**
		 * Import Post Meta
		 *
		 * @since 2.3.0
		 *
		 * @param  integer $post_id  Post ID.
		 * @param  array   $metadata  Post meta.
		 * @return void
		 */
		public function import_post_meta( $post_id, $metadata ) {

			$metadata = (array) $metadata;

			foreach ( $metadata as $meta_key => $meta_value ) {

				if ( $meta_value ) {

					if ( '_elementor_data' === $meta_key ) {

						$raw_data = json_decode( stripslashes( $meta_value ), true );

						if ( is_array( $raw_data ) ) {
							$raw_data = wp_slash( wp_json_encode( $raw_data ) );
						} else {
							$raw_data = wp_slash( $raw_data );
						}
					} else {

						if ( is_serialized( $meta_value, true ) ) {
							$raw_data = maybe_unserialize( stripslashes( $meta_value ) );
						} elseif ( is_array( $meta_value ) ) {
							$raw_data = json_decode( stripslashes( $meta_value ), true );
						} else {
							$raw_data = $meta_value;
						}
					}

					update_post_meta( $post_id, $meta_key, $raw_data );
				}
			}
		}

		/**
		 * Process post meta before WP importer.
		 *
		 * Normalize Elementor post meta on import, We need the `wp_slash` in order
		 * to avoid the unslashing during the `add_post_meta`.
		 *
		 * Fired by `wp_import_post_meta` filter.
		 *
		 * @since 2.4.1
		 *
		 * @param array $post_meta Post meta.
		 *
		 * @return array Updated post meta.
		 */
		public function on_wp_import_post_meta( $post_meta ) {
			foreach ( $post_meta as &$meta ) {
				if ( '_elementor_data' === $meta['key'] ) {
					$meta['value'] = wp_slash( $meta['value'] );
					break;
				}
			}

			return $post_meta;
		}

		/**
		 * Process post meta before WXR importer.
		 *
		 * Normalize Elementor post meta on import with the new WP_importer, We need
		 * the `wp_slash` in order to avoid the unslashing during the `add_post_meta`.
		 *
		 * Fired by `wxr_importer.pre_process.post_meta` filter.
		 *
		 * @since 2.4.1
		 *
		 * @param array $post_meta Post meta.
		 *
		 * @return array Updated post meta.
		 */
		public function on_wxr_importer_pre_process_post_meta( $post_meta ) {
			if ( '_elementor_data' === $post_meta['key'] ) {
				$post_meta['value'] = wp_slash( $post_meta['value'] );
			}

			return $post_meta;
		}
	}

	/**
	 * Initialized by calling 'get_instance()' method
	 */
	Responsive_Ready_Sites_Importer::get_instance();

endif;
