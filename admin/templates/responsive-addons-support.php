<?php
/**
 * Responsive Add Ons
 *
 * @package Responsive Add Ons
 */

?>
<div class="responsive-addons-tabs">
	<div id="responsive-addons-support" class="tab-content active">
		<div class="column-4">
			<h3><?php esc_html_e( 'Community Support' ); ?></h3>
			<p><?php esc_html_e( 'Free Responsive user? Use the WordPress.org support forums to get help from our experts and other Responsive users.' ); ?></p>
			<a href="https://wordpress.org/support/theme/responsive/" target="_blank"><?php esc_html_e( 'WordPress.org Support' ); ?></a>
		</div>
		<div class="column-4">
			<h3><?php esc_html_e( 'Premium Support' ); ?></h3>
			<p><?php esc_html_e( 'Free Responsive user? Use the WordPress.org support forums to get help from our experts and other Responsive users.' ); ?></p>
			<?php if ( is_resp_pro_license_is_active() ) { ?>
				<a href="https://cyberchimps.com/my-account/support/" target="_blank"><?php esc_html_e( 'Request Support' ); ?></a>
			<?php } else { ?>
				<a href="https://cyberchimps.com/responsive-premium/" target="_blank"><?php esc_html_e( 'Upgrade To Responsive Premium' ); ?></a>
			<?php } ?>
		</div>
		<div class="column-4">
			<h3><?php esc_html_e( 'Documentation' ); ?></h3>
			<p><?php esc_html_e( 'Free Responsive user? Use the WordPress.org support forums to get help from our experts and other Responsive users.' ); ?></p>
			<a href="https://docs.cyberchimps.com/responsive/" target="_blank"><?php esc_html_e( 'See Documentation' ); ?></a>
		</div>
	</div>
</div>
<?php
	/**
	 * Check if Responsive Addons Pro License is Active.
	 */
function is_resp_pro_license_is_active() {
	global $wcam_lib;
	$license_status = $wcam_lib->license_key_status();

	if ( ! empty( $license_status['data']['activated'] ) && $license_status['data']['activated'] ) {
		return true;
	} else {
		return false;
	}
}
?>
