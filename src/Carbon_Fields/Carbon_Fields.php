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
class Carbon_Fields {

    /**
     * Load Carbon Fields
     * 
     * Call the Carbon Fields boot method.
     */
    public function crb_load(){
        NS\Dependencies\Carbon_Fields\Carbon_Fields::boot();
    }

    /**
     * Test Carbon Fields Options Page
     */
    function crb_attach_theme_options() {
        Container::make( 'theme_options', __( 'Carbon Fields Options' ) )
            ->add_fields( array(
                Field::make( 'text', 'crb_text', 'Text Field' ),
            ) );
    }
}
