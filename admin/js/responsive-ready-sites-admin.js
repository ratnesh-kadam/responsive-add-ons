/**
 * Responsive Ready Sites importer events
 *
 * @package Responsive Ready Sites
 */

/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 */
var ResponsiveSitesAjaxQueue = (function() {

	var requests = [];

	return {

		/**
		 * Add AJAX request
		 */
		add:  function(opt) {
			requests.push( opt );
		},

		/**
		 * Remove AJAX request
		 */
		remove:  function(opt) {
			if ( jQuery.inArray( opt, requests ) > -1 ) {
				requests.splice( $.inArray( opt, requests ), 1 );
			}
		},

		/**
		 * Run / Process AJAX request
		 */
		run: function() {
			var self = this,
				oriSuc;

			if ( requests.length ) {
				oriSuc = requests[0].complete;

				requests[0].complete = function() {
					if ( typeof(oriSuc) === 'function' ) {
						oriSuc();
					}
					requests.shift();
					self.run.apply( self, [] );
				};

				jQuery.ajax( requests[0] );

			} else {

				self.tid = setTimeout(
					function() {
						self.run.apply( self, [] );
					},
					1000
				);
			}
		},

		/**
		 * Stop AJAX request
		 */
		stop:  function() {

			requests = [];
			clearTimeout( this.tid );
		}
	};

}());

