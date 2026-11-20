# MageBooster License kullanım kılavuzu

**Paket:** `magebooster/module-license`
**Lisans:** ücretli, seri numarası gerekir
**Destek:** info@magebooster.com

## Ne işe yarar

License, ücretli MageBooster ürünlerinin seri numaralarını kontrol eder. Kontrol mağazanın içinde, internete çıkmadan yapılır; MageBooster sunucusuna istek gitmez.

- **İmza kontrolü:** Her seri numarası Ed25519 ile imzalıdır. Modül bu imzayı kendi içindeki açık anahtarla doğrular.
- **Seriden okunanlar:** ürün, lisanslı alan adları, bitiş tarihi ve düzenlenme tarihi.
- **Alan adı kontrolü:** Mağazadaki her mağaza görünümünün base URL'si lisanslı bir alan adıyla eşleşmelidir.
  - Lisanslı alan adının `www.` ile başlayan hali kendiliğinden kabul edilir.
  - `*.ornek.com` gibi bir yazım tüm alt alan adlarını kapsar.
- **Geliştirme ortamları:** `localhost`, `127.0.0.1`, `*.test`, `*.localhost` ve `*.local` adresleri alan adı kontrolüne takılmaz.
- **Ek süre:** Bitiş tarihinden sonra 14 günlük ek süre vardır. Bu sürede ürün çalışmaya devam eder, yalnızca uyarı gösterilir.
- **Uyarı:** Bir seri numarasında sorun varsa yönetim panelinde bir bildirim çıkar. Bildirim sizi Lisanslar sayfasına götürür.

CurrencyRates gibi ücretli modüller bu modüle bağlıdır.

## Gereksinimler

- PHP 8.0 veya üzeri, `sodium` eklentisi açık olmalı
- Magento 2.4.4 – 2.4.9
- **Luma sürümü:** `magebooster/module-license`
- **Hyvä sürümü:** `magebooster/module-license-hyva`

İki sürüm ayrı ayrı indirilir.

## Composer ile kurulum

```bash
composer require magebooster/module-license
```

Hyvä temalı mağazada bunun yerine `magebooster/module-license-hyva` paketini kurun.

## Zip dosyasıyla kurulum

1. Arşivi `app/code/MageBooster/License` klasörüne açın. `registration.php` dosyası doğrudan bu klasörde olmalı.
2. Aşağıdaki etkinleştirme komutlarını çalıştırın.

## Modülü etkinleştirme

