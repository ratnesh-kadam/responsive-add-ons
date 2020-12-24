<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://cyberchimps.com/
 * @since      1.0.0
 *
 * @package    Responsive Ready Sites
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="responsive-ready-site-preview"></div>
<div id="responsive-ready-sites-import-options"></div>
<div id="responsive-ready-sites-admin-page">
	<div class="responsive-sites-header">
		<span class="ready-site-list-title"><?php esc_html_e( 'Responsive Ready Websites', 'responsive-addons' ); ?></span>
		<p class="ready-site-list-intro"><?php esc_html_e( 'Build your Responsive website in 3 simple steps - import a ready website, change content and launch.', 'responsive-addons' ); ?></p>
	</div>
	<div class="theme-browser rendered">
		<div id="responsive-sites" class="themes wp-clearfix"></div>
	</div>
	<div class="spinner-wrap">
		<span class="spinner"></span>
	</div>

</div>

<?php
/**
 * TMPL - List
 */
?>

<script type="text/template" id="tmpl-responsive-sites-list">

	<# if ( data.items.length ) { #>
	
	<# for ( key in data.items ) { #>
		<div class="theme inactive ra-site-single {{ data.items[ key ].status }}" tabindex="0" aria-describedby="responsive-theme-action responsive-theme-name"
			 data-demo-id="{{{ data.items[ key ].id }}}"
			 data-demo-url="{{{ data.items[ key ]['site_url'] }}}"
			 data-demo-slug="{{{  data.items[ key ].slug }}}"
			 data-demo-name="{{{  data.items[ key ].title.rendered }}}"
			 data-active-site="{{{  data.active_site }}}"
			 data-demo-type="{{{ data.items[ key ].demo_type }}}"
			 data-wpforms-path="{{{ data.items[ key ].wpforms_path }}}"
			 data-allow-pages="{{{ data.items[ key ].allow_pages }}}"
			 data-check_plugins_installed="{{{ data.items[ key ].check_plugins_installed }}}"
			 data-screenshot="{{{ data.items[ key ]['featured_image_url'] }}}"
			 data-required-plugins="{{ JSON.stringify(data.items[ key ]['required_plugins']) }}"
			 data-pages="{{ JSON.stringify(data.items[ key ]['pages'] )}}"
			 data-required-pro-plugins="{{ JSON.stringify(data.items[ key ]['required_pro_plugins']) }}">
			<input type="hidden" class="site_options_data" value="{{ JSON.stringify(data.items[ key ][ 'site_options_data' ]) }}">
		<div class="inner">
					<span class="site-preview" data-href="{{ data.items[ key ]['responsive-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="data title">
						<div class="theme-screenshot" style="background-image: url('{{ data.items[ key ]['featured_image_url'] }}');"></div>
					</span>
			<span class="demo-type {{{ data.items[ key ].demo_type }}}">{{{ data.items[ key ].demo_type }}}</span>
			<# if (data.items[ key ].slug === data.active_site ) { #>
				<span class="current_active_site"><?php esc_html_e( 'Currently Active', 'responsive-addons' ); ?></span>
			<# } #>
			<div class="theme-id-container">
				<h3 class="theme-name" id="responsive-theme-name">{{{ data.items[ key ].title.rendered }}}</h3>
				<div class="theme-actions">
					<button class="button-primary button preview install-theme-preview"><?php esc_html_e( 'Preview', 'responsive-addons' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<# } #>
	<# } else { #>
	<p class="no-themes" style="display:block;">
		<?php esc_html_e( 'No Demos found', 'responsive-addons' ); ?>
		<span class="description">
				<?php
				/* translators: %1$s External Link */
				printf( esc_html__( 'No More Sites', 'responsive-addons' ) );
				?>
			</span>
	</p>
	<# } #>
</script>
<?php
/** Site suggestion block */
?>
<script type="text/template" id="tmpl-responsive-sites-suggestions">
	<div class="theme inactive ra-site-single responsive-sites-suggestions">
		<div class="inner">
			<p>
				<?php
				/* translators: %1$s External Link */
				printf( __( 'Can\'t find a Responsive Ready Site that suits your purpose ?<br><a target="_blank" href="%1$s">Suggest A Site</a>', 'responsive-addons' ), esc_url( 'mailto:support@cyberchimps.com?Subject=New%20Site%20Suggestion' ) );
				?>
			</p>
		</div>
	</div>
</script>
<?php
/** Single Demo Preview */
?>

<script type="text/template" id="tmpl-responsive-ready-site-preview">
	<div class="responsive-ready-site-preview theme-install-overlay wp-full-overlay collapsed"
		 data-demo-id="{{{data.id}}}"
		 data-demo-url="{{{data.demo_url}}}"
		 data-demo-api="{{{data.demo_api}}}"
		 data-demo-name="{{{data.name}}}"
		 data-active-site="{{{data.active_site}}}"
		 data-demo-type="{{{data.demo_type}}}"
		 data-wpforms-path="{{{data.wpforms_path}}}"
		 data-check_plugins_installed="{{{data.check_plugins_installed}}}"
		 data-demo-slug="{{{data.slug}}}"
		 data-screenshot="{{{data.screenshot}}}"
		 data-required-plugins="{{data.required_plugins}}"
		 data-required-pro-plugins="{{data.required_pro_plugins}}"
		 data-pages="{{data.pages}}">
		<input type="hidden" class="responsive-site-options" value="{{data.site_options_data}}" >
		<div class="wp-full-overlay-header">
			<div>
				<span class="responsive-site-demo-name">{{data.name}}</span>
				<# if ( data.demo_type == "free" || ( data.is_responsive_addons_pro_installed && data.is_responsive_addons_pro_license_active ) ) { #>

				<a class="button button-primary responsive-addons responsive-demo-import-options-{{{data.demo_type}}}" href="#"><?php esc_html_e( 'Import Site', 'responsive-addons' ); ?></a>

					<# if ( data.allow_pages ) { #>

					<a class="button button-primary responsive-addons responsive-page-import-options-{{{data.demo_type}}}" href="#"><?php esc_html_e( 'Import Template', 'responsive-addons' ); ?></a>

					<# } #>
				<# } else { #>

				<a class="button button-primary responsive-addons responsive-buy-pro" href="https://cyberchimps.com/responsive-go-pro/?utm_source=free-to-pro&utm_medium=responsive-add-ons&utm_campaign=responsive-pro&utm_content=preview-ready-site" target="_blank"><?php esc_html_e( 'Buy Responsive Pro', 'responsive-addons' ); ?></a>

				<# } #>
				<button class="close-full-overlay responsive-addons"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'responsive-addons' ); ?></span></button>
			</div>
		</div>
		<div class="wp-full-overlay-main">
			<iframe src="{{{data.demo_url}}}" title="<?php esc_attr_e( 'Preview', 'responsive-addons' ); ?>"></iframe>
		</div>
	</div>
