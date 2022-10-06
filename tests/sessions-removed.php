<?php

require_once 'MZMBOAccess_WPUnitTestCase.php';
require_once 'MBO_Access_Test_Options.php';

class Tests_Session extends MZMBOAccess_WPUnitTestCase {



	function setUp() {

		parent::setUp();

		global $wpdb;

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$sql = "CREATE TABLE {$wpdb->prefix}sm_sessions (
		  session_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  session_key char(32) NOT NULL,
		  session_value LONGTEXT NOT NULL,
		  session_expiry BIGINT(20) UNSIGNED NOT NULL,
		  PRIMARY KEY  (session_key),
		  UNIQUE KEY session_id (session_id)
		) $collate;";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$this->el( dbDelta( $sql ) );
		new MZoo\MzMboAccess\Session\MzAccessSession();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_set() {
		$this->assertEquals( '"bar"', MZoo\MzMboAccess\Session\MzAccessSession::instance()->set( 'foo', 'bar' ) );
	}

	public function test_get() {
		$this->assertEquals( 'bar', MZoo\MzMboAccess\Session\MzAccessSession::instance()->get( 'foo' ) );
	}

	// public function test_use_cart_cookie() {
	// $this->assertTrue( MZMBO()->session->use_cart_cookie() );
	// define( 'MZMBO_USE_CART_COOKIE', false );
	// $this->assertFalse( MZMBO()->session->use_cart_cookie());
	// }

	public function test_should_start_session() {
		$blacklist = MZoo\MzMboAccess\Session\MzAccessSession::instance()->get_blacklist();
		foreach ( $blacklist as $uri ) {
			$this->go_to( '/' . $uri );
			$this->assertFalse( MZoo\MzMboAccess\Session\MzAccessSession::instance()->should_start_session() );
		}
	}
}
