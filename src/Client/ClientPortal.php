<?php
/**
 * ClientPortal class
 *
 * @package MZMBOACCESS
 */

namespace MZoo\MzMboAccess\Client;

use MZoo\MzMindbody as MZ;
use MZoo\MzMboAccess as NS;
use MZoo\MzMindbody\Core as Core;
use MZoo\MzMindbody\Common as Common;
use MZoo\MzMindbody\Libraries as Libraries;
use MZoo\MzMindbody\Schedule as Schedule;
use MZoo\MzMindbody\Common\Interfaces as Interfaces;

/**
 * Class that holds Client Interface Methods for Ajax requests
 */
class ClientPortal extends RetrieveClient {

	/**
	 * Format for date display, specific to MBO API Plugin.
	 *
	 * @since  1.0.1
	 * @access public
	 * @var    string $date_format WP date format option.
	 */
	public $date_format;

	/**
	 * Format for time display, specific to MBO API Plugin.
	 *
	 * @since  1.0.1
	 * @access public
	 * @var    string $time_format
	 */
	public $time_format;

	/**
	 * Class constructor
	 *
	 * @since 2.4.7
	 */
	public function __construct() {
		$this->date_format = Core\MzMindbodyApi::$date_format;
		$this->time_format = Core\MzMindbodyApi::$time_format;
		parent::__construct();
	}

	/**
	 * Client Log In
	 */
	public function ajax_client_login() {

		check_ajax_referer( 'mz_signup_nonce', 'nonce' );

		// Create the MBO Object.
		$this->getMboResults();

		// Init message.
		$result['message'] = '';

		$result['type'] = 'success';

		// Parse the serialized form into an array.
		$params = array();
		parse_str( $_REQUEST['form'], $params );

		if ( empty( $params ) || ! is_array( $params ) ) {
			$result['type'] = 'error';
		} else {
			$credentials = array(
				'Username' => $params['email'],
				'Password' => $params['password'],
			);

			$login = $this->log_client_in( $credentials );

			if ( 'error' === $login['type'] ) {
				$result['type'] = 'error';
			}

			$result['message'] = $login['message'];

			$result['client_details'] = $login['deeper_client_info'];

			$result['client_id'] = $login['client_id'];
		}

		if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
			'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			$result = wp_json_encode( $result );
			echo $result;

		} else {
			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
		}

		die();
	}

	/**
	 * Client Log Out
	 */
	public function ajax_client_logout() {

		check_ajax_referer( $_REQUEST['nonce'], 'mz_client_log_out' );

		ob_start();

		$result['type'] = 'success';

		$this->client_log_out();

		// update class attribute to hold logged out status.
		$this->client_logged_in = false;

		esc_html_e( 'Logged Out', 'mz-mindbody-api' );

		echo '<br/>';

		$result['message'] = ob_get_clean();

		if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
			'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			$result = wp_json_encode( $result );
			echo $result;

		} else {
			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
		}

		die();
	}




	/**
	 * Check Client Logged In
	 *
	 * Function run by ajax to continually check if client is logged in
	 */
	public function ajaxCheckClientLogged() {

		check_ajax_referer( $_REQUEST['nonce'], 'mz_check_client_logged' );

		$result = array();

		$result['type']    = 'success';
		$result['message'] = $this->check_client_logged();

		if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
			'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			$result = wp_json_encode( $result );
			echo $result;

		} else {
			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
		}

		die();
	}


	/**
	 * Get Clients
	 *
	 * Get multiple clients from MBO
	 */
	public function ajax_get_clients() {

		check_ajax_referer( $_REQUEST['nonce'], 'mz_client_request' );

		$result = array();

		$result['type']    = 'success';
		$result['message'] = $this->get_clients( array( $_REQUEST['client_id'] ) );

		if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
			'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			$result = wp_json_encode( $result );
			echo $result;

		} else {
			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
		}

		die();
	}


	/**
	 * Get Client
	 *
	 * Like Get Clients (above), but return only the first client.
	 */
	public function ajax_get_client_details() {

		check_ajax_referer( $_REQUEST['nonce'], 'mz_client_request' );

		$result = array();

		$result['type'] = 'success';

		$client = $this->get_client( $_REQUEST['client_id'] );

		$result['client'] = $this->update_client_session( $client );

		if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
			'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			$result = wp_json_encode( $result );
			echo $result;

		} else {
			header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
		}

		die();
	}
}
