<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container\Condition;

/**
 * Check if the currently logged in user has a specific role
 *
 * Operator "CUSTOM" is passed an array of all user roles
 */
class Current_User_Role_Condition extends User_Role_Condition {

	/**
	 * Get roles for a user from the environment
	 *
	 * @param  array         $environment
	 * @return array<string>
	 */
	protected function get_user_roles( $environment ) {
		$user = wp_get_current_user();
		$roles = $user ? $user->roles : array();
		return $roles;
	}
}