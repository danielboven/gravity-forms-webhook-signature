window.GFWebhookSignatureSettings = null;

( function( $ ) {

	GFWebhookSignatureSettings = function () {

		this.init = function() {
			this.bindGenerateKeys();
		}

		this.bindGenerateKeys = function() {

			$( '#gform_webhook_signature_generate_keys' ).on( 'click', function( e ) {

				// Prevent default event.
				e.preventDefault();

				if (confirm('Are you sure you want to overwrite the current keys?')) {

					// Set signature keys value.
					$( 'input#generate_keys' ).click();

					// Submit form.
					$( '#gform-settings-save' ).click();

				}

			} );

		}

		this.init();

	}

	$( document ).ready( GFWebhookSignatureSettings );

} )( jQuery );
