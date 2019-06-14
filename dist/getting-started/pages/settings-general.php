<?php
/**
 * General tab for the settings page.
 *
 * @package ResponsiveBlocks\Settings
 */

$responsive_blocks_mailchimp_api_key = get_option( 'responsive_blocks_mailchimp_api_key', '' );
?>
<table class="form-table">
	<tbody>
		<tr>
			<th>
				<label for="responsive-blocks-settings[mailchimp-api-key]">
					<?php esc_html_e( 'Mailchimp API Key', 'responsive-blocks' ); ?>
				</label>
			</th>
			<td>
				<input type="text" id="responsive-blocks-settings[mailchimp-api-key]" name="responsive-blocks-settings[mailchimp-api-key]" size="40" value="<?php echo esc_attr( $responsive_blocks_mailchimp_api_key ); ?>" />
				<?php
					$responsive_blocks_mailchimp_api_key_link = sprintf(
						'<p><a href="%1$s" target="_blank" rel="noreferrer noopener">%2$s</a></p>',
						'https://mailchimp.com/help/about-api-keys/',
						esc_html__( 'Find your Mailchimp API key.', 'responsive-blocks' )
					);
					echo wp_kses_post( $responsive_blocks_mailchimp_api_key_link );
					?>
			</td>
		</tr>
	</tbody>
</table>
