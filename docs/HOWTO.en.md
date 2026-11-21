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
- Without a valid serial, a module keeps working for a 30-day grace period. After that it stops its paid function, without breaking the store.
- While a module has no valid serial, every admin page shows a notice that cannot be dismissed, with links to the Licenses page and to your serial numbers. Shoppers never see it.

All paid MageBooster modules depend on this module.

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

Go to **Stores > Configuration > MageBooster > Licenses**. To open this page, an admin user needs the ACL resource `MageBooster_License::config`.

**License status** lists every installed paid MageBooster module:

| Status | Meaning |
|---|---|
| Active | A valid serial covers the module. |
| Grace period | No valid serial yet. The module keeps working; the details column shows the reason and the days left. |
| Switched off | The 30-day grace period has ended. The module has stopped its paid function; the store keeps working. |

**Serial numbers** has one field per installed module, plus one for its Hyvä edition (for example **Moka United payments** and **Moka United payments (Hyvä edition)**). Fill in the one you bought. Config path: `magebooster_license/serials/<code>`, for example `magebooster_license/serials/moka` and `magebooster_license/serials/moka_hyva`.

The reason shown for a module without a valid serial is one of:

| Reason | Message |
|---|---|
| missing | No serial number has been entered. |
| invalid | The serial number is not valid. |
| expired | The serial number expired on <date>. |
| wrong_product | This serial number is for another MageBooster product. |
| wrong_domain | This serial number does not cover the domain <host>. |

### Grace period and admin notice

- Without a valid serial, a module keeps working for **30 days**. The 30 days start the first time License sees the module without a valid serial, and are stored per module.
- The grace period is never restarted by itself. A valid serial ends it; if that serial later stops being valid (for example it expires), a new 30-day period starts.
- During and after the grace period, every admin page shows a system message that cannot be dismissed. It lists each affected module with the reason and "keeps working for X more day(s)" or "switched off since <date>", and links to the Licenses page and to https://store.magebooster.com/licences/account/.
- Shoppers never see a licence message.
- What "switched off" means is described in each module's guide. It never breaks the storefront: payment methods are hidden, MageBooster Checkout falls back to the standard checkout, and so on.

On the command line, `bin/magento magebooster:license:status` prints the same status table.

## Entering a serial

1. Copy the serial from your MageBooster order email.
2. Paste it into the field of that module (or its Hyvä edition). The serial looks like `MB1.xxxxx.yyyyy`.
3. Click **Save Config**.
4. Check **License status**: the module should show **Active**.

## For developers

To check a module's licence from your own code, inject `MageBooster\License\Api\LicenseInterface`:

```php
$this->license->isActive('moka');        // true when licensed or still in the grace period
$state = $this->license->getModuleState('moka');
$state->isValid();      // a valid serial covers it
$state->isInGrace();    // no valid serial, grace period running
$state->getDaysLeft();  // days left in the grace period
$state->getStatus();    // the serial check result (code and message)
```

`isValid()` on the interface is kept for older modules and now returns the same as `isActive()`.

To register a module, add an item to the `products` argument of `MageBooster\License\Model\Products` in your module's `di.xml`. The item key is the module code; `codes` lists the product codes whose serials are accepted (the Hyvä edition code is accepted by the base module):

```xml
<item name="moka" xsi:type="array">
    <item name="label" xsi:type="string">Moka United payments</item>
    <item name="codes" xsi:type="array">
        <item name="moka" xsi:type="string">moka</item>
        <item name="moka-hyva" xsi:type="string">moka-hyva</item>
    </item>
</item>
```

The grace start dates are stored in the `flag` table, flag code `magebooster_license_grace` (JSON, module code => start time in UTC).

The serial format is `MB1.<base64url payload>.<base64url signature>`. The payload is JSON with these keys:

- `p`: the product code
- `d`: the licensed domains
- `e`: the expiry date (Y-m-d)
- `i`: the issue date

## Testing

1. Leave a serial field empty and save. **License status** should show **Grace period** with "No serial number has been entered." and 30 days left, and the admin notice should appear.
2. Paste a valid serial and save. The status should change to **Active** and the module should disappear from the admin notice.
3. Run `bin/magento magebooster:license:status` to see the same result on the command line.
4. On a development host such as `shop.test`, the domain check is skipped. To test the domain check, use a real host.

## Troubleshooting

- **"The serial number is not valid."** Make sure the serial was copied in full, with no spaces or line breaks.
- **"does not cover the domain ..."** One of your store views uses a base URL that the serial does not cover. Check every store view's base URL. A serial can be reissued for the correct domains.
- **"This serial number is for another MageBooster product."** The serial was pasted into the wrong field, or the serial was bought for another module.
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
