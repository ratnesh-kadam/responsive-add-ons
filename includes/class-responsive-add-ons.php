<?php
/**
 * Responsive Addons setup
 *
 * @package Responsive_Addons
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Responsive_Add_Ons Class.
 *
 * @class Responsive_Add_Ons
 */
class Responsive_Add_Ons {

	/**
	 * Options
	 *
	 * @since 1.0.0
	 * @var   array Options
	 */
	public $options;

	/**
	 * Options
	 *
	 * @since 1.0.0
	 * @var   array Plugin Options
	 */
	public $plugin_options;

	/**
	 * API Url
	 *
	 * @since 2.0.0
	 * @var   string API Url
	 */
	public static $api_url;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_notices', array( &$this, 'add_theme_installation_notice' ), 1 );
		add_action( 'wp_head', array( &$this, 'responsive_head' ) );
		add_action( 'plugins_loaded', array( &$this, 'responsive_addons_translations' ) );
		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", array( &$this, 'plugin_settings_link' ) );

		// Responsive Ready Site Importer Menu.
		add_action( 'admin_enqueue_scripts', array( &$this, 'responsive_ready_sites_admin_enqueue_scripts' ) );

		add_action( 'admin_enqueue_scripts', array( &$this, 'responsive_ready_sites_admin_enqueue_styles' ) );

		if ( is_admin() ) {
			add_action( 'wp_ajax_responsive-ready-sites-activate-theme', array( $this, 'activate_theme' ) );
			add_action( 'wp_ajax_responsive-ready-sites-required-plugins', array( &$this, 'required_plugin' ) );
			add_action( 'wp_ajax_responsive-ready-sites-required-plugin-activate', array( &$this, 'required_plugin_activate' ) );
			add_action( 'wp_ajax_responsive-ready-sites-set-reset-data', array( &$this, 'set_reset_data' ) );
			add_action( 'wp_ajax_responsive-ready-sites-backup-settings', array( &$this, 'backup_settings' ) );
			add_action( 'wp_ajax_responsive-is-theme-active', array( &$this, 'check_responsive_theme_active' ) );
			// Dismiss admin notice.
			add_action( 'wp_ajax_responsive-notice-dismiss', array( &$this, 'dismiss_notice' ) );
			// Check if Responsive Addons pro plugin is active.
			add_action( 'wp_ajax_check-responsive-add-ons-pro-installed', array( $this, 'is_responsive_pro_is_installed' ) );

			// Check if Responsive Addons pro license is active.
			add_action( 'wp_ajax_check-responsive-add-ons-pro-license-active', array( $this, 'is_responsive_pro_license_is_active' ) );
		}

		// Responsive Addons Menu.
		add_action( 'admin_menu', array( $this, 'responsive_add_ons_admin_menu' ) );

		// Remove all admin notices from specific pages.
		add_action( 'admin_init', array( $this, 'responsive_add_ons_on_admin_init' ) );

		// Redirect to Getting Started Page on Plugin Activation
		add_action( 'admin_init', array( $this, 'responsive_add_ons_maybe_redirect_to_getting_started' ) );

		$this->options        = get_option( 'responsive_theme_options' );
		$this->plugin_options = get_option( 'responsive_addons_options' );

		$this->load_responsive_sites_importer();

		add_action( 'responsive_addons_importer_page', array( $this, 'menu_callback' ) );

		// Add rating links to the Responsive Addons Admin Page.
		add_filter( 'admin_footer_text', array( $this, 'responsive_addons_admin_rate_us' ) );

