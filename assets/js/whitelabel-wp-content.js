( function($) {

	WhitelabelWPContent = {

		/**
		 * Init
		 */
		init: function()
		{
			this._bind();

			$( ".whitelabel-wp-content__page-wrap:first-child" ).addClass( "whitelabel-item__open" );
			WhitelabelWPContent._select_plugins();
			WhitelabelWPContent._add_tool_tip_msg();
		},

		/**
		 * Bind events
		 */
		_bind: function()
		{
			$( document ).on( 'hover', '.whitelabel-field-help', WhitelabelWPContent._add_tool_tip_msg );
			$( document ).on( 'click', '.update_whitelabel_data',	WhitelabelWPContent._whitelabel_data );
			$( document ).on( 'click', '.whitelabel-wp-content__title', WhitelabelWPContent._toggle_plugin_settings );
			$( document ).on( 'change', '.required-plugins', WhitelabelWPContent._select_respective_plugins );

			$( '.required-plugins' ).on( 'change', function() {
				if( $( '.required-plugins:checked' ).length > 3 ) {
					this.checked = false;
				}
			});
		},

		/**
		 * Select Respective Plugins
		 */
		_select_plugins: function( event ) {

			$( '.required-plugins' ).each( function( index ) {

				var pluginSlug = jQuery( this ).val();

				if ( $( this ).is( ':checked' ) ) {
					$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).addClass( "whitelabel-this-plugin" );
					$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).show();
				} else {
					$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).removeClass( "whitelabel-this-plugin" );
					$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).hide();
				}
			});
		},

		/**
		 * Single Export
		 *
		 * Export single astra site from Astra site export page.
		 */
		_whitelabel_data: function( event ) {
			/* Act on the event */
			event.preventDefault();

			$this = jQuery( this );
			$this.addClass('installing updating-message');
			$this.text( whitelabelLocalizeStings.processing );

			/**
			 * Whitelabel Data
			 *
			 * Grab all the pages related data
			 */
			var whitelabelMeta = [];


			jQuery('.whitelabel-this-plugin').each(function(index, el) {

				var pluginInit  = jQuery( this ).data( 'plugin-init' );
				var pluginSlug  = jQuery( this ).data( 'plugin-slug' );
				var pluginTitle = jQuery( this ).data( 'plugin-title' );

				/**
				 * Required Plugins for site.
				 */
				var pluginData = [];

				jQuery( this ).find('.required-page-plugins .plugin-detail-input').each(function(index, el) {
					individualPluginData = jQuery( this );
					var selfPlugin = {};
					var key = individualPluginData.attr('id');
					selfPlugin[key] = individualPluginData.val();
					pluginData.push( selfPlugin );
				});

				whitelabelMeta.push( { 'init' : pluginInit, 'id' : pluginSlug, 'title' : pluginTitle, 'required_meta_data' : pluginData } );
			});


			/**
			 * Required Plugins.
			 */
			var requiredPlugins = [];

			jQuery('.required-plugins:checked').each(function(index, el) {
				pluginCheckboxes = jQuery( this );
				var plugin = {};
				plugin['slug'] 	= pluginCheckboxes.val();
				plugin['init'] 	= pluginCheckboxes.attr('init');
				plugin['name'] 	= pluginCheckboxes.attr('name');
				requiredPlugins.push( plugin );
			});

			var data = {
				action: 'whitelabel_wp_environment',
				security: whitelabelLocalizeStings.ajax_nonce,
				required_meta_data: JSON.stringify( requiredPlugins ),
				site_whitelabel_meta: whitelabelMeta,
			}

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: data,
			})
			.done(function( response ) {
				setTimeout(function() {
					$this.removeClass('installing updating-message');
					$this.text( whitelabelLocalizeStings.processed );
					location.reload();
				}, 1000);
			})
			.fail(function() {
				console.log("error");
			});
		},

		/**
		 * Toggle Page Setting accordion
		 */
		_toggle_plugin_settings: function( event ) {

			$page_wrapper = $( this ).parent().parent();
			var target = $( this ).closest('.required-page-plugins').find('.whitelabel-wp-content__table-wrap');

			if( $page_wrapper.hasClass( 'whitelabel-item__open' ) ) {
				$page_wrapper.removeClass('whitelabel-item__open');
				target.slideUp();
			} else {
				target.slideDown();
				$page_wrapper.addClass('whitelabel-item__open');
			}
		},

		/**
		 * Tooltip.
		 */
		_add_tool_tip_msg: function(event) {
			var tip_wrap = $(this).closest('.whitelabel-content__page-row');
			closest_tooltip = tip_wrap.find('.whitelabel-tooltip-text');
			closest_tooltip.toggleClass('display_tool_tip');
		},

		/**
		 * Select Respective Plugins
		 */
		_select_respective_plugins: function( event ) {

			if( jQuery( this ).length > 2 ) {
				this.checked = false;
			}

			var pluginSlug = jQuery( this ).val();

			if ( $( this ).is( ':checked' ) ) {
				$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).addClass( "whitelabel-this-plugin" );
				$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).show();
			} else {
				$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).removeClass( "whitelabel-this-plugin" );
				$( '.required-page-plugins[data-plugin-slug="' + pluginSlug + '"]' ).closest( '.whitelabel-wp-content__page-wrap' ).hide();
			}
		},
	};

	/**
	 * Initialization
	 */
	$( function() {
		"use strict";
		WhitelabelWPContent.init();
	});

})(jQuery);
