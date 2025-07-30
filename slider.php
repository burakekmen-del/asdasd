<?php if (!empty($banners)): ?>
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<style>
/* Modern Glass Morphism Slider */
.slider-modern {
    --primary-gradient: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
    --secondary-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    --glass-bg: rgba(255, 255, 255, 0.25);
    --glass-border: rgba(255, 255, 255, 0.18);
    --shadow-color: 0 6% 12%;
    --transition-smooth: cubic-bezier(0.16, 1, 0.3, 1);
}

.slider-modern .swiper-slide {
    min-height: 380px;
    height: auto;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.slider-card {
    display: flex;
    width: 100%;
    max-width: 1200px;
    min-height: 300px;
    background: var(--glass-bg);
    border-radius: 2rem;
    box-shadow: 0 8px 32px hsl(var(--shadow-color)/0.15);
    backdrop-filter: blur(16px) saturate(1.8);
    -webkit-backdrop-filter: blur(16px) saturate(1.8);
    border: 1px solid var(--glass-border);
    overflow: hidden;
    transition: all 0.6s var(--transition-smooth);
    will-change: transform;
}

.slider-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 48px hsl(var(--shadow-color)/0.25);
}

.slider-content {
    flex: 0 0 45%;
    min-width: 300px;
    padding: 3rem 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.slider-content::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--primary-gradient);
    z-index: -1;
    clip-path: polygon(0 0, 100% 0, 80% 100%, 0% 100%);
}

.slider-title {
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    font-weight: 800;
    line-height: 1.2;
    color: white;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 8px rgba(0,0,0,0.12);
    letter-spacing: -0.5px;
}

.slider-desc {
    color: rgba(255,255,255,0.9);
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    max-width: 90%;
}

.slider-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.95);
    color: #4a00e0;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 0.9rem 2.5rem;
    border-radius: 100px;
    text-decoration: none;
    transition: all 0.4s var(--transition-smooth);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: none;
    cursor: pointer;
    width: fit-content;
}

.slider-btn:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}

.slider-btn i {
    transition: transform 0.3s ease;
}

.slider-btn:hover i {
    transform: translateX(3px);
}

.slider-image {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.slider-image::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--secondary-gradient);
    z-index: -1;
}

.slider-img-container {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.slider-img-container img {
    max-width: 100%;
    max-height: 280px;
    object-fit: contain;
    filter: drop-shadow(0 8px 24px rgba(0,0,0,0.15));
    transition: all 0.8s var(--transition-smooth);
    transform-style: preserve-3d;
}

.slider-card:hover .slider-img-container img {
    transform: scale(1.08) rotateY(5deg);
}

.slider-img-decoration {
    position: absolute;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: linear-gradient(45deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 100%);
    filter: blur(20px);
    z-index: 0;
}

.decoration-1 {
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
}

.decoration-2 {
    bottom: -30px;
    left: -30px;
    width: 150px;
    height: 150px;
}
.swiper.slider-modern {
    background: transparent !important;
    box-shadow: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
/* Navigation */
.swiper-pagination-bullet {
    width: 10px;
    height: 10px;
    background: rgba(255,255,255,0.5);
    opacity: 1;
    transition: all 0.3s ease;
}

.swiper-pagination-bullet-active {
    width: 24px;
    border-radius: 8px;
    background: white;
}

.swiper-button-next, 
.swiper-button-prev {
    color: white;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.3s ease;
}

.swiper-button-next::after, 
.swiper-button-prev::after {
    font-size: 1.2rem;
    font-weight: bold;
}

.swiper-button-next:hover, 
.swiper-button-prev:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 992px) {
    .slider-card {
        flex-direction: column;
        min-height: auto;
    }
    .slider-content {
        flex: 1;
        min-width: 100%;
        padding: 2rem 1.5rem;
    }
    .slider-content::before {
        clip-path: polygon(0 0, 100% 0, 100% 90%, 0% 100%);
    }
    .slider-title {
        font-size: 1.8rem;
    }
    .slider-image {
        padding: 1.5rem;
        min-height: 200px;
    }
    .slider-img-container img {
        max-height: 180px;
    }
}
@media (max-width: 576px) {
    .slider-modern .swiper-slide {
        padding: 0.5rem;
    }
    .slider-content {
        padding: 1.5rem 1rem;
    }
    .slider-title {
        font-size: 1.5rem;
    }
    .slider-desc {
        font-size: 1rem;
    }
    .slider-btn {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
}
</style>

<div class="swiper slider-modern mb-5">
    <div class="swiper-wrapper">
        <?php foreach ($banners as $b): ?>
            <?php
                $imgPath = 'uploads/banners/' . htmlspecialchars($b['image']);
                $productId = $b['product_id'] ?? null;
                $productUrl = $productId ? "product.php?id={$productId}" : (htmlspecialchars($b['link'] ?? '#'));
                $description = $b['description'] ?? ($lang == 'en' ? 'Discover this amazing product' : 'Bu harika ürünü keşfedin');
            ?>
            <div class="swiper-slide">
                <div class="slider-card">
                    <div class="slider-content">
                        <h2 class="slider-title"><?= htmlspecialchars($b['title']) ?></h2>
                        <p class="slider-desc"><?= htmlspecialchars($description) ?></p>
                        <a href="<?= $productUrl ?>" class="slider-btn">
                            <?= $lang == 'en' ? 'Explore Now' : 'Keşfet' ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="slider-image">
                        <div class="slider-img-decoration decoration-1"></div>
                        <div class="slider-img-decoration decoration-2"></div>
                        <div class="slider-img-container">
                            <img src="<?= $imgPath ?>" 
                                 loading="lazy"
                                 alt="<?= htmlspecialchars($b['title']) ?>">
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){
    if(document.querySelector('.slider-modern')){
        new Swiper('.slider-modern', {
            loop: true,
            autoplay: { 
                delay: 6000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            effect: 'creative',
            creativeEffect: {
                prev: {
                    shadow: true,
                    translate: ['-20%', 0, -1],
                    opacity: 0
                },
                next: {
                    translate: ['100%', 0, 0],
                }
            },
            speed: 1000,
            grabCursor: true,
            parallax: true,
            pagination: { 
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },
            breakpoints: {
                768: {
                    slidesPerView: 1,
                    spaceBetween: 20
                }
            }
        });
    }
});
</script>
<?php endif; ?>