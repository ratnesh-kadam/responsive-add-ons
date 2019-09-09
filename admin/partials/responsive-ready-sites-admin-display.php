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
<div class="spinner-wrap">
	<span class="spinner is-active"></span>
</div>
<div id="responsive-ready-sites-admin-page" style="display: none;">
	<div class="responsive-sites-header">
		<span class="ready-site-list-title">Responsive Ready Websites</span>
		<p class="ready-site-list-intro">Build your Responsive website in 3 simple steps - import a ready website, change content and launch.</p>
	</div>
	<div class="theme-browser rendered">
		<div id="responsive-sites" class="themes wp-clearfix"></div>
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

	<# if (data.items[ key ].active ) { #>
		<div class="theme active ra-site-single {{ data.items[ key ].status }}" tabindex="0" aria-describedby="responsive-theme-action responsive-theme-name" data-demo-id="{{{ data.items[ key ].id }}}"
		 data-demo-url="{{{ data.items[ key ]['site_url'] }}}"
		 data-demo-slug="{{{  data.items[ key ].slug }}}"
		 data-demo-name="{{{  data.items[ key ].title.rendered }}}"
			 data-demo-type="{{{ data.items[ key ].demo_type }}}"
		 data-screenshot="{{{ data.items[ key ]['featured_image_url'] }}}"
		 data-required-plugins="{{ JSON.stringify(data.items[ key ]['required_plugins']) }}"
		 data-required-pro-plugins="{{ JSON.stringify(data.items[ key ]['required_pro_plugins']) }}">

		<# } else { #>

		<div class="theme inactive ra-site-single {{ data.items[ key ].status }}" tabindex="0" aria-describedby="responsive-theme-action responsive-theme-name"
			 data-demo-id="{{{ data.items[ key ].id }}}"
			 data-demo-url="{{{ data.items[ key ]['site_url'] }}}"
			 data-demo-slug="{{{  data.items[ key ].slug }}}"
			 data-demo-name="{{{  data.items[ key ].title.rendered }}}"
			 data-demo-type="{{{ data.items[ key ].demo_type }}}"
			 data-screenshot="{{{ data.items[ key ]['featured_image_url'] }}}"
			 data-required-plugins="{{ JSON.stringify(data.items[ key ]['required_plugins']) }}"
			 data-required-pro-plugins="{{ JSON.stringify(data.items[ key ]['required_pro_plugins']) }}">
			<# } #>
			<input type="hidden" class="site_options_data" value="{{ JSON.stringify(data.items[ key ][ 'site_options_data' ]) }}">
		<div class="inner">
					<span class="site-preview" data-href="{{ data.items[ key ]['responsive-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="data title">
						<div class="theme-screenshot" style="background-image: url('{{ data.items[ key ]['featured_image_url'] }}');"></div>
					</span>
			<span class="demo-type {{{ data.items[ key ].demo_type }}}">{{{ data.items[ key ].demo_type }}}</span>
			<div class="theme-id-container">
				<# if (data.items[ key ].active ) { #>
				<h3 class="theme-name" id="responsive-theme-name">Active : {{{ data.items[ key ].title.rendered }}}</h3>
				<# } else { #>
				<h3 class="theme-name" id="responsive-theme-name">{{{ data.items[ key ].title.rendered }}}</h3>
				<div class="theme-actions">
					<button class="button-primary button preview install-theme-preview"><?php esc_html_e( 'Preview', 'responsive-addons' ); ?></button>
				</div>
				<# } #>
			</div>
		</div>
	</div>
	<# } #>
	<# } else { #>
	<p class="no-themes" style="display:block;">
		<?php _e( 'No Demos found', 'responsive-addons' ); ?>
		<span class="description">
				<?php
				/* translators: %1$s External Link */
				printf( __( 'No More Sites', 'responsive-addons' ) );
				?>
			</span>
	</p>
	<# } #>
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
		 data-demo-type="{{{data.demo_type}}}"
		 data-demo-slug="{{{data.slug}}}"
		 data-screenshot="{{{data.screenshot}}}"
		 data-required-plugins="{{data.required_plugins}}"
		 data-required-pro-plugins="{{data.required_pro_plugins}}">
		<input type="hidden" class="responsive-site-options" value="{{data.site_options_data}}" >
		<div class="wp-full-overlay-header">
			<div>
				<span class="responsive-site-demo-name">{{data.name}}</span>
				<# if ( data.demo_type == "free" || ( data.is_responsive_addons_pro_installed && data.is_responsive_addons_pro_license_active ) ) { #>

				<a class="button button-primary responsive-addons responsive-demo-import-options-{{{data.demo_type}}}" href="#"><?php esc_html_e( 'Import Site', 'responsive-addons' ); ?></a>

				<# } else { #>

				<a class="button button-primary responsive-addons responsive-buy-pro" href="https://www.cyberchimps.com/responsive-pricing/" target="_blank"><?php esc_html_e( 'Buy Responsive Pro', 'responsive-addons' ); ?></a>

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
					<p>Importing this ready site will &hellip;</p>
					<ul class="responsive-ready-site-contents">
						<li class="responsive-ready-sites-import-plugins">
							<strong><?php _e( 'Install Required Plugins', 'responsive-addons' ); ?></strong>
							<span class="responsive-ready-sites-tooltip-icon" data-tip-id="responsive-ready-sites-tooltip-plugins-settings"><span class="dashicons dashicons-editor-help"></span></span>
							<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-plugins-settings" style="display: none;">
								<ul class="required-plugins-list"><span class="spinner is-active"></span></ul>
							</div>
						</li>
						<li class="responsive-ready-sites-reset-data">
							<label>
								<strong>Delete Previous Import</strong>
							</label>
							<span class="responsive-ready-sites-tooltip-icon" data-tip-id="responsive-ready-sites-tooltip-reset-data"><span class="dashicons dashicons-editor-help"></span></span>
							<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-reset-data" style="display: none;">
								<p><?php _e( 'Deletes previous import including customizer settings and content. Plugins are not deleted.', 'responsive-addons' ); ?></p>
							</div>
						</li>
						<li class="responsive-ready-sites-import-xml">
							<label>
								<strong>Import Content</strong>
							</label>
							<span class="responsive-ready-sites-tooltip-icon" data-tip-id="responsive-ready-sites-tooltip-site-content"><span class="dashicons dashicons-editor-help"></span></span>
							<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-site-content" style="display: none;">
								<p><?php _e( 'Imports sample pages, posts, images and menus. Depending on your internet speed this may take 2-10 minutes.', 'responsive-addons' ); ?></p>
							</div>
							<div class="responsive-ready-sites-import-process-wrap" style="display: none;">
								<progress class="responsive-ready-sites-import-process" max="100" value="0"></progress>
							</div>
						</li>
						<li class="responsive-ready-sites-import-customizer">
							<label>
								<strong>Import Customizer Settings</strong>
							</label>
							<span class="responsive-ready-sites-tooltip-icon" data-tip-id="responsive-ready-sites-tooltip-customizer-settings"><span class="dashicons dashicons-editor-help"></span></span>
							<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-customizer-settings" style="display: none;">
								<p><?php _e( 'Imports sample customizer settings including site identity, typography, colors and other theme options.', 'responsive-addons' ); ?></p>
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
