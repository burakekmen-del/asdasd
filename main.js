// Affiluxe main.js — Modern, erişilebilir, SEO/i18n uyumlu, mikro etkileşimli, performanslı
// Helper: i18n metinleri ve ARIA için
function getLangText(key, lang) {
    const messages = {
        'favLogin': {'tr': 'Favorilere eklemek için giriş yapmalısınız.', 'en': 'You must be logged in to add to favorites.'},
        'loadError': {'tr': 'Bir hata oluştu, lütfen tekrar deneyin.', 'en': 'An error occurred, please try again.'},
        'favAdded': {'tr': 'Favorilere eklendi!', 'en': 'Added to favorites!'},
        'favRemoved': {'tr': 'Favorilerden çıkarıldı!', 'en': 'Removed from favorites!'},
        'compareAdded': {'tr': 'Karşılaştırmaya eklendi!', 'en': 'Added to compare!'},
        'compareRemoved': {'tr': 'Karşılaştırmadan çıkarıldı.', 'en': 'Removed from compare.'},
        'maxCompare': {'tr': 'En fazla 4 ürün karşılaştırabilirsin!', 'en': 'You can compare up to 4 products!'},
        'copied': {'tr': 'Bağlantı kopyalandı!', 'en': 'Link copied!'},
        'searchEmpty': {'tr': 'Lütfen arama terimi girin.', 'en': 'Please enter a search term.'}
    };
    return messages[key] && messages[key][lang] ? messages[key][lang] : key;
}

