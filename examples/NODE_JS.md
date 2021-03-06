# Signature verification example Node.js

To verify a signature generated by `gravity-forms-webhook-signature` on a webhook request using *Node.js*  (with *Express*, for example), you can use the code below. Note that the native *Node.js* cryptography module called `crypto` is required.

## Code

```javascript
import crypto from "crypto"

function validateSignatureWebhook(req) {
    // the public key as found in the WP add-on settings
    // formatted in a single line without BEGIN and END lines
    const publicKey = '...'

    // convert postal public key to PEM (X.509) format
    const publicKeyPem =  '-----BEGIN PUBLIC KEY-----\r\n'+
        chunk_split(publicKey, 64, '\r\n')+
        '-----END PUBLIC KEY-----'
        
    const signature = req.headers['x-gform-signature']

    const verifier = crypto.createVerify('SHA1')
    verifier.update(json_encode(req.body))

    // return verify result (true or false)
    return verifier.verify(publicKeyPem, signature, 'base64')
}
```

## Notes

1. `chunk_split()` is not a native JS function. I have used the following code from [this](https://locutus.io/php/strings/chunk_split/) post:

```javascript
function chunk_split (body, chunklen, end) { // eslint-disable-line camelcase
    //  discuss at: https://locutus.io/php/chunk_split/
    // original by: Paulo Freitas
    //    input by: Brett Zamir (https://brett-zamir.me)
    // bugfixed by: Kevin van Zonneveld (https://kvz.io)
    // improved by: Theriault (https://github.com/Theriault)
    //   example 1: chunk_split('Hello world!', 1, '*')
    //   returns 1: 'H*e*l*l*o* *w*o*r*l*d*!*'
    //   example 2: chunk_split('Hello world!', 10, '*')
    //   returns 2: 'Hello worl*d!*'
  
    chunklen = parseInt(chunklen, 10) || 76
    end = end || '\r\n'
  
    if (chunklen < 1) {
      return false
    }
  
    return body.match(new RegExp('.{0,' + chunklen + '}', 'g'))
      .join(end)
}
```

An alternative is to create a multiline string containg the public key. In that case the `chunk_split()` function is not necessary.

2. Moreover, `json_encode()` is a function which converts the object into a string with the same character encoding as the PHP function [`json_encode`](https://www.php.net/manual/en/function.json-encode.php) does. Visit [this page](https://stackoverflow.com/a/56647087/7346359) for more information. Using the following function takes care of that:
```javascript
function json_encode(s) {
  //         goal: use a regex to simulate PHP json_encode
  // regex source: https://github.com/titarenko/json_encode/blob/master/index.js
  //  inspired by: https://gist.github.com/composite/8396541
  return JSON.stringify(s)
    .replace(/[\u0080-\uFFFF]/g,
        c => '\\u'+('0000'+c.charCodeAt(0).toString(16)).slice(-4)
    )
    .replace(/[\/]/g, () => '\\\/')
}
```
3. Calling the function `validateSignatureWebhook()` with an *Express* request object will work. In case you're using a different framework/setup than *Express*, you might have to adjust `req.headers['x-gform-signature']` and `req.body`.
4. I recommend putting the constant `publicKey` in a `.env` file. If you do, you could replace it with this: `const { PUBLIC_KEY } = process.env`.