```bash
bin/magento module:enable MageBooster_License
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

`setup:di:compile` ve `setup:static-content:deploy` adımları yalnızca production modunda gerekir.

## Yönetim paneli ayarları

Ayarlar **Stores > Configuration > MageBooster > Licenses** sayfasında, **Serial numbers** grubundadır. Sayfayı açabilmek için yönetici kullanıcının `MageBooster_License::config` yetkisi olmalıdır.

| Alan | Ayar yolu | Açıklama |
|---|---|---|
| Currency Rates (TCMB) | `magebooster_license/serials/currency_rates` | CurrencyRates modülünün seri numarası. `MB1` ile başlar. |
| MageBooster Checkout Pro | `magebooster_license/serials/checkout_pro` | Checkout modülünün seri numarası. `MB1` ile başlar. |

Her alanın altında o seri numarasının güncel durumu yazar. Ekrandaki mesajlar İngilizcedir:

| Durum | Ekrandaki mesaj | Ürün çalışır mı? |
|---|---|---|
| valid | Valid until <tarih> | Evet |
| grace | Süresi doldu, ek süre içinde (uyarıyla) | Evet, 14 gün |
| missing | Enter your serial number. | Hayır |
| invalid | The serial number is not valid. | Hayır |
| expired | Süresi doldu, ek süre de bitti | Hayır |
| wrong_product | This serial number is for another MageBooster product. | Hayır |
| wrong_domain | The serial number does not cover the domain <alan adı> | Hayır |

## Seri numarası girme

1. Seri numarasını MageBooster sipariş e-postanızdan kopyalayın. Biçimi `MB1.xxxxx.yyyyy` şeklindedir.
2. Numarayı ilgili alana yapıştırın.
3. **Save Config** düğmesine tıklayın.
4. Alanın altındaki durum satırını kontrol edin.

## Geliştiriciler için

Kendi kodunuzdan lisans durumunu sormak için `MageBooster\License\Api\LicenseInterface` arayüzünü enjekte edin:

```php
$this->license->isValid('currency-rates'); // valid veya grace ise true
$this->license->getStatus('currency-rates'); // durum nesnesi
```

Yeni bir ürün kodu tanımlamak için kendi modülünüzün `di.xml` dosyasında `MageBooster\License\Model\Products` sınıfının `products` argümanına bir kayıt ekleyin.

Seri numarasının yapısı `MB1.<base64url veri>.<base64url imza>` şeklindedir. Veri kısmı bir JSON nesnesidir:

- `p`: ürün kodu
- `d`: alan adları
- `e`: bitiş tarihi (Y-m-d)
- `i`: düzenlenme tarihi

## Test

1. Seri alanını boş bırakıp kaydedin. Durum satırında "Enter your serial number." yazmalı.
2. Geçerli bir seri numarası girip kaydedin. Durum satırında "Valid until ..." yazmalı.
3. `magaza.test` gibi geliştirme adreslerinde alan adı kontrolü atlanır. Alan adı kontrolünü denemek için gerçek bir adres kullanın.

## Sorun giderme

- **"The serial number is not valid."** Numaranın tamamını kopyaladığınızdan emin olun. Başında veya sonunda boşluk, arada satır sonu olmamalı.
- **"does not cover the domain ..."** Mağaza görünümlerinden birinin base URL'si lisansın kapsamadığı bir alan adında. Her mağaza görünümünün adresini kontrol edin. Gerekirse doğru alan adları için yeni seri numarası alabilirsiniz.
- **"This serial number is for another MageBooster product."** Numara yanlış alana yapıştırılmış.
- **`sodium` hatası:** PHP `sodium` eklentisini açın.

## Kaldırma

Önce License modülüne bağlı ücretli modülleri kaldırın. Ardından şu komutları çalıştırın:

```bash
bin/magento module:disable MageBooster_License
composer remove magebooster/module-license
bin/magento setup:upgrade
bin/magento cache:flush
```

- **Zip ile kurduysanız:** `composer remove` yerine `app/code/MageBooster/License` klasörünü silin.
- **Kayıtlı seri numaraları:** `core_config_data` tablosunda `magebooster_license/serials/*` yolunda kalır. Artık gerekmiyorsa bu satırları silebilirsiniz.

## Lisans ve kopyalama

Ticari lisans, bkz. `LICENSE.txt`. Telif hakkı (c) 2026 WebWorks 101 OÜ (MageBooster). Tüm hakları saklıdır.

- Bir seri numarası = bir canlı kurulum (bir mağaza alan adı). Aynı mağazanın test (staging) ve geliştirme kopyaları serbesttir.
- Her seri numarası ve her indirme, satın alan müşteriye bağlıdır. Luma ve Hyvä sürümleri ayrı ürünlerdir, seri numaraları da ayrıdır.
- Kodu kendi lisanslı mağazanız için değiştirebilirsiniz. Telif ve lisans notlarını silmeyin.
- Yasak olanlar: başka mağazalara kopyalamak, dağıtmak, yeniden satmak, alt lisans vermek, herkese açık depolarda, pazar yerlerinde veya forumlarda yayınlamak, lisans kontrolünü ya da bu notları kaldırmak, seri numarasını paylaşmak.
- İzinsiz kopyalama veya kullanımda seri numarası iade yapılmadan iptal edilir, güncelleme ve destek durur; WebWorks 101 OÜ tazminat isteyebilir ve yasal yola gidebilir. Estonya hukuku geçerlidir; yetkili mahkeme Harju İlçe Mahkemesi'dir (Harju Maakohus).

## Destek

info@magebooster.com
