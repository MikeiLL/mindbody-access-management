<?php

namespace MZ_MBO_Access\Core;

use MZ_MBO_Access as NS;
use MZ_Mindbody as MZ;
use MZ_MBO_Access\Admin as Admin;

/**
 * Fired during plugin activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link  http://mzoo.org
 * @since 1.0.0
 *
 * @author Mike iLL/mZoo.org
 **/
class Activator
{



    /**
     * Check php version and that MZMBO is active.
     *
     * @since 1.0.0
     */
    public static function activate()
    {

        if (! self::is_mzmbo_active()) {
            $admin_object = new Admin\Admin(NS\PLUGIN_NAME, NS\PLUGIN_VERSION, NS\PLUGIN_TEXT_DOMAIN);
            add_action('admin_notices', array( $admin_object, 'admin_notice' ));
            deactivate_plugins(NS\PLUGIN_BASENAME);
        }
    }

    /**
     * Checks if MZMBO is active.
     *
     * @since 1.0.0
     *
     * @return bool true if MZMBO is active, false otherwise
     */
    public static function is_mzmbo_active()
    {
        $active_plugins = (array) get_option('active_plugins', array());
        return in_array('mz-mindbody-api/mz-mindbody.php', $active_plugins) || array_key_exists('mz-mindbody-api/mz-mindbody.php', $active_plugins);
    }
}
