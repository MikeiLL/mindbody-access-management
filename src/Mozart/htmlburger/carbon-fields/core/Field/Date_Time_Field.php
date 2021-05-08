<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field;

/**
 * Date and time picker field class.
 */
class Date_Time_Field extends Time_Field {

	/**
	 * {@inheritDoc}
	 */
	protected $picker_options = array(
		'allowInput' => true,
		'enableTime' => true,
		'enableSeconds' => true,
	);

	/**
	 * {@inheritDoc}
	 */
	protected $storage_format = 'Y-m-d H:i:s';

	/**
	 * {@inheritDoc}
	 */
	protected $input_format_php = 'Y-m-d g:i:s A';

	/**
	 * {@inheritDoc}
	 */
	protected $input_format_js = 'Y-m-d h:i:S K';
}
