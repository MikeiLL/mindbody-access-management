<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by Mike iLL Kilmer on 08-May-2021 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container\Fulfillable\Translator;

use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container\Fulfillable\Fulfillable;
use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container\Fulfillable\Fulfillable_Collection;
use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container\Condition\Condition;
use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Exception\Incorrect_Syntax_Exception;

abstract class Translator {

	/**
	 * Translate a Fulfillable to foreign data
	 *
	 * @param  Fulfillable $fulfillable
	 * @return mixed
	 */
	public function fulfillable_to_foreign( Fulfillable $fulfillable ) {
		if ( $fulfillable instanceof Condition ) {
			return $this->condition_to_foreign( $fulfillable );
		}

		if ( $fulfillable instanceof Fulfillable_Collection ) {
			return $this->fulfillable_collection_to_foreign( $fulfillable );
		}

		Incorrect_Syntax_Exception::raise( 'Attempted to translate an unsupported object: ' . print_r( $fulfillable, true ) );
		return null;
	}

	/**
	 * Translate a Condition to foreign data
	 *
	 * @param  Condition $condition
	 * @return mixed
	 */
	abstract protected function condition_to_foreign( Condition $condition );

	/**
	 * Translate a Fulfillable_Collection to foreign data
	 *
	 * @param  Fulfillable_Collection $fulfillable_collection
	 * @return mixed
	 */
	abstract protected function fulfillable_collection_to_foreign( Fulfillable_Collection $fulfillable_collection );

	/**
	 * Translate foreign data to a Fulfillable
	 *
	 * @param  mixed       $foreign
	 * @return Fulfillable
	 */
	abstract public function foreign_to_fulfillable( $foreign );
}
