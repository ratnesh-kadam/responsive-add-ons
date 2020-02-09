/**
 * Responsive Ready Sites importer events
 *
 * @since 1.0.0
 * @package Responsive Ready Sites
 */

/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 *
 * @since 1.0.0
 */
var ResponsiveSitesAjaxQueue = (function() {

	var requests = [];

	return {

		/**
		 * Add AJAX request
		 *
		 * @since 1.0.0
		 */
		add:  function(opt) {
			requests.push( opt );
		},

		/**
		 * Remove AJAX request
		 *
		 * @since 1.0.0
		 */
		remove:  function(opt) {
			if ( jQuery.inArray( opt, requests ) > -1 ) {
				requests.splice( $.inArray( opt, requests ), 1 );
			}
		},

		/**
		 * Run / Process AJAX request
		 *
		 * @since 1.0.0
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
		 *
		 * @since 1.0.0
		 */
		stop:  function() {

			requests = [];
			clearTimeout( this.tid );
		}
	};

}());

(function( $ ) {

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

		templateData: {},

		site_customizer_data: '',

		required_plugins: '',

		xml_path         : '',
		wpforms_path	: '',
		import_start_time  : '',
		import_end_time    : '',

		init: function()
		{
			this._resetPagedCount();
			this._bind();
		},

		/**
		 * Binds events for the Responsive Ready Sites.
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
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
			$( document ).on( 'responsive-ready-sites-reset-data'							, ResponsiveSitesAdmin._backup_before_rest_options );
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
			$( document ).on( 'responsive-ready-sites-import-options-done' , ResponsiveSitesAdmin._importEnd );
			$( document ).on( 'wp-plugin-installing'      , ResponsiveSitesAdmin._pluginInstalling );
			$( document ).on( 'wp-plugin-install-success' , ResponsiveSitesAdmin._installSuccess );
		},

		/**
		 * Do Nothing.
		 */
		_doNothing: function( event ) {
			event.preventDefault();
		},

		/**
		 * Import Complete.
		 */
		_importEnd: function( event ) {

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

							// Pass - Import Complete.
						}
					}
				);
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
		 * Import Site options Screen
		 */
		_importSiteOptionsScreen: function(event) {
			event.preventDefault();

			var site_id = $( this ).data( 'demo-id' ) || '';

			var self = $( this ).parents( '.responsive-ready-site-preview' );

			$( '#responsive-ready-site-preview' ).hide();

			$( '#responsive-ready-sites-import-options' ).show();

			var demoId                = self.data( 'demo-id' ) || '',
				apiURL                = self.data( 'demo-api' ) || '',
				demoType              = self.data( 'demo-type' ) || '',
				demoURL               = self.data( 'demo-url' ) || '',
				screenshot            = self.data( 'screenshot' ) || '',
				demo_name             = self.data( 'demo-name' ) || '',
				demo_slug             = self.data( 'demo-slug' ) || '',
				requiredPlugins       = self.data( 'required-plugins' ) || '',
				responsiveSiteOptions = self.find( '.responsive-site-options' ).val() || '';

			var template = wp.template( 'responsive-ready-sites-import-options-page' );

			templateData = [{
				id: demoId,
				demo_type: demoType,
				demo_url: demoURL,
				demo_api: apiURL,
				screenshot: screenshot,
				name: demo_name,
				slug: demo_slug,
				required_plugins: JSON.stringify( requiredPlugins ),
				responsive_site_options: responsiveSiteOptions,
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

		_toggle_tooltip: function( event ) {
			event.preventDefault();
			var tip_id = $( this ).data( 'tip-id' ) || '';
			if ( tip_id && $( '#' + tip_id ).length ) {
				$( '#' + tip_id ).toggle();
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
				ResponsiveSitesAdmin._ready_for_import_site();
			}

		},

		_ready_for_import_site: function () {
			var notinstalled = responsiveSitesAdmin.required_plugins.notinstalled || 0;
			var inactive     = responsiveSitesAdmin.required_plugins.inactive || 0;

			if ( ResponsiveSitesAdmin._areEqual( notinstalled.length, inactive.length ) ) {
				$( document ).trigger( 'responsive-ready-sites-install-and-activate-required-plugins-done' );
			}
		},

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

			var demoId                             = anchor.data( 'demo-id' ) || '',
				apiURL                             = anchor.data( 'demo-api' ) || '',
				demoURL                            = anchor.data( 'demo-url' ) || '',
				screenshot                         = anchor.data( 'screenshot' ) || '',
				demo_name                          = anchor.data( 'demo-name' ) || '',
				demo_slug                          = anchor.data( 'demo-slug' ) || '',
				requiredPlugins                    = anchor.data( 'required-plugins' ) || '',
				responsiveSiteOptions              = anchor.find( '.responsive-site-options' ).val() || '',
				demo_type                          = anchor.data( 'demo-type' ) || '',
				isResponsiveAddonsProInstalled     = ResponsiveSitesAdmin._checkResponsiveAddonsProInstalled(),
				isResponsiceAddonsProLicenseActive = ResponsiveSitesAdmin._checkRespomsiveAddonsProLicenseActive();

			var template = wp.template( 'responsive-ready-site-preview' );

			templateData = [{
				id: demoId,
				demo_url: demoURL + '/?utm_source=free-to-pro&utm_medium=responsive-ready-site-importer&utm_campaign=responsive-pro&utm_content=preview',
				demo_api: apiURL,
				screenshot: screenshot,
				name: demo_name,
				slug: demo_slug,
				required_plugins: JSON.stringify( requiredPlugins ),
				responsive_site_options: responsiveSiteOptions,
				demo_type: demo_type,
				is_responsive_addons_pro_installed: isResponsiveAddonsProInstalled,
			}];
			$( '#responsive-ready-site-preview' ).append( template( templateData[0] ) );
			$( '.theme-install-overlay' ).css( 'display', 'block' );

		},

		/**
		 * Check if Responsive Addons Pro is installed or not
		 */
		_checkRespomsiveAddonsProLicenseActive: function() {
			var is_pro_license_active;
			$.ajax(
				{
					url: responsiveSitesAdmin.ajaxurl,
					async: false,
					type : 'POST',
					dataType: 'json',
					data: {
						'action': 'check-responsive-add-ons-pro-license-active',
					}
				}
			)
				.done(
					function ( response ) {
						is_pro_license_active = response;
					}
				);

			if (is_pro_license_active.success) {
				return true;
			} else {
				return false;
			}
		},

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
							},
							success: function( result ){

								if ( result.success ) {

									var pluginsList = responsiveSitesAdmin.required_plugins.inactive;

									// Reset not installed plugins list.
									responsiveSitesAdmin.required_plugins.inactive = ResponsiveSitesAdmin._removePluginFromQueue( single_plugin.slug, pluginsList );

									ResponsiveSitesAdmin._ready_for_import_site();

								}
							}
						}
					);
				}
			);
			ResponsiveSitesAjaxQueue.run();
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
		 * Fires when a nav item is clicked.
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _importDemo
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
		 * Import Process Starts
		 *
		 * @since 1.0.0
		 * @method _process_import
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

							$( document ).trigger( 'responsive-ready-sites-import-set-site-data-done' );
						}
					}
				);
		},

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
				$( document ).trigger( 'responsive-ready-sites-install-and-activate-required-plugins-done' );
			}
		},

		_resetData: function( event ) {
			event.preventDefault();

			$( '.responsive-ready-sites-reset-data .responsive-ready-sites-tooltip-icon' ).addClass( 'processing-import' );
			$( document ).trigger( 'responsive-ready-sites-reset-data' );
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

		/**
		 * Install Success
		 */
		_installSuccess: function( event, response ) {

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
										ResponsiveSitesAdmin._ready_for_import_site();
									}
								}
							);

					},
					1200
				);
			}
		},

		_backup_before_rest_options: function() {
			ResponsiveSitesAdmin._backupOptions( 'responsive-ready-sites-backup-settings-before-reset-done' );
			ResponsiveSitesAdmin.backup_taken = true;
		},

		_recheck_backup_options: function() {
			ResponsiveSitesAdmin._backupOptions( 'responsive-ready-sites-backup-settings-done' );
			ResponsiveSitesAdmin.backup_taken = true;
		},

		_backupOptions: function( trigger_name ) {
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-backup-settings',
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
		 * Installing Plugin
		 */
		_pluginInstalling: function(event, args) {
			event.preventDefault();
			$( '.responsive-ready-sites-import-plugins .responsive-ready-sites-tooltip-icon' ).addClass( 'processing-import' );

		},

		_reset_customizer_data: function() {
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-customizer-data'
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

		_reset_site_options: function() {
			// Site Options.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-site-options'
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

		_reset_widgets_data: function() {
			// Widgets.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-widgets-data'
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
		 * Full Overlay
		 */
		_closeFullOverlay: function (event) {
			event.preventDefault();
			location.reload();
		},

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
								},
								success: function( result ){

									if ( ResponsiveSitesAdmin.reset_processed_posts < ResponsiveSitesAdmin.site_imported_data['reset_posts'].length ) {
										ResponsiveSitesAdmin.reset_processed_posts += 1;
									}

									ResponsiveSitesAdmin.reset_remaining_posts -= 1;
									if ( 0 == ResponsiveSitesAdmin.reset_remaining_posts ) {
										$( document ).trigger( 'responsive-ready-sites-delete-posts-done' );
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
				$( document ).trigger( 'responsive-ready-sites-delete-posts-done' );
				$( '.responsive-ready-sites-reset-data .responsive-ready-sites-tooltip-icon' ).addClass( 'processed-import' );
				$( document ).trigger( 'responsive-ready-sites-reset-data-done' );
			}
		},

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

		_reset_everything: function () {

			// reset customizer data.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-customizer-data'
					},
				}
			)
				.fail(
					function( jqXHR ){
						// display message on fail.
					}
				)
				.done(
					function ( data ) {
						// reverted customizer data.
					}
				);

			// reset options data.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-site-options'
					},
				}
			)
				.fail(
					function( jqXHR ){
						// display message on fail.
					}
				)
				.done(
					function ( data ) {
						// options are reverted.
					}
				);

			// Widgets.
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-widgets-data'
					},
				}
			)
				.fail(
					function( jqXHR ){
						// display message on fail.
					}
				)
				.done(
					function ( data ) {
						// widgets data is reverted.
					}
				);

			// delete posts.
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
								},
								success: function( result ){

									if ( ResponsiveSitesAdmin.reset_processed_posts < ResponsiveSitesAdmin.site_imported_data['reset_posts'].length ) {
										ResponsiveSitesAdmin.reset_processed_posts += 1;
									}

									ResponsiveSitesAdmin.reset_remaining_posts -= 1;
								}
							}
						);
					}
				);
				ResponsiveSitesAjaxQueue.run();

			}

			// delete wp-forms.
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
								},
								success: function( result ){

									if ( ResponsiveSitesAdmin.reset_processed_wp_forms < ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'].length ) {
										ResponsiveSitesAdmin.reset_processed_wp_forms += 1;
									}

									ResponsiveSitesAdmin.reset_remaining_wp_forms -= 1;
								}
							}
						);
					}
				);
				ResponsiveSitesAjaxQueue.run();

			}

			// delete terms.
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
								},
								success: function( result ){
									if ( ResponsiveSitesAdmin.reset_processed_terms < ResponsiveSitesAdmin.site_imported_data['reset_terms'].length ) {
										ResponsiveSitesAdmin.reset_processed_terms += 1;
									}

									ResponsiveSitesAdmin.reset_remaining_terms -= 1;
								}
							}
						);
					}
				);
				ResponsiveSitesAjaxQueue.run();

			}
		},

		_is_responsive_theme_active: function() {
			$.ajax(
				{
					url: responsiveSitesAdmin.ajaxurl,
					type: 'POST',
					data: {
						'action': 'responsive-is-theme-active',
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
