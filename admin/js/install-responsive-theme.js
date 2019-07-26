(function($){

    InstallResponsiveTheme = {

        /**
         * Init
         */
        init: function() {
            this._bind();
        },

        /**
         * Binds events for the Responsive Theme Installation.
         *
         * @since 1.3.2
         *
         * @access private
         * @method _bind
         */
        _bind: function()
        {
            $( document ).on( 'click', '.responsive-sites-theme-not-installed', InstallResponsiveTheme._install_and_activate );
            $( document ).on( 'click', '.responsive-sites-theme-installed-but-inactive', InstallResponsiveTheme._activateTheme );
            $( document ).on('wp-theme-install-success' , InstallResponsiveTheme._activateTheme);
        },

        /**
         * Activate Theme
         *
         * @since 1.3.2
         */
        _activateTheme: function( event, response ) {
            event.preventDefault();

            $('#responsive-theme-activation-nag a').addClass('processing');

            if( response ) {
                $('#responsive-theme-activation-nag a').text( ResponsiveInstallThemeVars.installed );
            } else {
                $('#responsive-theme-activation-nag a').text( ResponsiveInstallThemeVars.activating );
            }

            // WordPress adds "Activate" button after waiting for 1000ms. So we will run our activation after that.
            setTimeout( function() {

                $.ajax({
                    url: ResponsiveInstallThemeVars.ajaxurl,
                    type: 'POST',
                    data: {
                        'action' : 'responsive-ready-sites-activate-theme'
                    },
                })
                    .done(function (result) {
                        if( result.success ) {
                            $('#responsive-theme-activation-nag a').text( ResponsiveInstallThemeVars.activated );

                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }

                    });

            }, 3000 );

        },

        /**
         * Install and activate
         *
         * @since 1.3.2
         *
         * @param  {object} event Current event.
         * @return void
         */
        _install_and_activate: function(event ) {
            event.preventDefault();
            var theme_slug = $(this).data('theme-slug') || '';
            var btn = $( event.target );

            if ( btn.hasClass( 'processing' ) ) {
                return;
            }

            btn.text( ResponsiveInstallThemeVars.installing ).addClass('processing');

            if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
                wp.updates.requestFilesystemCredentials( event );
            }

            wp.updates.installTheme( {
                slug: theme_slug
            });
        }

    };

    /**
     * Initialize
     */
    $(function(){
        InstallResponsiveTheme.init();
    });

})(jQuery);