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
    define( 'RESPONSIVE_ADDONS_DIR', plugin_dir_path( RESPONSIVE_ADDONS_FILE ) );
}

if( !class_exists( 'Responsive_Addons' ) ) {

	class Responsive_Addons {

		public $options;

		public $plugin_options;

		

		public function __construct() {

			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'after_setup_theme', array( &$this, 'after_setup_theme' ) );
			add_action( 'admin_menu', array( &$this, 'add_menu' ) );
			add_action( 'wp_head', array( &$this, 'responsive_head' ) );
			add_action( 'plugins_loaded', array( &$this, 'responsive_addons_translations' ) );
			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( &$this, 'plugin_settings_link' ) );

			// Responsive Ready Site Importer Menu
            add_action('admin_menu', array( &$this, 'add_responsive_ready_sites_menu' ) );

			$this->options        = get_option( 'responsive_theme_options' );
			$this->plugin_options = get_option( 'responsive_addons_options' );

			$this->load_responsive_sites_importer();
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
			if( ! $this->is_responsive() ) {
				add_options_page(
					__( 'Responsive Add Ons', 'responsive-addons' ),
					__( 'Responsive Add Ons', 'responsive-addons' ),
					'manage_options',
					'responsive_addons',
					array( &$this, 'plugin_settings_page' )
				);
			}
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
                <?php require_once plugin_dir_path( __FILE__ ) . 'admin/partials/responsive-ready-sites-admin-display.php'; ?>
            </div>
            <?php
        }

        /**
         * Load Responsive Ready Sites Importer
         *
         * @since 1.0.8
         */
        private function load_responsive_sites_importer() {
            require_once RESPONSIVE_ADDONS_DIR . 'includes/importers/class-responsive-ready-sites-importer.php';
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




