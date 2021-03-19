<?php

namespace MZoo\MzMboAccess\Admin;

use MZoo\MzMboAccess as NS;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link  http://mzoo.org
 * @since 1.0.0
 *
 * @author Mike iLL/mZoo.org
 */
class Admin {




	/**
	 * Notify Admin when plugin deactivated.
	 *
	 * TODO: abstract, maybe
	 *
	 * @since 2.5.7
	 */
	public function admin_notice() {
		echo wp_kses_post(
			sprintf(
				'<div class="notice notice-error"><p>%s</p></div>',
				__( 'Missing Plugin Dependency MZ Mindbody Api.', NS\PLUGIN_TEXT_DOMAIN )
			)
		);
	}
}
