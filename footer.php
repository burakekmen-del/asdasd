<footer class="footer mt-auto bg-footer pt-0" >
  <div class="container footer-container">
    <div class="footer-grid">
      <div class="footer-column">
        <a href="/" class="footer-logo d-flex align-items-center gap-2 text-decoration-none">
          <img src="/assets/images/affiluxe-logo.png" alt="Affiluxe" class="footer-logo-img">
          <span class="footer-logo-text">Affiluxe</span>
        </a>
        <div class="footer-slogan mt-2 mb-2 text-primary fw-bold"> <?= $lang == 'en' ? 'The best way to shop smart' : 'Alışverişin en avantajlı hali' ?> </div>
        <div class="footer-info-text text-muted small"> <?= $lang == 'en' ? "Shop safely and earn commissions from the world's largest marketplaces, only at Affiluxe. Deals, discounts and best links in one place." : "Türkiye’nin ve dünyanın en büyük pazaryerlerinden, güvenli ve komisyonlu alışveriş için Affiluxe. Fırsatlar, indirimler ve avantajlı linkler tek adreste." ?> </div>
        <div class="footer-social mt-3 mb-1 d-flex gap-3 align-items-center">
          <a href="https://twitter.com/affiluxetr" target="_blank" rel="nofollow noopener" class="footer-social-link bg-twitter" title="Twitter" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="https://instagram.com/affiluxe" target="_blank" rel="nofollow noopener" class="footer-social-link bg-instagram" title="Instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="mailto:destek@affiluxe.com.tr" class="footer-social-link bg-mail" title="E-posta" aria-label="E-posta"><i class="fa fa-envelope"></i></a>
        </div>
      </div>
      <div class="footer-column">
        <h3 class="footer-title"> <?= $lang == 'en' ? 'Quick Links' : 'Hızlı Linkler' ?> </h3>
        <ul class="footer-links">
          <li><a href="/"> <?= $lang == 'en' ? 'Home' : 'Ana Sayfa' ?> </a></li>
          <li><a href="/deals.php"> <?= $lang == 'en' ? 'Deals' : 'Fırsatlar' ?> </a></li>
          <li><a href="/blog.php"> <?= $lang == 'en' ? 'Blog' : 'Blog' ?> </a></li>
          <li><a href="/about.php"> <?= $lang == 'en' ? 'About' : 'Hakkımızda' ?> </a></li>
          <li><a href="/contact.php"> <?= $lang == 'en' ? 'Contact' : 'İletişim' ?> </a></li>
          <li><a href="/privacy.php"> <?= $lang == 'en' ? 'Privacy' : 'Gizlilik' ?> </a></li>
          <li><a href="/terms.php"> <?= $lang == 'en' ? 'Terms' : 'Şartlar' ?> </a></li>
          <li><a href="/kvkk.php"> <?= $lang == 'en' ? 'Data Policy (KVKK)' : 'KVKK' ?> </a></li>
          <li><a href="/footer_faq.php"> <?= $lang == 'en' ? 'FAQ' : 'SSS' ?> </a></li>
          <li><a href="/affiliate-disclosure.php"> <?= $lang == 'en' ? 'Affiliate Disclosure' : 'Affiliate Bilgilendirme' ?> </a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3 class="footer-title"> <?= $lang == 'en' ? 'Popular Categories' : 'Popüler Kategoriler' ?> </h3>
        <ul class="footer-links">
          <li><a href="/?category=kadin"> <?= $lang == 'en' ? 'Women Fashion' : 'Kadın Moda' ?> </a></li>
          <li><a href="/?category=erkek"> <?= $lang == 'en' ? 'Men Fashion' : 'Erkek Moda' ?> </a></li>
          <li><a href="/?category=elektronik"> <?= $lang == 'en' ? 'Electronics' : 'Elektronik' ?> </a></li>
          <li><a href="/?category=ev-yasam"> <?= $lang == 'en' ? 'Home & Living' : 'Ev & Yaşam' ?> </a></li>
          <li><a href="/?category=populer"> <?= $lang == 'en' ? 'Most Visited' : 'Çok Tıklananlar' ?> </a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3 class="footer-title"> <?= $lang == 'en' ? 'Newsletter' : 'Bülten' ?> </h3>
        <form method="post" action="/newsletter_subscribe.php" class="newsletter-form">
          <input type="email" name="email" class="form-control newsletter-input" placeholder="<?= $lang == 'en' ? 'Your email address' : 'E-posta adresiniz' ?>" required>
          <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
          <button type="submit" class="btn btn-primary newsletter-btn"> <?= $lang == 'en' ? 'Subscribe' : 'Abone Ol' ?> </button>
        </form>
        <div class="newsletter-tiny text-muted"> <?= $lang == 'en' ? 'Get weekly deals and campaigns in your inbox!' : 'Haftalık indirimler ve kampanyalar e-postana gelsin!' ?> </div>
      </div>
    </div>
    <div class="footer-bottom mt-4">
      <span class="text-muted footer-copyright">&copy; <?= date('Y') ?> Affiluxe.com.tr • <?= $lang == 'en' ? 'All rights reserved.' : 'Tüm hakları saklıdır.' ?></span>
      <br>
      <span class="text-muted footer-affiliate-disclaimer">
        <?= $lang == 'en'
          ? 'Some links on this site are affiliate links. If you make a purchase, we may earn a commission at no extra cost to you. <a href="/affiliate-disclosure.php" class="footer-affiliate-link">Learn more</a>'
          : 'Bu sitedeki bazı bağlantılar affiliate (ortaklık) linki içerebilir. Satın alma yaparsanız size ek bir maliyet olmadan komisyon kazanabiliriz. <a href="/affiliate-disclosure.php" class="footer-affiliate-link">Detaylı bilgi</a>'
        ?>
      </span>
    </div>
  </div>
</footer>
