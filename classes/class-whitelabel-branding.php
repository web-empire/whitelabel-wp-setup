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
	 * This class initializes whitelabel Branding -  Whitelabel_Branding initial setup
	 *
	 * @class Whitelabel_Branding
	 * @since 1.0.0
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

			$whitelabeled_wp_data = Whitelabel_WP_Content_Loader::$whitelabeled_wp_data;

			if ( ! empty( $whitelabeled_wp_data ) ) {
				foreach ( $whitelabeled_wp_data as $data => $key ) {
					$basename      = $key['init'];
					if ( isset( $plugins[ $basename ] ) ) {
						foreach ( $key['required_meta_data'] as $required_meta => $final_meta ) {
							foreach ( $final_meta as $meta => $meta_key ) {
								if( ! empty( $meta_key ) ) {
									switch ( $meta ) {
										case 'Name':
											$plugins[ $basename ]['Name'] = esc_attr( $meta_key );
											$plugins[ $basename ]['Title'] = esc_attr( $meta_key );
											break;

										case 'Author':
											$plugins[ $basename ]['Author'] = esc_attr( $meta_key );
											$plugins[ $basename ]['AuthorName'] = esc_attr( $meta_key );
											break;

										case 'Version':
											$plugins[ $basename ]['Version'] = floatval( esc_attr( $meta_key ) );
											break;

										case 'DomainPath':
											$plugins[ $basename ]['DomainPath'] = esc_attr( $meta_key );
											break;

										case 'Network':
											$plugins[ $basename ]['Network'] = esc_attr( $meta_key );
											break;

										case 'PluginURI':
											$plugins[ $basename ]['PluginURI'] = esc_url( $meta_key );
											break;

										case 'AuthorURI':
											$plugins[ $basename ]['AuthorURI'] = esc_url( $meta_key );
											break;

										case 'Description':
											$plugins[ $basename ]['Description'] = esc_textarea( $meta_key );
											break;
									}
								}
							}
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
