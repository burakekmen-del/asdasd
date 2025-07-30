<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'lang_init.php';
include "header.php";
?>
<main class="flex-fill">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <h1 class="mb-4 display-5 fw-bold text-primary text-center">
                    <i class="fa fa-star me-2"></i><?= $lang_arr['why_us'] ?? ($lang == 'en' ? 'Why Us?' : 'Neden Biz?') ?>
                </h1>
                <p class="lead">
                    <b>Affiluxe</b> <?= $lang == 'en'
                    ? 'aims to offer users the most up-to-date, advantageous, and trustworthy shopping deals. With daily updated campaigns and honest, unbiased recommendations, we make your shopping experience easier.'
                    : 'olarak amacımız, kullanıcılarımıza en güncel, en avantajlı ve en güvenilir alışveriş fırsatlarını sunmaktır. Sitemizde her gün yenilenen kampanyalar, dürüst ve tarafsız öneriler ile alışveriş deneyiminizi kolaylaştırıyoruz.'
                    ?>
                </p>
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Updated Campaigns:' : 'Güncel Kampanyalar:' ?></b> <?= $lang == 'en' ? 'Save time and money with deals updated every day.' : 'Her gün güncellenen fırsatlar ile zamandan ve paradan tasarruf edin.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Trusted Alternatives:' : 'Güvenilir Alternatifler:' ?></b> <?= $lang == 'en' ? 'Only links from trusted stores and brands are published.' : 'Sadece güvenilir mağaza ve markalara ait bağlantılar yayınlanır.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'User-Friendly Interface:' : 'Kullanıcı Dostu Arayüz:' ?></b> <?= $lang == 'en' ? 'We offer a fast, modern, and easy-to-use site experience.' : 'Hızlı, modern ve kolay kullanılabilen bir site deneyimi sunuyoruz.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Impartiality:' : 'Tarafsızlık:' ?></b> <?= $lang == 'en' ? 'Only really advantageous deals are featured, without advertising bias.' : 'Sadece gerçekten avantajlı olan fırsatlar öne çıkarılır, reklam kaygısı gözetilmez.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Reliability:' : 'Güvenilirlik:' ?></b> <?= $lang == 'en' ? 'All our campaigns and deals are carefully selected.' : 'Tüm kampanya ve fırsatlarımızı titizlikle seçeriz.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Up-to-date Content:' : 'Güncel İçerik:' ?></b> <?= $lang == 'en' ? 'Take advantage of the best alternatives in the sector with daily updated deals.' : 'Her gün yenilenen fırsatlar ve sektördeki en iyi alternatifler sunulur.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'User-Focused:' : 'Kullanıcı Odaklılık:' ?></b> <?= $lang == 'en' ? 'We prioritize user experience with our simple and clear interface.' : 'Sade ve anlaşılır arayüzümüzle kullanıcı deneyimini ön planda tutarız.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Support:' : 'Destek:' ?></b> <?= $lang == 'en' ? 'Our support team is always ready to answer your questions quickly.' : 'Her sorunuzu hızlıca cevaplayan destek ekibimiz her zaman yanınızda.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Ad-Free Experience:' : 'Reklamsız Deneyim:' ?></b> <?= $lang == 'en' ? 'Focus only on the information you need, without unnecessary ads.' : 'Gereksiz reklamlar olmadan, sadece ihtiyacınız olan bilgilere odaklanabilirsiniz.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Free Service:' : 'Ücretsiz Hizmet:' ?></b> <?= $lang == 'en' ? 'You can benefit from all the deals on our site for free.' : 'Sitemizdeki tüm fırsatlardan ücretsiz olarak yararlanabilirsiniz.' ?></li>
                    <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i><b><?= $lang == 'en' ? 'Community Engagement:' : 'Topluluk Katılımı:' ?></b> <?= $lang == 'en' ? 'Our users can contribute to the site with suggestions or by reporting deals.' : 'Kullanıcılarımız öneri ve kampanya bildirimi ile siteye katkı sağlayabilir.' ?></li>
                </ul>
                <p>
                    <?= $lang == 'en'
                    ? 'Enjoy shopping with Affiluxe privilege. For your questions or suggestions, you can use our <a href="contact.php">contact</a> page.'
                    : 'Siz de Affiluxe ayrıcalığıyla alışverişin tadını çıkarın. Sorularınız veya önerileriniz için <a href="contact.php">iletişim</a> sayfamızı kullanabilirsiniz.'
                    ?>
                </p>
            </div>
        </div>
    </div>
</main>
<?php include "footer.php"; ?>