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
 * Empty datastore class.
 */
class Empty_Datastore extends Datastore {
	/**
	 * {@inheritDoc}
	 */
	public function init() {}

	/**
	 * {@inheritDoc}
	 */
	public function load( Field $field ) {}

	/**
	 * {@inheritDoc}
	 */
	public function save( Field $field ) {}

	/**
	 * {@inheritDoc}
	 */
	public function delete( Field $field ) {}
}
