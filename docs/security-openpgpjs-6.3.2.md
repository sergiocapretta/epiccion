# OpenPGP.js 6.3.2 — dependency integrity note

## Current integration

The OpenPGP.js browser bundle is loaded by `web/keys.php` and
`web/openpgp-message.php` from the version-pinned jsDelivr URL:

`https://cdn.jsdelivr.net/npm/openpgp@6.3.2/dist/openpgp.min.js`

Both script tags use Subresource Integrity (SRI), with
`crossorigin="anonymous"`. A browser should refuse to execute the script if
the bytes delivered by the CDN do not match the declared SHA-384 digest.

## Digest verification

The digest calculated locally from the supplied `openpgp.min.js` artifact did
not match the digest calculated on the user's Fedora computer from the file
downloaded directly from jsDelivr. The verified digest for the jsDelivr
response at the time of the test is:

```
sha384-Q6tBjZrAPJTIxj5F9KxCni/T9dTGDsXfi4U7aBZOl+3zPHpzU6gKcyKzAhs8Xeoa
```

This digest was calculated from the response downloaded from the pinned
jsDelivr URL. It should be rechecked if the dependency version or CDN URL
changes. The mismatch indicates that the supplied local artifact was not
byte-for-byte identical to the bytes served by jsDelivr; the reason for that
difference has not yet been established.

## License and maintenance

The upstream bundle header refers to the LGPL license and the OpenPGP.js
website. OpenPGP.js 6.3.2 is distributed under LGPL-3.0-or-later; retain the
upstream license notice and include the corresponding license text if the
bundle is redistributed in the repository.

The bundle remains CDN-hosted; it has **not** been vendored locally. SRI checks
the downloaded file against the declared digest, but does not eliminate
availability dependence on the CDN or protect against a compromised
application server or malicious first-party page code.

## Manual regression checklist

- Open the key-management page and confirm the library-ready status.
- Generate a key pair and export both keys.
- Import a private key file and verify the passphrase flow.
- Import a public key file and confirm the contact appears in the address book.
- Encrypt a message to that contact and copy/download the emoji payload.
- Decrypt the payload with the matching private key and passphrase.
- Confirm the legacy password-based mode remains accessible.
- In browser developer tools, check that the CDN script loads successfully and
  does not report an SRI integrity error.
