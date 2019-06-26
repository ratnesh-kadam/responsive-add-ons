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
					ResponsiveSitesAdmin._log_message( 'Importing Content.. ' + progress );
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
		widgets_data: '',

		templateData: {},

		site_customizer_data: '',

		required_plugins: '',

		xml_path         : '',
		wpforms_path	: '',
		import_start_time  : '',
		import_end_time    : '',

		init: function()
		{
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
			$( document ).on( 'click'                     , '.import-demo-data', ResponsiveSitesAdmin._importDemo );
			$( document ).on( 'click'                     , '.theme-browser .theme-screenshot, .theme-browser .more-details, .theme-browser .install-theme-preview', ResponsiveSitesAdmin._preview );
			$( document ).on( 'click'                     , '.close-full-overlay', ResponsiveSitesAdmin._closeFullOverlay );
			$( document ).on( 'click', '.responsive-demo-import-options', ResponsiveSitesAdmin._importSiteOptionsScreen );
			$( document ).on( 'click', '.responsive-ready-sites-tooltip-icon', ResponsiveSitesAdmin._toggle_tooltip );
			$( document ).on( 'click', '.responsive-ready-site-import', ResponsiveSitesAdmin._importTest );

			$( document ).on( 'responsive-ready-sites-install-start'       , ResponsiveSitesAdmin._process_import );

			$( document ).on( 'responsive-ready-sites-import-set-site-data-done'   		, ResponsiveSitesAdmin._resetData );
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

		_importTest: function( event ) {
			event.preventDefault();
			$( '.responsive-ready-site-import' ).addClass( 'updating-message installing' )
				.text( "Importing.." );
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
					}
				}
			)
				.done(
					function ( data ) {

						// Fail - Import In-Complete.
						if ( false === data.success ) {
							// log.
						} else {

							var date = new Date();

							ResponsiveSitesAdmin.import_end_time = new Date();
							var diff                             = ( ResponsiveSitesAdmin.import_end_time.getTime() - ResponsiveSitesAdmin.import_start_time.getTime() );

							var time    = '';
							var seconds = Math.floor( diff / 1000 );
							var minutes = Math.floor( seconds / 60 );
							var hours   = Math.floor( minutes / 60 );

							minutes = minutes - ( hours * 60 );
							seconds = seconds - ( minutes * 60 );

							if ( hours ) {
								time += hours + ' Hours ';
							}
							if ( minutes ) {
								time += minutes + ' Minutes ';
							}
							if ( seconds ) {
								time += seconds + ' Seconds';
							}

							var	output = '<h2>Done</h2>';
							output    += '<p>Your Ready site has been imported successfully in ' + time + '! Now go ahead, customize the text, images, and design to make it yours!</p>';
							output    += '<p><a class="button button-primary button-hero" href="' + responsiveSitesAdmin.siteURL + '" target="_blank">View Site <i class="dashicons dashicons-external"></i></a></p>';

							$( '.responsive-ready-sites-import-display' ).remove();
							$( '.result_preview' ).html( output );

							var data     = [{
								items: [
									{id: 1, name: "First Demo", responsive_site_url: "https://ccdemos.cyberchimps.com", featured_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testone", status: "publish"},
									{id: 2, name: "Second Demo", responsive_site_url: "https://ccdemos.cyberchimps.com", featured_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testtwo", status: "publish"},
									{id: 3, name: "Third Demo", responsive_site_url: "https://ccdemos.cyberchimps.com", featured_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testthree", status: "publish"}
								]
							}];
							var template = wp.template( 'responsive-sites-list' );

							jQuery( '#responsive-sites' ).show().html( template( data ) );
							// Pass - Import Complete.
						}
					}
				);
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
				responsive_demo_type: demoType,
				responsive_demo_url: demoURL,
				demo_api: apiURL,
				screenshot: screenshot,
				demo_name: demo_name,
				slug: demo_slug,
				required_plugins: JSON.stringify( requiredPlugins ),
				responsive_site_options: responsiveSiteOptions,
			}];
			$( '#responsive-ready-sites-import-options' ).append( template( templateData[0] ) );
			$( '.theme-install-overlay' ).css( 'display', 'block' );
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
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( "Importing WPForms....." );
					},
				}
			)
				.done(
					function ( forms){
						if (false === forms.success) {
							// log.
						} else {
							ResponsiveSitesAdmin._log_message( "WPForms Imported Successfully" );
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
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						action	: 'responsive-ready-sites-import-customizer-settings',
						site_customizer_data : ResponsiveSitesAdmin.site_customizer_data,
					},
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( 'Importing Customizer Data.....' );
					},
				}
			)
				.done(
					function ( forms){
						if (false === forms.success) {
							// log.
						} else {
							ResponsiveSitesAdmin._log_message( 'Customizer Setting Imported' );
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
						options_data : ResponsiveSitesAdmin.options_data,
					},
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( 'Importing Options..' );
					},
				}
			)
				.fail(
					function( jqXHR ){
						// log message.
					}
				)
				.done(
					function ( options_data ) {

						// Fail - Import Site Options.
						if ( false === options_data.success ) {
							ResponsiveSitesAdmin._log_message( options_data );
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
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( 'Importing Widgets..' );
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_message( jqXHR.status + ' ' + jqXHR.responseText, true );
					}
				)
				.done(
					function ( widgets_data ) {

						if ( false === widgets_data.success ) {
							ResponsiveSitesAdmin._log_message( widgets_data.data );

						} else {

							ResponsiveSitesAdmin._log_message( 'Imported Widgets!' );
							$( document ).trigger( 'responsive-ready-sites-import-widgets-done' );
						}
						$( document ).trigger( 'responsive-ready-sites-import-widgets-done' );
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

			// First Install Bulk.
			if ( not_installed.length > 0 ) {
				ResponsiveSitesAdmin._installAllPlugins( not_installed );
			}

			// Second Activate Bulk.
			if ( activate_plugins.length > 0 ) {
				ResponsiveSitesAdmin._activateAllPlugins( activate_plugins );
			}

			$( document ).trigger( 'responsive-ready-sites-install-required-plugins-done' );

		},

		/**
		 * Individual Site Preview
		 *
		 * On click on image, more link & preview button.
		 */
		_preview: function( event ) {

			event.preventDefault();

			var site_id = $( this ).parents( '.site-single' ).data( 'demo-id' ) || '';

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

			var demoId                = anchor.data( 'demo-id' ) || '',
				apiURL                = anchor.data( 'demo-api' ) || '',
				demoType              = anchor.data( 'demo-type' ) || '',
				demoURL               = anchor.data( 'demo-url' ) || '',
				screenshot            = anchor.data( 'screenshot' ) || '',
				demo_name             = anchor.data( 'demo-name' ) || '',
				demo_slug             = anchor.data( 'demo-slug' ) || '',
				requiredPlugins       = anchor.data( 'required-plugins' ) || '',
				responsiveSiteOptions = anchor.find( '.responsive-site-options' ).val() || '';

			var template = wp.template( 'responsive-ready-site-preview' );

			templateData = [{
				id: demoId,
				responsive_demo_type: demoType,
				responsive_demo_url: demoURL,
				demo_api: apiURL,
				screenshot: screenshot,
				demo_name: demo_name,
				slug: demo_slug,
				required_plugins: JSON.stringify( requiredPlugins ),
				responsive_site_options: responsiveSiteOptions,
			}];
			$( '#responsive-ready-site-preview' ).append( template( templateData[0] ) );
			$( '.theme-install-overlay' ).css( 'display', 'block' );
		},

		/**
		 * Activate All Plugins.
		 */
		_activateAllPlugins: function( activate_plugins ) {

			ResponsiveSitesAdmin._log_message( 'Activating Required Plugins..' );

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

									// Enable Demo Import Button.
								} else {
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

			ResponsiveSitesAdmin._log_message( 'Installing Required Plugins..' );

			$.each(
				not_installed,
				function(index, single_plugin) {

					ResponsiveSitesAdmin._log_message( 'Installing Plugin - ' + ResponsiveSitesAdmin.ucwords( single_plugin.name ) );

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
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					dataType: 'json',
					data : {
						action  : 'responsive-ready-sites-import-xml',
						xml_path : ResponsiveSitesAdmin.xml_path,
					},
					beforeSend: function () {
						$( '.cybershimpssite-import-process-wrap' ).show();
						ResponsiveSitesAdmin._log_message( 'Importing XML data' );
					},
				}
			)
				.done(
					function ( xml_data ) {

						// 2. Fail - Import XML Data.
						if ( false === xml_data.success ) {
							// log.
						} else {

							// 2. Pass - Import XML Data.

							// Import XML though Event Source.
							wxrImport.data = xml_data.data;
							wxrImport.render();

							$( '.current-importing-status-description' ).html( '' ).show();

							$( '.responsive-ready-sites-result-preview .inner' ).append( '<div class="responsive-ready-sites-import-process-wrap"><progress class="responsive-ready-sites-import-process" max="100" value="0"></progress></div>' );

							var evtSource       = new EventSource( wxrImport.data.url );
							evtSource.onmessage = function ( message ) {
								var data = JSON.parse( message.data );
								switch ( data.action ) {
									case 'updateDelta':

										wxrImport.updateDelta( data.type, data.delta );
										break;

									case 'complete':
										evtSource.close();

										document.getElementsByClassName( "cybershimps-sites-import-process" ).value = '100';
										$( '.cybershimps-sites-import-process-wrap' ).hide();

										$( document ).trigger( 'responsive-ready-sites-import-xml-done' );

										break;
								}
							};
							evtSource.addEventListener(
								'log',
								function ( message ) {
									var data    = JSON.parse( message.data );
									var message = data.message || '';
									if ( message && 'info' === data.level ) {
										message = message.replace(
											/"/g,
											function(letter) {
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

			$( '.responsive-ready-sites-result-preview' ).show();
			var output = '<div class="current-importing-status-title"></div><div class="current-importing-status-description"></div>';
			$( '.current-importing-status' ).html( output );

			$( document ).trigger( 'responsive-ready-sites-install-start' );

		},

		_log_message: function( data, append ) {

			var markup = '<p>' + data + '</p>';
			if (typeof data == 'object' ) {
				var markup = '<p>' + JSON.stringify( data ) + '</p>';
			}

			if ( append ) {
				$( '.current-importing-status-title' ).append( markup );
			} else {
				$( '.current-importing-status-title' ).html( markup );
			}
		},

		/**
		 * Import Process Starts
		 *
		 * @since 1.0.0
		 * @method _process_import
		 */
		_process_import: function() {

			var apiURL = 'http://127.0.0.1:8081/astrademo/test.json';
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
						'action'  : 'responsive-ready-sites-import-set-site-data',
						'api_url' : apiURL,
					},
				}
			)
				.done(
					function ( demo_data ) {

						// Check is site imported recently and set flag.

						// 1. Fail - Request Site Import.
						if ( false === demo_data.success ) {

						} else {

							ResponsiveSitesAdmin.xml_path             = encodeURI( demo_data.data['xml_path'] ) || '';
							ResponsiveSitesAdmin.wpforms_path         = encodeURI( demo_data.data['wpforms_path'] ) || '';
							ResponsiveSitesAdmin.site_customizer_data = JSON.stringify( demo_data.data['site_customizer_data'] ) || '';
							ResponsiveSitesAdmin.required_plugins     = JSON.stringify( demo_data.data['required_plugins'] ) || '';
							ResponsiveSitesAdmin.widgets_data         = JSON.stringify( demo_data.data['site_widgets_data'] ) || '';

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
									.fail(
										function( jqXHR ){

										}
									)
									.done(
										function ( response ) {
											var required_plugins = response.data['required_plugins'] || '';

											responsiveSitesAdmin.required_plugins = required_plugins;
											ResponsiveSitesAdmin._bulkPluginInstallActivate();
											$( document ).trigger( 'responsive-ready-sites-import-set-site-data-done' );
										}
									);

							} else {
								// log message.
							}
						}

					}
				);
		},

		_resetData: function( event ) {
			event.preventDefault();

			// if ( $( '.responsive-ready-sites-reset-data' ).find('.checkbox').is(':checked') ) {
			$( document ).trigger( 'responsive-ready-sites-reset-data' );
			// } else {
			// $(document).trigger( 'responsive-ready-sites-reset-data-done' );
			// }
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

			event.preventDefault();

			// Reset not installed plugins list.
			var pluginsList                                    = responsiveSitesAdmin.required_plugins.notinstalled;
			responsiveSitesAdmin.required_plugins.notinstalled = ResponsiveSitesAdmin._removePluginFromQueue( response.slug, pluginsList );

			// WordPress adds "Activate" button after waiting for 1000ms. So we will run our activation after that.
			setTimeout(
				function() {

						ResponsiveSitesAdmin._log_message( 'Installing Plugin - ' + ResponsiveSitesAdmin.ucwords( response.slug ) );

						$.ajax(
							{
								url: responsiveSitesAdmin.ajaxurl,
								type: 'POST',
								data: {
									'action'            : 'responsive-ready-sites-required-plugin-activate',
									'init'              : response.slug + '/' + response.slug + '.php',
								},
							}
						)
						.done(
							function (result) {

								if ( result.success ) {
									 var pluginsList = responsiveSitesAdmin.required_plugins.inactive;

									 ResponsiveSitesAdmin._log_message( 'Installed Plugin - ' + ResponsiveSitesAdmin.ucwords( 'Testing' ) );

									 // Reset not installed plugins list.
									 responsiveSitesAdmin.required_plugins.inactive = ResponsiveSitesAdmin._removePluginFromQueue( response.slug, pluginsList );

								}
							}
						);

				},
				1200
			);

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
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( 'Processing Customizer Settings Backup..' );
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_message( jqXHR.status + ' ' + jqXHR.responseText, true );
					}
				)
				.done(
					function ( data ) {

						// 1. Pass - Import Customizer Options.
						ResponsiveSitesAdmin._log_message( 'Customizer Settings Backup Done..' );

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

			ResponsiveSitesAdmin._log_message( 'Installing Plugin - ' );

		},

		_reset_customizer_data: function() {
			$.ajax(
				{
					url  : responsiveSitesAdmin.ajaxurl,
					type : 'POST',
					data : {
						action : 'responsive-ready-sites-reset-customizer-data'
					},
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( 'Reseting Customizer Data..' );
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_message( jqXHR.status + ' ' + jqXHR.responseText + ' ' + jqXHR.statusText, true );
					}
				)
				.done(
					function ( data ) {
						ResponsiveSitesAdmin._log_message( 'Complete Resetting Customizer Data..' );
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
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( 'Reseting Site Options..' );
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_message( jqXHR.status + ' ' + jqXHR.responseText + ' ' + jqXHR.statusText, true );
					}
				)
				.done(
					function ( data ) {
						ResponsiveSitesAdmin._log_message( 'Complete Reseting Site Options..' );
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
					beforeSend: function() {
						ResponsiveSitesAdmin._log_message( 'Reseting Widgets..' );
					},
				}
			)
				.fail(
					function( jqXHR ){
						ResponsiveSitesAdmin._log_message( jqXHR.status + ' ' + jqXHR.responseText + ' ' + jqXHR.statusText, true );
					}
				)
				.done(
					function ( data ) {
						ResponsiveSitesAdmin._log_message( 'Complete Reseting Widgets..' );
						$( document ).trigger( 'responsive-ready-sites-reset-widgets-data-done' );
					}
				);
		},

		/**
		 * Full Overlay
		 */
		_closeFullOverlay: function (event) {
			event.preventDefault();

			$( 'body' ).removeClass( 'importing-site' );
			$( '.theme-install-overlay' ).css( 'display', 'none' );
			$( '.theme-install-overlay' ).remove();
			$( '.responsive-ready-sites-advanced-options-wrap' ).hide();
			$( '#responsive-sites' ).show();
			$( '.theme-preview-on' ).removeClass( 'theme-preview-on' );
		},

		_reset_posts: function() {

			if ( ResponsiveSitesAdmin.site_imported_data['reset_posts'].length ) {

				ResponsiveSitesAdmin.reset_remaining_posts = ResponsiveSitesAdmin.site_imported_data['reset_posts'].length;

				$.each(
					ResponsiveSitesAdmin.site_imported_data['reset_posts'],
					function(index, post_id) {

						ResponsiveSitesAdmin._log_message( 'Deleting Posts..' );

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

									ResponsiveSitesAdmin._log_message( 'Deleting Post ' + ResponsiveSitesAdmin.reset_processed_posts + ' of ' + ResponsiveSitesAdmin.site_imported_data['reset_posts'].length + '<br/>' + result.data );

									ResponsiveSitesAdmin.reset_remaining_posts -= 1;
									if ( 0 == ResponsiveSitesAdmin.reset_remaining_posts ) {
										$( document ).trigger( 'responsive-ready-sites-delete-posts-done' );
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
				$( document ).trigger( 'responsive-ready-sites-reset-data-done' );
			}
		},

		_reset_wp_forms: function() {

			if ( ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'].length ) {
				ResponsiveSitesAdmin.reset_remaining_wp_forms = ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'].length;

				$.each(
					ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'],
					function(index, post_id) {
						ResponsiveSitesAdmin._log_message( 'Deleting WP Forms..' );
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

									ResponsiveSitesAdmin._log_message( 'Deleting Form ' + ResponsiveSitesAdmin.reset_processed_wp_forms + ' of ' + ResponsiveSitesAdmin.site_imported_data['reset_wp_forms'].length + '<br/>' + result.data );

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
						ResponsiveSitesAdmin._log_message( 'Deleting Terms..' );
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

									ResponsiveSitesAdmin._log_message( 'Deleting Term ' + ResponsiveSitesAdmin.reset_processed_terms + ' of ' + ResponsiveSitesAdmin.site_imported_data['reset_terms'].length + '<br/>' + result.data );

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
