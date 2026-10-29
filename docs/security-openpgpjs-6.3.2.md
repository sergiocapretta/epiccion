# OpenPGP.js 6.3.2 — dependency integrity note

## Current integration

The OpenPGP.js browser bundle is loaded by `web/keys.php` and
`web/openpgp-message.php` from the version-pinned jsDelivr URL:

`https://cdn.jsdelivr.net/npm/openpgp@6.3.2/dist/openpgp.min.js`

Both script tags use Subresource Integrity (SRI), with
`crossorigin="anonymous"`. A browser should refuse to execute the script if
the bytes delivered by the CDN do not match the declared SHA-384 digest.

## Digest of the supplied artifact

The file supplied for this change was named `openpgp.min.js`, measured 395,216
bytes, and its header identifies it as OpenPGP.js v6.3.2, dated 2026-09-24.
The SHA-384 digest calculated from those supplied bytes is:

```
sha384-7tO8fWyOAxbPe09OQda/ET6jmiowqRD/y/FQQ0XhzcxOpMQFvEJHvK41zh6UZuAX
```

This digest pins the supplied file's exact contents. **It is not an independent
proof that the supplied bytes match the upstream project's release artifact**:
network access to retrieve and compare the CDN response was unavailable during
this change. Before production use, compare the downloaded file with the
official 6.3.2 package artifact through a trusted channel and update the digest
if needed.

## License and maintenance

The upstream bundle header refers to the LGPL license and the OpenPGP.js
website. OpenPGP.js 6.3.2 is distributed under LGPL-3.0-or-later; retain the
upstream license notice and include the corresponding license text if the
bundle is redistributed in the repository.

The bundle remains CDN-hosted in this change; it has **not** been vendored
locally. This preserves the current loading arrangement while adding an
integrity check. SRI does not eliminate availability dependence on the CDN, and
it does not protect against a compromised application server or malicious
first-party page code.

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
