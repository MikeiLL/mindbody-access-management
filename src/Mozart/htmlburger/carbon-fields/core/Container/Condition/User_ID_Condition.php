<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container\Condition;

/**
 * Check for a specific user id
 */
class User_ID_Condition extends Condition {

	/**
	 * Check if the condition is fulfilled
	 *
	 * @param  array $environment
	 * @return bool
	 */
	public function is_fulfilled( $environment ) {
		$user_id = $environment['user_id'];
		return $this->compare(
			$user_id,
			$this->get_comparison_operator(),
			$this->get_value()
		);
	}
}