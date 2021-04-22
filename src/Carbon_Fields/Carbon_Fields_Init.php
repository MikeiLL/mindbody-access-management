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
     * Post Listings Array
     * 
     * @since 2.1.1
     * @access private
     * @var array $posts_for_options get posts to use for redirect selection.
     */
    private $posts_for_options;

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
        $this->get_post_listing();
		Container\Container::make( 'theme_options', __( 'MBO Access Levels' ) )
			->add_fields(
				array(
					Field::make( 'complex', 'mbo_access_access_levels', __( 'Access Level' ) )
					->add_fields(
						'access_level',
						array(
							Field::make( 'text', 'access_level_name', __( 'Name' ) ),
							Field::make( 'multiselect', 'access_level_contracts', __( 'Mindbody Contracts' ) )
								->add_options( self::get_mbo_contracts() ),
                            Field::make( 'multiselect', 'access_level_memberships', __( 'Mindbody Memberships' ) )
                                ->add_options( self::get_mbo_memberships() ),
							Field::make( 'multiselect', 'access_level_services', __( 'Mindbody Services' ) )
								->add_options( self::get_mbo_services() )
							Field::make( 'multiselect', 'access_level_redirect_post', __( 'Mindbody Services' ) )
								->add_options( self::get_posts_for_options() )
						)
					)->set_help_text( __("Generate Access Levels by Mindbody Subscriptions, Memberships and/or Services.", 'mz-mbo-access') ),
				)
			);

	}
    get_posts_for_options
	/**
	 * Get Mindbody Contracts
     * 
     * @since 2.1.1
     * 
     * @return dictionary of MBO subscriptions by Id.
	 */
	public static function get_mbo_contracts() {
		$sale_object = new Sale\RetrieveSale();
		return $sale_object->get_contracts( true );
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

	/**
	 * Get Mindbody Memberships
     * 
     * @since 2.1.1
     * 
     * @return dictionary (Active) of MBO site memberships by MembershipId.
	 */
	public static function get_mbo_services() {
		$sale_object = new Sale\RetrieveSale();
		return $sale_object->get_services( true );
	}

    /**
     * Get listing of WP Pages
     */
    private function get_posts_for_options(){ 
        if (! empty($this->posts_for_options)){
            return $this->posts_for_options; // Already did this. 
        }
        $posts = get_posts();
        foreach($posts as $k => $post){
            $this->posts_for_options[$post['ID'] => $post['post_title']
        }
        return $this->posts_for_options;
	}
    }
}
