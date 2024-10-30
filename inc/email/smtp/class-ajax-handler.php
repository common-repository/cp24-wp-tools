<?php
/**
 * Ajax handler.
 * Handle all ajax request.
 *
 * @package CP24\Email\Inc\Email\Smtp
 * @since 1.0.0
 * @version 1.0.0
 */

namespace CP24\Email\Inc\Email\Smtp;

defined( 'ABSPATH' ) || exit;

use CP24\Email\Inc\Email\Email;

/**
 * Ajax handler.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 * @since 1.0.0
 * @version 1.0.0
 */
class Ajax_Handler {
	const SMTP_OPTION_NAME       = 'cp24_email_smtp_details';
	const SMTP_MULTI_OPTION_NAME = 'cp24_email_smtp_multi_details';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_cp24_email_smtp_handler', [ $this, 'ajax_handler' ] );
		add_action( 'phpmailer_init', [ $this, 'modify_phpmailer' ] );
	}

	/**
	 * Ajax handler.
	 *
	 * Get and take care of both post & get ajax request;
	 *
	 * @since 1.0.0
	 * @version 1.0.0
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
	 * Save SMTP details.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	private function save_smtp_details() {
		$smtp = filter_input( INPUT_POST, 'smtp', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FORCE_ARRAY );
		$smtp = $this->sanitize_email_array( $smtp );

		if ( empty( $smtp ) || ! is_array( $smtp ) ) {
			$smtp = [];
		}

		update_option( self::SMTP_OPTION_NAME, $smtp );

		wp_send_json_success( $smtp );
	}

	/**
	 * Save multi SMTP details.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	private function save_multi_smtp_details() {
		$smtp = filter_input( INPUT_POST, 'smtp', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FORCE_ARRAY );
		$smtp = $this->sanitize_email_array( $smtp );

		if ( empty( $smtp ) || ! is_array( $smtp ) ) {
			$smtp = [];
		}

		update_option( self::SMTP_MULTI_OPTION_NAME, $smtp );

		wp_send_json_success(
			[
				'multi'       => $smtp,
				'multi_count' => count( $smtp ),
			]
		);
	}

	/**
	 * Modify phpmailer.
	 *
	 * @param Object $phpmailer PHPMailer instance.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public function modify_phpmailer( $phpmailer ) {
		$smtp = get_option( self::SMTP_OPTION_NAME, [] );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->Mailer   = 'smtp';
		$phpmailer->Host     = $smtp['host'];
		$phpmailer->SMTPAuth = true;
		$phpmailer->Port     = $smtp['port'];
		$phpmailer->Username = $smtp['username'];
		$phpmailer->Password = $smtp['password'];

		// Additional settings.
		$phpmailer->SMTPSecure = 'STARTTLS';
		$phpmailer->From       = $smtp['from'];
		$phpmailer->FromName   = $smtp['from_name'];
		// phpcs:enable
	}

	/**
	 * Send test email by SMTP.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	private function send_test_email_by_smtp() {
		$email = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FORCE_ARRAY );
		$email = $this->sanitize_email_array( $email );
		$smtp  = get_option( self::SMTP_OPTION_NAME, [] );

		if ( empty( $smtp ) || ! is_array( $smtp ) ) {
			wp_send_json_error( esc_html__( 'Please add your SMTP details first and save it.', 'cp24' ) );
		}

		if ( empty( $smtp['from'] ) ) {
			wp_send_json_error( esc_html__( 'SMTP from field can not be empty.', 'cp24' ) );
		}

		add_action( 'wp_mail_failed', function( $error ) {
			$message = $error->get_error_message();

			add_filter( 'growtop_email_smtp_test_error_message', function() use( $message ) {
				return $message;
			}, 10, 1 );
		} );

		add_filter( 'wp_mail_from', function() use ( $smtp ) {
			return $smtp['from'];
		}, 10, 1 );

		$result = wp_mail( $email['email'], $email['subject'], $email['email_text'] );

		if ( $result ) {
			wp_send_json_success( esc_html__( 'Email sent successfully.', 'cp24' ) );
		}

		$error = apply_filters( 'growtop_email_smtp_test_error_message', esc_html__( 'Something went wrong.', 'cp24' ) );

		wp_send_json_error( $error );
	}

	/**
	 * Sanitize email array.
	 *
	 * @param array $email_array Email array.
	 * @return array Sanitized email array.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	private function sanitize_email_array( $email_array ) {
		$sanitized_array = [];

		foreach ( $email_array as $key => $value ) {
			if ( 'from' === $key || 'email' === $key ) {
				$sanitized_array[ $key ] = sanitize_email( $value );
				continue;
			}

			if ( 'port' === $key ) {
				$sanitized_array[ $key ] = absint( $value );
				continue;
			}

			$sanitized_array[ $key ] = sanitize_text_field( $value );
		}

		return $sanitized_array;
	}
}

new Ajax_Handler();
