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


## Manual update check in the application

The key-management and message pages display the installed OpenPGP.js version and
check the npm registry for the latest stable package version. The check is
informational only: it does not download or execute a new library version.
It requires a network connection and the browser to permit the request to
`registry.npmjs.org`; if the check fails, use the official release page:
https://github.com/openpgpjs/openpgpjs/releases

### Updating the pinned dependency

1. Review the official OpenPGP.js release notes and assess compatibility before
   changing the pinned version.
2. Download the exact distribution file for the chosen version from the
   version-pinned jsDelivr URL, for example:
   `https://cdn.jsdelivr.net/npm/openpgp@VERSION/dist/openpgp.min.js`
3. Calculate SHA-384 from the downloaded bytes, without altering or reformatting
   the file. On Fedora/Linux, replace `VERSION` with the exact version:
   ```sh
   set -o pipefail
   printf 'sha384-'
   curl -fsSL 'https://cdn.jsdelivr.net/npm/openpgp@VERSION/dist/openpgp.min.js' \
     | openssl dgst -sha384 -binary \
     | openssl base64 -A
   printf '\\n'
   ```
4. Verify the resulting digest against the exact downloaded file and update both
   `web/keys.php` and `web/openpgp-message.php`: the URL version and
   `integrity="sha384-…"` must match. Do not remove `crossorigin="anonymous"`.
5. Update `INSTALLED_VERSION` in `web/openpgp-update-check.js` and this
   document to the version actually selected and verified.
6. Run the manual regression checklist above, including key generation,
   private/public key import, encryption and decryption, and legacy password
   mode. Check the browser console for failed CDN loads or SRI errors.
7. Deploy to the test site first. Do not update production or merge the branch
   until the tests pass.

Never copy an SRI digest from a different version or from a different file
artifact. The version number and SRI digest must refer to the exact bytes
served by the pinned URL.
