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
<div class="wrap" id="responsive-ready-sites-admin-page">
    <h1>Responsive Ready Sites Page</h1>
    <div class="responsive-ready-sites-import-display">
        <p>
            <button class="button-primary import-demo-data">Import Demo Site Data</button>
        </p>
    </div>
    <div class="result_preview">
    </div>
    <div class="responsive-ready-sites-result-preview" style="display: none;">
        <div class="inner">
            <h2><?php esc_html_e( 'We\'re importing your website.', 'responsive-ready-sites' ); ?></h2>
            <p><?php esc_html_e( 'The process can take anywhere between 2 to 10 minutes depending on the size of the website and speed of connection.', 'responsive-ready-sites' ); ?></p>
            <p><?php esc_html_e( 'Please do not close this browser window until the site is imported completely.', 'responsive-ready-sites' ); ?></p>
            <div class="current-importing-status-wrap">
                <div class="current-importing-status">
                    <div class="current-importing-status-title"></div>
                    <div class="current-importing-status-description"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="responsive-ready-sites-import-process-wrap" style="display: none;">
        <progress class="responsive-ready-sites-import-process" max="100" value="0"></progress>
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

    <div class="theme responsive-theme site-single {{ data[0].items[ key ].status }}" tabindex="0" aria-describedby="responsive-theme-action responsive-theme-name"
         data-demo-id="{{{ data[0].items[ key ].id }}}"
         data-demo-url="{{{ data[0].items[ key ]['responsive_site_url'] }}}"
         data-demo-slug="{{{  data[0].items[ key ].slug }}}"
         data-demo-name="{{{  data[0].items[ key ].name }}}"
         data-screenshot="{{{ data[0].items[ key ]['featured_image_url'] }}}">

        <div class="inner">
					<span class="site-preview" data-href="{{ data[0].items[ key ]['responsive-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="data title">
						<div class="theme-screenshot" style="background-image: url('{{ data[0].items[ key ]['featured_image_url'] }}');"></div>
					</span>
            <div class="theme-id-container">
                <h3 class="theme-name" id="responsive-theme-name">{{ data[0].items[ key ].name }}</h3>
                <div class="theme-actions">
                    <button class="button-primary button preview install-theme-preview"><?php esc_html_e( 'Preview', 'responsive-ready-sites' ); ?></button>
                </div>
            </div>
        </div>
    </div>
    <# } #>
    <# } else { #>
    <p class="no-themes" style="display:block;">
        <?php _e( 'No Demos found', 'responsive-ready-sites' ); ?>
        <span class="description">
				<?php
                /* translators: %1$s External Link */
                printf( __( 'No More Sites', 'responsive-ready-sites' ) );
                ?>
			</span>
    </p>
    <# } #>
</script>
<?php
/** Simgle Demo Preview */
?>

<script type="text/template" id="tmpl-responsive-ready-site-preview">
    <div class="responsive-ready-site-preview theme-install-overlay expanded">
        <div class="wp-full-overlay-main">
            <iframe src="{{{data.astra_demo_url}}}" title="<?php esc_attr_e( 'Preview', 'responsive-ready-sites' ); ?>"></iframe>
        </div>
    </div>
</script>