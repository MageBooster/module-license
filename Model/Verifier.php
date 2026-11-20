<?php
/*
 * Copyright (c) 2026 WebWorks 101 OÜ (MageBooster). All rights reserved.
 * Commercial licence, see LICENSE.txt. https://magebooster.com
 * Licensed per serial number for one production installation (one store domain); staging and
 * development copies of that store are allowed. Serial numbers and downloads are tied to the customer.
 * You may modify this code for your own licensed store; keep this notice.
 * Not allowed: copying to other stores, redistributing, reselling, sublicensing, publishing it publicly,
 * removing licence checks or notices, sharing serial numbers. Unauthorised use: the serial number is
 * revoked without refund, updates and support stop, damages and legal action may follow.
 * Estonian law applies; court: Harju County Court (Harju Maakohus).
 */
declare(strict_types=1);

namespace MageBooster\License\Model;

/**
 * Offline serial verification.
 *
 * Serial format: MB1.<base64url(payload JSON)>.<base64url(Ed25519 signature of "MB1." . payload part)>
 * Payload: {"p": product code, "d": [domains, "*.example.com" allowed], "e": "YYYY-MM-DD" expiry, "i": issued date}
 * Only the public key ships with the module; serials are issued with the private key, which never leaves MageBooster.
 */
class Verifier
{
    /** Public key (base64) of the MageBooster serial signing key. Replace with the production key before release. */
    public const PUBLIC_KEY = 'iUPaypR8QVrKmRcutDEhN6hdURJFzzOBTHg7qT1dxCg=';
    public const GRACE_DAYS = 14;

    /**
     * @param string[] $hosts store hosts the serial must cover
     */
    public function verify(string $serial, string $product, array $hosts, ?\DateTimeImmutable $now = null): Status
    {
        $serial = trim($serial);
        if ($serial === '') {
            return new Status(Status::MISSING, (string)__('Enter your serial number.'));
        }
        $parts = explode('.', $serial);
        if (count($parts) !== 3 || $parts[0] !== 'MB1') {
            return new Status(Status::INVALID, (string)__('The serial number is not valid.'));
        }
        $signature = $this->b64urlDecode($parts[2]);
        $publicKey = base64_decode(self::PUBLIC_KEY, true);
        if ($signature === null || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES || $publicKey === false
            || !sodium_crypto_sign_verify_detached($signature, 'MB1.' . $parts[1], $publicKey)) {
            return new Status(Status::INVALID, (string)__('The serial number is not valid.'));
        }
        $json = $this->b64urlDecode($parts[1]);
        $payload = $json === null ? null : json_decode($json, true);
        if (!is_array($payload) || !isset($payload['p'], $payload['d'], $payload['e']) || !is_array($payload['d'])) {
            return new Status(Status::INVALID, (string)__('The serial number is not valid.'));
        }
        if ($payload['p'] !== $product) {
            return new Status(Status::WRONG_PRODUCT, (string)__('This serial number is for another MageBooster product.'));
        }
        foreach ($hosts as $host) {
            if (!$this->hostAllowed($host, $payload['d'])) {
                return new Status(
                    Status::WRONG_DOMAIN,
                    (string)__('This serial number does not cover the domain %1.', $host)
                );
            }
        }
        $now = $now ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $expires = \DateTimeImmutable::createFromFormat('!Y-m-d', (string)$payload['e'], new \DateTimeZone('UTC'));
        if ($expires === false) {
            return new Status(Status::INVALID, (string)__('The serial number is not valid.'));
        }
        $endOfDay = $expires->modify('+1 day');
        if ($now < $endOfDay) {
            return new Status(Status::VALID, (string)__('Valid until %1.', $payload['e']), (string)$payload['e']);
        }
        if ($now < $endOfDay->modify('+' . self::GRACE_DAYS . ' days')) {
            return new Status(
                Status::GRACE,
                (string)__('The serial number expired on %1. Renew it within %2 days to keep this feature.', $payload['e'], self::GRACE_DAYS),
                (string)$payload['e']
            );
        }
        return new Status(Status::EXPIRED, (string)__('The serial number expired on %1.', $payload['e']), (string)$payload['e']);
    }

    /**
     * @param string[] $domains
     */
    private function hostAllowed(string $host, array $domains): bool
    {
        $host = strtolower(rtrim($host, '.'));
        if ($host === 'localhost' || $host === '127.0.0.1' || preg_match('/\.(test|localhost|local)$/', $host)) {
            return true; // development hosts
        }
        foreach ($domains as $domain) {
            $domain = strtolower(trim((string)$domain));
            if ($domain === $host || 'www.' . $domain === $host) {
                return true;
            }
            if (str_starts_with($domain, '*.') && str_ends_with($host, substr($domain, 1))) {
                return true;
            }
        }
        return false;
    }

    private function b64urlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
        return $decoded === false ? null : $decoded;
    }
}
