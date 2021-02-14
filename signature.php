<?php
/**
 * Plugin Name: Webhook Signature add-on for Gravity Forms
 * Description: Add a signature HTTP header to webhook requests to prevent man-in-the-middle and replay attacks.
 * Text Domain: gf-webhook-signature
 * Version: 1.0
 * Author: Daniel Boven
 * Author URI: https://daanboven.com/
 */

define( 'GF_WEBHOOK_SIGNATURE_ADDON_VERSION', '1.0' );

add_action( 'gform_loaded', array( 'GF_Webhook_Signature_AddOn_Bootstrap', 'load' ), 5 );

class GF_Webhook_Signature_AddOn_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfwebhooksignature.php' );

        GFAddOn::register( 'gfwebhooksignatureaddon' );
    }

}

function gf_webhook_signature_addon() {
    return gfwebhooksignatureaddon::get_instance();
}