<?php
/*
 * Plugin Name:       McKenzie Towne Mailchimp Management
 * Plugin URI:        http://www.stackeddesign.ca
 * Description:       Manage subsriptions to mailchimp.
 * Version:           0.0.1
 * Author:            James Wheeler
 * Text Domain:       single-post-meta-manager-locale
 * License:           MIT
 * License URI:       http://opensource.org/licenses/MIT
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

require plugin_dir_path( __FILE__ ) . 'public/public-controller.php';

/**
 *  Main Initalization class for Wordpress Plugin
 *  Actions are required in the constructor due to wordpress using the global namespace
 *  Instantiate the public and admin controller
 */
class McKenzieTownMailChimp {

    public function __construct() {
        // Public Facing
        $publicController = new McKenzieTownPublicController();
        add_action( 'init',  array($publicController, 'loadScripts') );
        add_shortcode('mckenzieMailChimpForm', array($publicController, 'loadForm'));


        // Add ajax request routes
        add_action( 'wp_ajax_mailChimpAjax', array( $publicController, 'mailChimpAjax' ) ); 
        add_action( 'wp_ajax_nopriv_mailChimpAjax', array( $publicController, 'mailChimpAjax' ) );
        add_action( 'wp_ajax_getSchools', array( $publicController, 'getSchools' ) ); 
        add_action( 'wp_ajax_nopriv_getSchools', array( $publicController, 'getSchools' ) );

        // Admin Facing
        // To be completed
    }

}

$chimp = new McKenzieTownMailChimp();