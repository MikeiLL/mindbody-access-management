<?php

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field;

use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Exception\Incorrect_Syntax_Exception;

/**
 * Text field class.
 */
class Text_Field extends Field {

	/**
	 * {@inheritDoc}
	 */
	protected $allowed_attributes = array( 'list', 'max', 'maxLength', 'min', 'pattern', 'placeholder', 'readOnly', 'step', 'type' );
}
