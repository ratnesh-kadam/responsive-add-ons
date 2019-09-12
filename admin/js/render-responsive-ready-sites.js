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
			this._getActiveSite();
			this._bind();

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
			$( document ).on( 'cyberchimps-sites-api-request-error'   , ResponsiveSitesRender._addReadySiteSuggestionBlock );
			$( document ).on( 'responsive-api-post-loaded'           , ResponsiveSitesRender._reinitGrid );
			$( document ).on( 'responsive-api-post-loaded-on-scroll' , ResponsiveSitesRender._reinitGridScrolled );
			$( document ).on( 'responsive-get-active-demo-site-done' , ResponsiveSitesRender._loadFirstGrid );
			$( document ).on( 'scroll'                          , ResponsiveSitesRender._scroll );
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

			if ( resetPagedCount ) {
				ResponsiveSitesRender._resetPagedCount();
			}

			ResponsiveSitesRender._apiAddParam_per_page();
			ResponsiveSitesRender._apiAddParam_page();
			ResponsiveSitesRender._apiAddParam_site_url();

			// API Request.
			var api_post = {
				id: 'cyberchimps-sites',
				slug: 'cyberchimps-sites?' + decodeURIComponent( $.param( ResponsiveSitesRender._api_params ) ),
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

			var active       = ResponsiveSitesRender.active_site;
			data.active_site = jQuery( 'body' ).attr( 'data-responsive-active-site' );

			jQuery( '#responsive-ready-sites-admin-page' ).show();
			jQuery( '#responsive-sites' ).show().html( template( data ) );

			$( '#responsive-ready-sites-admin-page' ).find( '.spinner' ).removeClass( 'is-active' );
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
							jQuery( 'body' ).attr( 'data-responsive-active-site', response.data.active_site );
						}
						$( document ).trigger( 'responsive-get-active-demo-site-done' );
					}
				);

		},

		/**
		 * On Scroll
		 */
		_scroll: function(event) {

			if ( ! $( 'body' ).hasClass( 'listed-all-sites' ) ) {

				var scrollDistance = jQuery( window ).scrollTop();

				var responsiveSitesBottom = Math.abs( jQuery( window ).height() - jQuery( '#responsive-sites' ).offset().top - jQuery( '#responsive-ready-sites-admin-page' ).height() );
				responsiveSitesBottom     = responsiveSitesBottom - 1;
				ajaxLoading               = jQuery( 'body' ).data( 'scrolling' );

				if (scrollDistance > responsiveSitesBottom && ajaxLoading == false) {
					ResponsiveSitesRender._updatedPagedCount();

					if ( ! $( '#responsive-sites .no-themes' ).length ) {
						$( '#responsive-ready-sites-admin-page' ).find( '.spinner' ).addClass( 'is-active' );
					}

					jQuery( 'body' ).data( 'scrolling', true );

					ResponsiveSitesRender._showSites( false, 'responsive-api-post-loaded-on-scroll' );
				}
			}
		},

		/**
		 * Append sites on scroll.
		 *
		 * @param  {object} event Object.
		 * @param  {object} data  API response data.
		 */
		_reinitGridScrolled: function( event, data ) {

			var template = wp.template( 'responsive-sites-list' );

			if ( data.items.length > 0 ) {

				$( 'body' ).removeClass( 'loading-content' );

				setTimeout(
					function() {
						jQuery( '#responsive-sites' ).append( template( data ) );

					},
					800
				);
			} else {
				$( 'body' ).addClass( 'listed-all-sites' );
			}

		},

		/**
		 * Reset Page Count.
		 */
		_resetPagedCount: function() {

			jQuery( 'body' ).attr( 'data-responsive-demo-last-request', '1' );
			jQuery( 'body' ).attr( 'data-responsive-demo-paged', '1' );
			jQuery( 'body' ).attr( 'data-scrolling', false );
			jQuery( 'body' ).attr( 'data-responsive-active-site', '' );

		},
		/**
		 * Add 'page' to api request.
		 *
		 * @private
		 */
		_apiAddParam_page: function() {
			var page_val                              = parseInt( jQuery( 'body' ).attr( 'data-responsive-demo-paged' ) ) || 1;
			ResponsiveSitesRender._api_params['page'] = page_val;
		},

		/**
		 * Update Page Count.
		 */
		_updatedPagedCount: function() {
			paged = parseInt( jQuery( 'body' ).attr( 'data-responsive-demo-paged' ) );
			jQuery( 'body' ).attr( 'data-responsive-demo-paged', paged + 1 );
			window.setTimeout(
				function () {
					jQuery( 'body' ).data( 'scrolling', false );
				},
				800
			);
		},
		/**
		 * Add per page Parameter.
		 */
		_apiAddParam_per_page: function() {
			var per_page_val = 6;
			if ( responsiveSitesRender.sites && responsiveSitesRender.sites["per_page"] ) {
				per_page_val = parseInt( responsiveSitesRender.sites["per_page"] );
			}
			ResponsiveSitesRender._api_params['per_page'] = per_page_val;
		},

		/**
		 * Add ready site suggestion Block
		 */
		_addReadySiteSuggestionBlock: function() {
			$( '#responsive-ready-sites-admin-page' ).find( '.spinner' ).removeClass( 'is-active' ).addClass( 'hide-me' );

			var template = wp.template( 'responsive-sites-suggestions' );
			if ( ! $( '.responsive-sites-suggestions' ).length ) {
				$( '#responsive-sites' ).append( template );
			}
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
