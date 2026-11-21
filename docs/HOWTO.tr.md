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
- **Ek süre:** Geçerli seri numarası yoksa modül 30 gün çalışmaya devam eder. Sonra ücretli işlevi durur, mağaza bozulmaz.
- **Uyarı:** Geçerli seri numarası olmayan bir modül varsa her yönetim paneli sayfasında kapatılamayan bir bildirim çıkar. Bildirim Lisanslar sayfasına ve seri numaralarınıza bağlantı verir. Müşteriler bu bildirimi görmez.

Tüm ücretli MageBooster modülleri bu modüle bağlıdır.

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

Ayarlar **Stores > Configuration > MageBooster > Licenses** sayfasındadır. Sayfayı açabilmek için yönetici kullanıcının `MageBooster_License::config` yetkisi olmalıdır.

**License status** tablosu kurulu tüm ücretli MageBooster modüllerini listeler:

| Durum | Anlamı |
|---|---|
| Etkin | Modülü geçerli bir seri numarası kapsıyor. |
| Ek süre | Henüz geçerli seri numarası yok. Modül çalışmaya devam eder; ayrıntı sütununda nedeni ve kalan gün sayısı yazar. |
| Kapalı | 30 günlük ek süre bitti. Modülün ücretli işlevi durdu; mağaza çalışmaya devam eder. |

**Serial numbers** grubunda kurulu her modül için bir alan, Hyvä sürümü için de ayrı bir alan vardır (örneğin **Moka United payments** ve **Moka United payments (Hyvä edition)**). Satın aldığınız sürümün alanını doldurun. Ayar yolu: `magebooster_license/serials/<kod>`, örneğin `magebooster_license/serials/moka` ve `magebooster_license/serials/moka_hyva`.

Geçerli seri numarası olmayan bir modül için gösterilen neden şunlardan biridir:

| Neden | Mesaj |
|---|---|
| missing | Seri numarası girilmemiş. |
| invalid | Seri numarası geçerli değil. |
| expired | Seri numarasının süresi <tarih> tarihinde doldu. |
| wrong_product | Bu seri numarası başka bir MageBooster ürünü için. |
| wrong_domain | Bu seri numarası <alan adı> alan adını kapsamıyor. |

### Ek süre ve yönetim paneli bildirimi

- Geçerli seri numarası yoksa modül **30 gün** çalışmaya devam eder. 30 gün, License modülü bu modülü geçerli seri numarası olmadan ilk gördüğü anda başlar ve her modül için ayrı tutulur.
- Ek süre kendiliğinden yeniden başlamaz. Geçerli bir seri numarası girilince ek süre biter; bu seri numarası sonradan geçersiz olursa (örneğin süresi dolarsa) yeni bir 30 günlük süre başlar.
- Ek süre boyunca ve sonrasında her yönetim paneli sayfasında kapatılamayan bir sistem mesajı görünür. Mesajda etkilenen her modül, nedeni ve "X gün daha çalışmaya devam eder" ya da "<tarih> tarihinden beri kapalı" bilgisi yazar. Mesaj Lisanslar sayfasına ve https://store.magebooster.com/licences/account/ adresine bağlantı verir.
- Müşteriler mağazada hiçbir lisans mesajı görmez.
- "Kapalı" durumunun her modülde ne anlama geldiği o modülün kılavuzunda yazar. Mağaza hiçbir zaman bozulmaz: ödeme yöntemleri gizlenir, MageBooster Checkout standart ödeme sayfasına döner ve benzeri.

Komut satırında `bin/magento magebooster:license:status` aynı durum tablosunu gösterir.

## Seri numarası girme

1. Seri numarasını MageBooster sipariş e-postanızdan kopyalayın. Biçimi `MB1.xxxxx.yyyyy` şeklindedir.
2. Numarayı o modülün (ya da Hyvä sürümünün) alanına yapıştırın.
3. **Save Config** düğmesine tıklayın.
4. **License status** tablosunda modülün **Etkin** göründüğünü kontrol edin.

## Geliştiriciler için

Kendi kodunuzdan bir modülün lisans durumunu sormak için `MageBooster\License\Api\LicenseInterface` arayüzünü enjekte edin:

```php
$this->license->isActive('moka');        // lisanslıysa ya da ek süre sürüyorsa true
$state = $this->license->getModuleState('moka');
$state->isValid();      // geçerli bir seri numarası var
$state->isInGrace();    // geçerli seri numarası yok, ek süre sürüyor
$state->getDaysLeft();  // ek sürede kalan gün
$state->getStatus();    // seri numarası kontrolünün sonucu (kod ve mesaj)
```

Arayüzdeki `isValid()` eski modüller için korunur ve artık `isActive()` ile aynı sonucu verir.

Bir modülü tanımlamak için kendi modülünüzün `di.xml` dosyasında `MageBooster\License\Model\Products` sınıfının `products` argümanına bir kayıt ekleyin. Kaydın adı modül kodudur; `codes` hangi ürün kodlarının seri numaralarının kabul edileceğini listeler (Hyvä sürümünün kodu temel modülde de kabul edilir):

```xml
<item name="moka" xsi:type="array">
    <item name="label" xsi:type="string">Moka United payments</item>
    <item name="codes" xsi:type="array">
        <item name="moka" xsi:type="string">moka</item>
        <item name="moka-hyva" xsi:type="string">moka-hyva</item>
    </item>
</item>
```

Ek sürenin başlangıç tarihleri `flag` tablosunda, `magebooster_license_grace` kodlu kayıtta tutulur (JSON, modül kodu => UTC başlangıç zamanı).

Seri numarasının yapısı `MB1.<base64url veri>.<base64url imza>` şeklindedir. Veri kısmı bir JSON nesnesidir:

- `p`: ürün kodu
- `d`: alan adları
- `e`: bitiş tarihi (Y-m-d)
- `i`: düzenlenme tarihi

## Test

1. Seri alanını boş bırakıp kaydedin. **License status** tablosunda **Ek süre**, "Seri numarası girilmemiş." ve 30 gün görünmeli; yönetim panelinde bildirim çıkmalı.
2. Geçerli bir seri numarası girip kaydedin. Durum **Etkin** olmalı ve modül bildirimden kalkmalı.
3. `bin/magento magebooster:license:status` komutuyla aynı sonucu komut satırında görebilirsiniz.
4. `magaza.test` gibi geliştirme adreslerinde alan adı kontrolü atlanır. Alan adı kontrolünü denemek için gerçek bir adres kullanın.

## Sorun giderme

- **"The serial number is not valid."** Numaranın tamamını kopyaladığınızdan emin olun. Başında veya sonunda boşluk, arada satır sonu olmamalı.
- **"does not cover the domain ..."** Mağaza görünümlerinden birinin base URL'si lisansın kapsamadığı bir alan adında. Her mağaza görünümünün adresini kontrol edin. Gerekirse doğru alan adları için yeni seri numarası alabilirsiniz.
- **"This serial number is for another MageBooster product."** Numara başka bir modülün alanına yapıştırılmış. Her numara kendi alanına girilir; Hyvä paketinin numarası (örneğin moka-hyva) "(Hyvä edition)" ile biten alana yazılır.
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
