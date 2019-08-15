<?php
/**
 * WHITELABEL_WP_CONTENT Admin
 *
 * @since  1.0.0
 * @package WHITELABEL_WP_CONTENT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Whitelabel_WP_Content_Loader' ) ) :

	/**
	 * Whitelabel_WP_Content_Loader
	 *
	 * @since 1.0.0
	 */
	class Whitelabel_WP_Content_Loader {

		/**
		 * Member Variable
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public $meta = null;

		/**
		 * All plugins
		 *
		 * @access private
		 * @since 1.0.0
		 */
		private static $all_plugins = array();

		/**
		 * Whitelabel enabled plugins
		 *
		 * @access private
		 * @since 1.0.0
		 */
		private static $whitelabel_enabled_plugins = array();

		/**
		 * Whitelabeled WP Data
		 *
		 * @access private
		 * @since 1.0.0
		 */
		public static $whitelabeled_wp_data = array();

		/**
		 * Whitelabeled new plugin sequence
		 *
		 * @access private
		 * @since 1.0.0
		 */
		private static $enabled_plugins_new_sequence = array();

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Define required things.
			add_action( 'admin_init', array( $this, 'run_loader' ) );

			// Required Actions.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_whitelabel_scripts' ) );

			// Ajax handler.
			add_action( 'wp_ajax_whitelabel_wp_environment', array( $this, 'whitelabel_wp_environment' ) );

			// Register Admin Menu page.
			if( is_multisite() ) {
				add_action( 'network_admin_menu', array( $this, 'register_network_whitelabel_menu' ) );
			} else {
				add_action( 'admin_menu', array( $this, 'register_whitelabel_menu' ) );
			}

			// Refresh branding after deactivation.
			register_deactivation_hook( 'WHITELABEL_WP_CONTENT_FILE', array( $this, 'deactivation_whitelabel_wp_setup' ) );
		}

		/**
		 * Run required things.
		 *
		 * @since 1.0.0
		 */
		public function run_loader() {

			// Render Admin data.
			self::$all_plugins = get_plugins();

			if( is_multisite() ) {
				self::$whitelabel_enabled_plugins = get_site_option( 'site_whitelabelled_plugins' );
				self::$whitelabeled_wp_data = get_site_option( 'site_whitelabelled_data' );
			} else {
				self::$whitelabel_enabled_plugins = get_option( 'site_whitelabelled_plugins' );
				self::$whitelabeled_wp_data = get_option( 'site_whitelabelled_data' );
			}

			// Get all required files.
			$this->load_core_files();

			// Render Admin Meta Fields.
			$this->meta = new Whitelabel_WP_Meta_Fields();

			// Load textdomain translations.
            $this->load_textdomain();
		}

		/**
		 * Load Core Files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_files() {

			// Admin Meta Fields.
			require_once WHITELABEL_WP_CONTENT_DIR . 'classes/class-whitelabel-wp-meta-fields.php';

			// Whitelabel Branding.
			require_once WHITELABEL_WP_CONTENT_DIR . 'classes/class-whitelabel-branding.php';
		}

		/**
		 * Whitelabel WP Plugin Deactivate
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function deactivation_whitelabel_wp_setup() {
			// flush rewrite rules.
			flush_rewrite_rules();
		}

		/**
		 * Load nwcc Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *      1. Global Languages /wp-content/languages/network-wide-custom-code/ folder
		 *      2. Local dorectory /wp-content/plugins/network-wide-custom-code/languages/ folder
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			/**
			 * Filters the languages directory path to use for AffiliateWP.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'whitelabel_languages_directory', WHITELABEL_WP_CONTENT_ROOT . '/languages/' );

			load_plugin_textdomain( 'whitelabel-wp-content', false, $lang_dir );
		}

		/**
		 * AJAX action to update meta.
		 *
		 * @return void
		 */
		public static function whitelabel_wp_environment() {

			check_ajax_referer( 'whitelabel-admin-nonce', 'security' );

			/**
			 * Set Whitelabel processed Plugins.
			 */
			// $required_meta_data = null;
			$is_multisite = is_multisite();

			$required_meta_data = self::sanitize_plugin_checkboxes( json_decode( wp_unslash( $_POST['required_meta_data'] ) ) );

			if( isset( $required_meta_data ) ) {
				if( $is_multisite ) {
					update_site_option( 'site_whitelabelled_plugins', $required_meta_data );
				} else {
					update_option( 'site_whitelabelled_plugins', $required_meta_data );
				}
			}

			/**
			 * Set Whitelabel processed Data.
			 */
			$site_whitelabel_meta = self::sanitize_form_inputs( $_POST['site_whitelabel_meta'] );

			if( isset( $site_whitelabel_meta ) ) {
				if( $is_multisite ) {
					update_site_option( 'site_whitelabelled_data', $site_whitelabel_meta );
				} else {
					update_option( 'site_whitelabelled_data', $site_whitelabel_meta );
				}
			}

			wp_send_json_success();
		}

		/**
		 * Loop through the checkboxes and sanitize each of the values.
		 *
		 * @param array $input_settings input settings.
		 * @return array
		 */
		public static function sanitize_plugin_checkboxes( $input_settings = array() ) {
			if( ! empty( $input_settings ) ) {
				foreach ( $input_settings as $plugin_key => $plugin ) {
					foreach ( $plugin as $meta => $meta_key ) {
						$meta_key = sanitize_key( $meta_key );
					}
				}
			}
			return $input_settings;
		}

		/**
		 * Loop through the input and sanitize each of the values.
		 *
		 * @param array $input_settings input settings.
		 * @return array
		 */
		public static function sanitize_form_inputs( $input_settings = array() ) {
			if ( ! empty( $input_settings ) ) {
				foreach ( $input_settings as $data => $key ) {
					foreach ( $key['required_meta_data'] as $required_meta => $final_meta ) {
						foreach ( $final_meta as $meta => $meta_key ) {
							if( ! empty( $meta_key ) ) {
								switch ( $meta ) {
									case 'Name':
									case 'Author':
										$meta_key = sanitize_text_field( $meta_key );
										break;

									case 'Version':
										$meta_key = floatval( wp_unslash( $meta_key ) );
										break;

									case 'PluginURI':
									case 'AuthorURI':
										$meta_key = esc_url( $meta_key );
										break;

									case 'Description':
										$meta_key = sanitize_textarea_field( $meta_key );
										break;

									default:
										$meta_key = sanitize_text_field( $meta_key );
										break;
								}
							}
						}
					}
				}
			}
			return $input_settings;
		}

		/**
		 * Enqueue Admin Scripts.
		 *
		 * @param  string $hook Current page string.
		 * @return void
		 */
		public function enqueue_whitelabel_scripts( $hook ) {

			wp_register_script( 'whitelabel-wp-content-js', WHITELABEL_WP_CONTENT_URL . 'assets/js/whitelabel-wp-content.js', array( 'jquery', 'jquery-ui-sortable' ), WHITELABEL_WP_CONTENT_VER, true );

			wp_register_style( 'whitelabel-wp-content-css', WHITELABEL_WP_CONTENT_URL . 'assets/css/whitelabel-wp-content.css', null, WHITELABEL_WP_CONTENT_VER, 'all' );

			// Localize the script with new data.
			$translation_array = array(
				'processing'                => esc_html__( 'Processing...', 'whitelabel-wp-content' ),
				'processed'                 => esc_html__( 'Done', 'whitelabel-wp-content' ),
				'ajax_nonce'           		=> wp_create_nonce( 'whitelabel-admin-nonce' ),
			);
			wp_localize_script( 'whitelabel-wp-content-js', 'whitelabelLocalizeStings', $translation_array );

			// Enqueue on astra site export page.
			if ( 'settings_page_whitelabel-wp-content' === $hook || 'toplevel_page_whitelabel-wp-content' === $hook ) {
				wp_enqueue_script( 'whitelabel-wp-content-js' );
				wp_enqueue_style( 'whitelabel-wp-content-css' );
			}
		}
        
		/**
		 * Register Whitelabel Admin Menu for network
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function register_network_whitelabel_menu() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			add_submenu_page( 'settings.php', esc_html__( 'Whitelabel WP Setup', 'whitelabel-wp-content' ), esc_html__( 'Whitelabel WP Setup', 'whitelabel-wp-content' ), 'manage_options', 'whitelabel-wp-content', array( $this, 'whitelabel_fetch_wp_content' ) );
		}
		
		/**
		 * Register Whitelabel Admin Menu for single site
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function register_whitelabel_menu() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			add_submenu_page( 'options-general.php', esc_html__( 'Whitelabel WP Setup', 'whitelabel-wp-content' ), esc_html__( 'Whitelabel WP Setup', 'whitelabel-wp-content' ), 'manage_options', 'whitelabel-wp-content', array( $this, 'whitelabel_fetch_wp_content' ) );
		}
		
		/**
		 * Active Theme
		 *
		 * @since 1.0.0
		 * @return void
		 */
		private function listed_active_theme() {
			$active_theme = wp_get_theme();
			?>
				<tr>
					<th scope="row">
						<span class="dashicons-before dashicons-admin-appearance"></span>
						<?php echo esc_html__( 'Active Theme', 'whitelabel-wp-content' ); ?>
					</th>
					<td>
						<div class="active-plugins-list">
							
							<div class="item">
								<label>
									<?php echo esc_attr( $active_theme->name ); ?>
								</label>
							</div>

						</div>
					</td>
				</tr>
			<?php
		}

        /**
		 * Active Plugins
		 *
		 * @since 1.0.0
		 * @return void
		 */
		private function listed_all_active_plugins() {

			$plugins       	 = self::$all_plugins;
			$exist_plugins 	 = array();
			$new_sequence    = array();
			$stored_plugins  = self::$whitelabel_enabled_plugins;

			if ( ! empty( $stored_plugins ) ) {
				foreach ( $stored_plugins as $plugin_key => $plugin ) {
					$exist_plugins[]               = $plugin->init;
					if ( isset( $plugins[ $plugin->init ] ) ) {
						$new_sequence[ $plugin->init ] = $plugins[ $plugin->init ];
						unset( $plugins[ $plugin->init ] );
					}
				}
			}

			// Merge the new sequence so only active plugins showing at the top/first.
			self::$enabled_plugins_new_sequence = array_merge( $new_sequence, $plugins );

			?>
			<tr>
				<th scope="row">
					<span class="dashicons-before dashicons-admin-plugins"></span>
					<?php echo esc_html__( 'Active Plugins', 'whitelabel-wp-content' ); ?>
				</th>
				<td>
					<div class="active-plugins-list">
						<?php
							foreach ( self::$enabled_plugins_new_sequence as $plugin_init => $plugin ) {

								// Mark checked for stored plugins.
								$checked = '';
								if ( in_array( $plugin_init, $exist_plugins, true ) ) {
									$checked = ' checked="checked" ';
								}

								// Highlight if plugin is network wide active.
								$is_nw_active = false;
								if ( is_plugin_active_for_network( $plugin_init ) ) {
									$is_nw_active = true;
								}

								$plugin_slug = strtok( $plugin_init, '/' );

								?>
									<div class="item">

										<?php
											echo $this->meta->get_checkbox_field(
												array(
													'id'		   => $plugin_slug,
													'init'		   => $plugin_init,
													'name'         => $plugin['Name'],
													'is_checked'   => $checked,
													'is_nw_active' => $is_nw_active,
												)
											);
										?>

									</div>
								<?php
							}
						?>
					</div>
				</td>
			</tr>

			<?php
		}
        
        /**
		 * Whitelabel It Page Contents
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function whitelabel_fetch_wp_content() {

			$webempire_visit_site_url = apply_filters( 'webempire_site_url', 'https://webempire.org.in/?utm_campaign=web-agency&utm_medium=website&utm_source=google' );
			$get_pro_url = apply_filters( 'get_pro_url', '#' );

			?>
			<form action='<?php admin_url( 'tools.php?page=whitelabel-wp-content' ); ?>' class="wrap form-whitelabel-wp" method='post'>

				<div class="social-menu-page-header">
					<div class="social-container social-flex">
						<div class="social-title">
							<a href="<?php echo esc_url( $webempire_visit_site_url ); ?>" target="_blank" rel="noopener" >
								<h1 class="plug-author-title"> WebEmpire </h1>
								<span class="social-plugin-version"><?php echo esc_attr( WHITELABEL_WP_CONTENT_VER ); ?></span>
							</a>
						</div>

						<div class="social-top-links">
							<?php
								esc_attr_e( 'Now Let\'s be a Brand of the setup!', 'whitelabel-wp-content' );
							?>
						</div>
					</div>
				</div>

				<div class="below-header-menu">
					<div class="whitelabel-help-links">
						<a rel="noopener" href="https://webempire.org.in/docs/?utm_source=google&utm_medium=social-post&utm_campaign=whitelabel-knowledge-base" target="_blank">                                
							<span class="whitelabel-link-icon"><i class="dashicons dashicons-book"></i></span> Knowledge Base </a>
						<a rel="noopener" href="https://webempire.org.in/support/?utm_source=google&utm_medium=email&utm_campaign=whitelabel-get-support" target="_blank">
							<span class="whitelabel-link-icon"><i class="dashicons dashicons-awards"></i></span> Request Support </a>
					</div>
				</div>

				<div class="whitelabel-wp-content-wrap">
					<div class="whitelabel-wp-content-site-wrap">
						<table class="form-table">
							<tbody>
								<?php

									/**
									 * Active Theme
									 */
									$this->listed_active_theme();
									
									/**
									 * Active Plugins List
									 */
									$this->listed_all_active_plugins();								

								?>
							</tbody>
						</table>

						<div>
							<p class="description">
								<?php
									esc_html_e( 'This plugin grants you priviledge to update branding of all the plugins on the plugins page.', 'whitelabel-wp-content' );
								?>
							</p>

							<p class="description">
								<?php
									$get_pro_url = '#';
									$description_get_pro = '<strong> Note: </strong> With this free version you can whitelabel only <strong> 3 </strong> plugins. For more features <a href="' . esc_url( $get_pro_url ) . '"> Get Pro. </a>';
									echo wp_kses_post( $description_get_pro );
								?>
							</p>
							
						</div>

						<br/><br/>						

					</div>

					<div class="whitelabel-wp-content-pages-wrap">
						<?php

							/**
							 * Upcoming Pro Features
							 */
							$this->upcoming_pro_features();

							/**
							 * Plugin Settings
							 */
							$this->plugin_settings();

						?>
					</div>
				</div>

				<a href="#" name="update_whitelabel_data" class="update_whitelabel_data submit button button-primary button-hero">
					<?php _e( 'Save Settings', 'whitelabel-wp-content' ); ?>
				</a>

				<?php wp_nonce_field( 'whitelabel_wp_content', 'start_whitelabel_wp_content' ); ?>

			</form>
			<?php
        }
        
        /**
		 * Plugin Settings
		 *
		 * @since 1.0.0
		 * @param  object  $plugin Plugin Object.
		 * @return void
		 */
		private function plugin_settings() {

			if ( ! empty( self::$enabled_plugins_new_sequence ) ) {

				foreach ( self::$enabled_plugins_new_sequence as $plugin_init => $plugin ) {

					// Mark checked for stored plugins.
					$plugin_slug = strtok( $plugin_init, '/' );
					?>
						<div class="whitelabel-wp-content__page-wrap" data-plugin-init="<?php echo esc_html( $plugin_init ); ?>" data-plugin-slug="<?php echo esc_html( $plugin_slug ); ?>" data-plugin-title="<?php echo esc_html( $plugin['Name'] ); ?>">
							<div class="required-page-plugins" data-plugin-slug="<?php echo esc_attr( $plugin_slug ); ?>">
								<div class="whitelabel-wp-content__title"><h3><?php echo esc_html( $plugin['Name'] ); ?></h3></div>
								<div class="whitelabel-wp-content__table-wrap">
									<table class="form-table">
										<tbody>

											<?php
												echo '<p style="font-size: 13px;">Whitelabel <strong>' . esc_html( $plugin['Name'] ) . '</strong> plugin with following details:</p>' . "\n";

												foreach ( $plugin as $detail => $key ) {
													/**
													 * Render item data content
													 */
													$this->render_item_data( $detail, $key, $plugin_slug );
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					<?php
				}
			}
		}

		/**
		 * Plugin Settings
		 *
		 * @since 1.0.0
		 * @param  object  $plugin Plugin Object.
		 * @return void
		 */
		private function upcoming_pro_features() {

			$upcoming_features	= array (
				'first'		=>	esc_html__( 'Hide this plugin.', 'whitelabel-wp-content' ),
				'second'	=>	esc_html__( 'Whitelabel Themes.', 'whitelabel-wp-content' ),
				'third'		=>	esc_html__( 'Get Whitelabelled Child Theme Generator.', 'whitelabel-wp-content' ),
				'fourth'	=>	esc_html__( 'Update Branding Permanently through wp-config file.', 'whitelabel-wp-content' ),
				'fifth'		=>	esc_html__( 'Much More with Improvements.', 'whitelabel-wp-content' ),
			);

			$features = '';
			$features .= '<p class="widget-feature"><b class="feature"> 01. </b>' . $upcoming_features['first'] . '</p>';
			$features .= '<p class="widget-feature"><b class="feature"> 02. </b>' . $upcoming_features['second'] . '</p>';
			$features .= '<p class="widget-feature"><b class="feature"> 03. </b>' . $upcoming_features['third'] . '</p>';
			$features .= '<p class="widget-feature"><b class="feature"> 04. </b>' . $upcoming_features['fourth'] . '</p>';
			$features .= '<p class="widget-feature"><b class="feature"> 05. </b>' . $upcoming_features['fifth'] . '</p>';

			$html_message = sprintf( '<div class="social-widget-features">%s</div>', wpautop( $features ) );

			?>
				<div class="whitelabel-wp-content__page-wrap whitelabel-pro-features">
					<div class="required-page-plugins">
						<div class="whitelabel-wp-content__title"><h3>Upcoming Pro Features</h3></div>
						<div class="whitelabel-wp-content__table-wrap">
							<table class="form-table">
								<tbody>
									<tr class="whitelabel-content__page-row">

										<th scope="row">
											<label>
												<?php
													echo esc_html__( 'Upcoming Features', 'whitelabel-wp-content' );
												?>
											</label>
										</th>

										<td>
											<?php
												echo wp_kses_post( $html_message );
											?>
										</td>
									</tr>
								</tbody>
							</table>

							<p>
								<?php
								esc_html_e( 'You can contribute to improvise this plugin or can give us suggessions to make it more better. Your contribution will highly appreciated!', 'whitelabel-wp-content' );
								?>
							</p>

							<?php
								$contribute_suggession = apply_filters( 'contribute_suggession', 'https://webempire.org.in/contact/?utm_campaign=web-agency&utm_medium=email&utm_source=google' );
								$contribute_suggession_text = apply_filters( 'contribute_suggession_text', __( 'Send a Suggession »', 'whitelabel-wp-content' ) );

								printf(
									/* translators: %1$s: demos link. */
									'%1$s',
									! empty( $contribute_suggession ) ? '<p> <a style="color: #8141bb; text-decoration: unset;" href=' . esc_url( $contribute_suggession ) . ' target="_blank" rel="noopener">' . esc_html( $contribute_suggession_text ) . '</a></p>' :
									esc_html( $contribute_suggession_text )
								);
							?>
							
						</div>
					</div>
				</div>
			<?php
		}

		/**
		 * Render item data content
		 *
		 * @since 1.0.0
		 * @param  array   $plugin Item details array.
		 * @return void
		 */
		private function render_item_data( $detail, $key, $plugin_slug = null ) {

			$whitelabel_version_dependency = apply_filters( 'whitelabel_version_dependency', false );
			$whitelabel_network_dependency = apply_filters( 'whitelabel_network_dependency', false );
			$whitelabel_domain_path_dependency = apply_filters( 'whitelabel_domain_path_dependency', false );

			if(
				'WC requires at least' == $detail ||
				'WC tested up to' == $detail ||
				'Woo' == $detail ||
				( 'Version' == $detail && ! $whitelabel_version_dependency ) ||
				( 'DomainPath' == $detail && ! $whitelabel_domain_path_dependency ) ||
				( 'Network' == $detail && ! $whitelabel_network_dependency ) ||
				'TextDomain' == $detail ||
				'Title' == $detail ||
				'AuthorName' == $detail
			) {
				return;
			}

			$whitelabeled_wp_data = self::$whitelabeled_wp_data;
			$flag = false;
			$meta_key = $key;

			if ( ! empty( $whitelabeled_wp_data ) ) {
				foreach ( $whitelabeled_wp_data as $data => $key ) {
					if( $plugin_slug === $key['id'] ) {
						foreach ( $key['required_meta_data'] as $required_meta => $final_meta ) {
							if( isset( $final_meta[$detail] ) && ! empty( $final_meta[$detail] ) ) {
								$flag = true;
								$meta_key = $final_meta[$detail];
							}
						}
					}
				}
			}

			?>

			<tr class="whitelabel-content__page-row">

				<th scope="row">

					<label for="<?php echo esc_attr( $detail ); ?>">

						<?php echo esc_attr( $detail ); ?>

					</label>
				</th>

				<td>
					<?php

						switch( $detail ) {

							case 'Description':
								echo $this->meta->get_area_field(
									array(
										'id'		   => $detail,
										'name'         => $plugin_slug,
										'value'   	   => sanitize_textarea_field( $meta_key ),
										'flag'		   => $flag,
										'help'		   => 'Here is the setting of plugin\'s ' . $detail . '.',
									)
								);
							break;

							default:
								echo $this->meta->get_text_field(
									array(
										'id'		   => $detail,
										'name'         => $plugin_slug,
										'value'   	   => sanitize_text_field( $meta_key ),
										'flag'		   => $flag,
										'help'		   => 'Here is the setting of plugin\'s ' . $detail . '.',
									)
								);
							break;
						}
					?>
				</td>
			</tr>

			<?php
		}
    }

    /**
	 * Kicking this off by new calling an instance
	 */
	new Whitelabel_WP_Content_Loader();

endif;
