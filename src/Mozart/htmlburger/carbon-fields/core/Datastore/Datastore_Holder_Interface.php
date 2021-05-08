<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Datastore;

interface Datastore_Holder_Interface {

	/**
	 * Return whether the datastore instance is the default one or has been overriden
	 *
	 * @return boolean
	 */
	public function has_default_datastore();

	/**
	 * Set datastore instance
	 *
	 * @param Datastore_Interface $datastore
	 * @param boolean             $set_as_default
	 * @return object $this
	 */
	public function set_datastore( Datastore_Interface $datastore, $set_as_default );

	/**
	 * Get the DataStore instance
	 *
	 * @return Datastore_Interface $datastore
	 */
	public function get_datastore();
}