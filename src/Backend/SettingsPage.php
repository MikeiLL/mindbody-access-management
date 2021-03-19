<?php

namespace MZoo\MzMboAccess\Backend;

use MZoo\MzMindbody as MZ;
use MZoo\MzMboAccess as NS;
use MZoo\MzMboAccess\Core as Core;
use MZoo\MzMboAccess\Common as Common;
use MZoo\MzMindbody\Libraries as Libraries;
use MZoo\MzMboAccess\Schedule as Schedule;

/**
 * This file contains the class which holds all the actions and methods to create the admin dashboard sections
 *
 * This file contains all the actions and functions to create the admin dashboard sections.
 * It should probably be refactored to use oop approach at least for the sake of consistency.
 *
 * @since 2.1.0
 *
 * @package MzMboAccess
 */
/**
 * Actions/Filters
 *
 * Related to all settings API.
 *
 * @since 1.0.0
 */

class SettingsPage {




	protected static $wposa_obj;

	public function __construct() {
		self::$wposa_obj = MZ\Core\MzMindbodyApi::$settings_page::$wposa_obj;
	}

	public function addSections() {

		// Section: Basic Settings.
		self::$wposa_obj->add_section(
			array(
				'id'    => 'mz_mbo_access',
				'title' => __( 'MZ MBO Access Settings', 'mz-mindbody-api' ),
			)
		);

		// Section: Depreciated.
		self::$wposa_obj->add_section(
			array(
				'id'    => 'mz_mbo_v_five',
				'title' => __( 'API Version 5', 'mz-mindbody-api' ),
			)
		);

		// Field: Title.
		self::$wposa_obj->add_field(
			'mz_mbo_v_five',
			array(
				'id'      => 'credentials_test',
				'type'    => 'title',
				'name'    => '<h1>API V5 Credentials Test</h1>',
				'default' => '',
			)
		);

		// Field: Title.
		self::$wposa_obj->add_field(
			'mz_mbo_v_five',
			array(
				'id'   => 'api_5_description',
				'type' => 'html',
				'name' => 'Note',
				'desc' => 'New MBO Dev accounts will not have access to the MBO V5 API.',
			)
		);

		// Field: Textarea.
		self::$wposa_obj->add_field(
			'mz_mbo_v_five',
			array(
				'id'   => 'credentials_test',
				'type' => 'html',
				'name' => __( 'Debug Output', 'mz-mindbody-api' ),
				'desc' => $this->mz_mindbody_v5_debug_text(),
			)
		);

		// Field: Regenerate Class Owners
		self::$wposa_obj->add_field(
			'mz_mbo_access',
			array(
				'id'          => 'level_1_contracts',
				'type'        => 'textarea',
				'name'        => __( 'Access Level One Contracts', 'mz-mindbody-api' ),
				'desc'        => __( 'Comma separated list (or single) MBO Service(s) or Contract(s)', 'mz_mbo_access' ),
				'placeholder' => 'Ten Class Pass, Single Class Drop-In',
			)
		);

		// Field: Regenerate Class Owners
		self::$wposa_obj->add_field(
			'mz_mbo_access',
			array(
				'id'          => 'level_2_contracts',
				'type'        => 'textarea',
				'name'        => __( 'Access Level Two Contracts', 'mz-mindbody-api' ),
				'desc'        => __( 'Comma separated list (or single) MBO Service(s) or Contract(s)', 'mz_mbo_access' ),
				'placeholder' => 'Weekly Class Pass, Monthly Yoga All Access Pass',
			)
		);

		// Field: Regenerate Class Owners
		self::$wposa_obj->add_field(
			'mz_mbo_access',
			array(
				'id'          => 'level_1_services',
				'type'        => 'textarea',
				'name'        => __( 'Access Level One Services', 'mz-mindbody-api' ),
				'desc'        => __( 'Comma separated list (or single) MBO Service(s) or Contract(s)', 'mz_mbo_access' ),
				'placeholder' => 'Ten Class Pass, Single Class Drop-In',
			)
		);

		// Field: Regenerate Class Owners
		self::$wposa_obj->add_field(
			'mz_mbo_access',
			array(
				'id'          => 'level_2_services',
				'type'        => 'textarea',
				'name'        => __( 'Access Level Two Services', 'mz-mindbody-api' ),
				'desc'        => __( 'Comma separated list (or single) MBO Service(s) or Contract(s)', 'mz_mbo_access' ),
				'placeholder' => 'Weekly Class Pass, Monthly Yoga All Access Pass',
			)
		);

		// Field: Regenerate Class Owners
		self::$wposa_obj->add_field(
			'mz_mbo_access',
			array(
				'id'   => 'mbo_access_shortcodes',
				'type' => 'html',
				'name' => __( 'Shortcodes and Atts', 'mz-mindbody-api' ),
				'desc' => $this->access_codes(),
			)
		);

		// Field: Server Check HTML.
		self::$wposa_obj->add_field(
			'mz_mbo_access',
			array(
				'id'   => 'server_check',
				'type' => 'html',
				'name' => __( 'Server Check', 'mz-mindbody-api' ),
				'desc' => $this->server_check(),
			)
		);
	}

