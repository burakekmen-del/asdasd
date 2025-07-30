<?php
// privacy.php - Affiluxe Privacy Policy Page
require_once __DIR__ . '/header.php';

// Dili otomatik olarak belirle veya ?lang parametresiyle seç
$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'tr';

// EN ve TR içerikleri - daha güvenilir, ciddi ve yasal üslup ile güncellendi
$privacy = [
    'en' => [
        'title' => 'Privacy Policy',
        'updated' => 'Last Updated: July 15, 2025',
        'intro' => 'This Privacy Policy describes how Affiluxe ("we", "us", or "our") collects, uses, and protects your personal data in compliance with applicable data protection laws and best practices. By using our website, you agree to this Privacy Policy.',
        'sections' => [
            [
                'title' => '1. Information We Collect',
                'items' => [
                    '<b>Account Information:</b> If you create an account, we securely collect and process your username, email address, and password (stored encrypted).',
                    '<b>Usage Data:</b> We may automatically collect data about your interactions with our website, such as visited pages, actions taken, and affiliate link clicks. This data is used in aggregate and is not linked to your identity.',
                    '<b>Cookies and Similar Technologies:</b> We use cookies and similar technologies to enhance site performance, enable essential features (such as login sessions), and for security purposes. You may disable cookies in your browser, but this may affect site functionality.'
                ]
            ],
            [
                'title' => '2. How We Use Your Information',
                'items' => [
                    'To provide, operate, and improve the Affiluxe platform and our services.',
                    'To allow you to browse, interact with, and purchase affiliate products.',
                    'To communicate with you regarding your account, security, or important updates.',
                    'To comply with legal obligations and protect the integrity and security of our website.'
                ]
            ],
            [
                'title' => '3. Affiliate Links & Third Parties',
                'items' => [
                    'Our website contains affiliate links (e.g., Amazon Associates). If you click these links and make a purchase, we may receive a commission. This does not affect the price you pay.',
                    'When you click a product’s "Buy" button, you are redirected to a third-party site. We are not responsible for the privacy, content, or practices of third-party websites. Please review their privacy policies.'
                ]
            ],
            [
                'title' => '4. Information Sharing and Disclosure',
                'items' => [
                    'We do <b>not</b> sell, rent, or share your personal information with third parties, except as strictly necessary to provide our services, comply with the law, or protect our rights.',
                    'We may engage trusted service providers (such as secure email delivery) who are contractually obligated to protect your data and use it only for specified purposes.',
                    'We may share aggregated, anonymized data for analytics and reporting purposes. This data cannot identify you personally.'
                ]
            ],
            [
                'title' => '5. Data Security',
                'items' => [
                    'We implement industry-standard technical and organizational measures to safeguard your data against unauthorized access, alteration, disclosure, or destruction.',
                    'Sensitive information, including passwords, is stored using strong encryption and is never visible in plain text.',
                    'Despite our best efforts, no method of electronic transmission or storage is 100% secure. In the event of a data breach, we will notify affected users as required by law.'
                ]
            ],
            [
                'title' => '6. Your Rights and Choices',
                'items' => [
                    'You may access, update, or request deletion of your account and personal data at any time by contacting us.',
                    'You may unsubscribe from marketing emails at any time by clicking the unsubscribe link in our emails.',
                    'Depending on your jurisdiction, you may have additional rights over your personal data in accordance with local laws.'
                ]
            ],
            [
                'title' => '7. Changes to This Policy',
                'text' => 'We reserve the right to update this Privacy Policy to reflect changes in our practices or legal requirements. The latest version will always be available on this page, with the revised date indicated.'
            ],
            [
                'title' => '8. Contact',
                'text' => 'If you have any questions or requests regarding your privacy or this policy, please contact us at <a href="mailto:info@affiluxe.com.tr">info@affiluxe.com.tr</a>.'
            ]
        ],
        'back' => 'Back to Registration'
    ],
    'tr' => [
        'title' => 'Gizlilik Politikası',
        'updated' => 'Güncellenme Tarihi: 15 Temmuz 2025',
        'intro' => 'Bu Gizlilik Politikası, Affiluxe ("biz", "bize" veya "bizim") olarak, kişisel verilerinizi yürürlükteki veri koruma mevzuatına ve en iyi uygulamalara uygun şekilde nasıl topladığımızı, kullandığımızı ve koruduğumuzu açıklar. Web sitemizi kullanarak bu politikayı kabul etmiş olursunuz.',
        'sections' => [
            [
                'title' => '1. Toplanan Bilgiler',
                'items' => [
                    '<b>Hesap Bilgileri:</b> Hesap oluşturduğunuzda, kullanıcı adınızı, e-posta adresinizi ve şifrenizi (şifreli olarak) güvenli bir şekilde toplarız ve işleriz.',
                    '<b>Kullanım Verileri:</b> Web sitemizle olan etkileşimleriniz (ziyaret edilen sayfalar, yapılan işlemler, tıklanan affiliate linkler gibi) otomatik olarak toplanabilir. Bu veriler anonim olarak kullanılır ve kimliğinizle ilişkilendirilmez.',
                    '<b>Çerezler ve Benzeri Teknolojiler:</b> Sitenin performansını artırmak, temel işlevleri (oturum, güvenlik vb.) sağlamak ve güvenliği korumak için çerezler ve benzeri teknolojiler kullanılır. Tarayıcınızdan çerezleri devre dışı bırakabilirsiniz; ancak bu durumda bazı fonksiyonlar çalışmayabilir.'
                ]
            ],
            [
                'title' => '2. Bilgilerinizin Kullanımı',
                'items' => [
                    'Affiluxe platformunu ve hizmetlerimizi sunmak, işletmek ve geliştirmek için.',
                    'Affiliate ürünlerini görüntülemeniz ve satın alabilmeniz için.',
                    'Hesabınız, güvenlik veya önemli güncellemeler hakkında sizinle iletişim kurmak için.',
                    'Yasal yükümlülüklere uymak ve web sitemizin bütünlüğünü ve güvenliğini korumak için.'
                ]
            ],
            [
                'title' => '3. Affiliate Linkler ve Üçüncü Taraflar',
                'items' => [
                    'Sitemizde Amazon Associates gibi affiliate linkler bulunabilir. Bu linklere tıkladığınızda ve alışveriş yaptığınızda, size ek bir maliyet olmadan komisyon kazanabiliriz.',
                    '"Satın Al" butonuna tıkladığınızda üçüncü taraf bir siteye yönlendirilirsiniz. Üçüncü taraf sitelerin gizlilik uygulamalarından, içeriklerinden veya işlemlerinden sorumlu değiliz. Lütfen ilgili sitelerin gizlilik politikalarını inceleyin.'
                ]
            ],
            [
                'title' => '4. Bilgi Paylaşımı ve Aktarımı',
                'items' => [
                    'Kişisel verileriniz hiçbir koşulda <b>satılmaz</b>, kiralanmaz veya üçüncü kişilerle paylaşılmaz; yalnızca hizmetlerimizin sunumu, yasal yükümlülükler veya haklarımızı korumak için zorunlu hallerde paylaşılır.',
                    'Güvenilir ve sözleşme ile veri koruma taahhüdü altındaki hizmet sağlayıcı (ör: güvenli e-posta gönderimi) firmalarla çalışılabilir.',
                    'Toplu ve anonim veri analiz ve raporlama amacıyla paylaşılabilir. Bu verilerden kimliğiniz tespit edilemez.'
                ]
            ],
            [
                'title' => '5. Veri Güvenliği',
                'items' => [
                    'Verilerinizi yetkisiz erişime, değiştirmeye, ifşaya veya imhaya karşı korumak için endüstri standardı teknik ve idari önlemler uygularız.',
                    'Şifre gibi hassas bilgiler güçlü şifreleme ile saklanır ve asla düz metin olarak tutulmaz.',
                    'Tüm tedbirlere rağmen elektronik aktarım veya depolama %100 güvenli olmayabilir. Olası veri ihlali durumunda, yasal gereklilikler doğrultusunda ilgili kullanıcılar bilgilendirilir.'
                ]
            ],
            [
                'title' => '6. Haklarınız ve Tercihleriniz',
                'items' => [
                    'Her zaman bize ulaşarak hesabınıza ve kişisel verilerinize erişim, güncelleme veya silme talebinde bulunabilirsiniz.',
                    'Eposta iletilerimizdeki abonelikten çık bağlantısı ile ticari iletileri almayı durdurabilirsiniz.',
                    'Bulunduğunuz ülkenin mevzuatına göre kişisel veriler üzerinde ek haklara sahip olabilirsiniz.'
                ]
            ],
            [
                'title' => '7. Politika Değişiklikleri',
                'text' => 'Gizlilik Politikamızda güncellemeler yapma hakkımız saklıdır. En güncel sürüm her zaman bu sayfada yayınlanır ve güncellenme tarihi belirtilir.'
            ],
            [
                'title' => '8. İletişim',
                'text' => 'Gizliliğiniz veya bu politika ile ilgili her türlü soru ve talepleriniz için <a href="mailto:info@affiluxe.com.tr">info@affiluxe.com.tr</a> adresinden bizimle iletişime geçebilirsiniz.'
            ]
        ],
        'back' => 'Kayıt Sayfasına Dön'
    ]
];
$c = $privacy[$lang];
?>
<main class="container my-5" style="min-height:70vh;">
    <div class="privacy-container mx-auto">
        <div class="privacy-title">
            <i class="bi bi-shield-lock"></i> <?= $c['title'] ?>
        </div>
        <div class="text-muted mb-4 text-center"><?= $c['updated'] ?></div>

        <div class="mb-4">
            <?= $c['intro'] ?>
        </div>

        <?php foreach ($c['sections'] as $sec): ?>
            <div class="privacy-section-title"><?= $sec['title'] ?></div>
            <?php if (isset($sec['items'])): ?>
                <ul class="privacy-list">
                    <?php foreach ($sec['items'] as $item): ?>
                        <li><?= $item ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif (isset($sec['text'])): ?>
                <p><?= $sec['text'] ?></p>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="mt-4 text-center">
            <a href="register.php?lang=<?= $lang ?>" class="btn btn-primary"><?= $c['back'] ?></a>
        </div>
        <div class="mt-3 text-center">
            <a href="?lang=en" class="btn btn-link <?= $lang=='en'?'fw-bold text-primary':'' ?>">English</a> |
            <a href="?lang=tr" class="btn btn-link <?= $lang=='tr'?'fw-bold text-primary':'' ?>">Türkçe</a>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
<style>
.privacy-container {
    max-width: 800px;
    margin: 56px auto;
    background: #fff;
    border-radius: var(--radius-md, 12px);
    box-shadow: var(--shadow-md, 0 4px 12px rgba(0,0,0,0.12));
    padding: 2.5rem 2rem 2rem 2rem;
}
.privacy-title {
    font-weight: 900;
    color: var(--primary, #6366F1);
    font-size: 2rem;
    letter-spacing: -1px;
    text-align: center;
    margin-bottom: 18px;
}
.privacy-section-title {
    color: var(--primary, #6366F1);
    font-size: 1.2rem;
    font-weight: 700;
    margin-top: 2.2rem;
    margin-bottom: .7rem;
}
.privacy-list {
    padding-left: 1.2em;
}
@media (max-width: 600px) {
    .privacy-container { padding: 1.2rem 0.5rem 1.2rem 0.5rem; }
}
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">