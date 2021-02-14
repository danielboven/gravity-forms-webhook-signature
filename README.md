# Gravity Forms Webhook Signature add-on

## Description

This plugin can sign the webhook events sent by the Gravity Forms WebHooks Add-On to your endpoints by including a signature in each event’s `X-Gform-Signature` header. This allows you to verify that the events were sent by the Gravity Forms add-on, not by a third party. As of right now, you must verify the signatures by manually using your own solution. However, an example of a Node.js (JavaScript) implementation is linked below.

Before you can verify signatures, you need to retrieve your endpoint’s public key (more information at [this question](#how-can-i-verify-the-signature)).

This plugin uses the same keys for every form and endpoint, meaning that the same keys will be used for every signature generated.

## Verification implementations

- **Node.js:** See the example on [Github](https://github.com/danielboven/gravity-forms-webhook-signature/blob/main/examples/NODE_JS.md).

## Installation

1. Upload this plugin to your WordPress website
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the *Settings* section 
4. Click on the button *Generate a new public – private key pair*, or paste your own in the fields above and save

## Frequently Asked Questions

### How can I verify the signature?

Navigate to the plugin settings. Copy the key in the *Public Key* field to your own application (receiver). Use it to verify the signature.

### How can I send feedback or get help with a bug?

I'd love to hear your bug reports, feature suggestions and any other feedback! Please head over to [the GitHub issues page](https://github.com/danielboven/gravity-forms-webhook-signature/issues) to search for existing issues or open a new one. While I'll try to triage issues reported here on the plugin forum, you'll get a faster response (and reduce duplication of effort) by keeping everything centralized in the GitHub repository.

### What format can I use for custom keys?

Keys that are generated by the plugin have the following format:
- **Digest algorithm:** SHA256
- **Private key type:** RSA (OPENSSL_KEYTYPE_RSA)
- **Private key bits:** 1024

It is therefore advised that in case you use custom keys, you use a similar format, since no other format than the one above has been tested.

You also have to include the **BEGIN** and **END** lines, for example:

**Public Key:**
```
-----BEGIN PUBLIC KEY-----
XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
XXXXXXXXXXXXXXXXXXXXXXXX
-----END PUBLIC KEY-----
```