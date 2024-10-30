<?php
/**
 * Log sent email class.
 *
 * @package CP24\Email\Inc\Email\Log
 * @since 1.1.0
 * @version 1.1.0
 */

namespace CP24\Email\Inc\Email\Log;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use CP24\Email\Inc\Email\Email;

/**
 * Moderate logging email feature.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 *
 * @since 1.1.0
 * @version 1.1.0
 */
class Log_Sent_Email {
	const STATUS_OPTION = 'email_log_status';
	const MAIL_LOG_LIST = 'cp24_email_logs_list';

	/**
	 * Class Construct.
	 *
	 * @since 1.1.0
	 * @version 1.1.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_cp24_wp_tools_email_log_ajax_handler', [ $this, 'ajax_handler' ] );
		add_action( 'wp_mail_succeeded', [ $this, 'log_succeeded_mail' ], 10, 1 );
	}

	/**
	 * Handle ajax request.
	 *
	 * @since 1.1.0
	 * @version 1.1.0
	 */
	public function ajax_handler() {
		check_ajax_referer( Email::EMAIL_NONCE, 'nonce' );

		if ( empty( $_REQUEST['sub_action'] ) ) { // phpcs:ignore
			wp_send_json_error();
		}

		$action = filter_var( wp_unslash( $_REQUEST['sub_action'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ); // phpcs:ignore
		$action = sanitize_text_field( $action );

		if ( ! method_exists( $this, $action ) ) {
			wp_send_json_error();
		}

		call_user_func( [ $this, $action ] );
	}

	/**
	 * Save log status.
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Recommended
	 *
	 * @since 1.1.0
	 * @version 1.1.0
	 */
	private function save_status() {
		if ( ! isset( $_REQUEST['status'] ) ) {
			wp_send_json_error();
		}

		$value = filter_var( wp_unslash( $_REQUEST['status'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$value = sanitize_text_field( $value );

		$cp24_email_settings                        = get_option( Email::EMAIL_SETTINGS, [] );
		$cp24_email_settings[ self::STATUS_OPTION ] = $value;

		update_option( Email::EMAIL_SETTINGS, $cp24_email_settings );

		wp_send_json_success();
	}

	/**
	 * Get logs.
	 *
	 * @since 1.1.0
	 * @version 1.1.0
	 */
	private function get_mails() {
		$list = get_option( self::MAIL_LOG_LIST, [] );

		wp_send_json_success( $list );
	}

	/**
	 * Log succeeded email.
	 *
	 * @param array $mail_data email data.
	 * @return void
	 * @since 1.1.0
	 * @version 1.1.0
	 */
	public function log_succeeded_mail( $mail_data ) {
		$cp24_email_settings = get_option( Email::EMAIL_SETTINGS, [] );
		$status              = $cp24_email_settings[ self::STATUS_OPTION ] ?? '';

		if ( empty( $status ) || false === $status || 'false' === $status ) {
			return;
		}

		$contact = $mail_data['to'][0];
		$subject = $mail_data['subject'];
		$time    = time();
		$logs    = get_option( self::MAIL_LOG_LIST, [] );

		$logs[ $time ] = [
			'to'      => $contact,
			'subject' => $subject,
		];

		if ( count( $logs ) > 10 ) {
			$keys   = array_keys( $logs );
			$values = array_slice( $logs, 1 );
			$logs   = array_combine( array_slice( $keys, 1 ), $values );
		}

		update_option( self::MAIL_LOG_LIST, $logs );
	}
}

new Log_Sent_Email();
