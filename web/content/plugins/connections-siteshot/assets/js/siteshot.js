 /*
  * TinyMCE
  */
(function() {
	tinymce.create('tinymce.plugins.siteshot', {
		init: function( ed, url ) {
			ed.addButton( 'siteshot', {

				title: 'SiteShot',
				image: url.replace( '/assets/js', '/assets/images' ) + '/siteshot-icon.png',
				onclick: function() {

					var website = prompt( "Website Address:", "http://connections-pro.com" );
					var width   = prompt( "SiteShot Width:", "300" );

					if ( website != null && website != '' ) {

						if ( width != null && width != '' ) {

							var shortcode = '[siteshot width="' + width + '" url="' + website + '"]';
							ed.execCommand( 'mceInsertContent', false, shortcode );

						} else {

							var shortcode = '[siteshot url="' + website + '"]';
							ed.execCommand( 'mceInsertContent', false, shortcode );
						}
					}
				}
			});
		},
		createControl: function( n, cm ) {
			return null;
		},
		getInfo: function() {
			return {
				longname:  "SiteShot",
				author:    'Steven A. Zahm',
				authorurl: 'http://connections-pro.com',
				infourl:   'http://connections-pro.com/add-on/siteshot/',
				version:   "1.0"
			};
		}
	});
	tinymce.PluginManager.add('siteshot', tinymce.plugins.siteshot);
})();