<?php
/*
Plugin Name: Responsive Add Ons
Plugin URI: http://wordpress.org/plugins/responsive-add-ons/
Description: Added functionality for the responsive theme
Version: 1.0.7
Author: CyberChimps
Author URI: http://www.cyberchimps.com
License: GPL2
*/
/*
Copyright 2013  CyberChimps  (email : support@cyberchimps.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Set Constants
if ( ! defined( 'RESPONSIVE_ADDONS_FILE' ) ) {
    define( 'RESPONSIVE_ADDONS_FILE', __FILE__ );
}

if ( ! defined( 'RESPONSIVE_ADDONS_DIR' ) ) {
    define( 'RESPONSIVE_ADDONS_DIR', plugin_dir_url( RESPONSIVE_ADDONS_FILE ) );
}

if ( ! defined( 'RESPONSIVE_ADDONS_URI' ) ) {
    define( 'RESPONSIVE_ADDONS_URI', plugins_url( '/', RESPONSIVE_ADDONS_FILE ) );
}

if( !class_exists( 'Responsive_Addons' ) ) {

	class Responsive_Addons {

		public $options;

		public $plugin_options;

		public static $api_url;

		public function __construct() {

			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'after_setup_theme', array( &$this, 'after_setup_theme' ) );
			add_action( 'admin_menu', array( &$this, 'add_menu' ) );
			add_action( 'wp_head', array( &$this, 'responsive_head' ) );
			add_action( 'plugins_loaded', array( &$this, 'responsive_addons_translations' ) );
			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( &$this, 'plugin_settings_link' ) );

			// Responsive Ready Site Importer Menu
            //add_action('admin_menu', array( &$this, 'add_responsive_ready_sites_menu' ) );
            add_action('admin_enqueue_scripts', array( &$this, 'responsive_ready_sites_admin_enqueue_scripts' ) );

            add_action('admin_enqueue_scripts', array( &$this, 'responsive_ready_sites_admin_enqueue_styles' ) );

            add_action( 'wp_ajax_responsive-ready-sites-required-plugins', array( &$this, 'required_plugin'));
            add_action( 'wp_ajax_responsive-ready-sites-required-plugin-activate', array(&$this, 'required_plugin_activate'));
            add_action( 'wp_ajax_responsive-ready-sites-set-reset-data', array(&$this, 'set_reset_data'));
            add_action( 'wp_ajax_responsive-ready-sites-backup-settings', array(&$this, 'backup_settings'));

            //get Active Site
            add_action( 'wp_ajax_responsive-ready-sites-get-active-site', array( $this, 'get_active_site' ) );


            $this->options        = get_option( 'responsive_theme_options' );
			$this->plugin_options = get_option( 'responsive_addons_options' );

			$this->load_responsive_sites_importer();

            self::set_api_url();
		}

		/**
		 * Stuff to do when you activate
		 */
		public static function activate() {
		}

		/**
		 * Clean up after Deactivation
		 */
		public static function deactivate() {
		}

        /**
         * Setter for $api_url
         *
         * @since  1.0.0
         */
        public static function set_api_url() {
            self::$api_url = apply_filters( 'responsive_ready_sites_api_url', 'https://websitedemos.net/wp-json/wp/v2/' );
        }

		/**
		 * Hook into WP admin_init
		 * Responsive 1.x settings
		 */
		public function admin_init( $options ) {

			// Check if the theme being used is Responsive. If True then add settings to Responsive settings, else set up a settings page
			if( $this->is_responsive() ) {
				add_filter( 'responsive_option_sections_filter', array( &$this, 'responsive_option_sections' ), 10, 1 );
				add_filter( 'responsive_options_filter', array( &$this, 'responsive_options' ), 10, 1 );

				/*$stop_responsive2 = isset( $this->options['stop_responsive2'] ) ? $this->options['stop_responsive2'] : '';

				// Check if stop_responsive2 toggle is on, if on then include update class from wp-updates.com
				if( 1 == $stop_responsive2 ) {
					// Notify user of theme update on "Updates" page in Dashboard.
					require_once( plugin_dir_path( __FILE__ ) . '/responsive-theme/wp-updates-theme.php' );
					new WPUpdatesThemeUpdater_797( 'http://wp-updates.com/api/2/theme', 'responsive' );
				}*/

			} else {
				$this->init_settings();
			}
		}

		/**
		 * Hook into WP after_setup_theme
		 * Responsive 2.x settings
		 */
		public function after_setup_theme() {

			// Check if the theme being used is Responsive. If True then add settings to Responsive settings, else set up a settings page
			if( $this->is_responsive() ) {

				add_filter( 'responsive_option_options_filter', array( $this, 'responsive_theme_options_set' ) );

			}
		}

		/**
		 * Create plugin translations
		 */
		public function responsive_addons_translations() {
			// Load the text domain for translations
			load_plugin_textdomain( 'responsive-addons', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Settings
		 */
		public function init_settings() {
			register_setting(
				'responsive_addons',
				'responsive_addons_options',
				array( &$this, 'responsive_addons_sanitize' )
			);

		}

		/**
		 * Add the menu
		 */
		public function add_menu() {
			// Hides Menu options if the current theme is responsive

            add_menu_page(
					__( 'Responsive Add-Ons', 'responsive-addons' ),
					__( 'Responsive Add-Ons', 'responsive-addons' ),
					'manage_options',
					'responsive-addons',
					array( &$this, 'plugin_settings_page' ),
                    'dashicons-admin-generic'
				);

            add_submenu_page(
                'responsive-addons',
                esc_html__( 'Responsive Add-Ons', 'responsive-addons' ),
                esc_html__( 'Responsive Ready Sites', 'responsive-addons' ),
                'manage_options',
                'responsive-blocks-ready-sites',
                array( &$this, 'menu_callback' )
            );

            add_submenu_page(
                'responsive-addons',
                esc_html__( 'Guttenberg Blocks', 'responsive-addons' ),
                esc_html__( 'Guttenberg Blocks', 'responsive-addons' ),
                'manage_options',
                'responsive-guttenberg-block',
                array( &$this, 'responsive_blocks_getting_started_page' )
            );

		}

        /**
         * Renders the plugin settings page.
         */
        public function responsive_blocks_render_settings_page() {

            $pages_dir = trailingslashit( dirname( __FILE__ ) ) . 'dist/getting-started/pages/';

            include $pages_dir . 'settings-main.php';
        }

        function responsive_blocks_getting_started_page() {
            $pages_dir = trailingslashit( dirname( __FILE__ ) ) . 'templates/';

            include $pages_dir . 'getting-started.php';
        }

        /**
		 * The settings page
		 */
		public function plugin_settings_page() {
			if( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			// Render the settings template
			include( sprintf( "%s/templates/settings.php", dirname( __FILE__ ) ) );
		}

		/**
		 * Test to see if the current theme is Responsive
		 *
		 * @return bool
		 */
		public static function is_responsive() {
			$theme = wp_get_theme();

			if( $theme->Name == 'Responsive' || $theme->Template == 'responsive' || $theme->Name == 'Responsive Pro' || $theme->Template == 'responsivepro' ) {
				return true;
			} else {
				return false;
			}
		}

		public function responsive_option_sections( $sections ) {

			$new_sections = array(
				array(
					'title' => __( 'Webmaster Tools', 'responsive-addons' ),
					'id'    => 'webmaster'
				)
			);

			$new = array_merge( $sections, $new_sections );

			return $new;
		}

		/*
		 * Responsive 1.x Settings
		 */
		public function responsive_options( $options ) {

			$new_options = array(
				'webmaster' => array(
					array(
						'title'       => __( 'Google Site Verification', 'responsive-addons' ),
						'subtitle'    => '',
						'heading'     => '',
						'type'        => 'text',
						'id'          => 'google_site_verification',
						'description' => __( 'Enter your Google ID number only', 'responsive-addons' ),
						'placeholder' => ''
					),
					array(
						'title'       => __( 'Bing Site Verification', 'responsive-addons' ),
						'subtitle'    => '',
						'heading'     => '',
						'type'        => 'text',
						'id'          => 'bing_site_verification',
						'description' => __( 'Enter your Bing ID number only', 'responsive-addons' ),
						'placeholder' => ''
					),
					array(
						'title'       => __( 'Yahoo Site Verification', 'responsive-addons' ),
						'subtitle'    => '',
						'heading'     => '',
						'type'        => 'text',
						'id'          => 'yahoo_site_verification',
						'description' => __( 'Enter your Yahoo ID number only', 'responsive-addons' ),
						'placeholder' => ''
					),
					array(
						'title'       => __( 'Site Statistics Tracker', 'responsive-addons' ),
						'subtitle'    => '<span class="info-box information help-links">' . __( 'Leave blank if plugin handles your webmaster tools', 'responsive-addons' ) . '</span>' . '<a style="margin:5px;" class="resp-addon-forum button" href="http://cyberchimps.com/forum/free/responsive/">Forum</a>' . '<a style="margin:5px;" class="resp-addon-guide button" href="http://cyberchimps.com/guide/responsive-add-ons/">' . __( 'Guide', 'responsive-addons' ) . '</a>',
						'heading'     => '',
						'type'        => 'textarea',
						'id'          => 'site_statistics_tracker',
						'class'       => array( 'site-tracker' ),
						'description' => __( 'Google Analytics, StatCounter, any other or all of them.', 'responsive-addons' ),
						'placeholder' => ''
					),
				)
			);

			$new = array_merge( $options, $new_options );

			// Commented for now to hide updates option
			/* Add stop_responsive2 options only to Responsive theme.
			if( $this->is_responsive() ) {
				$new['theme_elements'][] = array(
					'title'       => __( 'Disable Responsive 2 Updates', 'responsive-addons' ),
					'subtitle'    => '',
					'heading'     => '',
					'type'        => 'checkbox',
					'id'          => 'stop_responsive2',
					'description' => __( 'check to disable', 'responsive' ),
				);
			}*/

			return $new;
		}

		/*
		 * Responsive 2.x Settings
		 */
		public function responsive_theme_options_set( $options ) {

			$new_options['webmaster'] = array(
				'title'  => __( 'Webmaster Tools', 'responsive-addons' ),
				'fields' => array(
					array(
						'title'       => __( 'Google Site Verification', 'responsive-addons' ),
						'subtitle'    => '',
						'heading'     => '',
						'type'        => 'text',
						'id'          => 'google_site_verification',
						'description' => __( 'Enter your Google ID number only', 'responsive-addons' ),
						'placeholder' => '',
						'default'     => '',
						'validate'    => 'text'
					),
					array(
						'title'       => __( 'Bing Site Verification', 'responsive-addons' ),
						'subtitle'    => '',
						'heading'     => '',
						'type'        => 'text',
						'id'          => 'bing_site_verification',
						'description' => __( 'Enter your Bing ID number only', 'responsive-addons' ),
						'placeholder' => '',
						'default'     => '',
						'validate'    => 'text'
					),
					array(
						'title'       => __( 'Yahoo Site Verification', 'responsive-addons' ),
						'subtitle'    => '',
						'heading'     => '',
						'type'        => 'text',
						'id'          => 'yahoo_site_verification',
						'description' => __( 'Enter your Yahoo ID number only', 'responsive-addons' ),
						'placeholder' => '',
						'default'     => '',
						'validate'    => 'text'
					),
					array(
						'title'       => __( 'Site Statistics Tracker', 'responsive-addons' ),
						'subtitle'    => '<span class="info-box information help-links">' . __( 'Leave blank if plugin handles your webmaster tools', 'responsive-addons' ) . '</span>' . '<a style="margin:5px;" class="resp-addon-forum button" href="http://cyberchimps.com/forum/free/responsive/">Forum</a>' . '<a style="margin:5px;" class="resp-addon-guide button" href="http://cyberchimps.com/guide/responsive-add-ons/">' . __( 'Guide', 'responsive-addons' ) . '</a>',
						'heading'     => '',
						'type'        => 'textarea',
						'id'          => 'site_statistics_tracker',
						'class'       => array( 'site-tracker' ),
						'description' => __( 'Google Analytics, StatCounter, any other or all of them.', 'responsive-addons' ),
						'placeholder' => '',
						'default'     => '',
						'validate'    => 'js'
					),

				)
			);

			$new_options = array_merge( $options, $new_options );

			return $new_options;
		}

		/**
		 * Add to wp head
		 */
		public function responsive_head() {

			// Test if using Responsive theme. If yes load from responsive options else load from plugin options
			$responsive_options = ( $this->is_responsive() ) ? $this->options : $this->plugin_options;

			if( !empty( $responsive_options['google_site_verification'] ) ) {
				echo '<meta name="google-site-verification" content="' . esc_attr( $responsive_options['google_site_verification'] ) . '" />' . "\n";
			}

			if( !empty( $responsive_options['bing_site_verification'] ) ) {
				echo '<meta name="msvalidate.01" content="' . esc_attr( $responsive_options['bing_site_verification'] ) . '" />' . "\n";
			}

			if( !empty( $responsive_options['yahoo_site_verification'] ) ) {
				echo '<meta name="y_key" content="' . esc_attr( $responsive_options['yahoo_site_verification'] ) . '" />' . "\n";
			}

			if( !empty( $responsive_options['site_statistics_tracker'] ) ) {
				echo $responsive_options['site_statistics_tracker'];
			}
		}

		public function responsive_addons_sanitize( $input ) {

			$output = array();

			foreach( $input as $key => $test ) {
				switch( $key ) {
					case 'google_site_verification':
						$output[$key] = wp_filter_post_kses( $test );
						break;
					case 'yahoo_site_verification':
						$output[$key] = wp_filter_post_kses( $test );
						break;
					case 'bing_site_verification':
						$output[$key] = wp_filter_post_kses( $test );
						break;
					case 'site_statistics_tracker':
						$output[$key] = wp_kses_stripslashes( $test );
						break;

				}

			}

			return $output;
		}

		/**
		 * Add settings link to plugin activate page
		 *
		 * @param $links
		 *
		 * @return mixed
		 */
		public function plugin_settings_link( $links ) {
			if ( $this->is_responsive() ) {
				$settings_link = '<a href="themes.php?page=theme_options">' . __( 'Settings', 'responsive-addons' ) . '</a>';
			} else {
				$settings_link = '<a href="options-general.php?page=responsive_addons">' . __( 'Settings', 'responsive-addons' ) . '</a>';
			}
			array_unshift( $links, $settings_link );

			return $links;
		}

        /**
         * Add Responsive Ready Sites Menu
         *
         * @since 1.0.8
         */
        public function add_responsive_ready_sites_menu() {
            $page_title = apply_filters( 'responsive_ready_sites_menu_page_title', __( 'Responsive Ready Sites', 'responsive-addons' ) );

            $page = add_theme_page( $page_title, $page_title, 'manage_options', 'responsive_ready_sites', array( &$this, 'menu_callback' ) );
        }

        /**
         * Menu callback
         *
         * @since 1.0.8
         */
        public function menu_callback() {
            ?>
            <div class="responsive-sites-menu-page-wrapper">
                <?php $responsive_blocks_admin_dir = plugin_dir_path( __FILE__ ) . 'admin/'; ?>
                <?php require_once $responsive_blocks_admin_dir . 'partials/responsive-ready-sites-admin-display.php'; ?>
            </div>
            <?php
        }

        /**
         * Load Responsive Ready Sites Importer
         *
         * @since 1.0.8
         */
        public function load_responsive_sites_importer() {
            $responsive_blocks_includes_dir = plugin_dir_path( __FILE__ ) . 'includes/';
            require_once $responsive_blocks_includes_dir . 'importers/class-responsive-ready-sites-importer.php';
        }

        /**
         * Include Admin JS
         *
         * @since 1.0.8
         */
        public function responsive_ready_sites_admin_enqueue_scripts( $hook ){

            if( 'responsive-add-ons_page_responsive-blocks-ready-sites' !== $hook ){
                return;
            }
            wp_enqueue_script( 'responsive-ready-sites-fetch', RESPONSIVE_ADDONS_URI . 'admin/js/fetch.umd.js', array( 'jquery' ), '1.0.7', true );

            wp_enqueue_script( 'responsive-ready-sites-api', RESPONSIVE_ADDONS_URI . 'admin/js/responsive-ready-sites-api.js', array( 'jquery', 'responsive-ready-sites-fetch' ), '1.0.7', true );

            wp_enqueue_script( 'responsive-ready-sites-admin-js', RESPONSIVE_ADDONS_URI.'/admin/js/responsive-ready-sites-admin.js', array( 'jquery', 'wp-util', 'updates' ), '1.0.7', true );

            wp_enqueue_script( 'render-responsive-ready-sites', RESPONSIVE_ADDONS_URI. 'admin/js/render-responsive-ready-sites.js', array( 'wp-util', 'responsive-ready-sites-api', 'jquery' ), '1.0.7', true );

            $data = apply_filters(
                'responsive_sites_localize_vars',
                array(
                    'debug'             => ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || isset( $_GET['debug'] ) ) ? true : false, //phpcs:ignore
                    'ajaxurl'           => esc_url( admin_url( 'admin-ajax.php' ) ),
                    'siteURL'           => site_url(),
                    '_ajax_nonce'       => wp_create_nonce( 'responsive-addons' ),
                    'XMLReaderDisabled' => ! class_exists( 'XMLReader' ) ? true : false,
                    'required_plugins'   => array(),
                )
            );

            wp_localize_script( 'responsive-ready-sites-admin-js', 'responsiveSitesAdmin', $data );

            $data = apply_filters(
                'responsive_sites_localize_vars',
                array(
                    'ApiURL'  => self::$api_url,
                )
            );

            // Use this for premium demos.
            $request_params = apply_filters(
                'responsive_sites_api_params',
                array(
                    'site_url'     => '',
                )
            );

            wp_localize_script( 'responsive-ready-sites-api', 'responsiveSitesApi', $data );
            $data = apply_filters(
                'responsive_sites_render_localize_vars',
                array(
                    'sites'                => $request_params,
                    'settings'             => array(),
                )
            );

            wp_localize_script( 'render-responsive-ready-sites', 'responsiveSitesRender', $data );
        }

        /**
         * Include Admin css
         *
         * @since 1.0.8
         */
        public function responsive_ready_sites_admin_enqueue_styles() {
            //Responsive Ready Sites admin styles.
            wp_register_style( 'responsive-ready-sites-admin', RESPONSIVE_ADDONS_URI.'admin/css/responsive-ready-sites-admin.css', false, '1.0.0' );
            wp_enqueue_style( 'responsive-ready-sites-admin' );

            // Getting Started styles.
            wp_register_style( 'responsive-blocks-getting-started',  RESPONSIVE_ADDONS_URI.'admin/css/getting-started.css', false, '1.0.0' );
            wp_enqueue_style( 'responsive-blocks-getting-started' );
        }

        /**
         * Backup existing settings.
         */
        public function backup_settings() {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $file_name    = 'responsive-ready-sites-backup-' . date( 'd-M-Y-h-i-s' ) . '.json';
            $old_settings = get_option( 'responsive-settings', array() );
            update_option( 'responsive_ready_sites_' . $file_name, $old_settings );
            wp_send_json_success();
        }

        /**
         * Get Active Site
         */
        public function get_active_site() {
            $current_active_site = get_option('responsive_current_active_site');
        }

        /**
         * Set reset data
         */
        public function set_reset_data() {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            global $wpdb;

            $post_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_responsive_ready_sites_imported_post'" );
            $form_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_responsive_ready_sites_imported_wp_forms'" );
            $term_ids = $wpdb->get_col( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key='_responsive_ready_sites_imported_term'" );

            wp_send_json_success(
                array(
                    'reset_posts'    => $post_ids,
                    'reset_wp_forms' => $form_ids,
                    'reset_terms'    => $term_ids,
                )
            );
        }

        /**
         * Required Plugin
         *
         * @since 1.0.0
         * @return void
         */
        public function required_plugin() {

            // Verify Nonce.
            check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

            $response = array(
                'active'       => array(),
                'inactive'     => array(),
                'notinstalled' => array(),
            );

            if ( ! current_user_can( 'customize' ) ) {
                wp_send_json_error( $response );
            }

            $required_plugins             = ( isset( $_POST['required_plugins'] ) ) ? $_POST['required_plugins'] : array();

            if ( count( $required_plugins ) > 0 ) {
                foreach ( $required_plugins as $key => $plugin ) {

                    // Lite - Installed but Inactive.
                    if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) && is_plugin_inactive( $plugin['init'] ) ) {

                        $response['inactive'][] = $plugin;

                        // Lite - Not Installed.
                    } elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) ) {

                        $response['notinstalled'][] = $plugin;

                        // Lite - Active.
                    } else {
                        $response['active'][] = $plugin;
                    }
                }
            }

            // Send response.
            wp_send_json_success(
                array(
                    'required_plugins'             => $response,
                )
            );
        }


        /**
         * Required Plugin Activate
         *
         * @since 1.0.0
         */
        public function required_plugin_activate() {

            if ( ! current_user_can( 'install_plugins' ) || ! isset( $_POST['init'] ) || ! $_POST['init'] ) {
                wp_send_json_error(
                    array(
                        'success' => false,
                        'message' => __( 'No plugin specified', 'responsive-addons' ),
                    )
                );
            }

            $data               = array();
            $plugin_init        = ( isset( $_POST['init'] ) ) ? esc_attr( $_POST['init'] ) : '';

            $activate = activate_plugin( $plugin_init, '', false, true );

            if ( is_wp_error( $activate ) ) {
                wp_send_json_error(
                    array(
                        'success' => false,
                        'message' => $activate->get_error_message(),
                    )
                );
            }

            wp_send_json_success(
                array(
                    'success' => true,
                    'message' => __( 'Plugin Activated', 'responsive-addons' ),
                )
            );

        }
	}
}

