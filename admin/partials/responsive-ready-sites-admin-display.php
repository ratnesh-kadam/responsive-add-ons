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
<div class="wrap" id="responsive-ready-sites-admin-page">
	<div class="responsive-sites-header">
		<span>Responsive Ready Sites</span>
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

	<# if ( data[0].items.length ) { #>
	<# for ( key in data[0].items ) { #>

	<# if (data[0].items[ key ].active ) { #>
		<div class="theme active responsive-theme site-single {{ data[0].items[ key ].status }}" tabindex="0" aria-describedby="responsive-theme-action responsive-theme-name"
		 data-demo-id="{{{ data[0].items[ key ].id }}}"
		 data-demo-url="{{{ data[0].items[ key ]['responsive_site_url'] }}}"
		 data-demo-slug="{{{  data[0].items[ key ].slug }}}"
		 data-demo-name="{{{  data[0].items[ key ].name }}}"
		 data-screenshot="{{{ data[0].items[ key ]['featured_image_url'] }}}"
		 data-required-plugins="{{ JSON.stringify(data[0].items[ key ]['required_plugins']) }}">

		<# } else { #>

		<div class="theme responsive-theme site-single {{ data[0].items[ key ].status }}" tabindex="0" aria-describedby="responsive-theme-action responsive-theme-name"
			 data-demo-id="{{{ data[0].items[ key ].id }}}"
			 data-demo-url="{{{ data[0].items[ key ]['responsive_site_url'] }}}"
			 data-demo-slug="{{{  data[0].items[ key ].slug }}}"
			 data-demo-name="{{{  data[0].items[ key ].name }}}"
			 data-screenshot="{{{ data[0].items[ key ]['featured_image_url'] }}}"
			 data-required-plugins="{{ JSON.stringify(data[0].items[ key ]['required_plugins']) }}">
			<# } #>
		<div class="inner">
					<span class="site-preview" data-href="{{ data[0].items[ key ]['responsive-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="data title">
						<div class="theme-screenshot" style="background-image: url('{{ data[0].items[ key ]['featured_image_url'] }}');"></div>
					</span>
			<div class="theme-id-container">
				<# if (data[0].items[ key ].active ) { #>
				<h3 class="theme-name" id="responsive-theme-name">{{ data[0].items[ key ].name }}</h3>
				<div class="theme-actions">
					<button class="button-primary button preview install-theme-preview"><?php esc_html_e( 'UnInstall', 'responsive-addons' ); ?></button>
				</div>
				<# } else { #>
				<h3 class="theme-name" id="responsive-theme-name">{{ data[0].items[ key ].name }}</h3>
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
</script>
<?php
/** Single Demo Preview */
?>

<script type="text/template" id="tmpl-responsive-ready-site-preview">
	<div class="responsive-ready-site-preview theme-install-overlay wp-full-overlay collapsed"
		 data-demo-id="{{{data.id}}}"
		 data-demo-url="{{{data.responsive_site_url}}}"
		 data-demo-api="{{{data.demo_api}}}"
		 data-demo-name="{{{data.demo_name}}}"
		 data-demo-slug="{{{data.slug}}}"
		 data-screenshot="{{{data.screenshot}}}"
		 data-required-plugins="{{data.required_plugins}}">
		<input type="hidden" class="responsive-ready-site-options" value="{{data.responsive_ready_site_options}}" >
		<div class="wp-full-overlay-header">
			<div>
				<span class="responsive-site-demo-name">{{data.demo_name}}</span>
				<a class="button hide-if-no-customize button-primary responsive-addons responsive-demo-import-options" href="#"><?php esc_html_e( 'Import Site', 'responsive-addons' ); ?></a>
				<button class="close-full-overlay responsive-addons"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'responsive-addons' ); ?></span></button>
			</div>
		</div>
		<div class="wp-full-overlay-main">
			<iframe src="{{{data.responsive_demo_url}}}" title="<?php esc_attr_e( 'Preview', 'responsive-addons' ); ?>"></iframe>
		</div>
	</div>
</script>

<?php
/** Theme Import Options Page */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-options-page">
	<div class="responsive-ready-sites-advanced-options-wrap wp-full-overlay collapsed"
		 data-demo-id="{{{data.id}}}"
		 data-demo-url="{{{data.responsive_site_url}}}"
		 data-demo-api="{{{data.demo_api}}}"
		 data-demo-name="{{{data.demo_name}}}"
		 data-demo-slug="{{{data.slug}}}"
		 data-screenshot="{{{data.screenshot}}}"
		 data-required-plugins="{{data.required_plugins}}">
		<input type="hidden" class="responsive-ready-site-options" value="{{data.responsive_ready_site_options}}" >

		<div class="wp-full-overlay-header">
			<div>
				<span class="responsive-site-demo-name">{{data.demo_name}}</span>
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
						<li class="responsive-ready-sites-import-xml">
							<label>
								<strong>Import Content</strong>
							</label>
							<span class="responsive-ready-sites-tooltip-icon" data-tip-id="responsive-ready-sites-tooltip-site-content"><span class="dashicons dashicons-editor-help"></span></span>
							<div class="responsive-ready-sites-tooltip-message" id="responsive-ready-sites-tooltip-site-content" style="display: none;">
								<p><?php _e( 'Imports sample pages, posts, images and menus.', 'responsive-addons' ); ?></p>
							</div>
							<div class="responsive-ready-sites-import-process-wrap" style="display: none;">
								<progress class="responsive-ready-sites-import-process" max="100" value="0"></progress>
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
					</ul>
				</div>
				<div class="responsive-ready-sites-import-button-wrap">
					<a class="button button-hero hide-if-no-customize button-primary responsive-ready-site-import" href="#">
					<?php esc_html_e( 'Import Site', 'responsive-addons' ); ?>
					</a>
				</div>
			</div>
			<div class="result_preview" style="display: none">
			</div>
		</div>
	</div>
</script>
