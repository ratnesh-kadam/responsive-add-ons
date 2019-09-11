/**
 * Render Responsive Ready Sites Grid.
 *
 * @package RESPONSIVE ADDONS
 */

(function($){

	ResponsiveSitesRender = {

		_ref			: null,

		/**
		 * _api_params = {
		 * 		'search'                  : '',
		 * 		'per_page'                : '',
		 * 		'page'                    : '',
		 *   };
		 */
		_api_params		: {},

		active_site 	: '',

		init: function()
		{
			this._bind();
			this._getActiveSite();
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
			$( document ).on( 'responsive-api-post-loaded'           , ResponsiveSitesRender._reinitGrid );
			$( document ).on( 'responsive-get-active-demo-site-done' , ResponsiveSitesRender._loadFirstGrid );
		},

		_apiAddParam_site_url: function() {
			if ( responsiveSitesRender.sites && responsiveSitesRender.sites.site_url ) {
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

			if ( undefined === resetPagedCount ) {
				resetPagedCount = true
			}

			if ( undefined === trigger ) {
				trigger = 'responsive-api-post-loaded';
			}

			// Add Params for API request.
			ResponsiveSitesRender._api_params = {};

			var per_page_val = 50;

			ResponsiveSitesRender._api_params['per_page'] = per_page_val;

			ResponsiveSitesRender._apiAddParam_site_url();

			// API Request.
			var api_post = {
				id: '',
				slug: '?' + decodeURIComponent( $.param( ResponsiveSitesRender._api_params ) ),
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
		 * Update Responsive sites list.
		 *
		 * @param  {object} event Object.
		 * @param  {object} data  API response data.
		 */
		_reinitGrid: function( event, data ) {

			var template = wp.template( 'responsive-sites-list' );

			$( 'body' ).addClass( 'page-builder-selected' );
			$( 'body' ).removeClass( 'loading-content' );
			$( '.filter-count .count' ).text( data.items_count );

			var active = ResponsiveSitesRender.active_site;

			var temp = data.items[0];
			$.each(
				data.items,
				function(i , val){
					if (val.slug == active) {
						data.items[i].active = true;
						data.items[0]        = data.items[i];
						data.items[i]        = temp;
					} else {
						data.items[i].active = false;
					}
				}
			);

			jQuery( '.spinner-wrap' ).hide();
			jQuery( '#responsive-ready-sites-admin-page' ).show();
			jQuery( '#responsive-sites' ).show().html( template( data ) );
		},

		// Returns if a value is an array.
		_isArray: function(value) {
			return value && typeof value === 'object' && value.constructor === Array;
		},

		// Get active site.
		_getActiveSite: function() {
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					async: false,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-get-active-site',
					},
				}
			)
				.done(
					function ( response ) {
						if ( response.success ) {
							ResponsiveSitesRender.active_site = response.data.active_site;
						}
						$( document ).trigger( 'responsive-get-active-demo-site-done' );
					}
				);

		},
	};

	/**
	 * Initialize ResponsiveSitesRender
	 */
	$(
		function(){
			ResponsiveSitesRender.init();
		}
	);

})( jQuery );
