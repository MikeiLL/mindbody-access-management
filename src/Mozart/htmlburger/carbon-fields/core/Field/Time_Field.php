<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field;

/**
 * Time picker field class.
 */
class Time_Field extends Date_Field {

	/**
	 * {@inheritDoc}
	 */
	protected $picker_options = array(
		'allowInput' => true,
		'enableTime' => true,
		'noCalendar' => true,
		'enableSeconds' => true,
	);

	/**
	 * {@inheritDoc}
	 */
	protected $storage_format = 'H:i:s';

	/**
	 * {@inheritDoc}
	 */
	protected $input_format_php = 'g:i:s A';

	/**
	 * {@inheritDoc}
	 */
	protected $input_format_js = 'h:i:S K';
}
