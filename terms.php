<?php
// terms.php - Affiluxe Terms of Service Page
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terms of Service | Affiluxe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- main.css referansı kaldırıldı. Stil kodları bu dosyada <style> etiketiyle kullanılmalı. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .terms-container {
            max-width: 800px;
            margin: 56px auto;
            background: #fff;
            border-radius: var(--radius-md, 12px);
            box-shadow: var(--shadow-md, 0 4px 12px rgba(0,0,0,0.12));
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .terms-title {
            font-weight: 900;
            color: var(--primary, #6366F1);
            font-size: 2rem;
            letter-spacing: -1px;
            text-align: center;
            margin-bottom: 18px;
        }
        .terms-section-title {
            color: var(--primary, #6366F1);
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 2.2rem;
            margin-bottom: .7rem;
        }
        .terms-list {
            padding-left: 1.2em;
        }
        @media (max-width: 600px) {
            .terms-container { padding: 1.2rem 0.5rem 1.2rem 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="site-wrapper" style="min-height:100vh;display:flex;align-items:center;">
        <main class="container">
            <div class="terms-container">
                <div class="terms-title">
                    <i class="bi bi-file-text"></i> Terms of Service
                </div>
                <div class="text-muted mb-4 text-center">Last Updated: June 14, 2025</div>

                <div class="mb-4">
                    Welcome to Affiluxe! By using this website, you agree to the following terms and conditions.
                </div>

                <div class="terms-section-title">1. About Affiluxe</div>
                <p>
                    Affiluxe is a platform where products are shared only with affiliate links. All products on this website are for informational and browsing purposes only.
                </p>

                <div class="terms-section-title">2. How It Works</div>
                <ul class="terms-list">
                    <li>You can view and browse products listed on Affiluxe.</li>
                    <li>If you wish to buy a product, you will be redirected to the seller's website via an affiliate link by clicking the "Buy" or similar button.</li>
                    <li>Affiluxe does not sell any products directly. All purchases are made on third-party seller sites via affiliate links.</li>
                </ul>

                <div class="terms-section-title">3. Affiliate Links</div>
                <ul class="terms-list">
                    <li>Most or all product links on Affiluxe are affiliate links. This means Affiluxe may earn a commission when you click these links and make a purchase.</li>
                    <li>Using affiliate links does not change the price you pay on the seller's website.</li>
                </ul>

                <div class="terms-section-title">4. User Responsibilities</div>
                <ul class="terms-list">
                    <li>Users can only browse and review products on Affiluxe.</li>
                    <li>Affiluxe does not take responsibility for the content, quality, or delivery of products on third-party websites.</li>
                    <li>It is your responsibility to review and agree to the terms and privacy policies of any external website you visit via affiliate links.</li>
                </ul>

                <div class="terms-section-title">5. Disclaimer</div>
                <ul class="terms-list">
                    <li>Affiluxe is not responsible for the accuracy, availability, or legality of external sites or products.</li>
                    <li>All product information is provided for informational purposes only and may change at any time by the seller.</li>
                </ul>

                <div class="terms-section-title">6. Changes to Terms</div>
                <p>
                    Affiluxe may update these Terms of Service at any time. Continued use of the website means you agree to the latest terms.
                </p>

                <div class="terms-section-title">7. Contact</div>
                <p>
                    For any questions, contact us at <a href="mailto:support@affiluxe.com.tr">support@affiluxe.com.tr</a>.
                </p>

                <div class="mt-4 text-center">
                    <a href="register.php" class="btn btn-primary">Back to Registration</a>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>