<?php

GFForms::include_addon_framework();

class gfwebhooksignatureaddon extends GFAddOn {

	protected $_version = GF_WEBHOOK_SIGNATURE_ADDON_VERSION;
	protected $_min_gravityforms_version = '2.2';
	protected $_slug = 'webhooksignature';
	protected $_path = 'webhooksignature/signature.php';
	protected $_full_path = __FILE__;
    protected $_title = 'Webhook signature addon for Gravity Forms';
	protected $_short_title = 'Webhook Signature';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return gfwebhooksignatureaddon
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new gfwebhooksignatureaddon();
		}

		return self::$_instance;
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return array
	 */
	public function scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = array(
			array(
				'handle'  => 'gform_webhook_signature_pluginsettings',
				'deps'    => array( 'jquery' ),
				'src'     => $this->get_base_url() . "/js/plugin_settings{$min}.js",
				'version' => $this->_version,
				'enqueue' => array(
					array(
						'admin_page' => array( 'plugin_settings' ),
						'tab'        => $this->_slug,
					),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );

	}

	/**
	 * Fire the GF hook to load the signature setter
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_webhooks_request_args', array( $this, 'set_signature_payload'), 10 );
    }
    
    /**
     * Sign the JSON payload of the webhook
     * so that it can be verified by the receiver.
     *
     * @param array $request_args HTTP request arguments.
     */
    public function set_signature_payload( $request_args ) {
		$settings = $this->get_plugin_settings();

		if( empty( rgar( $settings, 'private_key' ) ) ) {
			// Necessary settings have not been defined, return the unedited input args
			return $request_args;
		}

        $private_key = openssl_pkey_get_private($settings['private_key']);

        // Create signature
		openssl_sign($request_args['body'], $signature, $private_key);
		
		// Get name for header field
		$header_name = rgar( $settings, 'signature_header', 'X-Gform-Signature');
        
        // Set signature as header
        $request_args['headers'][$header_name] = base64_encode($signature);

        return $request_args;
    }

	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */

	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Signing and verification', 'gf-webhook-signature' ),
				'fields' => array(
					array(
						'label'               => esc_html__( 'Public Key', 'gf-webhook-signature' ),
						'type'                => 'textarea',
						'name'                => 'public_key',
						'tooltip'             => esc_html__( 'This is the key for verifying the signatures, intended for use by the webhook receiver.', 'gf-webhook-signature' ),
						'class'               => 'large code',
					),
					array(
						'label'               => esc_html__( 'Private Key', 'gf-webhook-signature' ),
						'type'                => 'textarea',
						'name'                => 'private_key',
						'tooltip'             => esc_html__( 'Keep secret! This is the key used by this plugin to create the signatures.', 'gf-webhook-signature' ),
						'class'               => 'large code',
						'validation_callback' => array( $this, 'is_valid_private_key' ),
					),
					array(
						'name'  => null,
						'label' => null,
						'type'  => 'generate_keys_button',
						'description' => 'Warning: this action overwrites the current public and private key',
					),
					array(
						'type'    => 'checkbox',
						'name'    => 'generate_keys',
						'hidden'  => true,
						'choices' => array(
			                array(
								'label'         => null,
			                    'name'          => 'generate_keys',
			                    'default_value' => 0,
			                ),
						),
					),
				)
			),
			array(
				'title' => esc_html__( 'Webhook payload header', 'gf-webhook-signature' ),
				'fields' => array(
					array(
						'label'       => esc_html__( 'HTTP header field name', 'gf-webhook-signature' ),
						'type'        => 'text',
						'name'        => 'signature_header',
						'tooltip'     => esc_html__( 'This custom name will be used for the HTTP request header field containg the signature.', 'gf-webhook-signature' ),
						'class'       => 'small',
						'description' => 'When no value is entered, the name will default to <code>X-Gform-Signature</code>', 
					),
				)
			)
		);
	}

	/**
	 * Create Generate Keys settings field.
	 *
	 * @return string
	 */
	public function settings_generate_keys_button( $field ) {
		// Set return HTML.
		$button = sprintf(
			'<a id="%2$s" class="button">%1$s</a>',
			esc_html__( 'Generate a new public – private key pair', 'gf-webhook-signature' ),
			'gform_webhook_signature_generate_keys'
		);

		echo $button;
	}

	/**
	 * The validation callback for the 'private_key' setting on the plugin settings page.
	 *
	 * @param array  $field The current field meta
	 * @param string $value The setting value.
	 *
	 */
	public function is_valid_private_key( $field, $value ) {
		$public_key = openssl_pkey_get_private( $value );

		if( ! $public_key ) {
			$this->set_field_error( array( 'name' => 'private_key' ), esc_html__( "The entered private key is invalid. Please enter a valid private key.", 'gf-webhook-signature' ) );
		}
	}

	/**
	 * Updates plugin settings with the provided settings
	 * and check if key pair must be generated to save as settings
	 *
	 * @param array $settings - Plugin settings to be saved
	 */
	public function update_plugin_settings( $settings ) {
		// Prevent boolean for whether or not to generate keys from being stored in DB
		unset($settings['generate_keys']);
		
		// Save settings to DB
		parent::update_plugin_settings( $settings );
	}

	/**
	 * Gets settings from $_POST variable, returning a name/value collection of setting name and setting value
	 * only if the add-on has not generated a new key pair
	 */
	public function get_posted_settings() {
		$settings = parent::get_posted_settings();

		// If key pair needs to be generated
		if( rgar( $settings, 'generate_keys' ) ) {
			// Generate new key
			$key_res = openssl_pkey_new(
				array(
					'digest_alg' => 'sha256',
					'private_key_type' => OPENSSL_KEYTYPE_RSA,
					'private_key_bits' => 1024
				)
			);

			// Extract the private key
			openssl_pkey_export($key_res, $pem_private_key);

			// Set new keys
			$settings['private_key'] = $pem_private_key;
			$settings['public_key'] = openssl_pkey_get_details($key_res)['key'];

			// Reset value to be sent to client
			$settings['generate_keys'] = 0;
		}

		return $settings;
	}
}