	private function access_codes() {
		$return  = '';
		$return .= '<p>' . sprintf( '[%1$s] %2$s [%3$s]', 'mbo-client-access', __( 'Restricted content here between both tags', 'mz-mindbody-api' ), '/mbo-client-access' ) . '</p>';
		$return .= '<ul>';
		$return .= '<li><strong>level_1_redirect</strong>: ' . __( '(url string) URL to redirect users with level one access.', 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>level_2_redirect</strong>: ' . __( '(url string) URL to redirect users with level two access.', 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>denied_redirect</strong>: ' . __( "((url string) URL to redirect users to who are logged in but don't have access.", 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>call_to_action</strong>: ' . __( '(string) Message inviting user to submit form.', 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>password_reset_request</strong>: ' . __( '(string) Password Reset button text. Blank string to remove button.', 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>manage_on_mbo</strong>: ' . __( '(string) Link to MBO Site button text. Blank string to remove button.', 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>denied_message</strong>: ' . __( '(string) Message preceding list of items required for access.', 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>access_expired</strong>: ' . __( '(string) Message alerting client that access has expired.', 'mz-mindbody-api' ) . '</li>';
		$return .= '<li><strong>access_levels</strong>: ' . sprintf( __( '(int/list) (Default %1$d) Levels of access required to access content %1$d, %2$d or %3$s', 'mz-mindbody-api' ), 1, 2, '"1, 2"' ) . '</li>';
		$return .= '</ul>';
		$return .= sprintf( '[%1$s %2$s]%3$s[%4$s]', 'mbo-client-access', 'access_levels="1,2" denied_message="Not so fast, bub."', 'Restricted Content', '/mbo-client-access' );
		$return .= '<h3>' . __( 'Note', 'mz-mindbody-api' ) . '</h3>';
		$return .= sprintf( __( 'If %1$s %2$s or %3$s are included, the login form will redirect to one of those urls instead of revealing content. Content, if any, between shortcode tags will display when page is reloaded by logged in client.', 'mz-mindbody-api' ), '<code>contract_redirect</code>', '<code>level_2_redirect</code>', '<code>denied_redirect</code>' );
		return $return;
	}

	private function server_check() {

		$return          = '';
		$mz_requirements = 0;

		if ( ! file_exists( 'PEAR/Registry.php' ) ) {
			return '<p>SOAP warning (above).</p><div class="notice notice-warning is-dismissible" style="padding:1.5em;">Cannot confirm pear is installed. Check with server admin about pear/SOAP if you have issues.<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}

		include 'PEAR/Registry.php';

		$reg = new \PEAR_Registry();

		if ( extension_loaded( 'soap' ) ) {
			$return .= __( 'SOAP installed! ', 'mz-mindbody-api' );
		} else {
			$return         .= __( 'SOAP is not installed. ', 'mz-mindbody-api' );
			$mz_requirements = 1;
		}
		$return .= '&nbsp;';

		if ( class_exists( 'System' ) === true ) {
			$return .= __( 'PEAR installed! ', 'mz-mindbody-api' );
		} else {
			$return         .= __( 'PEAR is not installed. ', 'mz-mindbody-api' );
			$mz_requirements = 1;
		}

		if ( $mz_requirements == 1 ) {
			$return .= '<div class="settings-error"><p>';
			$return .= __( 'MZ Mindbody API requires SOAP and PEAR. Please contact your hosting provider or enable via your CPANEL of php.ini file.', 'mz-mindbody-api' );
			$return .= '</p></div>';
		} else {
			$return .= '<div class="" ><p>';
			$return .= __( 'Congratulations. Your server appears to be configured to integrate with mindbodyonline.', 'mz-mindbody-api' );
			$return .= '</p></div>';
		}
		return $return;
	}

	private function mz_mindbody_v5_debug_text() {
		return '<a href="#" class="button" id="mzTestCredentialsV5">' . __( 'Test Credentials', 'mz-mindbody-api' ) . '</a><div id="displayTestV5"></div>';
	}
}
