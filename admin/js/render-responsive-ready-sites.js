(function($){

    ResponsiveSitesRender = {

        _ref			: null,

        /**
         * _api_params = {
         * 		'search'                  : '',
         * 		'per_page'                : '',
         * 		'page'                    : '',
         *   };
         *
         */
        _api_params		: {},

        init: function()
        {
            this._bind();
            this._loadFirstGrid();
        },

        /**
         * Binds events for the Responsive Sites.
         *
         * @since 1.0.0
         * @access private
         * @method _bind
         */
        _bind: function()
        {
            $( document ).on('responsive-api-post-loaded'           , ResponsiveSitesRender._reinitGrid );
        },

        _apiAddParam_site_url: function() {
            if( responsiveSitesRender.sites && responsiveSitesRender.sites.site_url ) {
                ResponsiveSitesRender._api_params['site_url'] = responsiveSitesRender.sites.site_url;
            }
        },

        /**
         * Show Sites
         *
         * @param  {Boolean} resetPagedCount Reset Paged Count.
         * @param  {String}  trigger         Filtered Trigger.
         */
        _showSites: function( resetPagedCount, trigger ) {

            if( undefined === resetPagedCount ) {
                resetPagedCount = true
            }

            if( undefined === trigger ) {
                trigger = 'responsive-api-post-loaded';
            }

            // Add Params for API request.
            ResponsiveSitesRender._api_params = {};

            ResponsiveSitesRender._apiAddParam_site_url();

            // API Request.
            var api_post = {
                id: 'responsive-sites',
                slug: 'responsive-sites?' + decodeURIComponent( $.param( ResponsiveSitesRender._api_params ) ),
                trigger: trigger,
            };

            ResponsiveSitesAPI._api_request( api_post );

        },

        /**
         * Load First Grid.
         *
         * This is triggered after all category loaded.
         *
         * @param  {object} event Event Object.
         */
        _loadFirstGrid: function() {

            ResponsiveSitesRender._showSites();

        },

        /**
         * Update Astra sites list.
         *
         * @param  {object} event Object.
         * @param  {object} data  API response data.
         */
        _reinitGrid: function( event, data ) {

            var template = wp.template('responsive-sites-list');

            $('body').addClass( 'page-builder-selected' );
            $('body').removeClass( 'loading-content' );
            $('.filter-count .count').text( data.items_count );

            jQuery('#responsive-sites').show().html(template( data ));

            $('body').removeClass('listed-all-sites');

        },

        // Returns if a value is an array
        _isArray: function(value) {
            return value && typeof value === 'object' && value.constructor === Array;
        }

    };

    /**
     * Initialize ResponsiveSitesRender
     */
    $(function(){
        ResponsiveSitesRender.init();
    });

})(jQuery);