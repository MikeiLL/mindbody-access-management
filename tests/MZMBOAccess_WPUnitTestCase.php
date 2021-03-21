<?php

require_once 'MBO_Access_Test_Options.php';
require 'bootstrap.php';

/**
 * Class MZMBO_WPUnitTestCase
 *
 * @package Mz_Mindbody_Api
 */

/**
 * Add a logging method to WP UnitTestCase Class.
 */
abstract class MZMBOAccess_WPUnitTestCase extends \WP_UnitTestCase
{


    public function el($message)
    {
        file_put_contents('./log_' . date('j.n.Y') . '.log', $message, FILE_APPEND);
    }

    public static function setUpBeforeClass()
    {
        // global vars setup.
        $basic_options_set = array(
            'mz_source_name'       => MBO_Access_Test_Options::$_MYSOURCENAME,
            'mz_mindbody_password' => MBO_Access_Test_Options::$_MYPASSWORD,
            'mz_mbo_app_name'      => MBO_Access_Test_Options::$_MYAPPNAME,
            'mz_mbo_api_key'       => MBO_Access_Test_Options::$_MYAPIKEY,
            'sourcename_not_staff' => 'on',
            'mz_mindbody_siteID'   => '-99',
        );

        add_option('mz_mbo_basic', $basic_options_set, '', 'yes');

        $tm = new MZoo\MzMindbody\Common\TokenManagement();

        $token = $tm->serve_token();

        MBO_Access_Test_Options::$_MYACCESSTOKEN = $token;

        $current = new \DateTime();
        $current->format('Y-m-d H:i:s');

        update_option(
            'mz_mbo_token',
            array(
                'stored_time' => $current,
                'AccessToken' => MBO_Access_Test_Options::$_MYACCESSTOKEN,
            )
        );
    }
}
