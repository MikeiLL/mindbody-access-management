<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field;

/**
 * Image field class.
 *
 * Allows selecting and saving a media attachment file,
 * where the image ID is saved in the database.
 */
class Image_Field extends File_Field {
	public $field_type = 'image';
}
