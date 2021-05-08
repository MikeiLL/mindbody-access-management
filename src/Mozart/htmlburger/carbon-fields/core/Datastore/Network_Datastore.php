<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Datastore;

use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field\Field;

/**
 * Theme options datastore class.
 */
class Network_Datastore extends Meta_Datastore {
	/**
	 * {@inheritDoc}
	 */
	public function get_meta_type() {
		return 'site';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_table_name() {
		global $wpdb;
		return $wpdb->sitemeta;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_table_field_name() {
		return 'site_id';
	}
}