document.addEventListener("DOMContentLoaded", function() {
    // Dil ve oturum
    var lang = document.documentElement.lang || 'tr';
    window.lang = lang;
    window.isLoggedIn = typeof window.IS_LOGGED_IN !== "undefined"
        ? window.IS_LOGGED_IN
        : (typeof isLoggedIn !== "undefined" ? isLoggedIn : false);

    // Toast fonksiyonu
    window.showToast = function(messageKey, type = 'success') {
        let text = getLangText(messageKey, window.lang || 'tr');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white ${type === 'error' ? 'bg-danger' : 'bg-success'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.borderRadius = '8px';
        toast.style.overflow = 'hidden';
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} me-2"></i>
                    ${text}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        document.body.appendChild(toast);
        if(window.bootstrap && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toast, {
                delay: 3000,
                animation: true
            });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        } else {
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.transition = 'opacity 0.5s';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }
    };

    // Favori ve karşılaştırma listesi
    if (!window.compareList) window.compareList = [];
    try {
        window.compareList = JSON.parse(localStorage.getItem('compareList')||'[]');
    } catch(e) {
        window.compareList = [];
    }
    function updateCompareIndicator() {
        document.querySelectorAll('.compare-count-badge').forEach(badge => {
            const count = window.compareList ? window.compareList.length : 0;
            badge.innerText = count;
            badge.style.display = count ? "inline-flex" : "none";
            badge.setAttribute('aria-label', count + ' ürün karşılaştırmada');
        });
    }

    // Karşılaştırma butonları
    function handleCompareClick(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = e.currentTarget;
        const pid = btn.dataset.product;
        if(!window.isLoggedIn) {
            showToast('favLogin', 'error');
            window.location.href = "login.php?redirect=" + encodeURIComponent(window.location.pathname + window.location.search);
            return;
        }
        const found = window.compareList.indexOf(pid);
        if(found > -1) {
            window.compareList.splice(found, 1);
            btn.classList.remove('active');
            showToast('compareRemoved');
        } else {
            if(window.compareList.length >= 4) {
                showToast('maxCompare', 'error');
                return;
            }
            window.compareList.push(pid);
            btn.classList.add('active');
            showToast('compareAdded');
        }
        localStorage.setItem('compareList', JSON.stringify(window.compareList));
        updateCompareIndicator();
    }
    function bindCompareButtons() {
        document.querySelectorAll('.compare-btn, .btn-compare').forEach(btn => {
            btn.removeEventListener('click', handleCompareClick);
            btn.addEventListener('click', handleCompareClick);
            if(window.compareList.includes(btn.dataset.product)) {
                btn.classList.add('active');
            }
        });
    }

    // Quick View Modal
    function handleQuickViewClick(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = e.currentTarget;
        // HATA KORUMASI: data-id integer ve >0 mu?
        const pid = btn.dataset.id || btn.dataset.product;
        if (!pid || isNaN(pid) || pid <= 0) {
            showToast('loadError', 'error');
            return;
        }
        const quickViewContent = document.getElementById('quickViewContent');
        if(!quickViewContent) return;
        quickViewContent.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                <p class="mt-3">${getLangText('loadError', lang)}</p>
            </div>
        `;
        const modalEl = document.getElementById('quickViewModal');
        if(modalEl) {
            const modal = window.bootstrap && bootstrap.Modal
                ? bootstrap.Modal.getOrCreateInstance(modalEl)
                : { show: () => modalEl.style.display = 'block' };
            modal.show();
        }
        fetch(`quick_view.php?id=${pid}&lang=${lang}`)
            .then(r => r.text())
            .then(html => {
                quickViewContent.innerHTML = html;
                bindFavButtons();
                bindCompareButtons();
                // Slider init
                if(typeof Swiper !== 'undefined' && quickViewContent.querySelector('.swiper')) {
                    new Swiper('.swiper', {
                        loop: false,
                        pagination: { el: '.swiper-pagination' },
                        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
                    });
                }
            })
            .catch(() => {
                quickViewContent.innerHTML = `
                    <div class="alert alert-danger">
                        ${getLangText('loadError', lang)}
                    </div>
                `;
            });
    }
    function bindQuickViewButtons() {
        document.querySelectorAll('.quick-view-trigger, .btn-quick-view').forEach(btn => {
            btn.removeEventListener('click', handleQuickViewClick);
            btn.addEventListener('click', handleQuickViewClick);
        });
    }

    // Kopyala butonları ve sosyal paylaşım link kopyalama
    function handleCopyClick(e) {
        const copyBtn = e.target.closest('.copy-link-btn, .btn-copy-link');
        if(!copyBtn) return;
        e.preventDefault();
        e.stopPropagation();
        const link = copyBtn.dataset.link || window.location.href;
        navigator.clipboard.writeText(link).then(() => {
            const originalHTML = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i>';
            showToast('copied');
            setTimeout(() => {
                copyBtn.innerHTML = originalHTML;
            }, 2000);
        }).catch(() => {
            showToast('loadError', 'error');
        });
    }

    // AFFILIATE DIŞ SİTE MODALI (Bootstrap 5)
    document.querySelectorAll('[data-aff-external]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var modal = document.getElementById('affExternalModal');
            if (!modal) {
                // Modal bileşeni HTML'de yoksa varsayılan yönlendirme
                window.open(this.getAttribute('data-href'), '_blank', 'noopener');
                return;
            }
            var bsModal = window.bootstrap && bootstrap.Modal
                ? bootstrap.Modal.getOrCreateInstance(modal)
                : { show: () => { modal.style.display = 'block'; } };
            document.getElementById('affExternalPlatform').innerText = this.getAttribute('data-platform') || '';
            document.getElementById('affExternalGoBtn').setAttribute('href', this.getAttribute('data-href'));
            bsModal.show();
        });
    });

    // Favori butonları bind (quick view sonrası yeni gelen içerikte de çalışsın)
    window.bindFavButtons = function() {
        // Event delegation olduğu için burada ekstra bind yapmaya gerek yok
        // Sadece ikon durumunu güncellemek gerekirse burada yapılabilir
    };

    // Tüm event listener'ları bağla (karşılaştırma, quickview ve copy için)
    function initAll() {
        bindCompareButtons();
        bindQuickViewButtons();
        updateCompareIndicator();
        if(typeof initDarkMode !== "undefined") initDarkMode();
        document.body.addEventListener('click', handleCopyClick);
        // Tooltip: Tüm .has-tooltip öğelerine erişilebilir tooltip ekle
        document.querySelectorAll('.has-tooltip').forEach(function(el) {
            el.addEventListener('mouseenter', function() {
                let tip = document.createElement('div');
                tip.className = 'tooltip show';
                tip.innerText = el.getAttribute('data-tooltip') || '';
                document.body.appendChild(tip);
                const rect = el.getBoundingClientRect();
                tip.style.left = (rect.left + window.scrollX + rect.width/2 - tip.offsetWidth/2) + 'px';
                tip.style.top = (rect.top + window.scrollY - tip.offsetHeight - 8) + 'px';
                el._tooltip = tip;
            });
            el.addEventListener('mouseleave', function() {
                if(el._tooltip) { el._tooltip.remove(); el._tooltip = null; }
            });
        });
    }

    initAll();

    // ======= SWIPER - Amazon ve Temu kartlarını eşzamanlı, sabit ve çok yavaş kaydır (sonsuz loop, asla durmaz!) =======
    if (typeof Swiper !== "undefined") {
        const continuousSpeed = 14000; // 14sn'de 1 ekran kayar, çok yavaş ve sabit
        function getLoopedSlidesCount(sel) {
            const c = document.querySelector(sel + ' .swiper-wrapper');
            return c ? c.children.length : 0;
        }
        // Amazon Swiper
        const amazonSwiper = new Swiper('.amazon-swiper', {
            slidesPerView: 2,
            spaceBetween: 16,
            speed: continuousSpeed,
            loop: true,
            loopedSlides: getLoopedSlidesCount('.amazon-swiper'),
            allowTouchMove: true,
            autoplay: {
                delay: 0, // hiç durmadan
                disableOnInteraction: false,
                pauseOnMouseEnter: false,
            },
            cssMode: false,
            freeMode: false, // momentum yok, sabit hız
            grabCursor: true,
            pagination: { el: '.amazon-swiper .swiper-pagination', clickable: true },
            breakpoints: {
                0: { slidesPerView: 1.3 },
                400: { slidesPerView: 1.7 },
                600: { slidesPerView: 2 }
            }
        });
        // Temu Swiper
        const temuSwiper = new Swiper('.temu-swiper', {
            slidesPerView: 2,
            spaceBetween: 16,
            speed: continuousSpeed,
            loop: true,
            loopedSlides: getLoopedSlidesCount('.temu-swiper'),
            allowTouchMove: true,
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
                pauseOnMouseEnter: false,
            },
            cssMode: false,
            freeMode: false,
            grabCursor: true,
            pagination: { el: '.temu-swiper .swiper-pagination', clickable: true },
            breakpoints: {
                0: { slidesPerView: 1.3 },
                400: { slidesPerView: 1.7 },
                600: { slidesPerView: 2 }
            }
        });
        // Eşzamanlı başlat/durdur
        function syncAutoplay(sw1, sw2) {
            sw1.on('sliderMove', function() { sw2.autoplay.stop(); });
            sw2.on('sliderMove', function() { sw1.autoplay.stop(); });
            sw1.on('autoplayStart', function() { if (!sw2.autoplay.running) sw2.autoplay.start(); });
            sw2.on('autoplayStart', function() { if (!sw1.autoplay.running) sw1.autoplay.start(); });
        }
        syncAutoplay(amazonSwiper, temuSwiper);

        // ============ MIX SWIPER: Tüm Platformlardan Fırsatlar 4'lü grid/slider ============
        // (Her slide'da 4 ürün, swipe ve arrow ile kaydırılabilir, responsive, loop'lu)
        const mixSwiper = new Swiper('.mix-swiper', {
            slidesPerView: 4,
            spaceBetween: 20,
            loop: true,
            loopedSlides: getLoopedSlidesCount('.mix-swiper'),
            allowTouchMove: true,
            grabCursor: true,
            speed: 700,
            autoplay: { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true },
            pagination: { el: '.mix-swiper .swiper-pagination', clickable: true },
            navigation: {
                nextEl: '.mix-swiper .swiper-button-next',
                prevEl: '.mix-swiper .swiper-button-prev'
            },
            breakpoints: {
                0:    { slidesPerView: 1.1, spaceBetween: 8 },
                480:  { slidesPerView: 2,   spaceBetween: 12 },
                700:  { slidesPerView: 3,   spaceBetween: 16 },
                1024: { slidesPerView: 4,   spaceBetween: 20 }
            }
        });
    }

    // --------- Mega Menü Hover Animasyonu ve Gecikmeli Kapanma ---------
    const megaDropdown = document.querySelector('.mega-dropdown');
    if (megaDropdown) {
        let hideTimeout;
        const menu = megaDropdown.querySelector('.aff-dropdown-menu');
        megaDropdown.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout);
            menu && (menu.style.opacity = '1', menu.style.visibility = 'visible', menu.style.transform = 'translateY(0)');
        });
        megaDropdown.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(() => {
                menu && (menu.style.opacity = '', menu.style.visibility = '', menu.style.transform = '');
            }, 120); // yumuşak kapanış için küçük bir gecikme
        });
    }

    // --------- Header Arama Alanı Micro-interaction ---------
    const headerSearch = document.querySelector('.aff-header-search input[type="text"]');
    if(headerSearch) {
        headerSearch.addEventListener('focus', function() {
            this.parentElement.style.boxShadow = '0 2px 12px 0 rgba(120,86,255,0.14)';
        });
        headerSearch.addEventListener('blur', function() {
            this.parentElement.style.boxShadow = '';
        });
        headerSearch.addEventListener('keydown', function(e){
            if(e.key === "Enter" && !this.value.trim()) {
                window.showToast && showToast('searchEmpty', 'error');
                e.preventDefault();
            }
        });
    }

    // =========== TRENDYOL TARZI MEGA KATEGORİ MENÜ: TIKLAMA İLE AÇILIR/KAPANIR VE ALT KATEGORİ GÖSTERİMİ ===========
    var toggle = document.getElementById('categoryStripToggle');
    var menu = document.getElementById('megaCategoryMenu');
    var sidebarLinks = document.querySelectorAll('.sidebar-link');
    var megaContent = document.getElementById('megaCategoryContent');
    if (toggle && menu) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        });
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
    }
    if (sidebarLinks && megaContent) {
        // altKategoriler değişkenini PHP'den JS'e aktarınız!
        var altKategoriler = window.altKategoriler || {};
        sidebarLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                sidebarLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                var categoryId = this.getAttribute('data-category');
                var html = '';
                if (altKategoriler[categoryId]) {
                    for (const group in altKategoriler[categoryId]) {
                        html += '<div class="dropdown-group">';
                        html += '<div class="dropdown-group-title">' + group + '</div><ul>';
                        altKategoriler[categoryId][group].forEach(function(item) {
                            html += '<li><a href="#" class="dropdown-link">' + item + '</a></li>';
                        });
                        html += '</ul></div>';
                    }
                } else {
                    html = '<div class="mega-category-dropdown-inner" style="color:#888;font-size:1.05em;">Kategori seçiniz veya alt kategori bulunamadı.</div>';
                }
                megaContent.innerHTML = html;
            });
        });
    }
});

// HTML escape
window.htmlspecialchars = function(str) {
    if (typeof str !== 'string') return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
};

// Favori için event delegation — Tüm .fav-btn ve .btn-favorite'larda her zaman çalışır
document.body.addEventListener('click', function(e) {
    const btn = e.target.closest('.fav-btn, .btn-favorite');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    const pid = btn.dataset.product;
    if (!pid) return;
    const icon = btn.querySelector('.icon-heart') || btn.querySelector('.fa-heart');
    fetch('favorites_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=toggle&product_id=' + encodeURIComponent(pid) + '&lang=' + encodeURIComponent(window.lang),
        credentials: 'include'
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            btn.classList.toggle('active', res.fav);
            if(icon) icon.classList.toggle('fas', res.fav);
            if (typeof showToast === "function") showToast(res.fav ? 'favAdded' : 'favRemoved');
        } else if(res.error === 'login_required') {
            if (typeof showToast === "function") showToast('favLogin', 'error');
            setTimeout(function() {
                window.location.href = "login.php?redirect=" + encodeURIComponent(window.location.pathname + window.location.search);
            }, 1200);
        } else {
            if (typeof showToast === "function") showToast('loadError', 'error');
        }
    })
    .catch(() => { if (typeof showToast === "function") showToast('loadError', 'error'); });
});

// ======================
// HEADER HIDE ON SCROLL
// ======================
(function(){
    let lastScroll = window.scrollY;
    const header = document.querySelector('.aff-header');
    let ticking = false;

    function onScroll() {
        const currentScroll = window.scrollY;
        if (currentScroll > lastScroll && currentScroll > 64) {
            header && header.classList.add('header-hide');
        } else {
            header && header.classList.remove('header-hide');
        }
        lastScroll = currentScroll;
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(onScroll);
            ticking = true;
        }
    });
})();