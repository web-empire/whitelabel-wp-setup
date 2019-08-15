<?php
/**
 * WHITELABEL_WP_CONTENT Common Functions
 *
 * @since  1.0.0
 * @package WHITELABEL_WP_CONTENT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Whitelabel_WP_Meta_Fields' ) ) :

    /**
	 * Whitelabel_WP_Meta_Fields Class
	 *
	 * @since 1.0.0
	 */
    class Whitelabel_WP_Meta_Fields {

        /**
         * Get Check-box field.
         *
         * @param string $field_data Field key.
         * @return array.
         */
        public function get_checkbox_field( $field_data ) {

            $field_span     = '';
            $field_checkbox = '';
            $field_html     = '';
            $value          = $field_data['id'];
            $checked        = $field_data['is_checked'];
            $is_nw_active   = $field_data['is_nw_active'];
            $label          = isset( $field_data['name'] ) ? $field_data['name'] : '';

            if ( isset( $field_data['is_nw_active'] ) && $field_data['is_nw_active'] ) {
                $field_span = '<span class="network-active"> Network Active </span>';
            }

            $field_html .= '<label class="switch switch-yes-no">';
            $field_html .= '<input type="checkbox" ' . $checked . ' id="' . $value . '" class="switch-input required-plugins" init="' . $field_data['init'] . '" value="' . $value . '" name="' . $field_data['name'] . '">';
            $field_html .= '<span class="switch-label" data-on="Yeah" data-off="Nope"></span>';
            $field_html .= '<span class="switch-handle"></span>';
            $field_html .= '</label>';

            if ( ! empty( $label ) ) {
                $field_html .= '<label for="' . $value . '"><span>' . $label . '</span>' . wp_kses_post( $field_span ) . '</label>';
            }

            return $field_html;
        }

        /**
         * Get text meta field.
         *
         * @param string $field_data Field key.
         * @return array.
         */
        public function get_text_field( $field_data ) {

            $field_content  = '';
            $field_html     = '';
            $value          = $field_data['id'];
            $flag           = $field_data['flag'];
            $meta_key       = $field_data['value'];
            $label          = isset( $field_data['name'] ) ? $field_data['name'] : '';
            $help           = isset( $field_data['help'] ) ? $field_data['help'] : '';
            $help_img_url   = WHITELABEL_WP_CONTENT_URL . 'assets/img/whitelabel-help-' . $value .  '.png';
            $help_img       = WHITELABEL_WP_CONTENT_DIR . 'assets/img/whitelabel-help-' . $value .  '.png';

            switch( $value ) {

                case 'PluginURI':
                case 'AuthorURI':
                    $type = 'url';
                    $meta_key = esc_url( $meta_key );
                    break;

                case 'Version':
                    $type = 'number';
                    $meta_key = esc_attr( $meta_key );
                    break;

                default:
                    $type = 'text';
                    $meta_key = esc_attr( $meta_key );
                    break;
            }

            if ( isset( $flag ) && $flag ) {
                $field_content    = ' value="' . $meta_key . '" ';
            } else {
                $field_content    = ' placeholder="' . $meta_key . '" ';
            }

            if ( ! empty( $help ) && file_exists( $help_img ) ) {
				$field_html     .= '<i class="whitelabel-field-help dashicons dashicons-editor-help">';
				$field_html     .= '</i>';
				$field_html     .= '<span class="whitelabel-tooltip-text">';
                $field_html     .= $help;

                $field_html     .= '<br/><span><img src="' . $help_img_url . '" class="whitelabel-tooltip-image"></span>';
				$field_html     .= '</span>';
			}

            $field_html     .= '<input type="' . $type . '" id="' . $value . '" class="plugin-detail-input" init="' . $value . '" name="' . $label . '"' . $field_content . '>';

            return $field_html;
        }

        /**
         * Get text-area field.
         *
         * @param string $field_data Field key.
         * @return array.
         */
        public function get_area_field( $field_data ) {

            $field_html     = '';
            $placeholder    = '';
            $value          = $field_data['id'];
            $flag           = $field_data['flag'];
            $meta_key       = $field_data['value'];
            $label          = isset( $field_data['name'] ) ? $field_data['name'] : '';
            $help           = isset( $field_data['help'] ) ? $field_data['help'] : '';

            if ( isset( $flag ) && ! $flag ) {
                $placeholder    = ' placeholder="' . $meta_key . '" ';
            }

            if ( ! empty( $help ) ) {
				$field_html     .= '<i class="whitelabel-field-help dashicons dashicons-editor-help">';
				$field_html     .= '</i>';
				$field_html     .= '<span class="whitelabel-tooltip-text">';
                $field_html     .= $help;
                $help_img_url    = WHITELABEL_WP_CONTENT_URL . 'assets/img/whitelabel-help-' . $value .  '.png';
                $field_html     .= '<span><img src="' . $help_img_url . '" class="whitelabel-tooltip-image"></span>';
				$field_html     .= '</span>';
			}

            $field_html  .= '<textarea id="' . $value . '" class="plugin-detail-input" init="' . $value . '" name="' . $label . '"' . $placeholder . ' rows="4" cols="50">';
            
            if ( isset( $flag ) && $flag ) {
                $field_html    .= esc_textarea( $meta_key );
            }

            $field_html .= '</textarea>';

            return $field_html;
        }
    }

endif;
