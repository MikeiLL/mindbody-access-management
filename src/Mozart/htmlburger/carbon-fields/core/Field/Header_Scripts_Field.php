<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field;

/**
 * Header scripts field class.
 * Intended only for use in theme options container.
 */
class Header_Scripts_Field extends Scripts_Field {

	/**
	 * {@inheritDoc}
	 */
	protected $hook_name = 'wp_head';

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_help_text() {
		return __( 'If you need to add scripts to your header, you should enter them here.', 'carbon-fields' );
	}
}
