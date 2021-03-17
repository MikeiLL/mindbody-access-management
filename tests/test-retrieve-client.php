<?php

require_once 'MZMBOAccess_WPUnitTestCase.php';
require_once 'MBO_Access_Test_Options.php';

class Tests_Retrieve_Client extends MZMBOAccess_WPUnitTestCase
{


    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_get_signup_form_fields()
    {

        parent::setUp();

        $this->assertTrue(class_exists('MZ_MBO_Access\Client\Retrieve_Client'));

        $client_object = new MZ_MBO_Access\Client\Retrieve_Client();

        $response = $client_object->get_mbo_results();

        $this->assertTrue($response);

        $required_fields = $client_object->get_signup_form_fields();

        $this->assertTrue(in_array('Email', $required_fields));
        $this->assertTrue(in_array('FirstName', $required_fields));
        $this->assertTrue(in_array('LastName', $required_fields));
    }

    public function test_add_client()
    {
        // This API let's me create all the duplicate contacts I want with same name, email.
        // So only do this if we haven't already
        // Check https://developers.mindbodyonline.com/PublicDocumentation/V6#add-a-new-client
        // For recommended workflow as new feature being added May 11 2020
        if (! empty(MBO_Access_Test_Options::$_CLIENTPASSWORD)) {
            return false;
        }

        parent::setUp();

        $client_object = new MZ_MBO_Access\Client\Retrieve_Client();

        $required_fields = $client_object->get_signup_form_fields();

        $user_data = array();

        // Merge testa data with random data for additional required fields

        $length = 7; // of random string

        foreach ($required_fields as $k => $v) {
            switch ($v) {
                case 'FirstName':
                    $user_data['FirstName'] = MBO_Access_Test_Options::$_FIRSTNAME;
                    break;
                case 'LastName':
                    $user_data['LastName'] = MBO_Access_Test_Options::$_LASTNAME;
                    break;
                case 'Email':
                    $user_data['Email'] = MBO_Access_Test_Options::$_CLIENTEMAIL;
                    break;
                case 'State':
                    $user_data['State'] = MBO_Access_Test_Options::$_CLIENTSTATE;
                    break;
                case 'PostalCode':
                    $user_data['PostalCode'] = '32505';
                    break;
                case 'MobilePhone':
                    $user_data['MobilePhone'] = '8504333202';
                    break;
                case 'BirthDate':
                    $user_data['BirthDate'] = MBO_Access_Test_Options::$_CLIENTBIRTHDATE;
                    break;
                default:
                    $user_data[ $v ] = $v . '_' . substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
                    break;
            }
        }

        $new_client = $client_object->add_client($user_data);

        if (! empty($new_client['Client']['Id'])) {
            MBO_Access_Test_Options::$_CLIENTID = $new_client['Client']['Id']; // 100015679
        }

        // TODO More validation tests for various fields

        $this->assertTrue(is_array($new_client));

        $this->assertTrue(is_string(MBO_Access_Test_Options::$_CLIENTID));
        $this->assertTrue($new_client['Client']['Email'] == MBO_Access_Test_Options::$_CLIENTEMAIL);
    }

