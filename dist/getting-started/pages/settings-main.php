<?php
/**
 * Wrapper for main settings page.
 *
 * @package ResponsiveBlocks\Settings
 */

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified in settings save routine. This is a false positive.
	if ( ! empty( $_GET['responsive-blocks-settings-saved'] ) && $_GET['responsive-blocks-settings-saved'] === 'true' ) {
		echo '<div class="updated fade"><p>' . esc_html__( 'Settings saved.', 'responsive-blocks' ) . '</p></div>';
	}
	?>

	<form method="post" action="options.php" class="responsive-blocks-options-form">
			<?php
			require $pages_dir . 'settings-general.php';
			submit_button( esc_html__( 'Save Settings', 'responsive-blocks' ) );
			wp_nonce_field( 'responsive-blocks-settings-save-nonce', 'responsive-blocks-settings-save-nonce' );
			?>
	</form>
</div>
