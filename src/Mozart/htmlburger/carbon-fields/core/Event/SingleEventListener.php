<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Event;

class SingleEventListener extends PersistentListener {

	/**
	 * Flag if the event has been called
	 *
	 * @var boolean
	 */
	protected $called = false;

	/**
	 * {@inheritDoc}
	 */
	public function is_valid() {
		return ! $this->called;
	}

	/**
	 * {@inheritDoc}
	 */
	public function notify() {
		$this->called = true;
		return call_user_func_array( array( $this, 'parent::notify' ), func_get_args() );
	}
}