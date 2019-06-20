<?php
/**
 * Getting Started page
 *
 * @package Responsive Blocks
 */

/**
 * Load Getting Started styles in the admin
 *
 * @since 1.0.0
 * @param string $hook The current admin page.
 */
function responsive_blocks_start_load_admin_scripts( $hook ) {

	if ( ! ( $hook === 'toplevel_page_responsive-blocks' ) ) {
		return;
	}

	// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- Could be true or 'true'.
	$postfix = ( SCRIPT_DEBUG == true ) ? '' : '.min';

	/**
	 * Load scripts and styles
	 *
	 * @since 1.0
	 */

	// Getting Started javascript.
	wp_enqueue_script( 'responsive-blocks-getting-started', plugins_url( 'getting-started/getting-started.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );

	// Getting Started styles.
	wp_register_style( 'responsive-blocks-getting-started', plugins_url( 'getting-started/getting-started.css', dirname( __FILE__ ) ), false, '1.0.0' );
	wp_enqueue_style( 'responsive-blocks-getting-started' );

	// FontAwesome.
	wp_register_style( 'responsive-blocks-fontawesome', plugins_url( '/assets/fontawesome/css/all' . $postfix . '.css', dirname( __FILE__ ) ), false, '1.0.0' );
	wp_enqueue_style( 'responsive-blocks-fontawesome' );
}
add_action( 'admin_enqueue_scripts', 'responsive_blocks_start_load_admin_scripts' );


/**
 * Adds a menu item for the Getting Started page.
 *
 * @since 1.0.0
 */
function responsive_blocks_getting_started_menu() {

	add_menu_page(
		__( 'Responsive Blocks', 'responsive-blocks' ),
		__( 'Responsive Blocks', 'responsive-blocks' ),
		'manage_options',
		'responsive-blocks',
		'responsive_blocks_getting_started_page_temp',
		'dashicons-screenoptions'
	);

	/*add_submenu_page(
		'responsive-blocks',
		esc_html__( 'Getting Started', 'responsive-blocks' ),
		esc_html__( 'Getting Started', 'responsive-blocks' ),
		'manage_options',
		'responsive-blocks',
		'responsive_blocks_getting_started_page'
	);*/

	if ( PHP_VERSION_ID >= 50600 ) {
		add_submenu_page(
			'responsive-blocks',
			esc_html__( 'Responsive Blocks Settings', 'responsive-blocks' ),
			esc_html__( 'Settings', 'responsive-blocks' ),
			'manage_options',
			'responsive-blocks-plugin-settings',
			'responsive_blocks_render_settings_page'
		);
	}

}
add_action( 'admin_menu', 'responsive_blocks_getting_started_menu' );

function responsive_blocks_getting_started_page_temp(){
	$str = dirname( __FILE__ );
	$len = strlen($str);
	$final_path = substr($str,0, $len-20);
	include($final_path.'templates\settings.php');
}

/**
 * Outputs the markup used on the Getting Started
 *
 * @since 1.0.0
 */
function responsive_blocks_getting_started_page() {

	/**
	 * Create recommended plugin install URLs
	 *
	 * @since 1.0.0
	 */
	$gberg_install_url = wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'install-plugin',
				'plugin' => 'gutenberg',
			),
			admin_url( 'update.php' )
		),
		'install-plugin_gutenberg'
	);

	$ab_install_url = wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'install-plugin',
				'plugin' => 'responsive-blocks',
			),
			admin_url( 'update.php' )
		),
		'install-plugin_responsive-blocks'
	);

	$ab_theme_install_url = wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'install-theme',
				'theme'  => 'responsive-blocks',
			),
			admin_url( 'update.php' )
		),
		'install-theme_responsive-blocks'
	);
	?>
	<div class="wrap ab-getting-started">
		<div class="intro-wrap">
			<div class="intro">
				<a href="<?php echo esc_url( 'https://goo.gl/NfXcof' ); ?>"><img class="responsive-logo" src="<?php echo esc_url( plugins_url( 'logo.png', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Visit Responsive Blocks', 'responsive-blocks' ); ?>" /></a>
				<h3><?php printf( esc_html__( 'Getting started with', 'responsive-blocks' ) ); ?> <strong><?php printf( esc_html__( 'Responsive Blocks', 'responsive-blocks' ) ); ?></strong></h3>
			</div>

			<ul class="inline-list">
				<li class="current"><a id="responsive-blocks-panel" href="#"><i class="fa fa-check"></i> <?php esc_html_e( 'Getting Started', 'responsive-blocks' ); ?></a></li>
				<li><a id="plugin-help" href="#"><i class="fa fa-plug"></i> <?php esc_html_e( 'Plugin Help File', 'responsive-blocks' ); ?></a></li>
				<?php if ( function_exists( 'responsive_blocks_setup' ) ) { ?>
					<li><a id="theme-help" href="#"><i class="fa fa-desktop"></i> <?php esc_html_e( 'Theme Help File', 'responsive-blocks' ); ?></a></li>
				<?php } ?>
			</ul>
		</div>

		<div class="panels">
			<div id="panel" class="panel">
				<div id="responsive-blocks-panel" class="panel-left visible">
					<div class="ab-block-split clearfix">
						<div class="ab-block-split-left">
							<div class="ab-titles">
								<h2><?php esc_html_e( 'Welcome to the future of site building with Gutenberg and Responsive Blocks!', 'responsive-blocks' ); ?></h2>
								<p><?php esc_html_e( 'The Responsive Blocks collection is now ready to use in your posts and pages. Simply search for "responsive" or "ab" in the block inserter to display the Responsive Blocks collection. Check out the help file link above for detailed instructions!', 'responsive-blocks' ); ?></p>
							</div>
						</div>
						<div class="ab-block-split-right">
							<div class="ab-block-theme">
								<img src="<?php echo esc_url( plugins_url( 'images/build-content.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Responsive Blocks Theme', 'responsive-blocks' ); ?>" />
							</div>
						</div>
					</div>

					<div class="ab-block-feature-wrap clear">
						<i class="fas fa-cube"></i>
						<h2><?php esc_html_e( 'Available Responsive Blocks', 'responsive-blocks' ); ?></h2>
						<p><?php esc_html_e( 'The following blocks are available in Responsive Blocks. More blocks are on the way so stay tuned!', 'responsive-blocks' ); ?></p>

						<div class="ab-block-features">
							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc26.svg', __FILE__ ) ); ?>" alt="Post Grid Block" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Post Grid Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add an eye-catching, full-width section with a big title, paragraph text, and a customizable button.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc430.svg', __FILE__ ) ); ?>" alt="Container Block" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Container Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Wrap several blocks into a section and add padding, margins, background colors and images.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc41.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Call To Action Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Call-To-Action Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add an eye-catching, full-width section with a big title, paragraph text, and a customizable button.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc4.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Testimonials Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Testimonial Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add a customer or client testimonial to your site with an avatar, text, citation and more.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc184.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Inline Notices Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Inline Notice Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add a colorful notice or message to your site with text, a title and a dismiss icon.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc50.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Sharing Icons Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Sharing Icons Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add social sharing icons to your page with size, shape, color and style options.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc94-f.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Author Profile Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Author Profile Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add a user profile box to your site with a title, bio info, an avatar and social media links.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc115.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Accordion Toggle', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Accordion Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add an accordion text toggle with a title and descriptive text. Includes font size and toggle options.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc45.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Customizable Button Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Customizable Button', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add a fancy stylized button to your post or page with size, shape, target, and color options.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc38.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Drop Cap Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Drop Cap Block', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add a stylized drop cap to the beginning of your paragraph. Choose from three different styles.', 'responsive-blocks' ); ?></p>
								</div>
							</div>

							<div class="ab-block-feature">
								<div class="ab-block-feature-icon"><img src="<?php echo esc_url( plugins_url( 'images/cc402.svg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Spacer and Divider Block', 'responsive-blocks' ); ?>" /></div>
								<div class="ab-block-feature-text">
									<h3><?php esc_html_e( 'Spacer & Divider', 'responsive-blocks' ); ?></h3>
									<p><?php esc_html_e( 'Add an adjustable spacer between your blocks with an optional divider with styling options.', 'responsive-blocks' ); ?></p>
								</div>
							</div>
						</div><!-- .ab-block-features -->
					</div><!-- .ab-block-feature-wrap -->
				</div><!-- .panel-left -->

				<!-- Plugin help file panel -->
				<div id="plugin-help" class="panel-left">
					<!-- Grab feed of help file -->
					<?php
						$plugin_help = get_transient( 'responsive-blocks-plugin-help-feed' );

					if ( false === $plugin_help ) {
						$plugin_feed = wp_remote_get( 'https://responsiveblocks.com/plugin-help-file//?responsiveblocks_api=post_content' );

						if ( ! is_wp_error( $plugin_feed ) && 200 === wp_remote_retrieve_response_code( $plugin_feed ) ) {
							$plugin_help = json_decode( wp_remote_retrieve_body( $plugin_feed ) );
							set_transient( 'responsive-blocks-plugin-help-feed', $plugin_help, DAY_IN_SECONDS );
						} else {
							$plugin_help = __( 'This help file feed seems to be temporarily down. You can always view the help file on the Responsive Blocks site in the meantime.', 'responsive-blocks' );
							set_transient( 'responsive-blocks-plugin-help-feed', $plugin_help, MINUTE_IN_SECONDS * 5 );
						}
					}

						echo wp_kses_post( $plugin_help );
					?>
				</div>

				<!-- Theme help file panel -->
				<?php if ( function_exists( 'responsive_blocks_setup' ) ) { ?>
					<div id="theme-help" class="panel-left">
						<!-- Grab feed of help file -->
						<?php
							$theme_help = get_transient( 'responsive-blocks-theme-help-feed' );

						if ( false === $theme_help ) {
							$theme_feed = wp_remote_get( 'https://responsiveblocks.com/theme-help-file//?responsiveblocks_api=post_content' );

							if ( ! is_wp_error( $theme_feed ) && 200 === wp_remote_retrieve_response_code( $theme_feed ) ) {
								$theme_help = json_decode( wp_remote_retrieve_body( $theme_feed ) );
								set_transient( 'responsive-blocks-theme-help-feed', $theme_help, DAY_IN_SECONDS );
							} else {
								$theme_help = __( 'This help file feed seems to be temporarily down. You can always view the help file on the Responsive Blocks site in the meantime.', 'responsive-blocks' );
								set_transient( 'responsive-blocks-theme-help-feed', $theme_help, MINUTE_IN_SECONDS * 5 );
							}
						}

							echo wp_kses_post( $theme_help );
						?>
					</div><!-- #theme-help -->
				<?php } ?>

				<div class="panel-right">

					<?php if ( ! function_exists( 'gutenberg_init' ) || ! function_exists( 'responsive_blocks_loader' ) ) { ?>
					<div class="panel-aside panel-ab-plugin panel-club ab-quick-start">
						<div class="panel-club-inside">
							<div class="cell panel-title">
								<h3><i class="fa fa-check"></i> <?php esc_html_e( 'Quick Start Checklist', 'responsive-blocks' ); ?></h3>
							</div>

							<ul>
							<li class="cell 
							<?php
							if ( function_exists( 'gutenberg_init' ) ) {
								echo 'step-complete'; }
							?>
							">
									<strong><?php esc_html_e( '1. Install the Gutenberg plugin.', 'responsive-blocks' ); ?></strong>
									<p><?php esc_html_e( 'Gutenberg adds the new block-based editor to WordPress. You will need this to work with the Responsive Blocks plugin.', 'responsive-blocks' ); ?></p>

									<?php if ( ! array_key_exists( 'gutenberg/gutenberg.php', get_plugins() ) ) { ?>
										<a class="button-primary club-button" href="<?php echo esc_url( $gberg_install_url ); ?>"><?php esc_html_e( 'Install Gutenberg now', 'responsive-blocks' ); ?> &rarr;</a>
									<?php } elseif ( array_key_exists( 'gutenberg/gutenberg.php', get_plugins() ) && ! is_plugin_active( 'gutenberg/gutenberg.php' ) ) { ?>
										<?php activate_plugin( 'gutenberg/gutenberg.php' ); ?>
										<strong><i class="fa fa-check"></i> <?php esc_html_e( 'Plugin activated!', 'responsive-blocks' ); ?></strong>
									<?php } else { ?>
										<strong><i class="fa fa-check"></i> <?php esc_html_e( 'Plugin activated!', 'responsive-blocks' ); ?></strong>
									<?php } ?>
								</li>

								<li class="cell 
								<?php
								if ( function_exists( 'responsive_blocks_loader' ) ) {
									echo 'step-complete'; }
								?>
								">
									<strong><?php esc_html_e( '2. Install the Responsive Blocks plugin.', 'responsive-blocks' ); ?></strong>
									<p><?php esc_html_e( 'Responsive Blocks adds several handy content blocks to the Gutenberg block editor.', 'responsive-blocks' ); ?></p>

									<?php if ( ! array_key_exists( 'responsive-blocks/responsiveblocks.php', get_plugins() ) ) { ?>
										<a class="button-primary club-button" href="<?php echo esc_url( $ab_install_url ); ?>"><?php esc_html_e( 'Install Responsive Blocks now', 'responsive-blocks' ); ?> &rarr;</a>
									<?php } elseif ( array_key_exists( 'responsive-blocks/responsiveblocks.php', get_plugins() ) && ! is_plugin_active( 'responsive-blocks/responsiveblocks.php' ) ) { ?>
										<?php activate_plugin( 'responsive-blocks/responsiveblocks.php' ); ?>
										<strong><i class="fa fa-check"></i> <?php esc_html_e( 'Plugin activated!', 'responsive-blocks' ); ?></strong>
									<?php } else { ?>
										<strong><i class="fa fa-check"></i> <?php esc_html_e( 'Plugin activated!', 'responsive-blocks' ); ?></strong>
									<?php } ?>
								</li>
							</ul>
						</div>
					</div>
					<?php } ?>

					<?php if ( ! function_exists( 'responsive_blocks_setup' ) ) { ?>
					<div class="panel-aside panel-ab-plugin panel-club">
						<div class="panel-club-inside">
							<div class="cell panel-title">
								<h3><i class="fa fa-download"></i> <?php esc_html_e( 'Free Theme Download', 'responsive-blocks' ); ?></h3>
							</div>

							<ul>
								<li class="cell">
									<p><a class="ab-theme-image" href="<?php echo esc_url( 'https://goo.gl/FCT6xS' ); ?>"><img src="<?php echo esc_url( plugins_url( 'theme.jpg', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Visit Responsive Blocks', 'responsive-blocks' ); ?>" /></a></p>

									<p><?php esc_html_e( 'Download our FREE Responsive Blocks theme to help you get started with the Responsive Blocks plugin and the new WordPress block editor.', 'responsive-blocks' ); ?></p>

									<a class="button-primary club-button" target="_blank" href="<?php echo esc_url( $ab_theme_install_url ); ?>"><?php esc_html_e( 'Install Now', 'responsive-blocks' ); ?> &rarr;</a>
								</li>
							</ul>
						</div>
					</div>
					<?php } ?>

					<div class="panel-aside panel-ab-plugin panel-club">
						<div class="panel-club-inside">
							<div class="cell panel-title">
								<h3><i class="fa fa-envelope"></i> <?php esc_html_e( 'Stay Updated', 'responsive-blocks' ); ?></h3>
							</div>

							<ul>
								<li class="cell">
								<p><?php esc_html_e( 'Join the newsletter to receive emails when we add new blocks, release plugin and theme updates, send out free resources, and more!', 'responsive-blocks' ); ?></p>

									<a class="button-primary club-button" target="_blank" href="<?php echo esc_url( 'https://goo.gl/3pC6LE' ); ?>"><?php esc_html_e( 'Subscribe Now', 'responsive-blocks' ); ?> &rarr;</a>
								</li>
							</ul>
						</div>
					</div>

					<div class="panel-aside panel-ab-plugin panel-club">
						<div class="panel-club-inside">
							<div class="cell panel-title">
								<h3><i class="fa fa-arrow-circle-down"></i> <?php esc_html_e( 'Free Blocks & Tutorials', 'responsive-blocks' ); ?></h3>
							</div>

							<ul>
								<li class="cell">
									<p><?php esc_html_e( 'Check out the Responsive Blocks site to find block editor tutorials, free blocks and updates about the Responsive Blocks plugin and theme!', 'responsive-blocks' ); ?></p>
									<a class="button-primary club-button" target="_blank" href="<?php echo esc_url( 'https://goo.gl/xpujKp' ); ?>"><?php esc_html_e( 'Visit ResponsiveBlocks.com', 'responsive-blocks' ); ?> &rarr;</a>
								</li>
							</ul>
						</div>
					</div>
				</div><!-- .panel-right -->

				<div class="footer-wrap">
					<h2 class="visit-title"><?php esc_html_e( 'Free Blocks and Resources', 'responsive-blocks' ); ?></h2>

					<div class="ab-block-footer">
						<div class="ab-block-footer-column">
							<i class="far fa-envelope"></i>
							<h3><?php esc_html_e( 'Blocks In Your Inbox', 'responsive-blocks' ); ?></h3>
							<p><?php esc_html_e( 'Join the newsletter to receive emails when we add new blocks, release plugin and theme updates, send out free resources, and more!', 'responsive-blocks' ); ?></p>
							<a class="button-primary" href="https://responsiveblocks.com/subscribe?utm_source=AB%20Theme%20GS%20Page%20Footer%20Subscribe"><?php esc_html_e( 'Subscribe Today', 'responsive-blocks' ); ?></a>
						</div>

						<div class="ab-block-footer-column">
							<i class="far fa-edit"></i>
							<h3><?php esc_html_e( 'Articles & Tutorials', 'responsive-blocks' ); ?></h3>
							<p><?php esc_html_e( 'Check out the Responsive Blocks site to find block editor tutorials, free blocks and updates about the Responsive Blocks plugin and theme!', 'responsive-blocks' ); ?></p>
							<a class="button-primary" href="https://responsiveblocks.com/blog?utm_source=AB%20Theme%20GS%20Page%20Footer%20Blog"><?php esc_html_e( 'Visit the Blog', 'responsive-blocks' ); ?></a>
						</div>

						<div class="ab-block-footer-column">
							<i class="far fa-newspaper"></i>
							<h3><?php esc_html_e( 'Gutenberg News', 'responsive-blocks' ); ?></h3>
							<p><?php esc_html_e( 'Stay up to date with the new WordPress editor. Gutenberg News curates Gutenberg articles, tutorials, videos and more free resources.', 'responsive-blocks' ); ?></p>
							<a class="button-primary" href="http://gutenberg.news/?utm_source=AB%20Theme%20GS%20Page%20Footer%20Gnews"><?php esc_html_e( 'Visit Gutenberg News', 'responsive-blocks' ); ?></a>
						</div>
					</div>

					<div class="ab-footer">
						<p>
							<?php
							/* translators: %1$s StudioPress website URL. %2$s WP Engine website URL. */
							echo sprintf( esc_html__( 'Made by the fine folks at %1$s and %2$s.', 'responsive-blocks' ), '<a href=" ' . esc_url( 'https://studiopress.com/' ) . ' ">StudioPress</a>', '<a href=" ' . esc_url( 'https://wpengine.com/' ) . ' ">WP Engine</a>' );
							?>
						</p>
						<div class="ab-footer-links">
							<a href="https:/responsiveblocks.com/"><?php esc_html_e( 'ResponsiveBlocks.com', 'responsive-blocks' ); ?></a>
							<a href="https://responsiveblocks.com/blog/"><?php esc_html_e( 'Blog', 'responsive-blocks' ); ?></a>
							<a href="https://responsiveblocks.com/responsive-blocks-docs/"><?php esc_html_e( 'Docs', 'responsive-blocks' ); ?></a>
							<a href="https:/twitter.com/responsiveblocks"><?php esc_html_e( 'Twitter', 'responsive-blocks' ); ?></a>
						</div>
					</div>
				</div><!-- .footer-wrap -->
			</div><!-- .panel -->
		</div><!-- .panels -->
	</div><!-- .getting-started -->
	<?php
}

