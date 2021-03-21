<?php
/**
 * Plugin Name: Mindbody Access Management
 * Description: Child plugin for mZoo Mindbody Interface, which can limit user access to content based on MBO client account details.
 *
 * @package MZMBOACCESS
 *
 * @wordpress-plugin
 * Version:         2.0.7
 * Stable tag:      2.0.7
 * Author:          mZoo.org
 * Author URI:      http://www.mZoo.org/
 * Plugin URI:      http://www.mzoo.org/
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     mz-mbo-access
 * Domain Path:     /languages
 */

namespace MZoo\MzMboAccess;

use MZoo\MzMboAccess as NS;
use MZoo\MzMindbody;
use MZoo\MzMindbody\Core as Core;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * TODO consider more eloquent appoach like EDD JILT work!
 * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
 */

/*
 * Define Constants
 */

define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );

define( NS . 'PLUGIN_NAME', 'mz-mbo-access' );

define( NS . 'PLUGIN_VERSION', '2.0.7' );

define( NS . 'PLUGIN_NAME_DIR', plugin_dir_path( __FILE__ ) );

define( NS . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );

define( NS . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( NS . 'PLUGIN_TEXT_DOMAIN', 'mz-mbo-access' );

add_action( 'admin_init', __NAMESPACE__ . '\mbo_access_has_mindbody_api' );



/**
 * Autoload Classes
 */
$wp_mbo_access_autoload = NS\PLUGIN_NAME_DIR . '/vendor/autoload.php';
if ( file_exists( $wp_mbo_access_autoload ) ) {
	include_once $wp_mbo_access_autoload;
}

if ( ! class_exists( '\MZoo\MzMboAccess\Core\PluginCore' ) ) {
	exit( 'MZ MBO Access requires Composer autoloading, which is not configured' );
}

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in src/core/class-activator.php
 */

register_activation_hook( __FILE__, array( NS . 'Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented src/core/class-deactivator.php
 */

/*
 * TODO: This Class is causing a php Warning, then error that it's already
 * been declared. Not doing anything anyway so commenting out for now.
 * register_deactivation_hook( __FILE__, array( NS . '\Core\Deactivator', 'deactivate' ) );
 */

/**
 * Instance of the main plugin class.
 */
class MzMboAccess {

	/**
	 * The instance of the plugin.
	 *
	 * @since 1.0.1
	 * @var   Init $init Instance of the plugin.
	 */
	private static $instance;

	/**
	 * Main MzMindbody Instance.
	 *
	 * Insures that only one instance of MzMindbody exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * Totally borrowed from Easy_Digital_Downloads, and certainly used with some
	 * ignorance.
	 *
	 * @since     1.0.1
	 * @static
	 * @staticvar array $instance
	 * @see       MBO_Access()
	 * @return    object|MzMboAccess The one true MzMboAccess
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof PluginCore ) ) {
			self::$instance = new NS\Core\PluginCore();
			self::$instance->run();
		}

		return self::$instance;
	}
}

if ( ! function_exists( 'MBO_Access' ) ) {

	/**
	 * Begins execution of the plugin
	 *
	 * The main function for that returns MzMboAccess
	 *
	 * The main function responsible for returning the one true MzMboAccess
	 * Instance to functions everywhere.
	 *
	 * Borrowed from Easy_Digital_Downloads.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $mZmboAccess = MBO_Access(); ?>
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * Also returns copy of the app object so 3rd party developers
	 * can interact with the plugin's hooks contained within.
	 *
	 * @since  1.4
	 * @return object|MzMboAccess The one true MzMboAccess Instance.
	 */
	function MBO_Access() {
		return NS\MzMboAccess::instance();
	}
}

// Check the minimum required PHP version and run the plugin.
if ( version_compare( PHP_VERSION, 'MZoo\MzMindbody\MINIMUM_PHP_VERSION', '>=' ) ) {
	add_action( 'init', __NAMESPACE__ . '\\mz_mbo_access_plugin_init' );
}

/**
 * Insure that parent plugin, is active or deactivate plugin.
 */
function mbo_access_has_mindbody_api() {
	if ( is_admin() && current_user_can( 'activate_plugins' ) && 
    ! is_plugin_active( 'mz-mindbody-api/mz-mindbody.php' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\mbo_access_child_plugin_notice' );

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

/**
 * Child Plugin Notice
 */
function mbo_access_child_plugin_notice() {     ?>
	<div class="error">
		<p>
			<?php
			esc_html_e(
				'Whoops. MZ MBO Access plugin requires the parent plugin, 
                MZ Mindbody API, to be installed and active.',
				NS\PLUGIN_TEXT_DOMAIN
			);
			?>
		</p>
	</div>
	<?php
}
/**
 * Deactivate the plugin.
 *
 * @used by mbo_access_has_mindbody_api above
 */
function deactivate() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	$admin_object = new NS\Admin\Admin( NS\PLUGIN_NAME, NS\PLUGIN_VERSION, NS\PLUGIN_TEXT_DOMAIN );
	add_action( 'admin_notices', array( $admin_object, 'admin_notice' ) );
}

/**
 * Activate the plugin.
 *
 * If version_compare passes above, function is added to init action.
 */
function mz_mbo_access_plugin_init() {
	if ( defined( 'MZoo\MzMindbody\PLUGIN_NAME_DIR' ) ) {
		// MZ Mindbody API plugin is activated, add the hooks
		// Get MzMboAccess Instance.
		MBO_Access();
	} else {
		add_action( 'admin_init', __NAMESPACE__ . '\\deactivate' );
	}
}


?>
