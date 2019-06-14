"use strict";

var $ = jQuery;

var ResponsiveBlocksNewsletterSubmission = {

	init: function() {
		$( '.ra-newsletter-submit' ).on( 'click', function( event ) {

			event.preventDefault();

			wp.a11y.speak( responsive_blocks_newsletter_vars.l10n.a11y.submission_processing );

			var button = $( this );

			var button_text_original = button.val();

			button.val( responsive_blocks_newsletter_vars.l10n.button_text_processing ).prop( 'disabled', true );

			var form = $( this ).parents( 'form' );

			var nonce = button.parent().find( "[name='ra-newsletter-form-nonce']" ).val();

			var email = button.parent().find( "[name='ra-newsletter-email-address']" ).val();

			var provider = button.parent().find( "[name='ra-newsletter-mailing-list-provider']" ).val();

			var list = button.parent().find( "[name='ra-newsletter-mailing-list']" ).val();

			var successMessage = button.parent().find( "[name='ra-newsletter-success-message']" ).val();

			var errorMessageContainer = button.parents( '.ra-block-newsletter' ).find( '.ra-block-newsletter-errors' );

			var ampEndpoint = button.parent().find( "[name='ra-newsletter-amp-endpoint-request']" ).val();

			if ( ! email ) {
				setTimeout( function() {
					button.val( button_text_original ).prop( 'disabled', false );
					wp.a11y.speak( responsive_blocks_newsletter_vars.l10n.a11y.submission_failed );
				}, 400 );
				return;
			}

			if ( ! provider || ! list ) {
				form.html( '<p class="ra-newsletter-submission-message">' + responsive_blocks_newsletter_vars.l10n.invalid_configuration + '</p>' );
				return;
			}

			$.ajax( {
				data: {
					action: 'responsive_blocks_newsletter_submission',
					'ra-newsletter-email-address': email,
					'ra-newsletter-mailing-list-provider': provider,
					'ra-newsletter-mailing-list': list,
					'ra-newsletter-form-nonce': nonce,
					'ra-newsletter-success-message': successMessage,
					'ra-newsletter-amp-endpoint-request': ampEndpoint,
				},
				type: 'post',
				url: responsive_blocks_newsletter_vars.ajaxurl,
				success: function( response ) {
					if ( response.success ) {
						form.html( '<p class="ra-newsletter-submission-message">' + response.data.message + '</p>' );
						wp.a11y.speak( responsive_blocks_newsletter_vars.l10n.a11y.submission_succeeded );
					}

					if ( ! response.success ) {
						errorMessageContainer.html( '<p>' + response.data.message + '</p>' ).fadeIn();
						button.val( button_text_original ).prop( 'disabled', false );
						wp.a11y.speak( responsive_blocks_newsletter_vars.l10n.a11y.submission_failed );
					}

				},
				failure: function( response ) {
					errorMessageContainer.html( '<p>' + response.data.message + '</p>' ).fadeIn();
				}

			} );
		} );

		$( '.ra-newsletter-email-address-input' ).on( 'keyup', function( event ) {
			$( '.ra-block-newsletter-errors' ).html('').fadeOut();
		} );
	}
}

$( document ).ready( function() {
	ResponsiveBlocksNewsletterSubmission.init();
} );