/**
 * Renders the plugin settings page.
 */
function responsive_blocks_render_settings_page() {

	$pages_dir = trailingslashit( dirname( __FILE__ ) ) . 'pages/';

	include $pages_dir . 'settings-main.php';
}

add_action( 'admin_init', 'responsive_blocks_save_settings' );
/**
 * Saves the plugin settings.
 */
function responsive_blocks_save_settings() {

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Handled below.
	if ( empty( $_POST['responsive-blocks-settings'] ) ) {
		return;
	}

	if ( empty( $_POST['responsive-blocks-settings-save-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['responsive-blocks-settings-save-nonce'] ) ), 'responsive-blocks-settings-save-nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Handled below.
	$posted_settings = $_POST['responsive-blocks-settings'];

	/**
	 * Process the Mailchimp API key setting.
	 */
	if ( ! empty( $posted_settings['mailchimp-api-key'] ) ) {
		update_option( 'responsive_blocks_mailchimp_api_key', sanitize_text_field( wp_unslash( $posted_settings['mailchimp-api-key'] ) ), false );
	} else {
		delete_option( 'responsive_blocks_mailchimp_api_key' );
	}

	$redirect = remove_query_arg( 'responsive-blocks-settings-saved', wp_get_referer() );
	wp_safe_redirect( $redirect . '&responsive-blocks-settings-saved=true' );
	exit;
}
