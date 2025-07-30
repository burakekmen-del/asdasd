<!-- MODERN SSS (FAQ) - TÜRKÇE KARAKTER SORUNSUZ, HEADER ve FOOTER İÇİN UYUMLU -->
<section class="faq-section" lang="<?= $lang ?>">
  <h2 class="faq-title">
    <?= $lang === 'en' ? 'Frequently Asked Questions (FAQ)' : 'Sıkça Sorulan Sorular (SSS)' ?>
  </h2>
  <div class="faq-list">
    <?php
    $faq = [
      [
        'trQ' => 'Affiliate nedir?',
        'trA' => 'Affiliate, ürün veya hizmetlerin bağlantılarını paylaşarak satıştan komisyon kazanılan bir iş modelidir. Bağlantılarımız üzerinden alışveriş yaptığınızda size ek bir maliyet olmadan komisyon kazanabiliriz.',
        'enQ' => 'What is affiliate marketing?',
        'enA' => 'Affiliate means earning commission by sharing links to products or services. If you buy through our links, we may earn a commission at no extra cost to you.'
      ],
      [
        'trQ' => 'Bu ürünler orijinal mi?',
        'trA' => 'Sitemizdeki tüm ürünler resmi mağazalar veya güvenilir satıcılar tarafından sunulmaktadır. Orijinallik için satıcı ve ürün detaylarını kontrol ediniz.',
        'enQ' => 'Are these products original?',
        'enA' => 'All products here are offered by official stores or trusted sellers. Please check seller and product details for authenticity.'
      ],
      [
        'trQ' => 'Nasıl sipariş veririm?',
        'trA' => 'Beğendiğiniz ürüne tıkladığınızda ilgili mağazaya yönlendirilirsiniz. Siparişinizi mağaza sitesinin kendi sisteminden güvenle verebilirsiniz.',
        'enQ' => 'How do I order?',
        'enA' => 'Click on the product you like. You will be redirected to the seller’s website to complete your order securely.'
      ],
      [
        'trQ' => 'İade mümkün mü?',
        'trA' => 'İade ve değişim, sipariş verdiğiniz mağazanın politikalarına bağlıdır. İlgili mağazanın iade şartlarını mutlaka okuyun.',
        'enQ' => 'Can I return products?',
        'enA' => 'Returns and refunds depend on the policy of the store where you purchase. Please read the return policy of the seller.'
      ],
      [
        'trQ' => 'Sitenizden alışveriş yapmak güvenli mi?',
        'trA' => 'Evet. Tüm alışveriş işlemleri ilgili mağazanın veya pazaryerinin resmi sitesinde gerçekleşir. Sitemiz üzerinde ödeme veya kişisel bilgi alınmaz.',
        'enQ' => 'Is it safe to shop through your site?',
        'enA' => 'Yes. All purchases are completed on the official seller’s or marketplace’s website. We do not store your payment information.'
      ],
      [
        'trQ' => 'Kargo ve teslimat nasıl oluyor?',
        'trA' => 'Kargo, teslimat ve satış sonrası hizmetler tamamen ilgili mağazaya aittir. Sipariş vermeden önce mağazanın kargo politikalarını inceleyin.',
        'enQ' => 'How is shipping handled?',
        'enA' => 'Shipping, delivery, and after-sales service depend on the seller. Please check the shipping policy on the seller’s page.'
      ],
      [
        'trQ' => 'Gizlilik ve verilerim güvende mi?',
        'trA' => 'Gizliliğiniz önceliğimizdir. Kişisel veya ödeme bilgileriniz sitemizde toplanmaz. Detaylar için <a href="/privacy.php">Gizlilik Politikamızı</a> okuyun.',
        'enQ' => 'What about privacy and my data?',
        'enA' => 'Your privacy is important to us. We do not collect sensitive personal or payment data. Read our <a href="/privacy.php">Privacy Policy</a> for details.'
      ],
      [
        'trQ' => 'Sitede reklamlı/sponsorlu içerik var mı?',
        'trA' => 'Bazı bağlantılar sponsorlu, reklamlı veya affiliate olabilir. Her zaman şeffaf olmaya ve kullanıcı faydasını öne çıkarmaya çalışıyoruz. <a href="/affiliate-disclosure.php">Detaylı bilgi</a>.',
        'enQ' => 'Do you display sponsored or paid content?',
        'enA' => 'Some links may be sponsored, affiliate or contain ads. We always try to be transparent and prioritize user benefit. <a href="/affiliate-disclosure.php">Learn more</a>.'
      ],
      [
        'trQ' => 'Editörün seçimi ürünleri nasıl belirleniyor?',
        'trA' => 'Editörün seçimi ürünler; kullanıcı yorumları, fiyat/performans ve güncel kampanya avantajları dikkate alınarak seçilir.',
        'enQ' => 'How do you select featured or editor\'s pick products?',
        'enA' => 'Featured products are selected through a combination of user reviews, price/performance, and current campaign advantages.'
      ],
      [
        'trQ' => 'Yorum veya inceleme bırakabilir miyim?',
        'trA' => 'Evet, kayıtlı kullanıcılar ürün detay sayfalarında yorum yazabilir ve puan verebilir.',
        'enQ' => 'Can I leave a comment or review?',
        'enA' => 'Yes, registered users can leave comments and rate products on product detail pages.'
      ],
      [
        'trQ' => 'Destek hizmetiniz var mı?',
        'trA' => 'Her türlü soru veya sorun için <a href="/contact.php">İletişim</a> sayfamızı kullanabilirsiniz.',
        'enQ' => 'Do you have customer support?',
        'enA' => 'For any questions or problems, please use our <a href="/contact.php">Contact</a> page.'
      ],
      [
        'trQ' => 'Daha fazla yardıma mı ihtiyacınız var?',
        'trA' => '<a href="/contact.php">İletişim</a> sayfamızdan bize ulaşabilirsiniz.',
        'enQ' => 'Need more help?',
        'enA' => 'Contact us via the <a href="/contact.php">Contact</a> page.'
      ],
    ];
    foreach ($faq as $item) {
      $q = $lang === 'en' ? $item['enQ'] : $item['trQ'];
      $a = $lang === 'en' ? $item['enA'] : $item['trA'];
      echo '<details class="faq-details">
              <summary class="faq-summary"><b>' . $q . '</b></summary>
              <div class="faq-answer">' . $a . '</div>
            </details>';
    }
    ?>
  </div>
</section>