</script>

<?php
/** Theme Import Options Page */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-options-page">
		<div class="responsive-ready-sites-advanced-options-wrap wp-full-overlay collapsed"
			 data-demo-id="{{{data.id}}}"
			 data-demo-url="{{{data.demo_url}}}"
			 data-demo-api="{{{data.demo_api}}}"
			 data-demo-name="{{{data.name}}}"
			 data-demo-type="{{{data.demo_type}}}"
			 data-demo-slug="{{{data.slug}}}"
			 data-screenshot="{{{data.screenshot}}}"
			 data-required-plugins="{{data.required_plugins}}"
			 data-pages="{{data.pages}}"
			 data-required-pro-plugins="{{data.required_pro_plugins}}">
			<input type="hidden" class="responsive-site-options" value="{{data.site_options_data}}" >
			<input type="hidden" class="demo_site_id" value="{{{ data.id }}}">
			<div class="wp-full-overlay-header">
				<div>
					<span class="responsive-site-demo-name">{{data.name}}</span>
					<button class="close-full-overlay responsive-addons"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'responsive-addons' ); ?></span></button>
				</div>
			</div>
			<div class="wp-full-overlay-main">
				<div class="sites-import-process-errors" style="display: none">
					<div class="import-process-error">
						<div class="current-importing-status-error-title"></div>
					</div>
				</div>

				<div class="site-import-options">
					<div class="responsive-ready-sites-advanced-options">
						<h2>Importing {{data.demo_name}}</h2>
						<p><?php esc_html_e( 'Importing this ready site will &hellip;', 'responsive-addons' ); ?></p>
						<# if ( data.slug === data.active_site ) { #>
							<p><?php esc_html_e( 'This will delete previously imported site', 'responsive-addons' ); ?></p>
						<# } #>
						<ul class="responsive-ready-site-contents">
							<li class="responsive-ready-sites-import-plugins">
								<input type="checkbox" name="plugins" checked="checked" class="disabled checkbox" readonly>
								<strong><?php esc_html_e( 'Install Required Plugins', 'responsive-addons' ); ?></strong>
								<span class="responsive-ready-sites-tooltip-icon responsive-ready-sites-tooltip-plugins-settings" data-tip-id="responsive-ready-sites-tooltip-plugins-settings"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
								<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-plugins-settings" style="display: none;">
									<ul class="required-plugins-list"><span class="spinner is-active"></span></ul>
									<# if ( data.pro_plugins_flag ) { #>
										<div class="responsive-ready-sites-third-party-plugins-warning"><?php _e( 'This ready site requires third party Premium Plugins. you\'ll need to purchase, install and activate. Ignore this if installed already.', 'responsive-addons' ); ?></div>
										<ul class="required-third-party-plugins-list"><span class="is-active"></span></ul>
									<# } #>
								</div>
							</li>
							<li class="responsive-ready-sites-reset-data">
								<label>
									<input type="checkbox" name="reset" checked="checked" class="checkbox">
									<strong><?php esc_html_e( 'Delete Previous Import', 'responsive-addons' ); ?></strong>
								</label>
								<span class="responsive-ready-sites-tooltip-icon responsive-ready-sites-tooltip-reset-data" data-tip-id="responsive-ready-sites-tooltip-reset-data"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
								<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-reset-data" style="display: none;">
									<p><?php esc_html_e( 'Deletes previous import including customizer settings and content. Plugins are not deleted.', 'responsive-addons' ); ?></p>
								</div>
							</li>
							<li class="responsive-ready-sites-import-xml">
								<input type="checkbox" name="content" checked="checked" class="disabled checkbox" readonly>
								<strong><?php esc_html_e( 'Import Content', 'responsive-addons' ); ?></strong>
								<span class="responsive-ready-sites-tooltip-icon responsive-ready-sites-tooltip-site-content" data-tip-id="responsive-ready-sites-tooltip-site-content"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
								<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-site-content" style="display: none;">
									<p><?php esc_html_e( 'Imports sample pages, posts, images and menus. Depending on your internet speed this may take 2-10 minutes.', 'responsive-addons' ); ?></p>
								</div>
								<div class="responsive-ready-sites-import-process-wrap" style="display: none;">
									<progress class="responsive-ready-sites-import-process" max="100" value="0"></progress>
								</div>
							</li>
							<li class="responsive-ready-sites-import-customizer">
								<input type="checkbox" name="customizer" checked="checked" class="disabled checkbox" readonly>
								<strong><?php esc_html_e( 'Import Customizer Settings', 'responsive-addons' ); ?></strong>
								<span class="responsive-ready-sites-tooltip-icon responsive-ready-sites-tooltip-customizer-settings" data-tip-id="responsive-ready-sites-tooltip-customizer-settings"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
								<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-customizer-settings" style="display: none;">
									<p><?php esc_html_e( 'Imports sample customizer settings including site identity, typography, colors and other theme options.', 'responsive-addons' ); ?></p>
								</div>
								<div class="responsive-ready-sites-import-customizer-process-wrap" style="display: none;">
									<progress class="responsive-ready-sites-import-customizer-process" max="100" value="0"></progress>
								</div>
							</li>
						</ul>
					</div>
					<div class="responsive-ready-sites-import-button-wrap">
						<a class="button button-hero button-primary responsive-ready-site-import-{{{data.demo_type}}}" href="#">
							<?php esc_html_e( 'Import Site', 'responsive-addons' ); ?>
						</a>
					</div>
				</div>
				<div class="result_preview" style="display: none">
				</div>
			</div>
		</div>