(function( $ ) {

	/**
	 * WXR Import
	 *
	 * - updateDelta()
	 * - updateProgress()
	 * - render()
	 */
	var wxrImport = {
		complete: {
			posts: 0,
			media: 0,
			users: 0,
			comments: 0,
			terms: 0,
		},

		updateDelta: function (type, delta) {
			this.complete[ type ] += delta;

			var self = this;
			requestAnimationFrame(
				function () {
					self.render();
				}
			);
		},
		updateProgress: function ( type, complete, total ) {
			var text = complete + '/' + total;

			if ( 'undefined' !== type && 'undefined' !== text ) {
				total = parseInt( total, 10 );
				if ( 0 === total || isNaN( total ) ) {
					total = 1;
				}
				var percent      = parseInt( complete, 10 ) / total;
				var progress     = Math.round( percent * 100 ) + '%';
				var progress_bar = percent * 100;

				if ( progress_bar <= 100 ) {
					var process_bars        = document.getElementsByClassName( 'responsive-ready-sites-import-process' );
					var process_bars_length = process_bars.length;
					for ( var i = 0; i < process_bars_length; i++ ) {
						process_bars[i].value = progress_bar;
					}
				}
			}
		},
		render: function () {
			var types    = Object.keys( this.complete );
			var complete = 0;
			var total    = 0;

			for (var i = types.length - 1; i >= 0; i--) {
				var type = types[i];
				this.updateProgress( type, this.complete[ type ], this.data.count[ type ] );

				complete += this.complete[ type ];
				total    += this.data.count[ type ];
			}

			this.updateProgress( 'total', complete, total );
		}
	};

	/**
	 * Responsive Sites Admin
	 *
	 * - init()
	 * - _show_default_page_builder_sites()
	 * - _bind()
	 * - _resetPagedCount()
	 * - _doNothing()
	 * - _toggle_tooltip()
	 * - _areEqual()
	 * - _closeFullOverlay()
	 * - _importSiteOptionsScreen()
	 * - _importDemo()
	 * - _is_responsive_theme_active()
	 * - _log_error()
	 * - _checkResponsiveAddonsProInstalled()
	 * - _preview()
	 * - _renderDemoPreview()
	 * - _process_import()
	 * - _importSite()
	 * - _installRequiredPlugins()
	 * - _removePluginFromQueue()
	 * - _bulkPluginInstallActivate()
	 * - _installAllPlugins()
	 * - _pluginInstalling()
	 * - _pluginInstallSuccess()
	 * - _activateAllPlugins()
	 * - _ready_for_import_site()
	 * - _ready_for_import_template()
	 * - _resetData()
	 * - _is_reset_data()
	 * - _backup_before_reset_options()
	 * - _backupOptions()
	 * - _reset_customizer_data()
	 * - _reset_site_options()
	 * - _reset_widgets_data()
	 * - _reset_terms()
	 * - _reset_wp_forms()
	 * - _reset_posts()
	 * - _importWPForms()
	 * - _importXML()
	 * - _importCustomizerSettings()
	 * - _importWidgets()
	 * - _importCustomizerSettings()
	 * - _importSiteOptions()
	 * - _importSiteEnd()
	 * - _importPagePreviewScreen()
	 * - _change_site_preview_screenshot()
	 * - _set_preview_screenshot_by_page()
	 * - _importSinglePageOptions()
	 * - _importSinglePage()
	 * - _get_id()
	 * - _import_wpform()
	 * - _importPage()
	 * - ucwords()
	 * - _sync_templates_library_with_ajax()
	 */
	ResponsiveSitesAdmin = {

		reset_remaining_posts: 0,
		reset_remaining_wp_forms: 0,
		reset_remaining_terms: 0,
		reset_processed_posts: 0,
		reset_processed_wp_forms: 0,
		reset_processed_terms: 0,
		site_imported_data: null,

		current_site: [],
		current_screen: '',
		active_site_slug: '',
		active_site_title: '',
		active_site_featured_image_url: '',
		widgets_data: '',
		site_options_data: '',

		filter_array: [],
		autocompleteTags: [],

		templateData: {},

		site_customizer_data: '',

		required_plugins: '',

		xml_path         : '',
		wpforms_path	: '',
		import_start_time  : '',
		import_end_time    : '',

		current_page_id : '',
		processing_single_template: false,
		pro_plugins_flag: false,

		mouseLocation : false,

		init: function()
		{
			this._show_default_page_builder_sites();
			this._resetPagedCount();
			this._bind();
			this._addAutocomplete();
			this._autocomplete();
		},

		_show_default_page_builder_sites: function() {

			if( Object.keys( responsiveSitesAdmin.default_page_builder_sites ).length ) {

				var template          = wp.template( 'responsive-sites-list' );

				var data = responsiveSitesAdmin.default_page_builder_sites;

				data = ResponsiveSitesAdmin._filter_sites_by_page_builder(data);

				ResponsiveSitesAdmin.add_sites( data );

			} else {

				var temp = [];
				for (var i = 0; i < 8; i++) {
					temp['id-' + i] = {
						'title' : 'Lorem Ipsum',
						'class' : 'placeholder-site',
						'slug' 	: 'placeholder-site'
					};
				}

				ResponsiveSitesAdmin.add_sites(temp);
				$('#respnonsive-sites').addClass('temp');

				ResponsiveSitesAdmin._sync_templates_library_with_ajax( true );
			}
		},


		/**
		 * Binds events for the Responsive Ready Sites.
		 */
		_bind: function()
		{

			$( '.responsive-sites__category-filter-anchor, .responsive-sites__category-filter-items' ).hover(function(){
				ResponsiveSitesAdmin.mouseLocation = true;
			}, function(){
				ResponsiveSitesAdmin.mouseLocation = false;
			});

			$( "body" ).mouseup(function(){
				if( ! ResponsiveSitesAdmin.mouseLocation ) ResponsiveSitesAdmin._closeFilter();
			});

			// Site Import events.
			$( document ).on( 'click'                     , '.import-demo-data, .responsive-ready-site-import-free', ResponsiveSitesAdmin._importDemo );
			$( document ).on( 'click'                     , '.theme-browser .inactive.ra-site-single .theme-screenshot, .theme-browser .inactive.ra-site-single .more-details, .theme-browser .inactive.ra-site-single .install-theme-preview', ResponsiveSitesAdmin._preview );
			$( document ).on( 'click'                     , '.theme-browser .active.ra-site-single .theme-screenshot, .theme-browser .active.ra-site-single .more-details, .theme-browser .active.ra-site-single .install-theme-preview', ResponsiveSitesAdmin._doNothing );
			$( document ).on( 'click'                     , '.close-full-overlay', ResponsiveSitesAdmin._closeFullOverlay );
			$( document ).on( 'click', '.responsive-demo-import-options-free', ResponsiveSitesAdmin._importSiteOptionsScreen );
			$( document ).on( 'click', '.responsive-ready-sites-tooltip-icon', ResponsiveSitesAdmin._toggle_tooltip );
			$( document ).on( 'responsive-get-active-theme' , ResponsiveSitesAdmin._is_responsive_theme_active );
			$( document ).on( 'responsive-ready-sites-install-start'       , ResponsiveSitesAdmin._process_import );
			$( document ).on( 'responsive-ready-sites-import-set-site-data-done'   		, ResponsiveSitesAdmin._installRequiredPlugins );
			$( document ).on( 'responsive-ready-sites-install-and-activate-required-plugins-done', ResponsiveSitesAdmin._resetData );
			$( document ).on( 'responsive-ready-sites-reset-data'							, ResponsiveSitesAdmin._backup_before_reset_options );
			$( document ).on( 'responsive-ready-sites-backup-settings-before-reset-done'	, ResponsiveSitesAdmin._reset_customizer_data );
			$( document ).on( 'responsive-ready-sites-reset-customizer-data-done'			, ResponsiveSitesAdmin._reset_site_options );
			$( document ).on( 'responsive-ready-sites-reset-site-options-done'				, ResponsiveSitesAdmin._reset_widgets_data );
			$( document ).on( 'responsive-ready-sites-reset-widgets-data-done'				, ResponsiveSitesAdmin._reset_terms );
			$( document ).on( 'responsive-ready-sites-delete-terms-done'					, ResponsiveSitesAdmin._reset_wp_forms );
			$( document ).on( 'responsive-ready-sites-delete-wp-forms-done'				, ResponsiveSitesAdmin._reset_posts );
			$( document ).on( 'responsive-ready-sites-reset-data-done' , ResponsiveSitesAdmin._importWPForms );
			$( document ).on( 'responsive-ready-sites-import-wpforms-done' , ResponsiveSitesAdmin._importXML );
			$( document ).on( 'responsive-ready-sites-import-xml-done' , ResponsiveSitesAdmin._importCustomizerSettings );
			$( document ).on( 'responsive-ready-sites-import-customizer-settings-done' , ResponsiveSitesAdmin._importWidgets );
			$( document ).on( 'responsive-ready-sites-import-widgets-done' , ResponsiveSitesAdmin._importSiteOptions );
			$( document ).on( 'responsive-ready-sites-import-options-done' , ResponsiveSitesAdmin._importSiteEnd );

			// Single Page Import events.
			$( document ).on( 'click'                     , '.single-page-import-button-free', ResponsiveSitesAdmin._importSinglePageOptions );
			$( document ).on( 'click'                     , '.responsive-ready-page-import-free', ResponsiveSitesAdmin._importSinglePage );
			$( document ).on( 'click', '.responsive-page-import-options-free', ResponsiveSitesAdmin._importPagePreviewScreen );
			$( document ).on( 'click'                     , '#single-pages .site-single', ResponsiveSitesAdmin._change_site_preview_screenshot );
			$( document ).on( 'responsive-ready-page-install-and-activate-required-plugins-done' , ResponsiveSitesAdmin._importPage );
			$( document ).on( 'responsive-ready-sites-import-page-free-start'   		, ResponsiveSitesAdmin._installRequiredPlugins );

			// Wordpress Plugin install events.
			$( document ).on( 'wp-plugin-installing'      , ResponsiveSitesAdmin._pluginInstalling );
			$( document ).on( 'wp-plugin-install-success' , ResponsiveSitesAdmin._pluginInstallSuccess );

			//Improved layout
			$( document ).on( 'click', '.responsive-sites__category-filter-anchor', ResponsiveSitesAdmin._toggleFilter );
			$( document ).on('click', '.page-builder-icon', ResponsiveSitesAdmin._toggle_page_builder_list );
			$( document ).on( 'click', '.responsive-sites__filter-wrap-checkbox, .responsive-sites__filter-wrap', ResponsiveSitesAdmin._filterClick );
			$( document ).on('keyup input'                     , '#wp-filter-search-input', ResponsiveSitesAdmin._search );
			$( document ).on( 'click'                    , '.nav-tab-wrapper .page-builders li', ResponsiveSitesAdmin._change_page_builder );
			$( document ).on('click'                     , '.ui-autocomplete .ui-menu-item', ResponsiveSitesAdmin._show_search_term );
			$( document ).on('click', '.responsive-ready-sites-sync-templates-button', ResponsiveSitesAdmin._sync_library);
		},

		/**
		 * Reset Page Count.
		 */
		_resetPagedCount: function() {

			$( 'body' ).addClass( 'loading-content' );
			$( 'body' ).attr( 'data-responsive-demo-last-request', '1' );
			$( 'body' ).attr( 'data-responsive-demo-paged', '1' );
			$( 'body' ).attr( 'data-scrolling', false );

		},

		/**
		 * Do Nothing.
		 */
		_doNothing: function( event ) {
			event.preventDefault();
		},

		/**
		 * toggle tooltip
		 */
		_toggle_tooltip: function( event ) {
			event.preventDefault();
			var tip_id = $( this ).data( 'tip-id' ) || '';
			if ( tip_id && $( '#' + tip_id ).length ) {
				$( '#' + tip_id ).toggle();
				$('.' + tip_id + ' .dashicons').toggleClass('active');
			}
		},

		/**
		 * Check if arrays are equal
		 */
		_areEqual:function () {
			var len = arguments.length;
			for (var i = 1; i < len; i++) {
				if (arguments[i] === null || arguments[i] !== arguments[i - 1]) {
					return false;
				}
			}
			return true;
		},

		/**
		 * Close Full Overlay
		 */
		_closeFullOverlay: function (event) {
			event.preventDefault();
			location.reload();
		},

		/**
		 * Import Site options Screen
		 */
		_importSiteOptionsScreen: function(event) {
			event.preventDefault();

			var site_id = $( this ).data( 'demo-id' ) || '';

			var self = $( this ).parents( '.responsive-ready-site-preview' );

			$( '#responsive-ready-site-preview' ).hide();

			$( '#responsive-ready-sites-import-options' ).show();

			var demoId                  = self.data( 'demo-id' ) || '',
				apiURL                  = self.data( 'demo-api' ) || '',
				demoType                = self.data( 'demo-type' ) || '',
				active_site             = self.data( 'active-site' ) || '',
				check_plugins_installed = self.data( 'check_plugins_installed' ) || '',
				demoURL                 = self.data( 'demo-url' ) || '',
				screenshot              = self.data( 'screenshot' ) || '',
				demo_name               = self.data( 'demo-name' ) || '',
				pages                   = self.data( 'pages' ) || '',
				demo_slug               = self.data( 'demo-slug' ) || '',
				requiredPlugins         = self.data( 'required-plugins' ) || '',
				responsiveSiteOptions   = self.find( '.responsive-site-options' ).val() || '';

			var template = wp.template( 'responsive-ready-sites-import-options-page' );

			templateData = [{
				id: demoId,
				demo_type: demoType,
				check_plugins_installed: check_plugins_installed,
				demo_url: demoURL,
				active_site: active_site,
				demo_api: apiURL,
				screenshot: screenshot,
				name: demo_name,
				slug: demo_slug,
				required_plugins: JSON.stringify( requiredPlugins ),
				responsive_site_options: responsiveSiteOptions,
				pages: JSON.stringify( pages ),
				pro_plugins_flag: ResponsiveSitesAdmin.pro_plugins_flag,
			}];
			$( '#responsive-ready-sites-import-options' ).append( template( templateData[0] ) );
			$( '.theme-install-overlay' ).css( 'display', 'block' );

			if ( $.isArray( requiredPlugins ) ) {
				// or.
				var $pluginsFilter = $( '#plugin-filter' ),
					data           = {
						action           : 'responsive-ready-sites-required-plugins',
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
						required_plugins : requiredPlugins
				};

				// Add disabled class from import button.
				$( '.responsive-demo-import' )
					.addClass( 'disabled not-click-able' )
					.removeAttr( 'data-import' );

				$( '.required-plugins' ).addClass( 'loading' ).html( '<span class="spinner is-active"></span>' );

				// Required Required.
				$.ajax(
					{
						url  : responsiveSitesAdmin.ajaxurl,
						type : 'POST',
						data : data,
					}
				)
					.fail(
						function( jqXHR ){

							// Remove loader.
							$( '.required-plugins' ).removeClass( 'loading' ).html( '' );

						}
					)
					.done(
						function ( response ) {
							required_plugins = response.data['required_plugins'];

							// Remove loader.
							$( '.required-plugins' ).removeClass( 'loading' ).html( '' );
							$( '.required-plugins-list' ).html( '' );

							/**
							 * Count remaining plugins.
							 *
							 * @type number
							 */
							var remaining_plugins = 0;

							/**
							 * Not Installed
							 *
							 * List of not installed required plugins.
							 */
							if ( typeof required_plugins.notinstalled !== 'undefined' ) {

								// Add not have installed plugins count.
								remaining_plugins += parseInt( required_plugins.notinstalled.length );

								$( required_plugins.notinstalled ).each(
									function( index, plugin ) {
										$( '.required-plugins-list' ).append( '<li class="plugin-card plugin-card-' + plugin.slug + '" data-slug="' + plugin.slug + '" data-init="' + plugin.init + '" data-name="' + plugin.name + '">' + plugin.name + '</li>' );
									}
								);
							}

							/**
							 * Inactive
							 *
							 * List of not inactive required plugins.
							 */
							if ( typeof required_plugins.inactive !== 'undefined' ) {
								// Add inactive plugins count.
								remaining_plugins += parseInt( required_plugins.inactive.length );

								$( required_plugins.inactive ).each(
									function( index, plugin ) {
										$( '.required-plugins-list' ).append( '<li class="plugin-card plugin-card-' + plugin.slug + '" data-slug="' + plugin.slug + '" data-init="' + plugin.init + '" data-name="' + plugin.name + '">' + plugin.name + '</li>' );
									}
								);
							}

							/**
							 * Active
							 *
							 * List of not active required plugins.
							 */
							if ( typeof required_plugins.active !== 'undefined' ) {

								$( required_plugins.active ).each(
									function( index, plugin ) {
										$( '.required-plugins-list' ).append( '<li class="plugin-card plugin-card-' + plugin.slug + '" data-slug="' + plugin.slug + '" data-init="' + plugin.init + '" data-name="' + plugin.name + '">' + plugin.name + '</li>' );
									}
								);
							}

							if ( check_plugins_installed && typeof required_plugins.notinstalled !== 'undefined' && required_plugins.notinstalled.length > 0 ) {
								$( '.responsive-ready-site-import-free' ).addClass( 'disabled not-click-able' );
								$( '.responsive-ready-site-import-free' ).prop( 'disabled',true );
								$( '.responsive-ready-sites-install-plugins-title' ).append( '<span class="warning"> - Please make sure you have following plugins Installed</span>' );
								$( '#responsive-ready-sites-tooltip-plugins-settings' ).css( 'display', 'block' );
							}
							/**
							 * Enable Demo Import Button
							 *
							 * @type number
							 */
							responsiveSitesAdmin.requiredPlugins = required_plugins;
						}
					);

			}
		},

		/**
		 * Fires when a nav item is clicked.
		 */
		_importDemo: function(event) {
			event.preventDefault();

			var date = new Date();

			ResponsiveSitesAdmin.import_start_time = new Date();

			$( '.sites-import-process-errors .current-importing-status-error-title' ).html( '' );

			$( '.sites-import-process-errors' ).hide();
			$( '.responsive-ready-site-import-free' ).addClass( 'updating-message installing' )
				.text( "Importing.." );
			$( '.responsive-ready-site-import-free' ).addClass( 'disabled not-click-able' );

			var output = '<div class="current-importing-status-title"></div><div class="current-importing-status-description"></div>';
			$( '.current-importing-status' ).html( output );

			$( document ).trigger( 'responsive-get-active-theme' );

		},

		/**
		 * Check if Responsive theme is active
		 */
		_is_responsive_theme_active: function() {
			$.ajax(
				{
					url: responsiveSitesAdmin.ajaxurl,
					type: 'POST',
					data: {
						'action': 'responsive-is-theme-active',
						'_ajax_nonce'      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.done(
					function (result) {
						if (result.success) {
							$( document ).trigger( 'responsive-ready-sites-install-start' );
						} else {
							ResponsiveSitesAdmin._log_error( 'Responsive Ready Sites Importer plugin requires the Responsive theme. Please ensures that the Responsive theme is active.', true );
						}
					}
				);
		},

		/**
		 * Log error
		 */
		_log_error: function( data, append ) {

			$( '.sites-import-process-errors' ).css( 'display', 'block' );
			var markup = '<p>' + data + '</p>';
			if (typeof data == 'object' ) {
				var markup = '<p>' + JSON.stringify( data ) + '</p>';
			}

			if ( append ) {
				$( '.current-importing-status-error-title' ).append( markup );
			} else {
				$( '.current-importing-status-error-title' ).html( markup );
			}

			$( '.responsive-ready-site-import-free' ).removeClass( 'updating-message installing' )
				.text( "Import Site" );
			$( '.responsive-ready-site-import-free' ).removeClass( 'disabled not-click-able' );
			$( '.responsive-ready-sites-tooltip-icon' ).removeClass( 'processed-import' );
			$( '.responsive-ready-sites-tooltip-icon' ).removeClass( 'processing-import' );
			$( '.responsive-ready-sites-import-process-wrap' ).hide();
		},

		/**
		 * Check if Responsive pro is installed
		 */
		_checkResponsiveAddonsProInstalled: function() {
			var is_pro_installed;
			$.ajax(
				{
					url: responsiveSitesAdmin.ajaxurl,
					async: false,
					type : 'POST',
					dataType: 'json',
					data: {
						'action': 'check-responsive-add-ons-pro-installed',
						'_ajax_nonce'      : responsiveSitesAdmin._ajax_nonce,
					}
				}
			)
				.done(
					function ( response ) {
						is_pro_installed = response;
					}
				);

			if (is_pro_installed.success) {
				return true;
			} else {
				return false;
			}
		},

		/**
		 * Individual Site Preview
		 *
		 * On click on image, more link & preview button.
		 */
		_preview: function( event ) {

			event.preventDefault();

			var site_id = $( this ).parents( '.ra-site-single' ).data( 'demo-id' ) || '';

			var self = $( this ).parents( '.theme' );
			self.addClass( 'theme-preview-on' );

			$( '#responsive-sites' ).hide();

			$( '#responsive-ready-site-preview' ).show();

			self.addClass( 'theme-preview-on' );

			$( 'html' ).addClass( 'responsive-site-preview-on' );

			ResponsiveSitesAdmin._renderDemoPreview( self );
		},

		/**
		 * Render Demo Preview
		 */
		_renderDemoPreview: function(anchor) {

			var demoId                         = anchor.data( 'demo-id' ) || '',
				demoURL                        = anchor.data( 'demo-url' ) || '',
				screenshot                     = anchor.data( 'screenshot' ) || '',
				demo_name                      = anchor.data( 'demo-name' ) || '',
				active_site                    = anchor.data( 'active-site' ) || '',
				demo_slug                      = anchor.data( 'demo-slug' ) || '',
				wpforms_path                   = anchor.data( 'wpforms-path' ) || '',
				requiredPlugins                = anchor.data( 'required-plugins' ) || '',
				allow_pages                    = anchor.data( 'allow-pages' ) || false,
				pages                    	   = anchor.data( 'pages' ) || '',
				check_plugins_installed        = anchor.data( 'check_plugins_installed' ) || '',
				responsiveSiteOptions          = anchor.find( '.responsive-site-options' ).val() || '',
				demo_type                      = anchor.data( 'demo-type' ) || '',
				isResponsiveAddonsProInstalled = ResponsiveSitesAdmin._checkResponsiveAddonsProInstalled();

			var template = wp.template( 'responsive-ready-site-preview' );

			templateData = [{
				id: demoId,
				demo_url: demoURL + '/?utm_source=free-to-pro&utm_medium=responsive-ready-site-importer&utm_campaign=responsive-pro&utm_content=preview',
				demo_api: demoURL,
				screenshot: screenshot,
				name: demo_name,
				active_site: active_site,
				wpforms_path: wpforms_path,
				slug: demo_slug,
				required_plugins: JSON.stringify( requiredPlugins ),
				responsive_site_options: responsiveSiteOptions,
				demo_type: demo_type,
				check_plugins_installed: check_plugins_installed,
				is_responsive_addons_pro_installed: isResponsiveAddonsProInstalled,
				allow_pages: allow_pages,
				pages: JSON.stringify( pages ),
			}];

			$( '#responsive-ready-site-preview' ).append( template( templateData[0] ) );
			$( '.theme-install-overlay' ).css( 'display', 'block' );

		},

		/**
		 * Import Process Starts
		 */
		_process_import: function() {

			var site_id = $( '.responsive-ready-sites-advanced-options-wrap' ).find( '.demo_site_id' ).val();

			var apiURL = responsiveSitesAdmin.ApiURL + 'cyberchimps-sites/' + site_id;

			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-set-reset-data',
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.done(
					function ( response ) {
						if ( response.success ) {
							ResponsiveSitesAdmin.site_imported_data = response.data;
						}
					}
				);

			if ( apiURL ) {
				ResponsiveSitesAdmin._importSite( apiURL );
			}

		},

		/**
		 * Start Import Process by API URL.
		 *
		 * @param  {string} apiURL Site API URL.
		 */
		_importSite: function( apiURL ) {

			// Request Site Import.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						'action'  : 'responsive-ready-sites-import-set-site-data-free',
						'api_url' : apiURL,
						'_ajax_nonce'      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( demo_data ) {

						// Check is site imported recently and set flag.

						// 1. Fail - Request Site Import.
						if ( false === demo_data.success ) {
							ResponsiveSitesAdmin._log_error( demo_data.data, true );
						} else {

							ResponsiveSitesAdmin.xml_path                       = encodeURI( demo_data.data['xml_path'] ) || '';
							ResponsiveSitesAdmin.wpforms_path                   = encodeURI( demo_data.data['wpforms_path'] ) || '';
							ResponsiveSitesAdmin.active_site_slug               = demo_data.data['slug'] || '';
							ResponsiveSitesAdmin.active_site_title              = demo_data.data['title'];
							ResponsiveSitesAdmin.active_site_featured_image_url = demo_data.data['featured_image_url'];
							ResponsiveSitesAdmin.site_customizer_data           = JSON.stringify( demo_data.data['site_customizer_data'] ) || '';
							ResponsiveSitesAdmin.required_plugins               = JSON.stringify( demo_data.data['required_plugins'] ) || '';
							ResponsiveSitesAdmin.required_pro_plugins           = JSON.stringify( demo_data.data['required_pro_plugins'] || '' );
							ResponsiveSitesAdmin.widgets_data                   = JSON.stringify( demo_data.data['site_widgets_data'] ) || '';
							ResponsiveSitesAdmin.site_options_data              = JSON.stringify( demo_data.data['site_options_data'] ) || '';
							ResponsiveSitesAdmin.pages                          = JSON.stringify( demo_data.data['pages'] ) || '';

							$( document ).trigger( 'responsive-ready-sites-import-set-site-data-done' );
						}
					}
				);
		},

		/**
		 * Install required plugins
		 */
		_installRequiredPlugins: function( event ){

			var requiredPlugins = JSON.parse( ResponsiveSitesAdmin.required_plugins );

			if ( $.isArray( requiredPlugins ) ) {

				// Required Required.
				$.ajax(
					{
						url  : responsiveSitesAdmin.ajaxurl,
						type : 'POST',
						data : {
							action           : 'responsive-ready-sites-required-plugins',
							_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
							required_plugins : requiredPlugins
						},
					}
				)
					.done(
						function ( response ) {
							var required_plugins = response.data['required_plugins'] || '';

							responsiveSitesAdmin.required_plugins = required_plugins;
							ResponsiveSitesAdmin._bulkPluginInstallActivate();
						}
					);

			} else {
				if ( ResponsiveSitesAdmin.processing_single_template ) {
					$( document ).trigger( 'responsive-ready-page-install-and-activate-required-plugins-done' );
				} else {
					$( document ).trigger( 'responsive-ready-sites-install-and-activate-required-plugins-done' );
				}
			}
		},

		/**
		 * Remove plugin from the queue.
		 */
		_removePluginFromQueue: function( removeItem, pluginsList ) {
			return jQuery.grep(
				pluginsList,
				function( value ) {
					return value.slug != removeItem;
				}
			);
		},

		/**
		 * Bulk Plugin Active & Install
		 */
		_bulkPluginInstallActivate: function()
		{
			if ( 0 === responsiveSitesAdmin.required_plugins.length ) {
				return;
			}

			var not_installed 	 = responsiveSitesAdmin.required_plugins.notinstalled || '';
			var activate_plugins = responsiveSitesAdmin.required_plugins.inactive || '';

			// Install wordpress.org plugins.
			if ( not_installed.length > 0 ) {
				ResponsiveSitesAdmin._installAllPlugins( not_installed );
			}

			// Activate wordpress.org plugins.
			if ( activate_plugins.length > 0 ) {
				ResponsiveSitesAdmin._activateAllPlugins( activate_plugins );
			}

			if ( activate_plugins.length <= 0 && not_installed.length <= 0 ) {
				if ( ResponsiveSitesAdmin.processing_single_template ) {
					ResponsiveSitesAdmin._ready_for_import_template();
				} else {
					ResponsiveSitesAdmin._ready_for_import_site();
				}
			}

		},

		/**
		 * Install All Plugins.
		 */
		_installAllPlugins: function( not_installed ) {

			$.each(
				not_installed,
				function(index, single_plugin) {

					// Add each plugin activate request in Ajax queue.
					// @see wp-admin/js/updates.js.
					wp.updates.queue.push(
						{
							action: 'install-plugin', // Required action.
							data:   {
								slug: single_plugin.slug
							}
						}
					);
				}
			);

			// Required to set queue.
			wp.updates.queueChecker();
		},

		/**
		 * Installing Plugin
		 */
		_pluginInstalling: function(event, args) {
			event.preventDefault();
			$( '.responsive-ready-sites-import-plugins .responsive-ready-sites-tooltip-icon' ).addClass( 'processing-import' );

		},

		/**
		 * Install plugin success
		 */
		_pluginInstallSuccess: function( event, response ) {

			if ( typeof responsiveSitesAdmin.required_plugins.notinstalled !== 'undefined' && responsiveSitesAdmin.required_plugins.notinstalled ) {
				event.preventDefault();

				// Reset not installed plugins list.
				var pluginsList                                    = responsiveSitesAdmin.required_plugins.notinstalled;
				responsiveSitesAdmin.required_plugins.notinstalled = ResponsiveSitesAdmin._removePluginFromQueue( response.slug, pluginsList );

				// WordPress adds "Activate" button after waiting for 1000ms. So we will run our activation after that.
				setTimeout(
					function () {

						var $init = $( '.plugin-card-' + response.slug ).data( 'init' );

						$.ajax(
							{
								url: responsiveSitesAdmin.ajaxurl,
								type: 'POST',
								data: {
									'action': 'responsive-ready-sites-required-plugin-activate',
									'init': $init,
									'_ajax_nonce'      : responsiveSitesAdmin._ajax_nonce,
								},
							}
						)
							.done(
								function (result) {

									if (result.success) {
										var pluginsList = responsiveSitesAdmin.required_plugins.inactive;

										// Reset not installed plugins list.
										responsiveSitesAdmin.required_plugins.inactive = ResponsiveSitesAdmin._removePluginFromQueue( response.slug, pluginsList );

										$( '.responsive-ready-sites-import-plugins .responsive-ready-sites-tooltip-icon' ).addClass( 'processed-import' );

										if ( ResponsiveSitesAdmin.processing_single_template ) {
											ResponsiveSitesAdmin._ready_for_import_template();
										} else {
											ResponsiveSitesAdmin._ready_for_import_site();
										}
									}
								}
							);

					},
					1200
				);
			}
		},

		/**
		 * Activate All Plugins.
		 */
		_activateAllPlugins: function( activate_plugins ) {

			$.each(
				activate_plugins,
				function(index, single_plugin) {

					ResponsiveSitesAjaxQueue.add(
						{
							url: responsiveSitesAdmin.ajaxurl,
							type: 'POST',
							data: {
								'action'            : 'responsive-ready-sites-required-plugin-activate',
								'init'              : single_plugin.init,
								'_ajax_nonce'      : responsiveSitesAdmin._ajax_nonce,
							},
							success: function( result ){

								if ( result.success ) {

									var pluginsList = responsiveSitesAdmin.required_plugins.inactive;

									// Reset not installed plugins list.
									responsiveSitesAdmin.required_plugins.inactive = ResponsiveSitesAdmin._removePluginFromQueue( single_plugin.slug, pluginsList );

									if ( ResponsiveSitesAdmin.processing_single_template ) {
										ResponsiveSitesAdmin._ready_for_import_template();
									} else {
										ResponsiveSitesAdmin._ready_for_import_site();
									}

								}
							}
						}
					);
				}
			);
			ResponsiveSitesAjaxQueue.run();
		},

		/**
		 * Ready for site import
		 */
		_ready_for_import_site: function () {
			var notinstalled = responsiveSitesAdmin.required_plugins.notinstalled || 0;
			var inactive     = responsiveSitesAdmin.required_plugins.inactive || 0;

			if ( ResponsiveSitesAdmin._areEqual( notinstalled.length, inactive.length ) ) {
				$( document ).trigger( 'responsive-ready-sites-install-and-activate-required-plugins-done' );
			}
		},

		/**
		 * Ready for template import
		 *
		 * @private
		 */
		_ready_for_import_template: function () {
			var notinstalled = responsiveSitesAdmin.required_plugins.notinstalled || 0;
			var inactive     = responsiveSitesAdmin.required_plugins.inactive || 0;

			if ( ResponsiveSitesAdmin._areEqual( notinstalled.length, inactive.length ) ) {
				$( document ).trigger( 'responsive-ready-page-install-and-activate-required-plugins-done' );
			}
		},

		/**
		 * Trigger reset data event
		 */
		_resetData: function( event ) {
			event.preventDefault();

			if( ResponsiveSitesAdmin._is_reset_data() ) {
				$('.responsive-ready-sites-reset-data .responsive-ready-sites-tooltip-icon').addClass('processing-import');
				$(document).trigger('responsive-ready-sites-reset-data');
			} else {
				$( document ).trigger( 'responsive-ready-sites-reset-data-done' );
			}
		},

		/**
		 *
		 * Check if delete previous data checkbox is checked
		 */
		_is_reset_data: function() {
			if ( $( '.responsive-ready-sites-reset-data' ).find('.checkbox').is(':checked') ) {
				return true;
			}
			return false;
		},

		/**
		 * Backup before reset settings
		 */
		_backup_before_reset_options: function() {
			ResponsiveSitesAdmin._backupOptions( 'responsive-ready-sites-backup-settings-before-reset-done' );
			ResponsiveSitesAdmin.backup_taken = true;
		},

		/**
		 * Backup settings
		 */
		_backupOptions: function( trigger_name ) {
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-backup-settings',
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,

					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( data ) {

						// Custom trigger.
						$( document ).trigger( trigger_name );
					}
				);
		},

		/**
		 * Reset customizer data
		 */
		_reset_customizer_data: function() {
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-customizer-data',
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( data ) {
						$( document ).trigger( 'responsive-ready-sites-reset-customizer-data-done' );
					}
				);
		},

		/**
		 * Reset site options
		 */
		_reset_site_options: function() {
			// Site Options.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-site-options',
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( data ) {
						$( document ).trigger( 'responsive-ready-sites-reset-site-options-done' );
					}
				);
		},

		/**
		 * Reset widgets data
		 */
		_reset_widgets_data: function() {
			// Widgets.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-widgets-data',
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( data ) {
						$( document ).trigger( 'responsive-ready-sites-reset-widgets-data-done' );
					}
				);
		},

		/**
		 * Reset terms
		 */
		_reset_terms: function() {

			if ( ResponsiveSitesAdmin.site_imported_data['reset_terms'].length ) {
				ResponsiveSitesAdmin.reset_remaining_terms = ResponsiveSitesAdmin.site_imported_data['reset_terms'].length;

				$.each(
					ResponsiveSitesAdmin.site_imported_data['reset_terms'],
					function(index, term_id) {
						ResponsiveSitesAjaxQueue.add(
							{
								url: responsiveSitesAdmin.ajaxurl,
								type: 'POST',
								data: {
									action  : 'responsive-ready-sites-delete-terms',
									term_id : term_id,
									_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
								},
								success: function( result ){
									if ( ResponsiveSitesAdmin.reset_processed_terms < ResponsiveSitesAdmin.site_imported_data['reset_terms'].length ) {
										ResponsiveSitesAdmin.reset_processed_terms += 1;
									}

									ResponsiveSitesAdmin.reset_remaining_terms -= 1;
									if ( 0 == ResponsiveSitesAdmin.reset_remaining_terms ) {
										$( document ).trigger( 'responsive-ready-sites-delete-terms-done' );
									}
								}
							}
						);
					}
				);
				ResponsiveSitesAjaxQueue.run();

			} else {
				$( document ).trigger( 'responsive-ready-sites-delete-terms-done' );
			}
		},

		/**
		 * Reset wp forms
		 */
		_reset_wp_forms: function() {

			if ( ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'].length ) {
				ResponsiveSitesAdmin.reset_remaining_wp_forms = ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'].length;

				$.each(
					ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'],
					function(index, post_id) {
						ResponsiveSitesAjaxQueue.add(
							{
								url: responsiveSitesAdmin.ajaxurl,
								type: 'POST',
								data: {
									action  : 'responsive-ready-sites-delete-wp-forms',
									post_id : post_id,
									_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
								},
								success: function( result ){

									if ( ResponsiveSitesAdmin.reset_processed_wp_forms < ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'].length ) {
										ResponsiveSitesAdmin.reset_processed_wp_forms += 1;
									}

									ResponsiveSitesAdmin.reset_remaining_wp_forms -= 1;
									if ( 0 == ResponsiveSitesAdmin.reset_remaining_wp_forms ) {
										$( document ).trigger( 'responsive-ready-sites-delete-wp-forms-done' );
									}
								}
							}
						);
					}
				);
				ResponsiveSitesAjaxQueue.run();

			} else {
				$( document ).trigger( 'responsive-ready-sites-delete-wp-forms-done' );
			}

		},

		/**
		 * Reset Posts
		 */
		_reset_posts: function() {

			if ( ResponsiveSitesAdmin.site_imported_data['reset_posts'].length ) {

				ResponsiveSitesAdmin.reset_remaining_posts = ResponsiveSitesAdmin.site_imported_data['reset_posts'].length;

				$.each(
					ResponsiveSitesAdmin.site_imported_data['reset_posts'],
					function(index, post_id) {

						ResponsiveSitesAjaxQueue.add(
							{
								url: responsiveSitesAdmin.ajaxurl,
								type: 'POST',
								data: {
									action  : 'responsive-ready-sites-delete-posts',
									post_id : post_id,
									_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
								},
								success: function( result ){

									if ( ResponsiveSitesAdmin.reset_processed_posts < ResponsiveSitesAdmin.site_imported_data['reset_posts'].length ) {
										ResponsiveSitesAdmin.reset_processed_posts += 1;
									}

									ResponsiveSitesAdmin.reset_remaining_posts -= 1;
									if ( 0 == ResponsiveSitesAdmin.reset_remaining_posts ) {
										$( '.responsive-ready-sites-reset-data .responsive-ready-sites-tooltip-icon' ).removeClass( 'processing-import' );
										$( '.responsive-ready-sites-reset-data .responsive-ready-sites-tooltip-icon' ).addClass( 'processed-import' );
										$( document ).trigger( 'responsive-ready-sites-reset-data-done' );
									}
								}
							}
						);
					}
				);
				ResponsiveSitesAjaxQueue.run();

			} else {
				$( '.responsive-ready-sites-reset-data .responsive-ready-sites-tooltip-icon' ).addClass( 'processed-import' );
				$( document ).trigger( 'responsive-ready-sites-reset-data-done' );
			}
		},

		/**
		 * Import WpForms
		 */
		_importWPForms: function() {

			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						action	: 'responsive-ready-sites-import-wpforms',
						wpforms_path : ResponsiveSitesAdmin.wpforms_path,
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( forms){
						if (false === forms.success) {
							// log.
						} else {
							$( document ).trigger( 'responsive-ready-sites-import-wpforms-done' );
						}
					}
				)
		},

		/**
		 * Import XML Data.
		 */
		_importXML: function() {

			$.ajax(
				{
					url: responsiveSitesAdmin.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'responsive-ready-sites-import-xml',
						xml_path: ResponsiveSitesAdmin.xml_path,
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
					beforeSend: function () {
						$( '.responsive-ready-sites-import-process-wrap' ).show();
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function (xml_data) {

						// 2. Fail - Import XML Data.
						if (false === xml_data.success) {
							// log.
						} else {

							// 2. Pass - Import XML Data.

							// Import XML though Event Source.
							wxrImport.data = xml_data.data;
							wxrImport.render();

							$( '.current-importing-status-description' ).html( '' ).show();

							$( '.responsive-ready-sites-import-xml .inner' ).append( '<div class="responsive-ready-sites-import-process-wrap"><progress class="responsive-ready-sites-import-process" max="100" value="0"></progress></div>' );

							var evtSource       = new EventSource( wxrImport.data.url );
							evtSource.onmessage = function (message) {
								var data = JSON.parse( message.data );
								switch (data.action) {
									case 'updateDelta':

										wxrImport.updateDelta( data.type, data.delta );
										break;

									case 'complete':
										evtSource.close();

										document.getElementsByClassName( "cybershimps-sites-import-process" ).value = '100';
										$( '.cybershimps-sites-import-process-wrap' ).hide();

										$( '.responsive-ready-sites-import-xml .responsive-ready-sites-tooltip-icon' ).addClass( 'processed-import' );
										$( document ).trigger( 'responsive-ready-sites-import-xml-done' );

										break;
								}
							};
							evtSource.addEventListener(
								'log',
								function (message) {
									var data    = JSON.parse( message.data );
									var message = data.message || '';
									if (message && 'info' === data.level) {
										message = message.replace(
											/"/g,
											function (letter) {
												return '';
											}
										);
										// log message on screen.
									}
								}
							);
						}
					}
				);

		},

		/**
		 * Import Customizer Setting
		 */
		_importCustomizerSettings: function() {
			$.ajax(
				{
					url: responsiveSitesAdmin.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'responsive-ready-sites-import-customizer-settings',
						site_customizer_data: ResponsiveSitesAdmin.site_customizer_data,
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
					beforeSend: function () {
						$( '.responsive-ready-sites-import-customizer .responsive-ready-sites-tooltip-icon' ).addClass( 'processing-import' );
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function (forms) {
						if (false === forms.success) {
							// log.
						} else {
							$( '.responsive-ready-sites-import-customizer .responsive-ready-sites-tooltip-icon' ).removeClass( 'processing-import' );
							$( '.responsive-ready-sites-import-customizer .responsive-ready-sites-tooltip-icon' ).addClass( 'processed-import' );
							$( document ).trigger( 'responsive-ready-sites-import-customizer-settings-done' );
						}
					}
				)
		},

		/**
		 * Import Widgets.
		 */
		_importWidgets: function( event ) {
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						action       : 'responsive-ready-sites-import-widgets',
						widgets_data : ResponsiveSitesAdmin.widgets_data,
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( widgets_data ) {

						if ( false === widgets_data.success ) {
							ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );

						} else {

							$( document ).trigger( 'responsive-ready-sites-import-widgets-done' );
						}
					}
				);
		},

		/**
		 * Import Site Options.
		 */
		_importSiteOptions: function( event ) {

			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						action       : 'responsive-ready-sites-import-options',
						options_data : ResponsiveSitesAdmin.site_options_data,
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
					}
				)
				.done(
					function ( options_data ) {

						// Fail - Import Site Options.
						if ( false === options_data.success ) {
							ResponsiveSitesAdmin._log_error( 'There was an error while processing import. Please try again.', true );
						} else {

							// 3. Pass - Import Site Options.
							$( document ).trigger( 'responsive-ready-sites-import-options-done' );
						}
					}
				);
		},

		/**
		 * Import Site Complete.
		 */
		_importSiteEnd: function( event ) {

			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						action : 'responsive-ready-sites-import-end',
						slug: ResponsiveSitesAdmin.active_site_slug,
						title: ResponsiveSitesAdmin.active_site_title,
						featured_image_url: ResponsiveSitesAdmin.active_site_featured_image_url,
						_ajax_nonce      : responsiveSitesAdmin._ajax_nonce,
					}
				}
			)
				.done(
					function ( data ) {

						// Fail - Import In-Complete.
						if ( false === data.success ) {
							// log.
						} else {

							var	output = '<h2>Responsive Ready Site import complete.</h2>';
							output    += '<p><a class="button button-primary button-hero" href="' + responsiveSitesAdmin.siteURL + '" target="_blank">Launch Site</a></p>';

							$( '.site-import-options' ).hide();
							$( '.result_preview' ).html( '' ).show();
							$( '.result_preview' ).html( output );
						}
					}
				);
		},

		/**
		 * Import Single Page Preview Screen
		 */
		_importPagePreviewScreen: function(event) {
			event.preventDefault();

			var self = $( this ).parents( '.responsive-ready-site-preview' );

			$( '#responsive-ready-site-preview' ).hide();

			$( '#responsive-ready-sites-import-options' ).show();

			var apiURL                = self.data( 'demo-api' ) || '',
				demoType              = self.data( 'demo-type' ) || '',
				demo_name             = self.data( 'demo-name' ) || '',
				wpforms_path          = self.data( 'wpforms-path' ) || '',
				screenshot            = self.data( 'screenshot' ) || '',
				requiredPlugins       = self.data( 'required-plugins' ) || '',
				pages                 = self.data( 'pages' ) || '',
				responsiveSiteOptions = self.find( '.responsive-site-options' ).val() || '';

			var template = wp.template( 'responsive-ready-sites-import-page-preview-page' );

			templateData = [{
				demo_type: demoType,
				demo_api: apiURL,
				name: demo_name,
				wpforms_path: wpforms_path,
				required_plugins: JSON.stringify( requiredPlugins ),
				responsive_site_options: responsiveSiteOptions,
				pages:  pages,
				screenshot: screenshot
			}];
			$( '#responsive-ready-sites-import-options' ).append( template( templateData[0] ) )
			$( '.theme-install-overlay' ).css( 'display', 'block' );
		},

		/**
		 * Preview Templates for the Site
		 */
		_change_site_preview_screenshot: function( event ) {
			event.preventDefault();

			var item = $( this );

			ResponsiveSitesAdmin._set_preview_screenshot_by_page( item );
		},

		/**
		 * Set Preview Image for the Page
		 */
		_set_preview_screenshot_by_page: function( element ) {
			var large_img_url = $( element ).find( '.theme-screenshot' ).attr( 'data-featured-src' ) || '';
			var url           = $( element ).find( '.theme-screenshot' ).attr( 'data-src' ) || '';
			var page_name     = $( element ).find( '.theme-name' ).text() || '';
			var demo_type     = $( element ).find( '.theme-screenshot' ).attr( 'data-demo-type' ) || '';

			$( element ).siblings().removeClass( 'current_page' );
			$( element ).addClass( 'current_page' );

			$( '.single-page-import-button-' + demo_type ).removeClass( 'disabled' );
			if ( page_name ) {
				var title = responsiveSitesAdmin.importSingleTemplateButtonTitle.replace( '%s', page_name.trim() );
				$( '.single-page-import-button-' + demo_type ).text( title );
			}

			if ( url ) {
				$( '.single-site-preview' ).animate(
					{
						scrollTop: 0
					},
					0
				);
				$( '.single-site-preview img' ).addClass( 'loading' ).attr( 'src', url );
				var imgLarge    = new Image();
				imgLarge.src    = large_img_url;
				imgLarge.onload = function () {
					$( '.single-site-preview img' ).removeClass( 'loading' );
					$( '.single-site-preview img' ).attr( 'src', imgLarge.src );
				};
			}
		},

		/**
		 * Import Single Page options Screen
		 */
		_importSinglePageOptions: function(event) {
			event.preventDefault();

			var self = $( this ).parents( '.responsive-ready-sites-advanced-options-wrap' );

			var demo_api     = self.data( 'demo-api' ) || '',
				wpforms_path = self.data( 'wpforms-path' ) || '',
				demo_type 	 = self.data( 'demo-type' ) || '';

			var page_id = ResponsiveSitesAdmin._get_id( $( '#single-pages' ).find( '.current_page' ).attr( 'data-page-id' ) ) || '';

			var required_plugins = JSON.parse( $( '#single-pages' ).find( '.current_page' ).attr( 'data-required-plugins' ) ) || '';

			var includes_wp_forms = JSON.parse( $( '#single-pages' ).find( '.current_page' ).attr( 'data-includes-wp-forms' ) ) || false;

			$( '#site-pages' ).hide();

			$( '#responsive-ready-sites-import-options' ).show();

			var template = wp.template( 'responsive-ready-sites-import-single-page-options-page' );

			templateData = [{
				page_id: page_id,
				demo_api: demo_api,
				required_plugins: required_plugins,
				wpforms_path: wpforms_path,
				includes_wp_forms: includes_wp_forms,
				demo_type: demo_type,
				pro_plugins_flag: ResponsiveSitesAdmin.pro_plugins_flag,
			}];
			$( '#responsive-ready-sites-import-options' ).append( template( templateData[0] ) );
			$( '.theme-install-overlay' ).css( 'display', 'block' );

			$( '.required-plugins' ).removeClass( 'loading' ).html( '' );
			$( '.required-plugins-list' ).html( '' );
			$( required_plugins ).each(
				function( index, plugin ) {
					$( '.required-plugins-list' ).append( '<li class="plugin-card plugin-card-' + plugin.slug + '" data-slug="' + plugin.slug + '" data-init="' + plugin.init + '" data-name="' + plugin.name + '">' + plugin.name + '</li>' );
				}
			);
		},

		/**
		 * Import single page.
		 */
		_importSinglePage: function(event) {
			event.preventDefault();

			var date = new Date();

			var self = $( this ).parents( '.responsive-ready-sites-advanced-options-wrap.single-page-import-options-page' );

			var required_plugins  = self.data( 'required-plugins' ) || '',
				includes_wp_forms = self.data( 'includes-wp-forms' ) || false,
				wpforms_path      = self.data( 'wpforms-path' ) || '';

			ResponsiveSitesAdmin.current_page_id  = self.data( 'page-id' ) || '';
			ResponsiveSitesAdmin.current_page_api = self.data( 'demo-api' ) || '';
			ResponsiveSitesAdmin.required_plugins = JSON.stringify( required_plugins );

			if ( includes_wp_forms ) {
				ResponsiveSitesAdmin.wpforms_path = wpforms_path;
			} else {
				ResponsiveSitesAdmin.wpforms_path = '';
			}

			ResponsiveSitesAdmin.import_start_time = new Date();

			$( '.sites-import-process-errors .current-importing-status-error-title' ).html( '' );

			$( '.sites-import-process-errors' ).hide();
			$( '.responsive-ready-page-import-free' ).addClass( 'updating-message installing' )
				.text( "Importing.." );
			$( '.responsive-ready-page-import-free' ).addClass( 'disabled not-click-able' );

			ResponsiveSitesAdmin.processing_single_template = true;

			$( document ).trigger( 'responsive-ready-sites-import-page-free-start' );
		},

		/**
		 * Get Page id from attribute
		 */
		_get_id: function( site_id ) {
			return site_id.replace( 'id-', '' );
		},

		/**
		 * Import WP Forms
		 */
		_import_wpform: function( wpforms_path, callback ) {

			if ( '' == wpforms_path ) {
				if ( callback && typeof callback == "function") {
					callback( '' );
				}
				return;
			}

			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						action      : 'responsive-ready-sites-import-wpforms',
						wpforms_path : wpforms_path,
						_ajax_nonce : responsiveSitesAdmin._ajax_nonce,
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_error( jqXHR );
						ResponsiveSitesAdmin._log_error( jqXHR.status + jqXHR.statusText, 'Import WP Forms Failed!', jqXHR );
					}
				)
				.done(
					function ( response ) {

						// 1. Fail - Import WPForms Options.
						if ( false === response.success ) {
							ResponsiveSitesAdmin._log_error( response.data, 'Import WP Forms Failed!' );
						} else {
							if ( callback && typeof callback == "function") {
								callback( response );
							}
						}
					}
				);
		},

		/**
		 * Import page.
		 */
		_importPage: function() {

			$( '.responsive-ready-sites-import-xml .responsive-ready-sites-tooltip-icon' ).addClass( 'processing-import' );

			ResponsiveSitesAdmin._import_wpform(
				ResponsiveSitesAdmin.wpforms_path,
				function( form_response ) {

					page_api_url = ResponsiveSitesAdmin.current_page_api + '/wp-json/wp/v2/pages/' + ResponsiveSitesAdmin.current_page_id;

					fetch( page_api_url ).then(
						response => {
							return response.json();
						}
					).then(
						data => {
							// Import Single Page.
							$.ajax(
								{
									url: responsiveSitesAdmin.ajaxurl,
									type: 'POST',
									dataType: 'json',
									data: {
										'action': 'responsive-sites-create-page',
										'_ajax_nonce': responsiveSitesAdmin._ajax_nonce,
										'data': data,
										'current_page_api': ResponsiveSitesAdmin.current_page_api,
									},
									success: function (response) {
										if (response.success) {
											$( 'body' ).removeClass( 'importing-site' );
											$( '.site-import-options' ).hide();
											$( '.rotating,.current-importing-status-wrap,.notice-warning' ).remove();

											var output = '<h2>Responsive Ready Site import Page complete.</h2>';
											output    += '<p><a class="button button-primary button-hero" href="' + response.data['link'] + '" target="_blank">View Template</a></p>';

											$( '.single-site-wrap' ).hide();
											$( '.result_preview' ).html( '' ).show();
											$( '.result_preview' ).html( output );
										} else {
											ResponsiveSitesAdmin._log_error( 'Page Rest API Request Failed!', true );
										}
									}
								}
							);
						}
					).catch(
						err => {
							ResponsiveSitesAdmin._log_error( 'Page Rest API Request Failed!', true );
						}
					);
				}
			);
		},

		ucwords: function( str ) {
			if ( ! str ) {
				return '';
			}

			str = str.toLowerCase().replace(
				/\b[a-z]/g,
				function(letter) {
					return letter.toUpperCase();
				}
			);

			str = str.replace(
				/-/g,
				function(letter) {
					return ' ';
				}
			);

			return str;
		},

		_sync_templates_library_with_ajax: function( is_append ) {

			$.ajax({
				url: responsiveSitesAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'responsive-sites-get-sites-request-count',
				},
			})
				.fail(function (jqXHR) {
					console.log('The api request to fetch the sites request count fails');
				})
				.done(function (response) {

					var total = response.data;

					for( let i = 1; i <= total; i++ ) {

						ResponsiveSitesAjaxQueue.add({
							url: responsiveSitesAdmin.ajaxurl,
							type: 'POST',
							data: {
								action  : 'responsive-ready-sites-import-sites',
								page_no : i,
							},
							success: function( result ){
								if( is_append ) {
									if( ! ResponsiveSitesAdmin.isEmpty( result.data ) ) {

										var template          = wp.template( 'responsive-sites-list' );

										var data = ResponsiveSitesAdmin._filter_sites_by_page_builder(result.data);

										// First fill the placeholders and then append remaining sites.
										if ($('.placeholder-site').length) {
											for (site_id in result.data) {
												if ($('.placeholder-site').length) {
													$('.placeholder-site').first().remove();
												}
											}
											if ($('#responsive-sites .site-single:not(.placeholder-site)').length) {
												$('#responsive-sites .site-single:not(.placeholder-site)').last().after(template(data));
											} else {
												$('#responsive-sites').prepend(template(data));
											}
										} else {
											$('#responsive-sites').append(template(data));
										}

										responsiveSitesAdmin.default_page_builder_sites = $.extend({}, responsiveSitesAdmin.default_page_builder_sites, result.data);
									}

								}

								if (i === total && responsiveSitesAdmin.strings.syncCompleteMessage) {
									$('#wpbody-content').find('.responsive-sites-sync-templates-library-message').remove();
									var noticeContent = wp.updates.adminNotice({
										className: 'notice responsive-ready-sites-notice notice-success is-dismissible responsive-ready-sites-sync-templates-library-message',
										message: responsiveSitesAdmin.strings.syncCompleteMessage + ' <button type="button" class="notice-dismiss"><span class="screen-reader-text">' + responsiveSitesAdmin.dismiss + '</span></button>',
									});
									$('#screen-meta').after(noticeContent);

									$('.responsive-ready-sites-sync-templates-button').removeClass('updating-message');
								}
							}
						});
					}
					// Run the AJAX queue.
					ResponsiveSitesAjaxQueue.run();
				});
			ResponsiveSitesAdmin._sync_templates_library_complete();
			},

		_sync_templates_library_complete: function () {
			$.ajax({
				url: responsiveSitesAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'responsive-ready-sites-update-templates-library-complete',
				},
			}).done(function (response) {
				console.log("Ready Sites data Updated");
			});
		},

		_sync_library: function (event) {
			event.preventDefault();
			var button = $(this);

			if (button.hasClass('updating-message')) {
				return;
			}

			button.addClass('updating-message');

			$('.responsive-ready-sites-sync-templates-library-message').remove();

			var noticeContent = wp.updates.adminNotice({
				className: 'responsive-ready-sites-sync-templates-library-message responsive-ready-sites-notice notice notice-info',
				message: responsiveSitesAdmin.syncTemplatesLibraryStart + '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' + responsiveSitesAdmin.dismiss + '</span></button>',
			});

			$('#screen-meta').after(noticeContent);

			$(document).trigger('wp-updates-notice-added');

			$.ajax({
				url: responsiveSitesAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'responsive-ready-sites-update-templates-library',
				},
			})
				.done(function (response) {
					if (response.success) {
						if ('updated' === response.data) {

							$('#wpbody-content').find('.responsive-ready-sites-sync-templates-library-message').remove();
							var noticeContent = wp.updates.adminNotice({
								className: 'notice responsive-ready-sites-notice notice-success is-dismissible responsive-ready-sites-sync-templates-library-message',
								message: responsiveSitesAdmin.strings.syncCompleteMessage + ' <button type="button" class="notice-dismiss"><span class="screen-reader-text">' + responsiveSitesAdmin.dismiss + '</span></button>',
							});
							$('#screen-meta').after(noticeContent);
							button.removeClass('updating-message');
						} else {
							ResponsiveSitesAdmin._sync_templates_library_with_ajax();
						}
					} else {
						$('#wpbody-content').find('.responsive-ready-sites-sync-templates-library-message').remove();
						var noticeContent = wp.updates.adminNotice({
							className: 'notice responsive-ready-sites-notice notice-error is-dismissible responsive-ready-sites-sync-templates-library-message',
							message: response.data + ' <button type="button" class="notice-dismiss"><span class="screen-reader-text">' + responsiveSitesAdmin.dismiss + '</span></button>',
						});
						$('#screen-meta').after(noticeContent);
						button.removeClass('updating-message');
					}
				});
		},

		_toggleFilter: function( e ) {

			var items = $( '.responsive-sites__category-filter-items' );

			if ( items.hasClass( 'visible' ) ) {
				items.removeClass( 'visible' );
				items.hide();
			} else {
				items.addClass( 'visible' );
				items.show();
			}
		},

		_toggle_page_builder_list: function( event ) {
			event.preventDefault();
			$(this).toggleClass( 'active' );
			$('body').toggleClass( 'showing-page-builders' );
		},


		_closeFilter: function( e ) {

			var items = $( '.responsive-sites__category-filter-items' );
			items.removeClass( 'visible' );
			items.hide();
		},

		_filterClick: function( e ) {

			ResponsiveSitesAdmin.filter_array = [];

			if ( $( this ).hasClass( 'responsive-sites__filter-wrap' ) ) {
				$( '.responsive-sites__category-filter-anchor' ).attr( 'data-slug', $( this ).data( 'slug' ) );
				$( '.responsive-sites__category-filter-items' ).find( '.responsive-sites__filter-wrap' ).removeClass( 'category-active' );
				$( this ).addClass( 'category-active' );
				$( '.responsive-sites__category-filter-anchor' ).text( $( this ).text() );
				$( '.responsive-sites__category-filter-anchor' ).trigger( 'click' );
				$( '#wp-filter-search-input' ).val( '' );
			}

			var $filter_name = $( '.responsive-sites__category-filter-anchor' ).attr( 'data-slug' );

			if ( '' != $filter_name ) {
				ResponsiveSitesAdmin.filter_array.push( $filter_name );
			}

			if( $( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]:checked' ).length ) {
				$( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]' ).removeClass('active');
				$( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]:checked' ).addClass('active');
			}
			var $filter_type = $( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]:checked' ).val();

			if ( '' != $filter_type ) {
				ResponsiveSitesAdmin.filter_array.push( $filter_type );
			}

			ResponsiveSitesAdmin._closeFilter();

			$( '#wp-filter-search-input' ).trigger( 'keyup' );
		},

		_search: function(event) {

			var search_input  = $( this ),
				search_term   = $.trim( search_input.val() ) || '';

			if( 13 === event.keyCode ) {
				$('.responsive-sites-autocomplete-result .ui-autocomplete').hide();
				$('.search-form').removeClass('searching');
				$('#responsive-sites-admin').removeClass('searching');
			}

			$('body').removeClass('responsive-sites-no-search-result');

			var searchTemplateFlag = false,
				items = [];

			if( search_term.length ) {
				search_input.addClass('has-input');
				$('#responsive-sites-admin').addClass('searching');
				searchTemplateFlag = true;
			} else {
				search_input.removeClass('has-input');
				$('#responsive-sites-admin').removeClass('searching');
			}

			items = ResponsiveSitesAdmin._get_sites_and_pages_by_search_term( search_term );

			if( ! ResponsiveSitesAdmin.isEmpty( items ) ) {
				if ( searchTemplateFlag ) {
					ResponsiveSitesAdmin.add_sites_after_search( items );
				} else {
					ResponsiveSitesAdmin.add_sites( items );
				}
			} else {
				if( search_term.length ) {
					$('body').addClass('responsive-sites-no-search-result');
				}
				$('#responsive-sites').html( wp.template('responsive-sites-suggestions') );
			}
		},

		_get_sites_and_pages_by_search_term: function( search_term ) {

			var items = [],
				tags_strings = [];
			search_term = search_term.toLowerCase();

			var $page_builder = $('.page-builders .active').attr('data-page-builder') || 'elementor';

			if ( search_term == '' && ResponsiveSitesAdmin.filter_array.length == 0 && $page_builder == 'all' ) {
				return responsiveSitesAdmin.default_page_builder_sites;
			}

			var $filter_type = $( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]:checked' ).val();
			var $filter_name = $( '.responsive-sites__category-filter-anchor' ).attr( 'data-slug' );

			for( site_id in responsiveSitesAdmin.default_page_builder_sites ) {

				var current_site = responsiveSitesAdmin.default_page_builder_sites[site_id];
				var text_match = true;
				var free_match = true;
				var category_match = true;
				var page_builder_match = false;
				var match_id = '';

				if ( '' != search_term ) {
					text_match = false;
				}

				if ( '' != $filter_name ) {
					category_match = false;
				}

				if ( '' != $filter_type ) {
					free_match = false;
				}

				if( '' != $page_builder) {
					page_builder_match = false;
				}

				// Check in site title.
				if( current_site['title'] ) {
					var site_title = ResponsiveSitesAdmin._unescape_lower( current_site['title']['rendered'] );

					if( site_title.toLowerCase().includes( search_term ) ) {
						text_match = true;
						match_id = site_id;
					}
				}

				// Check in site tags.
				if ( null !== current_site['sites_tags'] && Object.keys(current_site['sites_tags']).length) {
					for( tag_id in current_site['sites_tags'] ) {
						var tag_title = current_site['sites_tags'][tag_id];
						tag_title = ResponsiveSitesAdmin._unescape_lower(tag_title.replace('-', ' '));
						if (tag_title.toLowerCase().includes(search_term)) {
							text_match = true;
							match_id = site_id;
						}
					}
				}

				for( filter_id in ResponsiveSitesAdmin.filter_array ) {
					var slug = ResponsiveSitesAdmin.filter_array[filter_id];
					if( slug == 'free' && 'free' == current_site['demo_type'] ) {
						free_match = true;
						match_id = site_id;
					}
					if( slug == 'premium' && 'free' != current_site['demo_type'] ) {
						free_match = true;
						match_id = site_id;
					}
					if ( slug != 'free' && slug != 'premium' && undefined != slug ) {
						for( cat_id in current_site['sites_category'] ) {
							if( slug.toLowerCase() == current_site['sites_category'][cat_id] ) {
								category_match = true;
								match_id = site_id;
							}
						}
					}
				}

				if ( $page_builder == 'all' || current_site['page_builder'] == $page_builder ) {
					page_builder_match = true;
				}

				if ( '' != match_id ) {
					if ( text_match && category_match && free_match && page_builder_match ) {
						items[site_id] = current_site;
						items[site_id]['type'] = 'site';
						items[site_id]['site_id'] = site_id;
						items[site_id]['pages_count'] = ( undefined != current_site['pages'] ) ? Object.keys( current_site['pages'] ).length : 0;
						tags_strings.push( ResponsiveSitesAdmin._unescape_lower( current_site['title']['rendered'] ));

						if ( null !== current_site['sites_tags'] && Object.keys(current_site['sites_tags']).length) {
							for (tag_id in current_site['sites_tags']) {
								var tag_title = current_site['sites_tags'][tag_id];
								tag_title = ResponsiveSitesAdmin._unescape_lower(tag_title.replace('-', ' '));
								if (tag_title.toLowerCase().includes(search_term)) {
									tags_strings.push(ResponsiveSitesAdmin._unescape_lower(tag_title));
								}
							}
						}
					}
				}
			}

			if ( tags_strings.length > 0 ) {
				ResponsiveSitesAdmin.autocompleteTags = tags_strings;
				ResponsiveSitesAdmin._autocomplete();
			}

			return items;
		},

		_filter_sites_by_page_builder: function( data ) {

			var items = [];

			var $page_builder = $('.page-builders .active').attr('data-page-builder') || 'elementor';

			for( site_id in data ) {

				var current_site = data[site_id];

				var page_builder_match = false;

				if ( current_site['page_builder'] === $page_builder ) {
					page_builder_match = true;
				}
				if ( page_builder_match ) {
					items[site_id] = current_site;
					items[site_id]['type'] = 'site';
					items[site_id]['site_id'] = site_id;
					items[site_id]['pages_count'] = ( undefined != current_site['pages'] ) ? Object.keys( current_site['pages'] ).length : 0;
				}
			}

			return items;
		},

		_unescape_lower: function( input_string ) {
			var input_string = ResponsiveSitesAdmin._unescape( input_string );
			return input_string.toLowerCase();
		},

		_unescape: function( input_string ) {
			var title = _.unescape( input_string );

			// @todo check why below character not escape with function _.unescape();
			title = title.replace('&#8211;', '-' );
			title = title.replace('&#8217;', "'" );

			return title;
		},

		isEmpty: function(obj) {
			for(var key in obj) {
				if(obj.hasOwnProperty(key))
					return false;
			}
			return true;
		},

		add_sites_after_search: function( data ) {
			var template          = wp.template( 'responsive-sites-list' );

			$('#responsive-sites').html( template( data ) );
		},


		add_sites: function( data ) {
			var template          = wp.template( 'responsive-sites-list' );

			$('#responsive-sites').html( template( data ) );
		},

		_change_page_builder: function() {

			ResponsiveSitesAdmin.filter_array = [];

			var $filter_name = $( '.responsive-sites__category-filter-anchor' ).attr( 'data-slug' );

			if ( '' != $filter_name ) {
				ResponsiveSitesAdmin.filter_array.push( $filter_name );
			}

			if( $( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]:checked' ).length ) {
				$( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]' ).removeClass('active');
				$( '.responsive-sites__filter-wrap-checkbox input[name=responsive-sites-radio]:checked' ).addClass('active');
			}
			var $filter_type = $( '.responsive-sites__filter-wrap-checkbox .checkbox.active' ).val();

			if ( '' != $filter_type ) {
				ResponsiveSitesAdmin.filter_array.push( $filter_type );
			}

			ResponsiveSitesAdmin._closeFilter();

			var page_builder_slug = $(this).attr('data-page-builder') || '';
			var page_builder_title = $(this).find('.title').text() || '';

			if( page_builder_title ) {
				$('.selected-page-builder').find('.page-builder-title').text( page_builder_title );
			}

			if( $('.page-builders [data-page-builder="'+page_builder_slug+'"]').length ) {
				$('.page-builders [data-page-builder="'+page_builder_slug+'"]').siblings().removeClass('active');
				$('.page-builders [data-page-builder="'+page_builder_slug+'"]').addClass('active');
			}

			$( '#wp-filter-search-input' ).trigger( 'keyup' );
		},

		_addAutocomplete: function() {

			var sites = responsiveSitesAdmin.default_page_builder_sites || [];
			var strings = [];

			// Add site title's in autocomplete.
			for( site_id in sites ) {

				var title = ResponsiveSitesAdmin._unescape( sites[ site_id ]['title'] );

				title = title.toLowerCase().replace('&#8211;', '-' );

				strings.push( title );
			}

			ResponsiveSitesAdmin.autocompleteTags = strings;
		},

		_autocomplete: function() {

			var strings = ResponsiveSitesAdmin.autocompleteTags;
			strings = _.uniq( strings );
			strings = _.sortBy( strings );

			$( "#wp-filter-search-input" ).autocomplete({
				appendTo: ".responsive-sites-autocomplete-result",
				classes: {
					"ui-autocomplete": "responsive-sites-auto-suggest"
				},
				source: function(request, response) {
					var results = $.ui.autocomplete.filter(strings, request.term);

					// Show only 10 results.
					response(results.slice(0, 15));
				},
				open: function( event, ui ) {
					$('.search-form').addClass( 'searching' );
				},
				close: function( event, ui ) {
					$('.search-form').removeClass( 'searching' );
				}
			});

			$( "#wp-filter-search-input" ).focus();
		},

		_show_search_term: function() {
			var search_term = $(this).text() || '';
			$('#wp-filter-search-input').val( search_term );
			$('#wp-filter-search-input').trigger( 'keyup' );
		},

	};

	/**
	 * Initialize ResponsiveSitesAdmin
	 */
	$(
		function(){
			ResponsiveSitesAdmin.init();
		}
	);

})( jQuery );
