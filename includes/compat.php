<?php
/**
 * Compatibility related functionality.
 *
 * Handles things like PHP version requirements
 * not met, unregistering blocks, etc.
 *
 * This file should remain PHP 5.2 compatible.
 *
 * @package ResponsiveBlocks\Compatibility
 */

if ( PHP_VERSION_ID < 50600 ) {
	add_action( 'enqueue_block_editor_assets', 'responsive_blocks_unregister_newsletter_block' );
}
/**
 * Unregisters the newsletter block on sites
 * running PHP < 5.6.
 */
function responsive_blocks_unregister_newsletter_block() {
	?>
	<script>
		window.addEventListener( 'DOMContentLoaded', function() {
			wp.domReady( function() {
				wp.blocks.unregisterBlockType( 'responsive-blocks/newsletter' );
			} );
		} );
	</script>
	<?php
}
