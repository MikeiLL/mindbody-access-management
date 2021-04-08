<?php
/**
 * Access Display
 *
 * Class to display access state to user.
 *
 * @package MZMBOACCESS
 */

namespace MZoo\MzMboAccess\Access;

use MZoo\MzMboAccess as NS;
use MZoo\MzMindbody as MZ;
use MZoo\MzMboAccess\Core as Core;
use MZoo\MzMboAccess\Session as Session;
use MZoo\MzMboAccess\Client as Client;
use MZoo\MzMindbody\Site as Site;
use MZoo\MzMindbody\Common as Common;
use MZoo\MzMindbody\Common\Interfaces as Interfaces;

/**
 * Access Display Class
 *
 * Shortcode class to display user content based on access.
 */
class AccessDisplay extends Interfaces\ShortcodeScriptLoader {


	/**
	 * If shortcode script has been enqueued.
	 *
	 * @since    2.4.7
	 * @access   private
	 *
	 * @used in handle_shortcode, addScript
	 * @var      boolean $added_already True if shorcdoe scripts have been enqueued.
	 */
	private static $added_already = false;

	/**
	 * Restricted content.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handle_shortcode, localize_script
	 * @var  string $restricted_content Content between two shortcode tags.
	 */
	public $restricted_content;

	/**
	 * Shortcode attributes.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handle_shortcode, localize_script
	 * @var  array $atts Shortcode attributes function called with.
	 */
	public $atts;

	/**
	 * Data to send to template
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handle_shortcode,
	 * @var  array    $data    array to send template.
	 */
	public $template_data;

	/**
	 * Status of client login
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handle_shortcode, localize_script
	 * @var  array    $data    array to send template.
	 */
	public $logged_in;

	/**
	 * Status of client access
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handle_shortcode, localize_script
	 * @var  bool    $has_access if current client has access current page.
	 */
	public $has_access;

	/**
	 * Level of client access
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handle_shortcode, localize_script
	 * @var  int    $client_access_level current client access level.
	 */
	public $client_access_level;

	/**
	 * Level One Contracts
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localize_script
	 * @var  array    $level_1_contracts of contracts from options page.
	 */
	public $level_1_contracts;

	/**
	 * Level Two Contracts
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localize_script
	 * @var  array    $level_2_contracts of contracts from options page.
	 */
	public $level_2_contracts;

	/**
	 * Level Three Contracts
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localize_script
	 * @var  array    $level_3_contracts of contracts from options page.
	 */
	public $level_3_contracts;

	/**
	 * Level One Services
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localize_script
	 * @var  array    $level_1_services of services from options page.
	 */
	public $level_1_services;

	/**
	 * Level Two Services
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localize_script
	 * @var  array    $level_2_services of services from options page.
	 */
	public $level_2_services;

	/**
	 * Level Three Services
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localize_script
	 * @var  array    $level_3_services of services from options page.
	 */
	public $level_3_services;

