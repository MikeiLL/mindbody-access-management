<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields;

/**
 * Block proxy factory class.
 * Used for shorter namespace access when creating a block.
 */
class Block extends Container {
	/**
	 * {@inheritDoc}
	 */
	public static function make() {
		return call_user_func_array( array( 'parent', 'make' ), array_merge( array( 'block' ), func_get_args() ) );
	}
}
