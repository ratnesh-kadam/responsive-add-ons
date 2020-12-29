<?php
/**
 * Plugin Name: Responsive Ready Sites Importer
 * Plugin URI: http://wordpress.org/plugins/responsive-add-ons/
 * Description: Import Responsive Ready Sites that help you launch your website quickly. Just import, update & hit the launch button.
 * Version: 2.4.3
 * Author: CyberChimps
 * License: GPL2
 *
 * @package         Responsive_Add_Ons
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

// Set Constants.
if ( ! defined( 'RESPONSIVE_ADDONS_FILE' ) ) {
	define( 'RESPONSIVE_ADDONS_FILE', __FILE__ );
}

if ( ! defined( 'RESPONSIVE_ADDONS_DIR' ) ) {
	define( 'RESPONSIVE_ADDONS_DIR', plugin_dir_path( RESPONSIVE_ADDONS_FILE ) );
}

if ( ! defined( 'RESPONSIVE_ADDONS_DIR_URL' ) ) {
	define( 'RESPONSIVE_ADDONS_DIR_URL', plugin_dir_url( RESPONSIVE_ADDONS_FILE ) );
}

if ( ! defined( 'RESPONSIVE_ADDONS_URI' ) ) {
	define( 'RESPONSIVE_ADDONS_URI', plugins_url( '/', RESPONSIVE_ADDONS_FILE ) );
}

if ( ! defined( 'RESPONSIVE_ADDONS_VER' ) ) {
	define( 'RESPONSIVE_ADDONS_VER', '2.4.3' );
}

if ( ! function_exists( 'ra_fs' ) ) {
	/**
	 * Helper function to access SDK.
	 */
	function ra_fs() {
		global $rao_fs;

		if ( ! isset( $rao_fs ) ) {
			// Include Analytics SDK.
			require_once dirname( __FILE__ ) . '/analytics/start.php';

			$rao_fs = ras_dynamic_init(
				array(
					'id'              => '1',
					'slug'            => 'responsive-add-ons',
					'product_name'    => 'Responsive Ready Sites Importer',
					'module_type'     => 'plugin',
					'version'         => '2.4.3',
					'plugin_basename' => 'responsive-add-ons/responsive-add-ons.php',
					'plugin_url'      => RESPONSIVE_ADDONS_DIR_URL,
				)
			);
		}

		return $rao_fs;
	}

	// Init Analytics.
	ra_fs();
	// SDK initiated.
	do_action( 'ra_fs_loaded' );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-responsive-add-ons.php';

/**
 * The code that runs during plugin activation.
 */
function activate_responsive_addons() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-responsive-add-ons-activator.php';
	Responsive_Add_Ons_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_responsive_addons() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-responsive-add-ons-deactivator.php';
	Responsive_Add_Ons_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_responsive_addons' );
register_deactivation_hook( __FILE__, 'deactivate_responsive_addons' );

/**
 * Initialize Plugin
 */
if ( class_exists( 'Responsive_Add_Ons' ) ) {

	// Initialise Class.
	$responsive = new Responsive_Add_Ons();
}

// load the latest sdk version from the active Responsive theme.
if ( ! function_exists( 'responsive_sdk_load_latest' ) ) :
	/**
	 * Always load the latest sdk version.
	 */
	function responsive_sdk_load_latest() {
		/**
		 * Don't load the library if we are on < 5.4.
		 */
		if ( version_compare( PHP_VERSION, '5.4.32', '<' ) ) {
			return;
		}
		require_once dirname( __FILE__ ) . '/admin/rollback/start.php';
	}
endif;
add_action( 'init', 'responsive_sdk_load_latest' );


if ( ! function_exists( 'responsive_addon_load_sdk' ) ) {
	/**
	 * Loads products array.
	 *
	 * @param array $products All products.
	 *
	 * @return array Products array.
	 */
	function responsive_addon_load_sdk( $products ) {
		$theme_name = wp_get_theme();
		if ( 'Responsive' === $theme_name->get( 'Name' ) ) {
			$products[] = get_template_directory() . '/style.css';
		}
		return $products;
	}
}
add_filter( 'responsive_sdk_products', 'responsive_addon_load_sdk' );
