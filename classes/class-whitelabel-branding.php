<?php
/**
 * White labeling for Whitelabel WP Content
 *
 * @package Whitelabel WP Content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Whitelabel_Branding' ) ) :

	/**
	 * Whitelabel_Branding initial setup
	 *
	 * @since 1.0.0
	 */
	/**
	 * This class initializes whitelabel Branding
	 *
	 * @class Whitelabel_Branding
	 */
	final class Whitelabel_Branding {

		/**
		 *  Constructor
		 */
		public function __construct() {
			// Initializes branding filter.
			add_filter( 'all_plugins', __CLASS__ . '::update_whitelabel_branding' );
		}

		/**
		 * Branding on the plugins page.
		 *
		 * @since 1.0.0
		 * @param array $plugins An array data for each plugin.
		 * @return array
		 */
		static public function update_whitelabel_branding( $plugins ) {

			$whitelabeled_wp_data = null;

			if( is_multisite() ) {
				$whitelabeled_wp_data = get_site_option( 'site_whitelabelled_data' );
			} else {
				$whitelabeled_wp_data = get_option( 'site_whitelabelled_data' );
			}

			if ( ! empty( $whitelabeled_wp_data ) ) {
				foreach ( $whitelabeled_wp_data as $data => $key ) {

					$basename      = $key['init'];
					$plugin_slug   = strtok( $basename, '/' );

					foreach ( $key['required_meta_data'] as $required_meta => $final_meta ) {
						if( isset( $final_meta['Name'] ) && ! empty( $final_meta['Name'] ) ) {
							$plugin_name		 					= sanitize_text_field( $final_meta['Name'] );
							$plugins[ $basename ]['Name']  			= esc_attr( $plugin_name );
							$plugins[ $basename ]['Title'] 			= esc_attr( $plugin_name );
						}

						if( isset( $final_meta['Version'] ) && ! empty( $final_meta['Version'] ) ) {
							$plugin_version		 					= sanitize_text_field( $final_meta['Version'] );
							$plugins[ $basename ]['Version']  		= esc_attr( $plugin_version );
						}

						if( isset( $final_meta['DomainPath'] ) && ! empty( $final_meta['DomainPath'] ) ) {
							$plugin_domainpath		 				= sanitize_text_field( $final_meta['DomainPath'] );
							$plugins[ $basename ]['DomainPath']  	= esc_attr( $plugin_domainpath );
						}

						if( isset( $final_meta['Network'] ) && ! empty( $final_meta['Network'] ) ) {
							$plugin_network		 					= sanitize_text_field( $final_meta['Network'] );
							$plugins[ $basename ]['Network']  		= esc_attr( $plugin_network );
						}

						if( isset( $final_meta['PluginURI'] ) && ! empty( $final_meta['PluginURI'] ) ) {
							$plugins[ $basename ]['PluginURI']  	= esc_url( $final_meta['PluginURI'] );
						}

						if( isset( $final_meta['Description'] ) && ! empty( $final_meta['Description'] ) ) {
							$plugin_description 					= sanitize_textarea_field( $final_meta['Description'] );
							$plugins[ $basename ]['Description']  	= esc_textarea( $plugin_description );
						}

						if( isset( $final_meta['Author'] ) && ! empty( $final_meta['Author'] ) ) {
							$plugin_author		 					= sanitize_text_field( $final_meta['Author'] );
							$plugins[ $basename ]['Author']  		= esc_attr( $plugin_author );
							$plugins[ $basename ]['AuthorName'] 	= esc_attr( $plugin_author );
						}

						if( isset( $final_meta['AuthorURI'] ) && ! empty( $final_meta['AuthorURI'] ) ) {
							$plugins[ $basename ]['AuthorURI']  	= esc_url( $final_meta['AuthorURI'] );
						}
					}
				}
			}
			
			return $plugins;
		}
	}

	/**
	 * Kicking this off by new calling an instance
	 */
	new Whitelabel_Branding();

endif;
