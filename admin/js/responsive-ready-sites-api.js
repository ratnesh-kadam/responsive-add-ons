/**
 * Responsive Ready Sites API
 *
 * @package RESPONSIVE ADDONS
 */

(function($){

	ResponsiveSitesAPI = {

		_api_url      : responsiveSitesApi.ApiURL,
		_stored_data  : {
			'responsive-ready-sites' : [],
		},

		/**
		 * API Request
		 */
		_api_request: function( args, callback ) {

			var params = {
				method: 'GET',
				cache: 'default',
			};

			if ( responsiveSitesRender.headers ) {
				params['headers'] = responsiveSitesRender.headers;
			}

			/*fetch( ResponsiveSitesAPI._api_url + args.slug, params).then(response => {
				if ( response.status === 200 ) {
					return response.json().then(items => ({
						items 		: items,
					}))
				} else {
					return response.json();
				}
			})
				.then(data => {
					if( 'object' === typeof data ) {
						data.items = [
							{id: 1, responsive_site_url: "https://ccdemos.cyberchimps.com", feature_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testone", status: "publish"},
							{id: 2, responsive_site_url: "https://ccdemos.cyberchimps.com", feature_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testtwo", status: "publish"},
							{id: 3, responsive_site_url: "https://ccdemos.cyberchimps.com", feature_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testthree", status: "publish"}
						];

						console.log(data.items);
						data['args'] = args;
						if( data.args.id ) {
							ResponsiveSitesAPI._stored_data[ args.id ] = $.merge( ResponsiveSitesAPI._stored_data[ data.args.id ], data.items );
						}

						if( 'undefined' !== typeof args.trigger && '' !== args.trigger ) {
							$(document).trigger( args.trigger, [data] );
						}

						if( callback && typeof callback == "function"){
							callback( data );
						}
					}
				});*/
			{}
			var data = [{items:[
				{id: 1, responsive_site_url: "https://ccdemos.cyberchimps.com/", featured_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testone", status: "publish", name: "Test One"},
				{id: 2, responsive_site_url: "https://websitedemos.net/fitness-trainer-04/", featured_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testtwo", status: "publish", name: "Test Two"},
				{id: 3, responsive_site_url: "https://websitedemos.net/disc-jockey-04/", featured_image_url: "https://websitedemos.net/wp-content/uploads/2019/06/learn-dash-featured-image.jpg", link: "https://websitedemos.net/blog/astra-site/ecourse-3/", wpforms_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wpforms.json", xml_path: "https://websitedemos.net/wp-content/uploads/astra-sites/457/wxr.xml", slug: "testthree", status: "publish",name: "Test Three"}
				]}];

			data['args'] = args;
			if ( data.args.id ) {
				// ResponsiveSitesAPI._stored_data[ args.id ] = $.merge( ResponsiveSitesAPI._stored_data[ data.args.id ], data.items );.
			}

			if ( 'undefined' !== typeof args.trigger && '' !== args.trigger ) {
				$( document ).trigger( args.trigger, [data] );
			}

			if ( callback && typeof callback == "function") {
				callback( data );
			}

		},

	};

})( jQuery );
