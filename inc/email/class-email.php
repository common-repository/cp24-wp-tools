<?php
/**
 * Email class.
 *
 * @package CP24\Email\Inc\Email
 * @since 1.0.0
 * @version 1.0.0
 */

namespace CP24\Email\Inc\Email;

defined( 'ABSPATH' ) || exit;

use CP24\Email\Inc\Email\Smtp\Ajax_Handler;

/**
 * Moderate email common requirements.
 *
 * @since 1.0.0
 * @version 1.0.0
 */
class Email {
	const EMAIL_NONCE    = 'cp24-email-nonce';
	const EMAIL_SETTINGS = 'cp24-wp-tools-email-settings';

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_cp24_email_get_options', [ $this, 'get_options' ] );
	}

	/**
	 * Get all option about email.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function get_options() {
		check_ajax_referer( self::EMAIL_NONCE, 'nonce' );

		$smtp       = get_option( Ajax_Handler::SMTP_OPTION_NAME, [] );
		$multi_smtp = get_option( Ajax_Handler::SMTP_MULTI_OPTION_NAME, [] );

		wp_send_json_success(
			[
				'smtp'           => $smtp,
				'multi'          => $multi_smtp,
				'multi_count'    => count( $multi_smtp ),
				'email_settings' => get_option( self::EMAIL_SETTINGS, [] ),
			]
		);
	}
}

new Email();
