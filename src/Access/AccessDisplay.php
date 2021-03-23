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
	 * @used in handleShortcode, addScript
	 * @var      boolean $added_already True if shorcdoe scripts have been enqueued.
	 */
	private static $added_already = false;

	/**
	 * Restricted content.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handleShortcode, localizeScript
	 * @var  string $restricted_content Content between two shortcode tags.
	 */
	public $restricted_content;

	/**
	 * Shortcode attributes.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handleShortcode, localizeScript
	 * @var  array $atts Shortcode attributes function called with.
	 */
	public $atts;

	/**
	 * Data to send to template
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handleShortcode,
	 * @var  @array    $data    array to send template.
	 */
	public $template_data;

	/**
	 * Status of client login
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handleShortcode, localizeScript
	 * @var  @array    $data    array to send template.
	 */
	public $logged_in;

	/**
	 * Status of client access
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handleShortcode, localizeScript
	 * @var  @bool    $has_access if current client has access current page.
	 */
	public $has_access;

	/**
	 * Level of client access
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in handleShortcode, localizeScript
	 * @var  @int    $client_access_level current client access level.
	 */
	public $client_access_level;

	/**
	 * Level One Services
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localizeScript
	 * @var  @array    $level_1_services of services from options page.
	 */
	public $level_1_services;

	/**
	 * Level Two Services
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @used in localizeScript
	 * @var  @array    $level_2_services of services from options page.
	 */
	public $level_2_services;

	/**
	 * Handle Shortcode
	 *
	 * @param    string $atts shortcode inputs.
	 * @param    string $content any content between start and end shortcode tags.
	 * @return   string shortcode content.
	 */
	public function handleShortcode( $atts, $content = null ) {

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

		// TODO can we avoid doing this here AND in access utilities?
		$mz_mbo_access_options  = get_option( 'mz_mbo_access' );
		$this->level_1_services = explode( ',', $mz_mbo_access_options['level_1_services'] );
		$this->level_2_services = explode( ',', $mz_mbo_access_options['level_2_services'] );
		$this->level_1_services = array_map( 'trim', $this->level_1_services );
		$this->level_2_services = array_map( 'trim', $this->level_2_services );

		$this->atts['access_levels'] = explode( ',', $this->atts['access_levels'] );
		$this->atts['access_levels'] = array_map( 'trim', $this->atts['access_levels'] );

		$this->restricted_content = $content;

		// Begin generating output.
		ob_start();

		$template_loader = new Core\TemplateLoader();

		$this->template_data = array(
			'atts'                   => $this->atts,
			'content'                => $this->restricted_content,
			'signup_nonce'           => wp_create_nonce( 'mz_mbo_signup_nonce' ),
			'siteID'                 => MZ\MZMBO()::$basic_options['mz_mindbody_siteID'],
			'email'                  => __( 'email', 'mz-mbo-access' ),
			'password'               => __( 'password', 'mz-mbo-access' ),
			'login'                  => __( 'Login', 'mz-mbo-access' ),
			'logout'                 => __( 'Logout', 'mz-mbo-access' ),
			'logged_in'              => false,
			'required_services'      => array(
				1 => $this->level_1_services,
				2 => $this->level_2_services,
			),
			'access_levels'          => $this->atts['access_levels'],
			'has_access'             => false,
			'client_name'            => '',
			'denied_message'         => $this->atts['denied_message'],
			'manage_on_mbo'          => $this->atts['manage_on_mbo'],
			'password_reset_request' => $this->atts['password_reset_request'],
		);

		$access_utilities = new AccessUtilities();

		$logged_client = NS\MBO_Access()->get_session()->get( 'MBO_Client' );

		if ( empty( $this->atts['level_1_redirect'] ) ||
				empty( $this->atts['level_2_redirect'] ) ||
				empty( $this->atts['denied_redirect'] ) ) {
			// If this is a content page check access permissions now.
			// First we will see if client access is already determined in client_session.
			if ( ! empty( $logged_client->access_level ) &&
				in_array( $logged_client->access_level, $this->atts['access_levels'], true ) ) {
				$this->template_data['has_access'] = true;
				$this->has_access                  = true;
			} else {
				// Need to ping the api.
				$client_access_level = $access_utilities->check_access_permissions( $logged_client->Id );
				if ( in_array( $client_access_level, $this->atts['access_levels'], true ) ) {
					$this->template_data['has_access'] = true;
					$this->has_access                  = true;
				}
			}
		}

		if ( ! empty( $logged_client->Id ) ) {
			$this->template_data['logged_in']   = true;
			$this->logged_in                    = true;
			$this->template_data['client_name'] = $logged_client->FirstName;
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

			$this->localizeScript();
		}
	}

	/**
	 * Localize Script.
	 *
	 * Send required variables as javascript object.
	 *
	 * @return   void
	 */
	public function localizeScript() {

		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

		$translated_strings = MZ\MZMBO()->i18n->get();

		$params = array(
			'ajaxurl'            => admin_url( 'admin-ajax.php', $protocol ),
			'login_nonce'        => wp_create_nonce( 'mz_mbo_access_nonce' ),
			'logout_nonce'       => wp_create_nonce( 'mz_client_log_out' ),
			'atts'               => $this->atts,
			'restricted_content' => $this->restricted_content,
			'siteID'             => $this->site_id,
			'logged_in'          => $this->logged_in,
			'has_access'         => $this->has_access,
			'denied_message'     => $this->denied_message,
			'required_services'  => array(
				1 => $this->level_1_services,
				2 => $this->level_2_services,
			),
		);
		wp_localize_script( 'mz_mbo_access_script', 'mz_mindbody_access', $params );
	}

	/**
	 * Ajax function to return mbo schedule
	 *
	 * @since 1.0.1
	 *
	 * This duplicates a lot of the handle_shortcode function, but
	 * is called via AJAX and used when navigating the schedule.
	 *
	 *
	 *
	 * Echo json wp_json_encode() version of HTML from template
	 */
	public function display_schedule() {

		check_ajax_referer( $_REQUEST['nonce'], 'mz_mbo_access_nonce', false );

		$atts = $_REQUEST['atts'];

		$result['type'] = 'success';

		$template_loader = new Core\TemplateLoader();

		$this->schedule_object = new RetrieveSchedule( $atts );

		// Call the API and if fails, display error message.
		if ( false === $this->schedule_object->getMboResults() ) {
			echo esc_html( '<div>' . __( 'Error returning schedule from Mindbody in Access Display.', 'mz-mbo-access' ) . '</div>' );
		}

		// Register attributes.
		$this->handleShortcode( $atts );

		// Update the data array.
		$this->template_data['time_format'] = $this->schedule_object->time_format;
		$this->template_data['date_format'] = $this->schedule_object->date_format;

		$template_loader->set_template_data( $this->template_data );

		// Initialize the variables, so won't be un-set.
		$horizontal_schedule = '';
		$grid_schedule       = '';
		if ( 'grid' === $this->display_type ||
				'both' === $this->display_type ) :
			ob_start();
			$grid_schedule = $this->schedule_object->sortClassesByTimeThenDate();
			// Update the data array.
			$this->template_data['grid_schedule'] = $grid_schedule;
			$template_loader->get_template_part( 'grid_schedule' );
			$result['grid'] = ob_get_clean();
		endif;

		if ( 'horizontal' === $this->display_type ||
				'both' === $this->display_type ) :
			ob_start();
			$horizontal_schedule = $this->schedule_object->sortClassesByDateThenTime();
			// Update the data array.
			$this->template_data['horizontal_schedule'] = $horizontal_schedule;
			$template_loader->get_template_part( 'horizontal_schedule' );
			$result['horizontal'] = ob_get_clean();
		endif;

		$result['message'] = __( 'Error. Please try again.', 'mz-mbo-access' );

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
