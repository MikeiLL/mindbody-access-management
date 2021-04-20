<?php
/**
 * The core plugin class.
 *
 * Define internationalization, admin-specific hooks,
 * and public-facing site hooks.
 *
 * @package MZMBOACCESS
 */

namespace MZoo\MzMboAccess\Carbon_Fields;

use MZoo\MzMboAccess as NS;
use MZoo\MzMindbody as MZ;
use MZoo\MzMboAccess\Dependencies\Carbon_Fields;
use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Container;
use MZoo\MzMboAccess\Dependencies\Carbon_Fields\Field;
use MZoo\MzMindbody\Sale;
use MZoo\MzMindbody\Site;

/**
 * Carbon Fields Extension Class.
 *
 * Hook in the Carbon Fields actions, filters, etc.
 *
 * @link  http://mzoo.org
 * @since 1.0.0
 *
 * @author Mike iLL/mZoo.org
 */
class Carbon_Fields_Init {

	/**
	 * Load Carbon Fields
     * 
     * @since 2.1.1
	 *
	 * Call the Carbon Fields boot method.
	 */
	public function crb_load() {
		Carbon_Fields\Carbon_Fields::boot();
	}

	/**
	 * Access Levels Page via Carbon Fields
     * 
     * @since 2.1.1
     * @return void
	 */
	public function access_levels_page() {
		Container\Container::make( 'theme_options', __( 'MBO Access Levels' ) )
			->add_fields(
				array(
					Field::make( 'complex', 'mbo_access_access_levels', __( 'Access Level' ) )
					->add_fields(
						'access_level',
						array(
							Field::make( 'text', 'access_level_name', __( 'Name' ) ),
							Field::make( 'multiselect', 'access_level_subscriptions', __( 'Mindbody Subscriptions' ) )
								->add_options( self::get_mbo_subscriptions() ),
							Field::make( 'multiselect', 'access_level_programs', __( 'Mindbody Programs' ) )
								->add_options( self::get_mbo_programs() ),
							Field::make( 'multiselect', 'access_level_memberships', __( 'Mindbody Memberships' ) )
								->add_options( self::get_mbo_memberships() )
						)
					)->set_help_text( __("Generate Access Levels by Mindbody Subscription, Program or Memberships.", 'mz-mbo-access') ),
				)
			);

	}

	/**
	 * Get Mindbody Subscriptions
     * 
     * @since 2.1.1
     * 
     * @return dictionary of MBO subscriptions by Id.
	 */
	public static function get_mbo_subscriptions() {
		$sale_object = new Sale\RetrieveSale();
		return $sale_object->get_contracts( true );
	}

	/**
	 * Get Mindbody Programs
     * 
     * @since 2.1.1
     * 
     * @return dictionary of MBO subscriptions by Id.
	 */
	public static function get_mbo_programs() {
		$site_object = new Site\RetrieveSite();
		return $site_object->get_site_programs( true );
	}

	/**
	 * Get Mindbody Memberships
     * 
     * @since 2.1.1
     * 
     * @return dictionary (Active) of MBO site memberships by MembershipId.
	 */
	public static function get_mbo_memberships() {
		$site_object = new Site\RetrieveSite();
		return $site_object->get_site_memberships( true );
	}
}
