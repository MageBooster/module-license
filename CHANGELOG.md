# Changelog

All notable changes to this package are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the package uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.2.0] - 2026-11-21

### Added
- Perpetual licences for one-time purchases: serials with expiry `9999-12-31` never stop the module.
- Optional `u` field in the serial: end date of included updates and support. The status message shows it
  ("Updates and support until ..."); after that date the module keeps working and the customer is asked to renew.
- `Status::isPerpetual()` and `Status::getUpdatesUntil()`.

## [1.1.0] - 2026-11-21

### Added
- 30-day grace period per module: it starts the first time a module is seen without a valid serial number (missing, invalid, expired, other domain or other product), is stored per module in the `flag` table (`magebooster_license_grace`) and is cleared by a valid serial number.
- `LicenseInterface::isActive()` and `getModuleState()` (state, reason, grace start/end, days left); the module's own code and its `-hyva` code are both accepted (`Products` takes `label` + `codes`).
- A critical admin system message on every admin page listing each affected module with the reason and "X days left" or "switched off since <date>", with links to the Licenses configuration and to https://store.magebooster.com/licences/account/. It cannot be dismissed and disappears once all serial numbers are valid.
- License status overview at the top of Stores > Configuration > MageBooster > Licenses.
- `bin/magento magebooster:license:status`.

### Changed
- Development hosts (localhost, *.localhost, *.test, *.local, 127.0.0.1) always pass the domain check.
- The separate 14-day expiry grace was removed; expired serial numbers use the same 30-day grace period.
- `isValid()` is kept for compatibility and now means `isActive()`.

## [1.0.1] - 2026-11-20

### Changed
- Production signing key: serials issued by store.magebooster.com now verify. Development serials are no longer accepted.

## [1.0.0] - 2026-11-20

First public release.

### Changed
- PHP 8.0 compatibility: readonly properties removed, `php: >=8.0` (was `^8.3`).
- Commercial MageBooster licence (WebWorks 101 OÜ) replaces the previous licence; `LICENSE.txt`, composer `license: proprietary`, copyright and licence notice in every source file.
- Admin notice text is escaped with `ENT_QUOTES | ENT_SUBSTITUTE`; the notice id uses sha256 instead of md5.

### Added
- Composer metadata for the public repository (homepage, authors, support, keywords), README header, guides in `docs/`, `.gitignore`.

## [0.1.0] - 2026-10-21

### Added
- Initial MageBooster_License module: offline Ed25519 serial-number checks and status in the admin.
