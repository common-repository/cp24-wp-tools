<?php
/**
 * Initialize required resources.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Cp24\Email\Inc;

defined( 'ABSPATH' ) || exit;

use CP24\Email\Inc\Email\Email;

/**
 * Initialize required resources.
 *
 * @since 1.0.0
 * @version 1.0.0
 */
class Init {
	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		$require_files = [
			'inc/email/class-email.php',
			'inc/email/smtp/class-ajax-handler.php',
			'inc/email/log-sent-email/class-log-sent-email.php',
		];

		foreach ( $require_files as $file ) {
			require_once CP24_MULTI_SMTP_PATH . $file;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_backend_scripts' ] );
		add_action( 'wp_ajax_cp24_smtp_get_cp24_list', [ $this, 'get_plugins_list' ] );
	}

	/**
	 * Get CP24 plugins list.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function get_plugins_list() {
		check_ajax_referer( Email::EMAIL_NONCE, 'nonce' );

		$url     = 'https://code-portal24.com/wp-json/code-portal/v1/plugins';
		$plugins = wp_remote_get( $url );

		if ( is_wp_error( $plugins ) ) {
			wp_send_json_error();
		}

		$body    = wp_remote_retrieve_body( $plugins );
		$plugins = json_decode( $body, true );

		wp_send_json_success( $plugins );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function enqueue_backend_scripts() {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$page = sanitize_text_field( $page );

		$plugin_admin_pages = CP24_DASHBOARD_PAGES;

		if ( ! in_array( $page, $plugin_admin_pages, true ) ) {
			return;
		}

		wp_enqueue_style(
			'growtop-email-backend-style',
			CP24_MULTI_SMTP_URL . 'dist/backend.css',
			[],
			'1.0.0'
		);

		wp_enqueue_script(
			'growtop-email-backend-script',
			CP24_MULTI_SMTP_URL . 'dist/backend.js',
			[ 'lodash', 'wp-element', 'wp-i18n', 'wp-util', 'jquery' ],
			'1.0.0',
			true
		);

		wp_localize_script(
			'growtop-email-backend-script',
			'growtopEmail',
			[
				'adminUrl' => admin_url( 'admin.php?page=cp24-email' ),
				'nonce'    => wp_create_nonce( Email::EMAIL_NONCE ),
			]
		);
	}
}

new Init();
