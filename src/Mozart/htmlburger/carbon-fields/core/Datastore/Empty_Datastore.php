<?php

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