</script>

<?php
/** Template Import Options Page */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-page-preview-page">
	<div class="responsive-ready-sites-advanced-options-wrap template-preview-page wp-full-overlay collapsed"
		 data-demo-api="{{{data.demo_api}}}"
		 data-demo-name="{{{data.name}}}"
		 data-screenshot="{{{data.screenshot}}}"
		 data-demo-type="{{{data.demo_type}}}"
		 data-wpforms-path="{{{data.wpforms_path}}}"
		 data-required-plugins="{{data.required_plugins}}"
		 data-required-pro-plugins="{{data.required_pro_plugins}}">
		<div class="wp-full-overlay-header">
			<div>
				<span class="responsive-site-demo-name">{{data.name}}</span>
				<button class="close-full-overlay responsive-addons"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'responsive-addons' ); ?></span></button>
			</div>
		</div>
		<div class="wp-full-overlay-main">
			<div class="sites-import-process-errors" style="display: none">
				<div class="import-process-error">
					<div class="current-importing-status-error-title"></div>
				</div>
			</div>

			<div class="theme-browser rendered">
				<div id="site-pages" class="themes wp-clearfix">
			<div class="single-site-wrap">
				<div class="single-site">
					<div class="single-site-preview-wrap">
						<div class="single-site-preview">
							<img class="theme-screenshot" data-src="" src="{{data.screenshot}}" />
						</div>
					</div>
					<div class="single-site-pages-wrap">
						<div class="responsive-pages-title-wrap">
							<span class="responsive-pages-title"><?php esc_html_e( 'Page Templates', 'responsive-addons' ); ?></span>
						</div>
						<div class="single-site-pages">
							<div id="single-pages">
								<# for (page_id in data.pages)  { #>
								<#
								var required_plugins = [];
								for( id in data.pages[page_id]['free_plugins']) {
									JSON.parse( data.required_plugins ).forEach( function( single_plugin ) {
										if ( data.pages[page_id]['free_plugins'][id] == single_plugin.slug ) {
											required_plugins.push( single_plugin );
										}
									}
								);
								}
								var required_pro_plugins = [];
								for( id in data.pages[page_id]['pro_plugins']) {
									JSON.parse( data.required_pro_plugins ).forEach( function( single_plugin ) {
										if ( data.pages[page_id]['pro_plugins'][id] == single_plugin.slug ) {
											required_pro_plugins.push( single_plugin );
										}
									}
								);
								}
								#>
								<div class="theme responsive-theme site-single" data-page-id="{{data.pages[page_id]['page_id']}}" data-required-pro-plugins="{{ JSON.stringify( required_pro_plugins )}}" data-required-plugins="{{ JSON.stringify( required_plugins )}}" data-includes-wp-forms="{{ data.pages[page_id]['includes_wp_forms'] }}" >
									<div class="inner">
										<#
										var featured_image_class = '';
										var featured_image = data.pages[page_id]['featured_image'] || '';
										if( '' === featured_image ) {
										featured_image = '<?php echo esc_url( RESPONSIVE_ADDONS_DIR . 'inc/assets/images/placeholder.png' ); ?>';
										featured_image_class = ' no-featured-image ';
										}

										var thumbnail_image = data.pages[page_id]['thumbnail-image-url'] || '';
										if( '' === thumbnail_image ) {
										thumbnail_image = featured_image;
										}
										#>
										<span class="site-preview" data-title="{{ data.pages[page_id]['page_title'] }}">
										<div class="theme-screenshot one loading {{ featured_image_class }}" data-src="{{ featured_image }}" data-featured-src="{{ featured_image }}" data-demo-type="{{ data.demo_type }}" style="background-image: url('{{ featured_image }}');"></div>
									</span>
										<div class="theme-id-container">
											<h3 class="theme-name">
												{{{ data.pages[page_id]['page_title'] }}}
											</h3>
										</div>
									</div>
								</div>
								<# } #>
							</div>
						</div>
					</div>
					<div class="single-site-footer">
						<div class="site-action-buttons-wrap">
							<a href="{{{data.demo_api}}}" class="button button-hero site-preview-button" target="_blank">Preview "{{data.name}}" Site <i class="dashicons dashicons-external"></i></a>
							<div class="site-action-buttons-right">
								<div style="margin-left: 5px;" class="button button-hero button-primary single-page-import-button-{{{ data.demo_type }}} disabled"><?php esc_html_e( 'Select Template', 'responsive-addons' ); ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
				</div>
			</div>
			<div class="result_preview" style="display: none">
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-responsive-ready-sites-import-single-page-options-page">
	<div class="responsive-ready-sites-advanced-options-wrap single-page-import-options-page wp-full-overlay collapsed"
		 data-page-id="{{{data.page_id}}}"
		 data-demo-api="{{{data.demo_api}}}"
		 data-includes-wp-forms="{{{data.includes_wp_forms}}}"
		 data-wpforms-path="{{{data.wpforms_path}}}"
		 data-required-plugins="{{ JSON.stringify( data.required_plugins )}}"
		 data-required-pro-plugins="{{ JSON.stringify( data.required_pro_plugins )}}">
		<div class="wp-full-overlay-header">
			<div>
				<span class="responsive-site-demo-name">{{data.name}}</span>
				<button class="close-full-overlay responsive-addons"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'responsive-addons' ); ?></span></button>
			</div>
		</div>
		<div class="wp-full-overlay-main">
			<div class="sites-import-process-errors" style="display: none">
				<div class="import-process-error">
					<div class="current-importing-status-error-title"></div>
				</div>
			</div>

			<div class="site-import-options">
				<div class="responsive-ready-sites-advanced-options">
					<h2>Importing {{data.demo_name}}</h2>
					<p><?php esc_html_e( 'Importing this ready template will &hellip;', 'responsive-addons' ); ?></p>
					<ul class="responsive-ready-site-contents">
						<li class="responsive-ready-sites-import-plugins">
							<input type="checkbox" name="plugins" checked="checked" class="disabled checkbox" readonly>
							<strong><?php esc_html_e( 'Install Required Plugins', 'responsive-addons' ); ?></strong>
							<span class="responsive-ready-sites-tooltip-icon responsive-ready-sites-tooltip-plugins-settings" data-tip-id="responsive-ready-sites-tooltip-plugins-settings"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
							<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-plugins-settings" style="display: none;">
								<ul class="required-plugins-list"><span class="spinner is-active"></span></ul>
								<# if ( data.pro_plugins_flag ) { #>
									<div class="responsive-ready-sites-third-party-plugins-warning"><?php _e( 'This ready template requires premium plugins. As these are third party premium plugins, you\'ll need to purchase, install and activate.', 'responsive-addons' ); ?></div>
									<ul class="required-third-party-plugins-list"><span class="is-active"></span></ul>
								<# } #>
							</div>
						</li>
						<li class="responsive-ready-sites-import-xml">
							<input type="checkbox" name="content" checked="checked" class="disabled checkbox" readonly>
							<strong><?php esc_html_e( 'Import Content', 'responsive-addons' ); ?></strong>
							<span class="responsive-ready-sites-tooltip-icon responsive-ready-sites-tooltip-site-content" data-tip-id="responsive-ready-sites-tooltip-site-content"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
							<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-site-content" style="display: none;">
								<p><?php esc_html_e( 'Imports ready template content. Depending on your internet speed this may take 1-3 minutes.', 'responsive-addons' ); ?></p>
							</div>
							<div class="responsive-ready-sites-import-process-wrap" style="display: none;">
								<progress class="responsive-ready-sites-import-process" max="100" value="0"></progress>
							</div>
						</li>
					</ul>
				</div>
				<div class="responsive-ready-sites-import-button-wrap">
					<a class="button button-hero button-primary responsive-ready-page-import-{{{ data.demo_type }}}" href="#">
						<?php esc_html_e( 'Import Template', 'responsive-addons' ); ?>
					</a>
				</div>
			</div>
			<div class="result_preview" style="display: none">
			</div>
		</div>
	</div>
</script>



