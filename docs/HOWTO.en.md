# MageBooster License: how-to

Package: `magebooster/module-license`
Licence: paid (serial number required)
Support: info@magebooster.com

## What it does

License checks the serial numbers of paid MageBooster products.
It runs inside your store and works offline: it makes no call to any MageBooster server.

- Each serial is signed with Ed25519. The module verifies that signature with a public key built into it.
- The module reads the product, the licensed domains, the expiry date and the issue date from the serial.
- Every store base URL (every store view) must match a licensed domain.
  - A `www.` variant of a licensed domain is accepted automatically.
  - A wildcard such as `*.example.com` covers subdomains.
- Development hosts always pass the domain check: `localhost`, `127.0.0.1`, `*.test`, `*.localhost` and `*.local`.
- After the expiry date, there is a 14-day grace period. During it the product keeps working and a warning is shown.
- When a serial has a problem, an admin notice appears with a link to the Licenses page.

Other paid modules (for example CurrencyRates) depend on this module.

## Requirements

- PHP 8.0 or later, with the `sodium` extension
- Magento 2.4.4 to 2.4.9
- Luma edition: `magebooster/module-license`
- Hyvä edition: `magebooster/module-license-hyva`
- The two editions are separate downloads.

## Install with Composer

```bash
composer require magebooster/module-license
```

For a Hyvä store, install `magebooster/module-license-hyva` instead.

## Install from a zip file

1. Unzip the archive into `app/code/MageBooster/License` so that `registration.php` sits directly in that folder.
2. Run the enabling commands below.

## Enable the module

```bash
bin/magento module:enable MageBooster_License
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

The `setup:di:compile` and `setup:static-content:deploy` steps are only needed in production mode.

## Admin configuration

Go to **Stores > Configuration > MageBooster > Licenses**, group **Serial numbers**.
To open this page, an admin user needs the ACL resource `MageBooster_License::config`.

| Field | Config path | What it means |
|---|---|---|
| Currency Rates (TCMB) | `magebooster_license/serials/currency_rates` | Serial for the CurrencyRates module. It starts with `MB1`. |
| MageBooster Checkout Pro | `magebooster_license/serials/checkout_pro` | Serial for the Checkout module. It starts with `MB1`. |

Under each field you will see the current status of that serial:

| Status | Message | Does the product work? |
|---|---|---|
| valid | Valid until <date> | Yes |
| grace | Expired, still in the grace period, with a warning | Yes, for 14 days |
| missing | Enter your serial number. | No |
| invalid | The serial number is not valid. | No |
| expired | Expired, and the grace period has ended | No |
| wrong_product | This serial number is for another MageBooster product. | No |
| wrong_domain | The serial number does not cover the domain <host> | No |

## Entering a serial

1. Copy the serial from your MageBooster order email.
2. Paste it into the matching field. The serial looks like `MB1.xxxxx.yyyyy`.
3. Click **Save Config**.
4. Check the status line under the field.

## For developers

To check a product's licence from your own code, inject `MageBooster\License\Api\LicenseInterface` and call one of its methods:

```php
$this->license->isValid('currency-rates'); // true for valid or grace
$this->license->getStatus('currency-rates'); // status object
```

To register a new product code, add an item to the `products` argument of `MageBooster\License\Model\Products` in your module's `di.xml`.

The serial format is `MB1.<base64url payload>.<base64url signature>`. The payload is JSON with these keys:

- `p`: the product code
- `d`: the licensed domains
- `e`: the expiry date (Y-m-d)
- `i`: the issue date

## Testing

1. Leave a serial field empty and save. The status should read "Enter your serial number."
2. Paste a valid serial and save. The status should read "Valid until ...".
3. On a development host such as `shop.test`, the domain check is skipped. To test the domain check, use a real host.

## Troubleshooting

- **"The serial number is not valid."** Make sure the serial was copied in full, with no spaces or line breaks.
- **"does not cover the domain ..."** One of your store views uses a base URL that the serial does not cover. Check every store view's base URL. A serial can be reissued for the correct domains.
- **"This serial number is for another MageBooster product."** The serial was pasted into the wrong field.
- **Error about `sodium`.** Enable the PHP `sodium` extension.

## Uninstall

First remove any paid modules that depend on License. Then run:

```bash
bin/magento module:disable MageBooster_License
composer remove magebooster/module-license
bin/magento setup:upgrade
bin/magento cache:flush
```

If you installed from a zip file, delete `app/code/MageBooster/License` instead of running `composer remove`.

Saved serials stay in `core_config_data` under `magebooster_license/serials/*`. Delete those rows if you no longer need them.

## Licence & copying

Commercial licence, see `LICENSE.txt`. Copyright (c) 2026 WebWorks 101 OÜ (MageBooster). All rights reserved.

- One serial number = one production installation (one store domain). Staging and development copies of that same store are allowed.
- Each serial number and each download is tied to the customer who bought it. Luma and Hyvä editions are separate products with separate serial numbers.
- You may modify the code for your own licensed store. Keep the copyright and licence notices.
- Not allowed: copying to other stores, redistributing, reselling, sublicensing, publishing in public repositories, marketplaces or forums, removing licence checks or these notices, sharing serial numbers.
- In case of unauthorised copying or use, the serial number is revoked without refund, updates and support stop, and WebWorks 101 OÜ may claim damages and take legal action. Estonian law applies; the court is Harju County Court.

## Support

info@magebooster.com