	/**
	 * Handle Shortcode
	 *
	 * @param    string $atts shortcode inputs.
	 * @param    string $content any content between start and end shortcode tags.
	 * @return   string shortcode content.
	 */
	public function handle_shortcode( $atts, $content = null ) {

		$this->atts = shortcode_atts(
			array(
				'siteid'                 => '',
				'denied_message'         => __( 'Access to this content requires one of', 'mz-mbo-access' ),
				'call_to_action'         => __( 'Login with your Mindbody account to access this content.', 'mz-mbo-access' ),
				'access_expired'         => __( 'Looks like your access has expired.', 'mz-mbo-access' ),
				'level_1_redirect'       => '',
				'level_2_redirect'       => '',
				'denied_redirect'        => '',
				'access_levels'          => 1,
				'manage_on_mbo'          => 'Visit Mindbody Site',
				'password_reset_request' => __( 'Forgot My Password', 'mz-mbo-access' ),
			),
			$atts
		);

		$this->site_id = ( isset( $atts['siteid'] ) ) ? $atts['siteid'] : MZ\MZMBO()::$basic_options['mz_mindbody_siteID'];

		$mz_mbo_access_options = get_option( 'mz_mbo_access' );

		// TODO can we avoid doing this here AND in access utilities?
		$this->level_1_contracts = explode( ',', $mz_mbo_access_options['level_1_contracts'] );
		$this->level_2_contracts = explode( ',', $mz_mbo_access_options['level_2_contracts'] );
		$this->level_3_contracts = explode( ',', $mz_mbo_access_options['level_3_contracts'] );
		$this->level_1_contracts = array_map( 'trim', $this->level_1_contracts );
		$this->level_2_contracts = array_map( 'trim', $this->level_2_contracts );
		$this->level_3_contracts = array_map( 'trim', $this->level_3_contracts );

		$this->level_1_services = explode( ',', $mz_mbo_access_options['level_1_services'] );
		$this->level_2_services = explode( ',', $mz_mbo_access_options['level_2_services'] );
		$this->level_3_services = explode( ',', $mz_mbo_access_options['level_3_services'] );
		$this->level_1_services = array_map( 'trim', $this->level_1_services );
		$this->level_2_services = array_map( 'trim', $this->level_2_services );
		$this->level_3_services = array_map( 'trim', $this->level_3_services );

		$this->atts['access_levels'] = explode( ',', $this->atts['access_levels'] );
		$this->atts['access_levels'] = array_map( 'trim', $this->atts['access_levels'] );

		$this->restricted_content = $content;

		// Check for Service-based access attribute.
		if ( false !== $this->atts['service_based_access'] ) {

			$this->service_access = explode( ',', $this->atts['service_based_access'] );
			$this->service_access = array_map( 'trim', $this->service_access );

		}

		// Begin generating output.
		ob_start();

		$template_loader = new Core\TemplateLoader();

		$this->template_data = array(
			'atts'                           => $this->atts,
			'content'                        => $this->restricted_content,
			// Tested in Client\ClientPortal ajax_client_login().
			'login_nonce'                    => wp_create_nonce( 'ajax_client_login' ),
			// Tested in Access\AccessPortal ajax_login_check_access_permissions().
			'check_access_permissions_nonce' => wp_create_nonce(
				'ajax_login_check_access_permissions'
			),
			'site_id'                        => MZ\MZMBO()::$basic_options['mz_mindbody_siteID'],
			'email'                          => __( 'email', 'mz-mbo-access' ),
			'password'                       => __( 'password', 'mz-mbo-access' ),
			'login'                          => __( 'Login', 'mz-mbo-access' ),
			'logout'                         => __( 'Logout', 'mz-mbo-access' ),
			'logged_in'                      => false,
			/**
			 * Required services value is filtered based
			 * on shortcode access levels within the template.
			 * Using array_filter to remove empties.
			 */
			'required_services'              => array(
				1 => array_filter(
					array_merge(
						$this->level_1_contracts,
						$this->level_1_services
					)
				),
				2 => array_filter(
					array_merge(
						$this->level_2_contracts,
						$this->level_2_services
					)
				),
				3 => array_filter(
					array_merge(
						$this->level_3_contracts,
						$this->level_3_services
					)
				),
			),
			'access_levels'                  => $this->atts['access_levels'],
			'has_access'                     => false,
			'client_name'                    => '',
			'denied_message'                 => $this->atts['denied_message'],
			'manage_on_mbo'                  => $this->atts['manage_on_mbo'],
			'password_reset_request'         => $this->atts['password_reset_request'],
		);

		$access_utilities = new AccessUtilities();

		$logged_client = NS\MBO_Access()->get_session()->get( 'MBO_Client' )->mbo_result;

		if ( empty( $this->atts['level_1_redirect'] ) ||
				empty( $this->atts['level_2_redirect'] ) ||
				empty( $this->atts['denied_redirect'] ) ) {
			// Above test true? Then this is a content page. Check access permissions now.
			// First, check client_session for access.
			if ( ! empty( $logged_client->access_level ) &&
				in_array( $logged_client->access_level, $this->atts['access_levels'], true ) ) {
				$this->template_data['has_access'] = true;
				$this->has_access                  = true;
			} else {
				// Need to ping the api.
				$client_access_level = $access_utilities->check_access_permissions( $logged_client->ID );
				if ( in_array( $client_access_level, $this->atts['access_levels'], true ) ) {
					$this->template_data['has_access'] = true;
					$this->has_access                  = true;
				}
			}
		}

		if ( ! empty( $logged_client->ID ) ) {
			$this->template_data['logged_in'] = true;
			$this->logged_in                  = true;
			// @codingStandardsIgnoreStart (MBO Naming conventions)
			$this->template_data['client_name'] = $logged_client->FirstName;
			// @codingStandardsIgnoreEnd
		}

		$template_loader->set_template_data( $this->template_data );
		$template_loader->get_template_part( 'access_container' );

		// Add Style with script adder.
		self::addScript();

		return ob_get_clean();
	}

	/**
	 * Add Script.
	 *
	 * Add scripts if not added already.
	 *
	 * @return   void
	 */
	public function addScript() {

		if ( ! self::$added_already ) {
			self::$added_already = true;

			wp_register_style( 'mz_mindbody_style', MZ\PLUGIN_NAME_URL . 'dist/styles/main.css', null, NS\PLUGIN_VERSION );
			wp_enqueue_style( 'mz_mindbody_style' );

			wp_register_script( 'mz_mbo_access_script', NS\PLUGIN_NAME_URL . 'dist/scripts/main.js', array( 'jquery' ), NS\PLUGIN_VERSION, true );
			wp_enqueue_script( 'mz_mbo_access_script' );

			$this->localize_script();
		}
	}

	/**
	 * Localize Script.
	 *
	 * Send required variables as javascript object.
	 *
	 * @return   void
	 */
	public function localize_script() {

		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

		$translated_strings = MZ\MZMBO()->i18n->get();

		$client_logged_nonce = wp_create_nonce( 'mz_check_client_logged' );

		$params = array(
			'ajaxurl'                  => admin_url( 'admin-ajax.php', $protocol ),
			// Tested in Client\ClientPortal ajax_client_login().
			'login_nonce'              => wp_create_nonce( 'ajax_client_login' ),
			// Tested in Client\ClientPortal ajax_client_logout().
			'logout_nonce'             => wp_create_nonce( 'ajax_client_logout' ),
			// Tested in Client\ClientPortal ajax_check_client_logged().
			'check_logged_nonce'       => $client_logged_nonce,
			// Tested in Access\AccessPortal ajax_login_check_access_permissions().
			'check_access_permissions' => wp_create_nonce(
				'ajax_login_check_access_permissions'
			),
			'atts'                     => $this->atts,
			'restricted_content'       => $this->restricted_content,
			'site_id'                  => $this->site_id,
			'logged_in'                => $this->check_client_logged,
			'has_access'               => $this->has_access,
			'denied_message'           => $this->denied_message,
			'required_services'        => array(
				1 => $this->level_1_services,
				2 => $this->level_2_services,
			),
		);

		wp_localize_script( 'mz_mbo_access_script', 'mz_mbo_access_vars', $params );
	}
}
