# MageBooster_License

Shared serial-number check for paid MageBooster modules (e.g. Currency Rates, Checkout Pro). Commercial licence, see `LICENSE.txt`.
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

## Licence & copying

Commercial licence, see `LICENSE.txt`. Copyright (c) 2026 WebWorks 101 OÜ (MageBooster). All rights reserved.

- One serial number = one production installation (one store domain). Staging and development copies of that same store are allowed.
- Each serial number and each download is tied to the customer who bought it. Luma and Hyvä editions are separate products with separate serial numbers.
- You may modify the code for your own licensed store. Keep the copyright and licence notices.
- Not allowed: copying to other stores, redistributing, reselling, sublicensing, publishing in public repositories, marketplaces or forums, removing licence checks or these notices, sharing serial numbers.
- In case of unauthorised copying or use, the serial number is revoked without refund, updates and support stop, and WebWorks 101 OÜ may claim damages and take legal action. Estonian law applies; the court is Harju County Court.

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

## Lisans ve kopyalama

Ticari lisans, bkz. `LICENSE.txt`. Telif hakkı (c) 2026 WebWorks 101 OÜ (MageBooster). Tüm hakları saklıdır.

- Bir seri numarası = bir canlı kurulum (bir mağaza alan adı). Aynı mağazanın test (staging) ve geliştirme kopyaları serbesttir.
- Her seri numarası ve her indirme, satın alan müşteriye bağlıdır. Luma ve Hyvä sürümleri ayrı ürünlerdir, seri numaraları da ayrıdır.
- Kodu kendi lisanslı mağazanız için değiştirebilirsiniz. Telif ve lisans notlarını silmeyin.
- Yasak olanlar: başka mağazalara kopyalamak, dağıtmak, yeniden satmak, alt lisans vermek, herkese açık depolarda, pazar yerlerinde veya forumlarda yayınlamak, lisans kontrolünü ya da bu notları kaldırmak, seri numarasını paylaşmak.
- İzinsiz kopyalama veya kullanımda seri numarası iade yapılmadan iptal edilir, güncelleme ve destek durur; WebWorks 101 OÜ tazminat isteyebilir ve yasal yola gidebilir. Estonya hukuku geçerlidir; yetkili mahkeme Harju İlçe Mahkemesi'dir (Harju Maakohus).
