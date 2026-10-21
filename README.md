# MageBooster_License

Shared serial-number check for paid MageBooster modules (e.g. Currency Rates, Checkout Pro). MIT.
Türkçe açıklama aşağıda.

## Where to enter the serial

Stores › Configuration › MageBooster › **Licenses**: one "Serial number" field per product.
While a serial is missing, invalid, expired or for another domain, an admin notice says so and links here.

## How the check works

- **Offline.** No call home: the serial is a signed text, checked with the MageBooster public key in this module
  (Ed25519, PHP `sodium`).
- **Format:** `MB1.<payload>.<signature>` (base64url). The payload holds the product code, the licensed domain(s)
  and the expiry date. The signature covers `MB1.<payload>`, so changing any part breaks it.
- **Domain:** every store base URL (secure and unsecure) must match a licensed domain. `www.` is allowed automatically,
  `*.example.com` covers subdomains. Development hosts (`localhost`, `127.0.0.1`, `*.test`, `*.localhost`, `*.local`)
  always pass, so staging copies on those names need no extra serial.
- **Expiry and grace:** after the expiry date the module keeps working for 14 more days with a warning, then stops
  (status "expired").
- Only the public key ships with the module. The private signing key is never in any repository.

## For module developers

```php
public function __construct(private \MageBooster\License\Api\LicenseInterface $license) {}
if (!$this->license->isValid('currency-rates')) { /* paid feature off */ }
```

Register the product in your module's `etc/di.xml` (type `MageBooster\License\Model\Products`, argument `products`)
so its serial field and notices appear.

---

# MageBooster_License (TR)

Ücretli MageBooster modülleri (ör. Currency Rates, Checkout Pro) için ortak seri numarası kontrolü.

- Seri numarası: Mağazalar › Yapılandırma › MageBooster › **Licenses** — her ürün için bir alan.
- Kontrol **çevrimdışıdır**: seri numarası imzalı bir metindir, modüldeki MageBooster açık anahtarıyla (Ed25519) doğrulanır.
- İçerik: ürün kodu, lisanslı alan adı/adları, bitiş tarihi. Herhangi bir parça değişirse imza bozulur.
- Mağazanın tüm temel adresleri lisanslı alan adıyla eşleşmelidir (`www.` otomatik, `*.alanadi.com` alt alan adlarını kapsar).
  Geliştirme adresleri (`localhost`, `*.test`, `*.local`, `*.localhost`) her zaman geçer.
- Süre dolduktan sonra 14 gün uyarıyla çalışmaya devam eder, sonra durur.
- Modülde yalnızca açık anahtar vardır; imzalama (özel) anahtarı hiçbir depoda bulunmaz.
