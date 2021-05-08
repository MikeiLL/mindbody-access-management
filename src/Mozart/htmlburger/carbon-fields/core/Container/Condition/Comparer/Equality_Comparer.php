<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container\Condition\Comparer;

class Equality_Comparer extends Comparer {

	/**
	 * Supported comparison signs
	 *
	 * @var array<string>
	 */
	protected $supported_comparison_operators = array( '=', '!=' );

	/**
	 * Check if comparison is true for $a and $b
	 *
	 * @param mixed  $a
	 * @param string $comparison_operator
	 * @param mixed  $b
	 * @return bool
	 */
	public function is_correct( $a, $comparison_operator, $b ) {
		switch ( $comparison_operator ) {
			case '=':
				return $a == $b;
			case '!=':
				return $a != $b;
		}
		return false;
	}
}