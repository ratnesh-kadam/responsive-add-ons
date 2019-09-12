<?php
/*
Plugin Name: Responsive Add Ons
Plugin URI: http://wordpress.org/plugins/responsive-add-ons/
Description: Import Responsive Ready Sites that help you launch your website quickly. Just import, update & hit the launch button.
Version: 2.0.5
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
			add_action('admin_notices', array( &$this, 'add_theme_installation_notice'), 1);
            add_action( 'wp_ajax_responsive-ready-sites-activate-theme', array( $this, 'activate_theme' ) );
			add_action( 'after_setup_theme', array( &$this, 'after_setup_theme' ) );
			add_action( 'wp_head', array( &$this, 'responsive_head' ) );
			add_action( 'plugins_loaded', array( &$this, 'responsive_addons_translations' ) );
			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( &$this, 'plugin_settings_link' ) );

			// Responsive Ready Site Importer Menu
            add_action('admin_enqueue_scripts', array( &$this, 'responsive_ready_sites_admin_enqueue_scripts' ) );

            add_action('admin_enqueue_scripts', array( &$this, 'responsive_ready_sites_admin_enqueue_styles' ) );

            add_action( 'wp_ajax_responsive-ready-sites-required-plugins', array( &$this, 'required_plugin'));
            add_action( 'wp_ajax_responsive-ready-sites-required-plugin-activate', array(&$this, 'required_plugin_activate'));
            add_action( 'wp_ajax_responsive-ready-sites-set-reset-data', array(&$this, 'set_reset_data'));
            add_action( 'wp_ajax_responsive-ready-sites-backup-settings', array(&$this, 'backup_settings'));

            //Dismiss admin notice
            add_action( 'wp_ajax_responsive-notice-dismiss', array(&$this, 'dismiss_notice'));

            //get Active Site
            add_action( 'wp_ajax_responsive-ready-sites-get-active-site', array( $this, 'get_active_site' ) );

            //Check if Responsive Addons pro plugin is active
            add_action( 'wp_ajax_check-responsive-add-ons-pro-installed', array( $this, 'is_responsive_pro_is_installed') );

            //Check if Responsive Addons pro license is active
            add_action('wp_ajax_check-responsive-add-ons-pro-license-active', array( $this, 'is_responsive_pro_license_is_active'));

            //Responsive Addons Page
            add_action( 'admin_menu', array( $this, 'responsive_addons_admin_page' ), 100 );

            $this->options        = get_option( 'responsive_theme_options' );
			$this->plugin_options = get_option( 'responsive_addons_options' );

			$this->load_responsive_sites_importer();

			add_action( 'responsive_addons_importer_page', array($this, 'menu_callback'));

            self::set_api_url();
		}

        /**
         * Add Admin Notice.
         */
        function add_theme_installation_notice() {

            $theme = wp_get_theme();

            if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme || $this->is_activation_theme_notice_expired() || is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' )) {
                return;
            }

            $class = 'responsive-notice notice notice-error';

            $theme_status = 'responsive-sites-theme-' . $this->get_theme_status();

            $image_path           =  RESPONSIVE_ADDONS_URI . 'admin/images/responsive-thumbnail.jpg';
            ?>
            <div id="responsive-theme-activation" class="<?php echo $class; ?>">
                <div class="responsive-addons-message-inner">
                    <div class="responsive-addons-message-icon">
                        <div class="">
                            <img src="<?php echo $image_path; ?>" alt="Responsive Addons">
                        </div>
                    </div>
                    <div class="responsive-addons-message-content">
                        <p><?php echo esc_html( 'Responsive theme needs to be active to use the Responsive Addons plugin.' ); ?> </p>
                        <p class="responsive-addons-message-actions">
                            <a href="#" class="<?php echo $theme_status; ?> button button-primary" data-theme-slug="responsive">Install & Activate Now</a>
                        </p>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Is notice expired?
         *
         * @since 2.0.3
         *
         * @return boolean
         */
        public static function is_activation_theme_notice_expired() {

            // Check the user meta status if current notice is dismissed.
            $meta_status = get_user_meta( get_current_user_id(), 'responsive-theme-activation', true );

            if ( empty( $meta_status ) ) {
                return false;
            }

            return true;
        }

        /**
         * Dismiss Notice.
         *
         * @since 2.0.3
         * @return void
         */
        public function dismiss_notice() {
            $notice_id           = ( isset( $_POST['notice_id'] ) ) ? sanitize_key( $_POST['notice_id'] ) : '';

            // check for Valid input
            if ( ! empty( $notice_id ) ) {
                update_user_meta( get_current_user_id(), $notice_id, 'notice-dismissed' );
                wp_send_json_success();
            }

            wp_send_json_error();
        }


        /**
         * Activate theme
         *
         * @since 2.0.3
         * @return void
         */
        function activate_theme() {

            switch_theme( 'responsive' );

            wp_send_json_success(
                array(
                    'success' => true,
                    'message' => __( 'Theme Activated', 'responsive-addons' ),
                )
            );
        }

        /**
         * Get theme install, active or inactive status.
         *
         * @since 1.3.2
         *
         * @return string Theme status
         */
        function get_theme_status() {

            $theme = wp_get_theme();

            // Theme installed and activate.
            if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
                return 'installed-and-active';
            }

            // Theme installed but not activate.
            foreach ( (array) wp_get_themes() as $theme_dir => $theme ) {
                if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
                    return 'installed-but-inactive';
                }
            }

            return 'not-installed';
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
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
            global $wpdb;

            $post_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_responsive_ready_sites_imported_post'" );
            foreach($post_ids as $post_id){
                wp_delete_post( $post_id, true );
            }

            $form_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_responsive_ready_sites_imported_wp_forms'" );
            foreach($form_ids as $form_id){
                wp_delete_post( $form_id, true );
            }

            $term_ids = $wpdb->get_col( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key='_responsive_ready_sites_imported_term'" );
            foreach($term_ids as $term_id){
                if ( $term_id ) {
                    $term = get_term( $term_id );
                    if ( $term ) {
                        wp_delete_term( $term_id, $term->taxonomy );
                    }
                }
            }

            delete_option('responsive_current_active_site');
        }


        /**
         * Setter for $api_url
         *
         * @since  1.0.0
         */
        public static function set_api_url() {
            self::$api_url = apply_filters( 'responsive_ready_sites_api_url', 'https://ccreadysites.cyberchimps.com/wp-json/wp/v2/' );
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
			$settings_link = '<a href="themes.php?page=responsive-add-ons">' . __( 'Settings', 'responsive-addons' ) . '</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}

        /**
         * Add Responsive Ready Sites Menu
         *
         * @since 2.0.0
         */
        public function add_responsive_ready_sites_menu() {
            $page_title = apply_filters( 'responsive_ready_sites_menu_page_title', __( 'Responsive Ready Sites', 'responsive-addons' ) );

            $page = add_theme_page( $page_title, $page_title, 'manage_options', 'responsive_ready_sites', array( &$this, 'menu_callback' ) );
        }

        /**
         * Menu callback
         *
         * @since 2.0.0
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
         * @since 2.0.0
         */
        public function load_responsive_sites_importer() {
            $responsive_blocks_includes_dir = plugin_dir_path( __FILE__ ) . 'includes/';
            require_once $responsive_blocks_includes_dir . 'importers/class-responsive-ready-sites-importer.php';
        }

        /**
         * Include Admin JS
         *
         * @since 2.0.0
         */
        public function responsive_ready_sites_admin_enqueue_scripts( $hook ){

            wp_enqueue_script('install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/js/install-responsive-theme.js', array( 'jquery', 'updates' ), '2.0.3', true);
            wp_enqueue_style( 'install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/css/install-responsive-theme.css', null, '2.0.3', 'all' );
            $data = apply_filters(
                'responsive_sites_install_theme_localize_vars',
                array(
                    'installed'  => __( 'Installed! Activating..', 'responsive-addons' ),
                    'activating' => __( 'Activating..', 'responsive-addons' ),
                    'activated'  => __( 'Activated! Reloading..', 'responsive-addons' ),
                    'installing' => __( 'Installing..', 'responsive-addons' ),
                    'ajaxurl'    => esc_url( admin_url( 'admin-ajax.php' ) ),
                )
            );
            wp_localize_script( 'install-responsive-theme', 'ResponsiveInstallThemeVars', $data );

            if( 'appearance_page_responsive-add-ons' === $hook && empty($_GET['action'] ) ) {

                wp_enqueue_script('responsive-ready-sites-fetch', RESPONSIVE_ADDONS_URI . 'admin/js/fetch.umd.js', array('jquery'), '2.0.0', true);

                wp_enqueue_script('responsive-ready-sites-api', RESPONSIVE_ADDONS_URI . 'admin/js/responsive-ready-sites-api.js', array('jquery', 'responsive-ready-sites-fetch'), '2.0.0', true);

                wp_enqueue_script('responsive-ready-sites-admin-js', RESPONSIVE_ADDONS_URI . 'admin/js/responsive-ready-sites-admin.js', array('jquery', 'wp-util', 'updates'), '2.0.0', true);

                wp_enqueue_script('render-responsive-ready-sites', RESPONSIVE_ADDONS_URI . 'admin/js/render-responsive-ready-sites.js', array('wp-util', 'responsive-ready-sites-api', 'jquery'), '2.0.0', true);

                $data = apply_filters(
                    'responsive_sites_localize_vars',
                    array(
                        'debug' => ((defined('WP_DEBUG') && WP_DEBUG) || isset($_GET['debug'])) ? true : false, //phpcs:ignore
                        'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
                        'siteURL' => site_url(),
                        '_ajax_nonce' => wp_create_nonce('responsive-addons'),
                        'XMLReaderDisabled' => !class_exists('XMLReader') ? true : false,
                        'required_plugins' => array(),
                        'ApiURL' => self::$api_url,
                    )
                );

                wp_localize_script('responsive-ready-sites-admin-js', 'responsiveSitesAdmin', $data);

                $data = apply_filters(
                    'responsive_sites_localize_vars',
                    array(
                        'ApiURL' => self::$api_url,
                    )
                );

                // Use this for premium demos.
                $request_params = apply_filters(
                    'responsive_sites_api_params',
                    array(
                        'site_url' => '',
                        'per_page' => 6,
                    )
                );

                wp_localize_script('responsive-ready-sites-api', 'responsiveSitesApi', $data);
                $data = apply_filters(
                    'responsive_sites_render_localize_vars',
                    array(
                        'sites' => $request_params,
                        'settings' => array(),
                    )
                );

                wp_localize_script('render-responsive-ready-sites', 'responsiveSitesRender', $data);
            }
        }

        /**
         * Include Admin css
         *
         * @since 2.0.0
         */
        public function responsive_ready_sites_admin_enqueue_styles() {
            //Responsive Ready Sites admin styles.
            wp_register_style( 'responsive-ready-sites-admin', RESPONSIVE_ADDONS_URI.'admin/css/responsive-ready-sites-admin.css', false, '1.0.0' );
            wp_enqueue_style( 'responsive-ready-sites-admin' );
        }

        /**
         * Backup existing settings.
         */
        public function backup_settings() {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $file_name    = 'responsive-ready-sites-backup-' . date( 'd-M-Y-h-i-s' ) . '.json';
            $old_settings = get_option( 'responsive_theme_options', array() );
            update_option( 'responsive_ready_sites_' . $file_name, $old_settings );
            wp_send_json_success();
        }

        /**
         * Get Active Site
         */
        public function get_active_site() {
            $current_active_site = get_option('responsive_current_active_site');
            wp_send_json_success(
                    array(
                            'active_site'   => $current_active_site
                    )
            );
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

                    if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) && is_plugin_inactive( $plugin['init'] ) ) {

                        $response['inactive'][] = $plugin;

                    } elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) ) {

                        $response['notinstalled'][] = $plugin;

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

        /**
         * Check if Responsive Addons Pro is installed.
         */
        public function is_responsive_pro_is_installed() {
            $responsive_pro_slug = 'responsive-addons-pro/responsive-addons-pro.php';
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins = get_plugins();

            if ( ! empty( $all_plugins[ $responsive_pro_slug ] ) ) {
                wp_send_json_success();
            } else {
                wp_send_json_error();
            }
        }

        /**
         * Check if Responsive Addons Pro License is Active.
         */
        public function is_responsive_pro_license_is_active() {
            global $wcam_lib;
            if( is_null( $wcam_lib ) ){
                wp_send_json_error();
            }
            $license_status = $wcam_lib->license_key_status();

            if ( ! empty( $license_status[ 'data' ][ 'activated' ] ) && $license_status[ 'data' ][ 'activated' ] ) {
                wp_send_json_success();
            } else {
                wp_send_json_error();
            }
        }

        /**
         * Check if Responsive Addons Pro License is Active.
         */
        public function responsive_pro_license_is_active() {
            global $wcam_lib;
            if( is_null( $wcam_lib ) ){
                return false;
            }
            $license_status = $wcam_lib->license_key_status();

            if ( ! empty( $license_status[ 'data' ][ 'activated' ] ) && $license_status[ 'data' ][ 'activated' ] ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Adding the theme menu page
         */
        public function responsive_addons_admin_page() {

            if(  $this->is_responsive() ){
                $menu_title = 'Add Ons';
            } else {
                $menu_title = 'Responsive Add Ons';
            }

            add_theme_page(
                'Responsive Add Ons',
                $menu_title,
                'administrator',
                'responsive-add-ons',
                array( $this, 'responsive_add_ons')
            );
        }

        /**
         * Responsive Addons Admin Page
         */
        public function responsive_add_ons() {

            if( $this->is_responsive_addons_pro_is_active() && !$this->responsive_pro_license_is_active() ){
                wp_redirect( admin_url( '/options-general.php?page=wc_am_client_responsive_addons_pro_dashboard' ) );
                exit;
            }

            $responsive_addon_dir = plugin_dir_path( __FILE__ );
            $responsive_addons_go_pro_screen = ( isset( $_GET['action'] ) && 'go_pro' === $_GET['action'] ) ? true : false; //phpcs:ignore

            $responsive_addon_license_screen = ( isset( $_GET['action'] ) && 'license' === $_GET['action'] ) ? true : false; //phpcs:ignore
            $responsive_addon_pro_support_screen = ( isset( $_GET['action'] ) && 'pro_support' === $_GET['action'] ) ? true : false; //phpcs:ignore?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Responsive Add Ons' ); ?></h1>
                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=responsive-add-ons' ) ); ?>" class="nav-tab<?php if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'go_pro' != $_GET['action'] && 'license' != $_GET['action'] && 'pro_support' != $_GET['action'] ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Ready Site Importer' ); ?></a>
                    <?php
                    if ( !$this->is_responsive_addons_pro_is_active( ) ) { ?>

                        <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'go_pro' ), admin_url( 'themes.php?page=responsive-add-ons' ) ) ); ?>" class="nav-tab<?php if ( $responsive_addons_go_pro_screen ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Go Pro' ); ?></a>

                    <?php } ?>
                        <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'pro_support' ), admin_url( 'themes.php?page=responsive-add-ons' ) ) ); ?>" class="nav-tab<?php if ( $responsive_addon_pro_support_screen ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Support' ); ?></a>
                </h2>
                    <?php
                    if ( $responsive_addons_go_pro_screen ) {

                        require_once $responsive_addon_dir.'admin/templates/responsive-addons-go-pro.php';

                    } elseif ( $responsive_addon_license_screen ) {

                        do_action('responsive_addons_pro_license_page');
                    } elseif ( $responsive_addon_pro_support_screen ) {

                        require_once $responsive_addon_dir.'admin/templates/responsive-addons-support.php';
                    } else {

                        do_action('responsive_addons_importer_page');
                    }
                    ?>
            </div>

            <?php
        }

        /**
         * Check if Responsive Addons Pro is installed.
         */
        public function is_responsive_addons_pro_is_active() {
            $responsive_pro_slug = 'responsive-addons-pro/responsive-addons-pro.php';
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if ( is_plugin_active( $responsive_pro_slug ) ) {
                return true;
            } else {
                return false;
            }
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




