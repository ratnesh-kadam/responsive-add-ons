<?php
/**
 * Batch Processing Importer
 *
 * @package Responsive Addons
 * @since 2.2.1
 */

if ( ! class_exists( 'Responsive_Ready_Sites_Batch_Processing_Importer' ) ) :

    /**
     * Responsive Ready Sites Batch Processing Gutenberg
     *
     * @since 2.2.1
     */
    class Responsive_Ready_Sites_Batch_Processing_Importer {

        /**
         * Instance
         *
         * @since 2.2.1
         * @access private
         * @var object Class object.
         */
        private static $instance;

        /**
         * Initiator
         *
         * @since 2.2.1
         * @return object initialized object of class.
         */
        public static function get_instance() {

            if ( ! isset( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor
         *
         * @since 2.2.1
         */
        public function __construct() {}

        /**
         * Import
         *
         * @since 1.0.14
         * @since 2.0.0 Added page no.
         *
         * @param  integer $page Page number.
         * @return array
         */
        public function import_sites( $page = 1 ) {

            $api_args        = array(
                'timeout' => 30,
            );
            $sites_and_pages = array();

            $query_args = apply_filters(
                'cyb_sites_import_sites_query_args',
                array(
                    'per_page' => 15,
                    'page'     => $page,
                )
            );

            $api_url = add_query_arg( $query_args, 'https://ccreadysites.cyberchimps.com/wp-json/wp/v2/cyberchimps-sites' );

            error_log('API URL'.$api_url);
            $response = wp_remote_get( $api_url, $api_args );
            if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
                $sites_and_pages = json_decode( wp_remote_retrieve_body( $response ), true );

                if ( isset( $sites_and_pages['code'] ) ) {
                    $message = isset( $sites_and_pages['message'] ) ? $sites_and_pages['message'] : '';
                } else {

                    update_site_option( 'cyb-sites-and-pages-page-' . $page, $sites_and_pages, 'no' );

                }
            } else {
                error_log( 'API Error: ' . $response->get_error_message() );
            }

            error_log( 'Complete storing data for page ' . $page );
            update_site_option( 'cyb-sites-batch-status-string', 'Complete storing data for page ' . $page, 'no' );

            return $sites_and_pages;
        }

        /**
         * Generate JSON file.
         *
         * @since 2.0.0
         *
         * @param  string $filename File name.
         * @param  array  $data     JSON file data.
         * @return void.
         */
        public function generate_file( $filename = '', $data = array() ) {
            if ( defined( 'WP_CLI' ) ) {
                $this->get_filesystem()->put_contents( ASTRA_SITES_DIR . 'inc/json/' . $filename . '.json', wp_json_encode( $data ) );
            }
        }

        /**
         * Get an instance of WP_Filesystem_Direct.
         *
         * @since 2.0.0
         * @return object A WP_Filesystem_Direct instance.
         */
        public static function get_filesystem() {
            global $wp_filesystem;

            require_once ABSPATH . '/wp-admin/includes/file.php';

            WP_Filesystem();

            return $wp_filesystem;
        }
    }

    /**
     * Initiating by calling 'get_instance()' method
     */
    Responsive_Ready_Sites_Batch_Processing_Importer::get_instance();

endif;
