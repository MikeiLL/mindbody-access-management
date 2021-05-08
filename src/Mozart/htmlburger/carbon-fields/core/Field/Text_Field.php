<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field;

use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Exception\Incorrect_Syntax_Exception;

/**
 * Text field class.
 */
class Text_Field extends Field {

	/**
	 * {@inheritDoc}
	 */
	protected $allowed_attributes = array( 'list', 'max', 'maxLength', 'min', 'pattern', 'placeholder', 'readOnly', 'step', 'type', 'is' );
}