    public function test_password_reset_email_request()
    {

        parent::setUp();

        $client_object = new MZ_MBO_Access\Client\Retrieve_Client();

        $user_data = array(
            'UserEmail'     => MBO_Access_Test_Options::$_CLIENTEMAIL,
            'UserFirstName' => MBO_Access_Test_Options::$_FIRSTNAME,
            '1UserLastName' => MBO_Access_Test_Options::$_LASTNAME,
        );

        $client_reset_request = $client_object->password_reset_email_request($user_data);

        $this->assertTrue($client_reset_request['Error']['Code'] == 'MissingRequiredFields');

        if (empty(MBO_Access_Test_Options::$_CLIENTPASSWORD)) {
            $user_data            = array(
                'UserEmail'     => MBO_Access_Test_Options::$_CLIENTEMAIL,
                'UserFirstName' => MBO_Access_Test_Options::$_FIRSTNAME,
                'UserLastName'  => MBO_Access_Test_Options::$_LASTNAME,
            );
            $client_reset_request = $client_object->password_reset_email_request($user_data);

            // We will use this method to not create a new user if one already
            // exists with this email and name
            if (empty($client_reset_request)) {
                return false;
            }

            $this->assertTrue(empty($client_reset_request));
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     * // prevent headers already sent error
     * // see: https://phpunit.de/manual/6.5/en/appendixes.annotations.html#appendixes.annotations.runInSeparateProcess
     */
    public function test_log_client_in()
    {

        if (empty(MBO_Access_Test_Options::$_CLIENTPASSWORD)) {
            return; // can't login yet.
        }

        parent::setUp();

        $client_object = new MZ_MBO_Access\Client\Retrieve_Client();

        $credentials = array(
            'Username' => MBO_Access_Test_Options::$_CLIENTEMAIL,
            'Password' => MBO_Access_Test_Options::$_CLIENTPASSWORD,
        );

        $validation_result = $client_object->validate_client($credentials);

        $this->assertTrue(! empty($validation_result['ValidateLoginResult']['GUID']));

        $session_result = $client_object->create_client_session($validation_result['ValidateLoginResult']['Client']);

        $this->assertTrue((bool) $validation_result);

        $is_or_is_not = $client_object->check_client_logged();

        $this->assertTrue(true == $is_or_is_not);

        $is_or_is_not = $client_object->client_log_out();

        $is_or_is_not = $client_object->check_client_logged();

        $this->assertTrue(false == $is_or_is_not);
    }

    public function test_validate_client_pass()
    {

        parent::setUp();

        $client_object = new MZ_MBO_Access\Client\Retrieve_Client();

        $good_password_strings = array(
            'Ad12DeDe',
            'Ad12#$6^6',
            'Zs3Dddddd198',
            'Q6Kdddojos',
            '15Gkk0II',
            'lkjasdf8lj',
            'kNKnkflkj3@kk',
        );

        $bad_password_strings = array(
            'Short1',
            'aVeryLongPasswordHereSee1',
            "'This!onenonums",
            "'this!onenocaps",
            '109893',
        );

        foreach ($good_password_strings as $pass) {
            $this->assertTrue(true == $client_object->verify_mbo_pass($pass));
        }

        foreach ($bad_password_strings as $pass) {
            $this->assertTrue(false == $client_object->verify_mbo_pass($pass));
        }
    }

    public function test_get_client_details()
    {

        if (empty(MBO_Access_Test_Options::$_CLIENTPASSWORD)) {
            return; // can't login yet.
        }

        parent::setUp();

        $client_object = new MZ_MBO_Access\Client\Retrieve_Client();

        $bad_credentials = array(
            'Username' => MBO_Access_Test_Options::$_CLIENTEMAIL,
            'Password' => 'abitoks',
        );

        $failed_validation_result = $client_object->validate_client($bad_credentials);

        $this->assertTrue($failed_validation_result['ValidateLoginResult']['Status'] == 'InvalidParameters');

        $this->assertTrue($failed_validation_result['ValidateLoginResult']['ErrorCode'] == 315);

        $credentials = array(
            'Username' => MBO_Access_Test_Options::$_CLIENTEMAIL,
            'Password' => MBO_Access_Test_Options::$_CLIENTPASSWORD,
        );

        $validation_result = $client_object->validate_client($credentials);

        $this->assertTrue(! empty($validation_result['ValidateLoginResult']['GUID']));

        $session_result = $client_object->create_client_session($validation_result['ValidateLoginResult']['Client']);

        $client_id = $validation_result['ValidateLoginResult']['Client']['ID'];

        $client_details = $client_object->get_client_details_from_session($client_id);

        $client_active_memberships = $client_object->get_client_active_memberships($client_id);

        $get_client_account_balance = $client_object->get_client_account_balance($client_id);

        $get_client_contracts = $client_object->get_client_contracts($client_id);

        $get_client_services = $client_object->get_client_services($client_id);

        $get_client_purchases = $client_object->get_client_purchases($client_id);
        foreach (
            array(
            'client_active_memberships: ' => $client_active_memberships,
            'get_client_contracts: '      => $get_client_contracts,
            'get_client_purchases: '      => $get_client_purchases,
            'get_client_services: '       => $get_client_services,
            ) as $k => $v
        ) {
            // print_r($k);
            // print_r($v);
        }
        $this->assertTrue(is_array($client_active_memberships));
        $this->assertTrue(isset($get_client_account_balance));
        $this->assertTrue(is_array($get_client_contracts));
        $this->assertTrue(is_array($get_client_services));
        $this->assertTrue(is_array($get_client_purchases));
    }
}
