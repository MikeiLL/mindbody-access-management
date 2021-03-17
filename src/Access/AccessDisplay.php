<?php

namespace MzMBO_Access\Access;

use MzMBO_Access as NS;
use MzMindbody as MZ;
use MzMBO_Access\Core as Core;
use MzMBO_Access\Session as Session;
use MzMBO_Access\Client as Client;
use MzMindbody\Inc\Site as Site;
use MzMindbody\Inc\Common as Common;
use MzMindbody\Inc\Common\Interfaces as Interfaces;

class Access_Display extends Interfaces\ShortcodeScriptLoader
{



    /**
     * If shortcode script has been enqueued.
     *
     * @since  1.0.0
     * @access public
     *
     * @used in handleShortcode, addScript
     * @var  boolean $addedAlready True if shorcdoe scripts have been enqueued.
     */
    static $addedAlready = false;

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

    public function handleShortcode($atts, $content = null)
    {

        $this->atts = shortcode_atts(
            array(
                'siteid'                 => '',
                'denied_message'         => __('Access to this content requires one of', NS\PLUGIN_TEXT_DOMAIN),
                'call_to_action'         => __('Login with your Mindbody account to access this content.', NS\PLUGIN_TEXT_DOMAIN),
                'access_expired'         => __('Looks like your access has expired.', NS\PLUGIN_TEXT_DOMAIN),
                'level_1_redirect'       => '',
                'level_2_redirect'       => '',
                'denied_redirect'        => '',
                'access_levels'          => 1,
                'manage_on_mbo'          => 'Visit Mindbody Site',
                'password_reset_request' => __('Forgot My Password', NS\PLUGIN_TEXT_DOMAIN),
            ),
            $atts
        );

        $this->siteID = ( isset($atts['siteid']) ) ? $atts['siteid'] : MZ\MZMBO()::$basic_options['mz_mindbody_siteID'];

        // TODO can we avoid doing this here AND in access utilities?
        $mz_mbo_access_options  = get_option('mz_mbo_access');
        $this->level_1_services = explode(',', $mz_mbo_access_options['level_1_services']);
        $this->level_2_services = explode(',', $mz_mbo_access_options['level_2_services']);
        $this->level_1_services = array_map('trim', $this->level_1_services);
        $this->level_2_services = array_map('trim', $this->level_2_services);

        $this->atts['access_levels'] = explode(',', $this->atts['access_levels']);
        $this->atts['access_levels'] = array_map('trim', $this->atts['access_levels']);

        $this->restricted_content = $content;

        // Begin generating output
        ob_start();

        $template_loader = new Core\Template_Loader();

        $this->template_data = array(
            'atts'                   => $this->atts,
            'content'                => $this->restricted_content,
            'signup_nonce'           => wp_create_nonce('mz_mbo_signup_nonce'),
            'siteID'                 => MZ\MZMBO()::$basic_options['mz_mindbody_siteID'],
            'email'                  => __('email', 'mz-mbo-access'),
            'password'               => __('password', 'mz-mbo-access'),
            'login'                  => __('Login', 'mz-mbo-access'),
            'logout'                 => __('Logout', 'mz-mbo-access'),
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

        $access_utilities = new Access_Utilities();

        $logged_client = NS\MBO_Access()->getSession()->get('MBO_Client');

        if (empty($this->atts['level_1_redirect']) || empty($this->atts['level_2_redirect']) || empty($this->atts['denied_redirect'])) {
            // If this is a content page check access permissions now
            // First we will see if client access is already determined in client_session
            if (! empty($logged_client->access_level) && in_array($logged_client->access_level, $this->atts['access_levels'])) {
                $this->template_data['has_access'] = true;
                $this->has_access                  = true;
            } else {
                // Need to ping the api
                $client_access_level = $access_utilities->check_access_permissions($logged_client->Id);
                if (in_array($client_access_level, $this->atts['access_levels'])) {
                    $this->template_data['has_access'] = true;
                    $this->has_access                  = true;
                }
            }
        }

        if (! empty($logged_client->Id)) {
            $this->template_data['logged_in']   = true;
            $this->logged_in                    = true;
            $this->template_data['client_name'] = $logged_client->FirstName;
        }

        $template_loader->set_template_data($this->template_data);
        $template_loader->get_template_part('access_container');

        // Add Style with script adder
        self::addScript();

        return ob_get_clean();
    }

    /*
    * What is this?
    */
    private function login_form()
    {

        $template_loader = new Core\Template_Loader();

        $template_loader->set_template_data($this->template_data);
    }

    public function addScript()
    {

        if (! self::$addedAlready) {
            self::$addedAlready = true;

            wp_register_style('mz_mindbody_style', MZ\PLUGIN_NAME_URL . 'dist/styles/main.css');
            wp_enqueue_style('mz_mindbody_style');

            wp_register_script('mz_mbo_access_script', NS\PLUGIN_NAME_URL . 'dist/scripts/main.js', array( 'jquery' ), NS\PLUGIN_VERSION, true);
            wp_enqueue_script('mz_mbo_access_script');

            $this->localizeScript();
        }
    }

    public function localizeScript()
    {

        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

        $translated_strings = MZ\MZMBO()->i18n->get();

        $params = array(
            'ajaxurl'            => admin_url('admin-ajax.php', $protocol),
            'login_nonce'        => wp_create_nonce('mz_mbo_access_nonce'),
            'logout_nonce'       => wp_create_nonce('mz_client_log_out'),
            'atts'               => $this->atts,
            'restricted_content' => $this->restricted_content,
            'siteID'             => $this->siteID,
            'logged_in'          => $this->logged_in,
            'has_access'         => $this->has_access,
            'denied_message'     => $this->denied_message,
            'required_services'  => array(
                1 => $this->level_1_services,
                2 => $this->level_2_services,
            ),
        );
        wp_localize_script('mz_mbo_access_script', 'mz_mindbody_access', $params);
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
     * Echo json json_encode() version of HTML from template
     */
    public function display_schedule()
    {

        check_ajax_referer($_REQUEST['nonce'], 'mz_mbo_access_nonce', false);

        $atts = $_REQUEST['atts'];

        $result['type'] = 'success';

        $template_loader = new Core\Template_Loader();

        $this->schedule_object = new Retrieve_Schedule($atts);

        // Call the API and if fails, return error message.
        if (false == $this->schedule_object->getMboResults()) {
            echo '<div>' . __('Error returning schedule from Mindbody in Access Display.', NS\PLUGIN_TEXT_DOMAIN) . '</div>';
        }

        // Register attributes
        $this->handleShortcode($atts);

        // Update the data array
        $this->template_data['time_format'] = $this->schedule_object->time_format;
        $this->template_data['date_format'] = $this->schedule_object->date_format;

        $template_loader->set_template_data($this->template_data);

        // Initialize the variables, so won't be un-set:
        $horizontal_schedule = '';
        $grid_schedule       = '';
        if ($this->display_type == 'grid' || $this->display_type == 'both') :
            ob_start();
            $grid_schedule = $this->schedule_object->sortClassesByTimeThenDate();
            // Update the data array
            $this->template_data['grid_schedule'] = $grid_schedule;
            $template_loader->get_template_part('grid_schedule');
            $result['grid'] = ob_get_clean();
        endif;

        if ($this->display_type == 'horizontal' || $this->display_type == 'both') :
            ob_start();
            $horizontal_schedule = $this->schedule_object->sortClassesByDateThenTime();
            // Update the data array
            $this->template_data['horizontal_schedule'] = $horizontal_schedule;
            $template_loader->get_template_part('horizontal_schedule');
            $result['horizontal'] = ob_get_clean();
        endif;

        $result['message'] = __('Error. Please try again.', NS\PLUGIN_TEXT_DOMAIN);

        if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }

        die();
    }
}
