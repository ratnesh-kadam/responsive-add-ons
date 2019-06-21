<?php
/**
 * Class Responsive_Ready_Sites_Plugin_Installer
 *
 * @since  1.0.0
 * @package Responsive Ready Sites Plugin Installer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Responsive Sites Plugin Installer
 *
 * @since  1.0.0
 */
class Responsive_Ready_Sites_Plugin_Installer {

	/**
	 * Instance of Responsive_Ready_Sites_Plugin_Installer
	 *
	 * @since  1.0.0
	 * @var Responsive_Ready_Sites_Plugin_Installer
	 */
	private static $instance = null;

	/**
	 * Instantiate Responsive_Ready_Sites_Plugin_Installer
	 *
	 * @since  1.0.0
	 * @return (Object) Responsive_Ready_Sites_Plugin_Installer.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Install required plugins
	 *
	 * @since 1.0.0
	 * @param array $required_plugins Array of Required Plugins.
	 * @return void
	 */
	public function install_plugins( $required_plugins ) {

		foreach ( $required_plugins as $plugin ) {
			self::install_activate_plugins( $plugin );
		}
	}

	/**
	 * Download, install and activate a plugin
	 *
	 * If the plugin directory already exists, this will only try to activate the plugin
	 *
	 * @param string $plugin The plugin array.
	 */
	public function install_activate_plugins( $plugin ) {

		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin['init'];

		/*
		 * Don't try installing plugins that already exist (wastes time downloading files that
		 * won't be used
		 */
		if ( ! file_exists( $plugin_path ) ) {
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $plugin['slug'],
					'fields' => array(
						'short_description' => false,
						'sections'          => false,
						'requires'          => false,
						'rating'            => false,
						'ratings'           => false,
						'downloaded'        => false,
						'last_updated'      => false,
						'added'             => false,
						'tags'              => false,
						'compatibility'     => false,
						'homepage'          => false,
						'donate_link'       => false,
					),
				)
			);

			// Replace with new QuietSkin for no output.
			$skin     = new Plugin_Installer_Skin( array( 'api' => $api ) );
			$upgrader = new Plugin_Upgrader( $skin );
			$install  = $upgrader->install( $api->download_link );
		}

		/*
		* The install results don't indicate what the main plugin file is, so we just try to
		* activate based on the slug. It may fail, in which case the plugin will have to be activated
		* manually from the admin screen.
		*/

		if ( file_exists( $plugin_path ) ) {
			activate_plugin( $plugin_path );
		}
	}


	/**
	 * Install Plugin Test.
	 *
	 * @param string $plugin Plugin.
	 * @return void
	 */
	public function install_plugins_test( $plugin ) {

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin['slug'],
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			)
		);
		if ( self::is_plugin_installed( $plugin['slug'] ) ) {
			self::upgrade_plugin( $plugin['slug'] );
		} else {
			self::install_plugin( $api->download_link );
		}
	}

	/**
	 * Check if plugin is Installed or Not.
	 *
	 * @since 1.0.0
	 * @param string $slug Slug.
	 * @return bool
	 */
	public function is_plugin_installed( $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		if ( ! empty( $all_plugins[ $slug ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Install required plugins
	 *
	 * @since 1.0.0
	 * @param string $plugin_zip Plugin Zip.
	 * @return string
	 */
	public function install_plugin( $plugin_zip ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();

		$upgrader  = new Plugin_Upgrader();
		$installed = $upgrader->install( $plugin_zip );

		return $installed;
	}

	/**
	 * Update Plugin
	 *
	 * @param string $plugin_slug Plugin Slug.
	 * @return bool|WP_Error
	 */
	public function upgrade_plugin( $plugin_slug ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();

		$upgrader = new Plugin_Upgrader();
		$upgraded = $upgrader->upgrade( $plugin_slug );

		return $upgraded;
	}
}
