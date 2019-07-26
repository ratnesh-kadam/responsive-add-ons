<?php
/**
 * Menu fix batch task.
 *
 * @package Responsive Addons
 * @since 2.0.3
 */

if ( ! class_exists( 'Responsive_Ready_Sites_Batch_Processing_Menu' ) ) :

    /**
     * Responsive_Ready_Sites_Batch_Processing_Menu
     *
     * @since 2.0.3
     */
    class Responsive_Ready_Sites_Batch_Processing_Menu {

        /**
         * Instance
         *
         * @since 2.0.3
         * @access private
         * @var object Class object.
         */
        private static $instance;

        /**
         * Initiator
         *
         * @since 2.0.3
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
         * @since 2.0.3
         */
        public function __construct() {}

        /**
         * Import
         *
         * @since 2.0.3
         * @return void
         */
        public function import() {

            self::fix_nav_menus();
        }

        /**
         * Import Module Images.
         *
         * @return object
         */
        public static function fix_nav_menus() {
            $term_id_menu = term_exists('menu1');
            if($term_id_menu){
                $theme_nav_menu_locations = get_theme_mod( 'nav_menu_locations' );
                $theme_nav_menu_locations['header-menu'] = $term_id_menu;
                set_theme_mod('nav_menu_locations', $theme_nav_menu_locations);
            }
        }

    }

    Responsive_Ready_Sites_Batch_Processing_Menu::get_instance();

endif;