		add_action( 'init', array( $this, 'app_output_buffer' ) );
		self::set_api_url();

	}

	/**
	 * Admin notice - install responsive theme
	 */
	public function add_theme_installation_notice() {

		$theme = wp_get_theme();

		if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme || $this->is_activation_theme_notice_expired() || is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ) {
			return;
		}

		$class = 'responsive-notice notice notice-error';

		$theme_status = 'responsive-sites-theme-' . $this->get_theme_status();

		$image_path = RESPONSIVE_ADDONS_URI . 'admin/images/responsive-thumbnail.jpg';
		?>
			<div id="responsive-theme-activation" class="<?php echo $class; ?>">
				<div class="responsive-addons-message-inner">
					<div class="responsive-addons-message-icon">
						<div class="">
							<img src="<?php echo $image_path; ?>" alt="Responsive Ready Sites Importer">
						</div>
					</div>
					<div class="responsive-addons-message-content">
						<p><?php echo esc_html( 'Responsive theme needs to be active to use the Responsive Ready Sites Importer plugin.' ); ?> </p>
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
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You are not allowed to activate the Theme', 'responsive-addons' ) );
		}

		$notice_id = ( isset( $_POST['notice_id'] ) ) ? sanitize_key( $_POST['notice_id'] ) : '';

		// check for Valid input.
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
	public function activate_theme() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_send_json_error( __( 'You are not allowed to activate the Theme', 'responsive-addons' ) );
		}

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
	public function get_theme_status() {

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
	 *
	 * @param array $options Options.
	 */
	public function admin_init( $options ) {
		$this->init_settings();
	}

	/**
	 * Create plugin translations
	 */
	public function responsive_addons_translations() {
		// Load the text domain for translations.
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
	 * Test to see if the current theme is Responsive
	 *
	 * @return bool
	 */
	public static function is_responsive() {
		$theme = wp_get_theme();

		if ( 'Responsive' == $theme->Name || 'responsive' == $theme->Template || 'Responsive Pro' == $theme->Name || 'responsivepro' == $theme->Template ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add to wp head
	 */
	public function responsive_head() {

		// Test if using Responsive theme. If yes load from responsive options else load from plugin options.
		$responsive_options = ( $this->is_responsive() ) ? $this->options : $this->plugin_options;

		if ( ! empty( $responsive_options['google_site_verification'] ) ) {
			echo '<meta name="google-site-verification" content="' . esc_attr( $responsive_options['google_site_verification'] ) . '" />' . "\n";
		}

		if ( ! empty( $responsive_options['bing_site_verification'] ) ) {
			echo '<meta name="msvalidate.01" content="' . esc_attr( $responsive_options['bing_site_verification'] ) . '" />' . "\n";
		}

		if ( ! empty( $responsive_options['yahoo_site_verification'] ) ) {
			echo '<meta name="y_key" content="' . esc_attr( $responsive_options['yahoo_site_verification'] ) . '" />' . "\n";
		}

		if ( ! empty( $responsive_options['site_statistics_tracker'] ) ) {
			echo $responsive_options['site_statistics_tracker'];
		}
	}

	/**
	 * Responsive Addons Sanitize
	 *
	 * @since 2.0.3
	 *
	 * @param string $input Input.
	 *
	 * @return string
	 */
	public function responsive_addons_sanitize( $input ) {

		$output = array();

		foreach ( $input as $key => $test ) {
			switch ( $key ) {
				case 'google_site_verification':
					$output[ $key ] = wp_filter_post_kses( $test );
					break;
				case 'yahoo_site_verification':
					$output[ $key ] = wp_filter_post_kses( $test );
					break;
				case 'bing_site_verification':
					$output[ $key ] = wp_filter_post_kses( $test );
					break;
				case 'site_statistics_tracker':
					$output[ $key ] = wp_kses_stripslashes( $test );
					break;

			}
		}

		return $output;
	}

	/**
	 * Add settings link to plugin activate page
	 *
	 * @param array $links Links.
	 *
	 * @return mixed
	 */
	public function plugin_settings_link( $links ) {
		$settings_link = '<a href="themes.php?page=responsive-add-ons">' . __( 'Settings', 'responsive-addons' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Menu callback
	 *
	 * @since 2.0.0
	 */
	public function menu_callback() {
		?>
			<div class="responsive-sites-menu-page-wrapper">
			<?php require_once RESPONSIVE_ADDONS_DIR . 'admin/partials/responsive-ready-sites-admin-display.php'; ?>
			</div>
			<?php
	}

	/**
	 * Load Responsive Ready Sites Importer
	 *
	 * @since 2.0.0
	 */
	public function load_responsive_sites_importer() {
		require_once RESPONSIVE_ADDONS_DIR . 'includes/importers/class-responsive-ready-sites-importer.php';
	}

	/**
	 * Include Admin JS
	 *
	 * @param string $hook Hook.
	 *
	 * @since 2.0.0
	 */
	public function responsive_ready_sites_admin_enqueue_scripts( $hook ) {

		wp_enqueue_script( 'install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/js/install-responsive-theme.js', array( 'jquery', 'updates' ), '2.0.3', true );
		wp_enqueue_style( 'install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/css/install-responsive-theme.css', null, '2.0.3', 'all' );
		$data = apply_filters(
			'responsive_sites_install_theme_localize_vars',
			array(
				'installed'   => __( 'Installed! Activating..', 'responsive-addons' ),
				'activating'  => __( 'Activating..', 'responsive-addons' ),
				'activated'   => __( 'Activated! Reloading..', 'responsive-addons' ),
				'installing'  => __( 'Installing..', 'responsive-addons' ),
				'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
				'_ajax_nonce' => wp_create_nonce( 'responsive-addons' ),
			)
		);
		wp_localize_script( 'install-responsive-theme', 'ResponsiveInstallThemeVars', $data );

		if ( 'responsive_page_responsive-add-ons' === $hook && empty( $_GET['action'] ) ) {

			wp_enqueue_script( 'responsive-ready-sites-fetch', RESPONSIVE_ADDONS_URI . 'admin/js/fetch.umd.js', array( 'jquery' ), '2.0.0', true );

			wp_enqueue_script( 'responsive-ready-sites-api', RESPONSIVE_ADDONS_URI . 'admin/js/responsive-ready-sites-api.js', array( 'jquery', 'responsive-ready-sites-fetch' ), '2.0.0', true );

			wp_enqueue_script( 'responsive-ready-sites-admin-js', RESPONSIVE_ADDONS_URI . 'admin/js/responsive-ready-sites-admin.js', array( 'jquery', 'wp-util', 'updates' ), '2.0.0', true );

			wp_enqueue_script( 'render-responsive-ready-sites', RESPONSIVE_ADDONS_URI . 'admin/js/render-responsive-ready-sites.js', array( 'wp-util', 'responsive-ready-sites-api', 'jquery' ), '2.0.0', true );

			$data = apply_filters(
				'responsive_sites_localize_vars',
				array(
					'debug' => ((defined('WP_DEBUG') && WP_DEBUG) || isset($_GET['debug'])) ? true : false, //phpcs:ignore
					'ajaxurl'                         => esc_url( admin_url( 'admin-ajax.php' ) ),
					'siteURL'                         => site_url(),
					'_ajax_nonce'                     => wp_create_nonce( 'responsive-addons' ),
					'XMLReaderDisabled'               => ! class_exists( 'XMLReader' ) ? true : false,
					'required_plugins'                => array(),
					'ApiURL'                          => self::$api_url,
					'importSingleTemplateButtonTitle' => __( 'Import "%s" Template', 'responsive-addons' ),
				)
			);

			wp_localize_script( 'responsive-ready-sites-admin-js', 'responsiveSitesAdmin', $data );

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
					'per_page' => 15,
				)
			);

			wp_localize_script( 'responsive-ready-sites-api', 'responsiveSitesApi', $data );
			$data = apply_filters(
				'responsive_sites_render_localize_vars',
				array(
					'sites'            => $request_params,
					'settings'         => array(),
					'active_site_data' => $this->get_active_site_data(),
				)
			);

			wp_localize_script( 'render-responsive-ready-sites', 'responsiveSitesRender', $data );
		}
	}

	/**
	 * Include Admin css
	 *
	 * @since 2.0.0
	 */
	public function responsive_ready_sites_admin_enqueue_styles() {
		// Responsive Ready Sites admin styles.
		wp_register_style( 'responsive-ready-sites-admin', RESPONSIVE_ADDONS_URI . 'admin/css/responsive-ready-sites-admin.css', false, '1.0.0' );
		wp_enqueue_style( 'responsive-ready-sites-admin' );
	}

	/**
	 * Backup existing settings.
	 */
	public function backup_settings() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'User does not have permission!', 'responsive-addons' ) );
		}

		$file_name    = 'responsive-ready-sites-backup-' . date( 'd-M-Y-h-i-s' ) . '.json';
		$old_settings = get_option( 'responsive_theme_options', array() );

		$upload_dir  = Responsive_Ready_Sites_Importer_Log::get_instance()->log_dir();
		$upload_path = trailingslashit( $upload_dir['path'] );
		$log_file    = $upload_path . $file_name;
		$file_system = Responsive_Ready_Sites_Importer_Log::get_instance()->get_filesystem();

		// If file Write fails.
		if ( false === $file_system->put_contents( $log_file, wp_json_encode( $old_settings ), FS_CHMOD_FILE ) ) {
			update_option( 'responsive_ready_sites_' . $file_name, $old_settings );
		}

		wp_send_json_success();
	}

	/**
	 * Get Active site data
	 */
	public function get_active_site_data() {
		$current_active_site = get_option( 'responsive_current_active_site' );
		return $current_active_site;
	}

	/**
	 * Set reset data
	 */
	public function set_reset_data() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );
		if ( ! current_user_can( 'install_plugins' ) ) {
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

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( $response );
		}

		$required_plugins = ( isset( $_POST['required_plugins'] ) ) ? $_POST['required_plugins'] : array();

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
				'required_plugins' => $response,
			)
		);
	}


	/**
	 * Required Plugin Activate
	 *
	 * @since 1.0.0
	 */
	public function required_plugin_activate() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Error: You don\'t have the required permissions to install plugins.', 'responsive-addons' ),
				)
			);
		}

		if ( ! isset( $_POST['init'] ) || ! $_POST['init'] ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Plugins data is missing.', 'responsive-addons' ),
				)
			);
		}

		$data        = array();
		$plugin_init = ( isset( $_POST['init'] ) ) ? esc_attr( $_POST['init'] ) : '';

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
		global $wcam_lib_responsive_pro;
		if ( is_null( $wcam_lib_responsive_pro ) ) {
			wp_send_json_error();
		}
		$license_status = $wcam_lib_responsive_pro->license_key_status();

		if ( ! empty( $license_status['data']['activated'] ) && $license_status['data']['activated'] ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Check if Responsive Addons Pro License is Active.
	 */
	public function responsive_pro_license_is_active() {
		global $wcam_lib_responsive_pro;
		if ( is_null( $wcam_lib_responsive_pro ) ) {
			return false;
		}
		$license_status = $wcam_lib_responsive_pro->license_key_status();

		if ( ! empty( $license_status['data']['activated'] ) && $license_status['data']['activated'] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adding the theme menu page
	 */
	public function responsive_addons_admin_page() {

		if ( $this->is_responsive() ) {
			$menu_title = 'Ready Sites';
		} else {
			$menu_title = 'Responsive Ready Sites';
		}

		add_theme_page(
			'Responsive Website Templates',
			$menu_title,
			'administrator',
			'responsive-add-ons',
			array( $this, 'responsive_add_ons' )
		);
	}

	/**
	 * Responsive Addons Admin Page
	 */
	public function responsive_add_ons_templates() {

		if ( $this->is_responsive_addons_pro_is_active() && ! $this->responsive_pro_license_is_active() ) {
			wp_redirect( admin_url( '/options-general.php?page=wc_am_client_responsive_addons_pro_dashboard' ) );
			exit();
		}
		?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Responsive Importer Options', 'responsive-addons' ); ?></h1>
				<h2 class="nav-tab-wrapper">
					<span class="nav-tab
										<?php
										if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'go_pro' != $_GET['action'] && 'license' != $_GET['action'] && 'pro_support' != $_GET['action'] ) {
											echo ' nav-tab-active';}
										?>
					"><?php esc_html_e( 'Templates', 'responsive-addons' ); ?></span>
				</h2>
					<?php
						do_action( 'responsive_addons_importer_page' );
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

	/**
	 * Add rating links to the Responsive Addons Admin Page
	 *
	 * @param string $footer_text The existing footer text.
	 *
	 * @return string
	 * @since 2.0.6
	 * @global string $typenow
	 */
	public function responsive_addons_admin_rate_us( $footer_text ) {
		$page        = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$show_footer = array( 'responsive-add-ons' );

		if ( in_array( $page, $show_footer ) ) {
			$rate_text = sprintf(
				/* translators: %s: Link to 5 star rating */
				__( 'If you like the <strong>Responsive Ready Sites Importer</strong> plugin please leave us a %s rating. It takes a minute and helps a lot. Thanks in advance!', 'responsive-addons' ),
				'<a href="https://wordpress.org/support/view/plugin-reviews/responsive-add-ons?filter=5#postform" target="_blank" class="responsive-rating-link" style="text-decoration:none;" data-rated="' . esc_attr__( 'Thanks :)', 'responsive-addons' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);

			return $rate_text;
		} else {
			return $footer_text;
		}
	}

	/**
	 * Output buffer
	 */
	public function app_output_buffer() {
		ob_start();
	}

	/**
	 * Check if Responsive theme or Child theme of Responsive is Active
	 *
	 * @since 2.1.1
	 */
	public function check_responsive_theme_active() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_send_json_error( __( 'User does not have permission!', 'responsive-addons' ) );
		}

		$current_theme = wp_get_theme();
		if ( ( 'Responsive' === $current_theme->get( 'Name' ) ) || ( is_child_theme() && 'Responsive' === $current_theme->parent()->get( 'Name' ) ) ) {
			wp_send_json_success(
				array( 'success' => true )
			);
		} else {
			wp_send_json_error(
				array( 'success' => false )
			);
		}
	}

	/**
	 * Register the menu for the plugin.
	 *
	 * @since 2.2.8
	 */
	public function responsive_add_ons_admin_menu() {
		// Create Menu for Responsive Pro.
		add_menu_page(
			__( 'Responsive', 'responsive-addons' ),
			__( 'Responsive', 'responsive-addons' ),
			'manage_options',
			'responsive_add_ons',
			array( $this, 'responsive_add_ons_getting_started' ),
			RESPONSIVE_ADDONS_URI . '/admin/images/responsive-add-ons-menu-icon.png',
			59.5
		);

		add_submenu_page(
			'responsive_add_ons',
			__( 'Getting Started', 'responsive-addons' ),
			__( 'Getting Started', 'responsive-addons' ),
			'manage_options',
			'responsive_add_ons',
			array( $this, 'responsive_add_ons_getting_started' ),
			10
		);

		add_submenu_page(
			'responsive_add_ons',
			'Responsive Ready Sites Importer',
			__( 'Ready Sites', 'responsive-addons' ),
			'manage_options',
			'responsive-add-ons',
			array( $this, 'responsive_add_ons_templates' ),
			20
		);

		add_submenu_page(
			'responsive_add_ons',
			'',
			__( 'Community Support', 'responsive-addons' ),
			'manage_options',
			'responsive_add_ons_community_support',
			array( $this, 'responsive_add_ons_community_support' ),
			30
		);

		if ( ! class_exists( 'Responsive_Addons_Pro' ) ) {
			add_submenu_page(
				'responsive_add_ons',
				'',
				__( 'Go Pro', 'responsive-addons' ),
				'manage_options',
				'responsive_add_ons_go_pro',
				array( $this, 'responsive_add_ons_go_pro' ),
				60
			);
		}
	}

	/**
	 * Display Getting Started Page.
	 *
	 * Output the content for the getting started page.
	 *
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_getting_started() {

		?>
		<div class="wrap">
			<div class="responsive-add-ons-getting-started">
				<div class="responsive-add-ons-getting-started__box postbox">
					<div class="responsive-add-ons-getting-started__header">
						<div class="responsive-add-ons-getting-started__title">
							<?php echo __( 'Getting Started', 'responsive-addons' ); ?>
						</div>
						<a class="responsive-add-ons-getting-started__skip" href="<?php echo esc_url( admin_url() ); ?>">
							<span class="responsive-add-ons-getting-started__skip_button"><span class="screen-reader-text">Skip</span></span>
						</a>
					</div>
					<div class="responsive-add-ons-getting-started__content">
						<div class="responsive-add-ons-getting-started__content--narrow">
							<h2><?php echo __( 'Click, Import, Launch!', 'responsive-addons' ); ?></h2>
							<p><?php echo __( 'Build Sites Fast with Responsive Pro. Fully Customizable, Mobile-Friendly with Premium Features.', 'responsive-addons' ); ?></p>
						</div>

						<div class="responsive-add-ons-getting-started__video">
							<iframe width="620" height="350" src="https://www.youtube-nocookie.com/embed/1eKjI0qjXPI?rel=0&amp;controls=1&amp;modestbranding=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>
					</div>
				</div>
			</div>
		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Go to Responsive Pro support.
	 *
	 * Fired by `admin_init` action.
	 *
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_community_support() {
		if ( empty( $_GET['page'] ) ) {
			return;
		}
		wp_redirect( 'https://www.facebook.com/groups/responsive.theme' );
		die;
	}

	/**
	 * Free vs Pro features list.
	 *
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_go_pro() {
		require_once RESPONSIVE_ADDONS_DIR . 'admin/templates/free-vs-pro.php';
	}

	/**
	 * On admin init.
	 *
	 * Preform actions on WordPress admin initialization.
	 *
	 * Fired by `admin_init` action.
	 *
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_on_admin_init() {

		$this->responsive_add_ons_remove_all_admin_notices();
	}

	/**
	 * @since 2.2.8
	 * @access private
	 */
	private function responsive_add_ons_remove_all_admin_notices() {
		$responsive_add_ons_pages = array(
			'responsive_add_ons',
			'responsive-add-ons',
			'responsive_addons_pro_system_info',
		);

		if ( empty( $_GET['page'] ) || ! in_array( $_GET['page'], $responsive_add_ons_pages, true ) ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
	}

	/**
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_maybe_redirect_to_getting_started() {
		if ( ! get_transient( 'responsive_add_ons_activation_redirect' ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		delete_transient( 'responsive_add_ons_activation_redirect' );

		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=responsive_add_ons' ) );

		exit;
	}
}
