# Changelog

All notable changes to this package are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the package uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

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
