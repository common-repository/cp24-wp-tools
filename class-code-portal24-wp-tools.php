<?php
/**
 * Plugin Name: CP24 WP Tools
 * Plugin URI: https://code-portal24.com/code-portal-wordpress-smtp/
 * Description: Easily send emails using SMTP servers with this plugin. Stay tuned for upcoming features!.
 * Version: 1.1.0
 * Requires at least: 5.6
 * Requires PHP: 7.0
 * Author: Hadi Mohammade
 * Author URI: https://code-portal24.com/
 * License: GPL2
 * Text Domain: cp24
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Code_Portal24_WP_Tools' ) ) {

	/**
	 * Plugin class.
	 *
	 * @since NEXT
	 * @package Growtop_WordPress_Multi_Smtp
	 */
	class Code_Portal24_WP_Tools {

		/**
		 * Constructor function for the CP24_Code_Portal_WP_Tools class.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function __construct() {
			// Define constants.
			$this->define_constants();

			// Hooks setup.
			add_action( 'admin_menu', [ $this, 'add_plugin_dashboard_page' ] );
			add_action( 'in_admin_header', [ $this, 'remove_admin_notices' ] );
			add_action( 'plugins_loaded', [ $this, 'include_init_file' ] );
		}

		/**
		 * Define constants.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		private function define_constants() {
			define( 'CP24_MULTI_SMTP_VERSION', '1.0.0' );
			define( 'CP24_MULTI_SMTP_FILE', __FILE__ );
			define( 'CP24_MULTI_SMTP_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CP24_MULTI_SMTP_URL', plugin_dir_url( __FILE__ ) );
			define( 'CP24_MULTI_SMTP_ASSETS_PATH', CP24_MULTI_SMTP_PATH . 'dist/' );
			define( 'CP24_MULTI_SMTP_ASSETS_URL', CP24_MULTI_SMTP_URL . 'dist/' );
			define(
				'CP24_DASHBOARD_PAGES',
				[
					'cp24_dashboard',
					'cp24-email',
				]
			);
		}

		/**
		 * Load files.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function include_init_file() {
			require_once CP24_MULTI_SMTP_PATH . 'inc/class-init.php';
		}

		/**
		 * Callback function for adding a plugin dashboard page.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function add_plugin_dashboard_page() {
			// Add the admin page.
			add_menu_page(
				esc_html__( 'Code Portal 24', 'cp24' ),
				esc_html__( 'Code Portal 24', 'cp24' ),
				'manage_options',
				'cp24_dashboard',
				[ $this, 'render_plugin_dashboard_page' ]
			);

			// Add the email sub-menu.
			add_submenu_page(
				'cp24_dashboard',
				esc_html__( 'Email', 'cp24' ),
				esc_html__( 'Email', 'cp24' ),
				'manage_options',
				'cp24-email',
				[ $this, 'render_email_page' ]
			);
		}

		/**
		 * Callback function for rendering the plugin dashboard page.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function render_plugin_dashboard_page() {
			echo '<div id="cp24-main-dashboard"></div>';
		}

		/**
		 * Callback function for rendering the email page.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function render_email_page() {
			echo '<div id="growtop-email-smtp"></div>';
		}

		/**
		 * Callback function for removing admin notices on the plugin's settings pages.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function remove_admin_notices() {
			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$page = sanitize_text_field( $page );

			$plugin_admin_pages = CP24_DASHBOARD_PAGES;

			if ( ! in_array( $page, $plugin_admin_pages, true ) ) {
				return;
			}

			remove_all_actions( 'admin_notices' );
		}
	}

	new Code_Portal24_WP_Tools();

}