/**
 * Initialize Plugin
 */
if( class_exists( 'Responsive_Addons' ) ) {

	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array( 'Responsive_Addons', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Responsive_Addons', 'deactivate' ) );

	// Initialise Class
	$responsive = new Responsive_Addons();
}

/*RESPONSIVE BLOCKS CODE STARTS HERE*/

			/**
			 * Exit if accessed directly
			 */
			if ( ! defined( 'ABSPATH' ) ) {
				exit;
			}

			/**
			 * Initialize the blocks
			 */
			function responsive_blocks_loader() {

				$responsive_blocks_includes_dir = plugin_dir_path( __FILE__ ) . 'includes/';
				$responsive_blocks_src_dir      = plugin_dir_path( __FILE__ ) . 'src/';
				$responsive_blocks_dist_dir     = plugin_dir_path( __FILE__ ) . 'dist/';

				/**
				 * Load the blocks functionality
				 */
				require_once plugin_dir_path( __FILE__ ) . 'dist/init.php';

				/**
				 * Load Getting Started page
				 */
				require_once plugin_dir_path( __FILE__ ) . 'dist/getting-started/getting-started.php';

				/**
				 * Load Social Block PHP
				 */
				require_once plugin_dir_path( __FILE__ ) . 'src/blocks/block-sharing/index.php';

				/**
				 * Load Post Grid PHP
				 */
				require_once plugin_dir_path( __FILE__ ) . 'src/blocks/block-post-grid/index.php';

				/**
				 * Load the newsletter block and related dependencies.
				 */
				if ( PHP_VERSION_ID >= 50600 ) {
					if ( ! class_exists( '\DrewM\MailChimp\MailChimp' ) ) {
						require_once $responsive_blocks_includes_dir . 'libraries/drewm/mailchimp-api/MailChimp.php';
					}

					require_once $responsive_blocks_includes_dir . 'exceptions/class-api-error-exception.php';
					require_once $responsive_blocks_includes_dir . 'exceptions/class-mailchimp-api-error-exception.php';
					require_once $responsive_blocks_includes_dir . 'interfaces/newsletter-provider-interface.php';
					require_once $responsive_blocks_includes_dir . 'classes/class-mailchimp.php';
					require_once $responsive_blocks_includes_dir . 'newsletter/newsletter-functions.php';
					require_once $responsive_blocks_src_dir . 'blocks/block-newsletter/index.php';
				}

				/**
				 * Compatibility functionality.
				 */
				require_once $responsive_blocks_includes_dir . 'compat.php';
			}
			add_action( 'plugins_loaded', 'responsive_blocks_loader' );


			/**
			 * Load the plugin textdomain
			 */
			function responsive_blocks_init() {
				load_plugin_textdomain( 'responsive-blocks', false, basename( dirname( __FILE__ ) ) . '/languages' );
			}
			add_action( 'init', 'responsive_blocks_init' );


			/**
			 * Adds a redirect option during plugin activation on non-multisite installs.
			 *
			 * @param bool $network_wide Whether or not the plugin is being network activated.
			 */
			function responsive_blocks_activate( $network_wide = false ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only used to do a redirect. False positive.
				if ( ! $network_wide && ! isset( $_GET['activate-multi'] ) ) {
					add_option( 'responsive_blocks_do_activation_redirect', true );
				}
			}
			register_activation_hook( __FILE__, 'responsive_blocks_activate' );


			/**
			 * Redirect to the Responsive Blocks Getting Started page on single plugin activation.
			 */
			function responsive_blocks_redirect() {
				if ( get_option( 'responsive_blocks_do_activation_redirect', false ) ) {
					delete_option( 'responsive_blocks_do_activation_redirect' );
					wp_safe_redirect( esc_url( admin_url( 'options-general.php?page=responsive_addons' ) ) );
					exit;
				}
			}
			add_action( 'admin_init', 'responsive_blocks_redirect' );


			/**
			 * Add image sizes
			 */
			function responsive_blocks_image_sizes() {
				// Post Grid Block.
				add_image_size( 'ra-block-post-grid-landscape', 600, 400, true );
				add_image_size( 'ra-block-post-grid-square', 600, 600, true );
			}
			add_action( 'after_setup_theme', 'responsive_blocks_image_sizes' );

			/**
			 * Returns the full path and filename of the main Responsive Blocks plugin file.
			 *
			 * @return string
			 */
			function responsive_blocks_main_plugin_file() {
				return __FILE__;
			}




		/*****RESPONSIVE BLOCKS CODE ENDS HERE******/




