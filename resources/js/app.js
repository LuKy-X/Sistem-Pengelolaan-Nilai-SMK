/**
 * Public site behaviour — ported from JHIC template and connected to Laravel backend.
 *
 * All features are progressive enhancements: pages render and remain usable
 * without JavaScript.
 */

/* =========================================================
   MOBILE NAV
   ========================================================= */
function initMobileNav() {
    document.querySelectorAll('[data-nav-toggle]').forEach(function (button) {
        var menu = document.getElementById(button.getAttribute('aria-controls'));
        if (!menu) return;

        var iconOpen  = button.querySelector('[data-nav-icon="open"]');
        var iconClose = button.querySelector('[data-nav-icon="close"]');

        function setState(open) {
            menu.classList.toggle('hidden', !open);
            button.setAttribute('aria-expanded', String(open));
            button.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
            if (iconOpen)  iconOpen.classList.toggle('hidden', open);
            if (iconClose) iconClose.classList.toggle('hidden', !open);
        }

        button.addEventListener('click', function () {
            setState(menu.classList.contains('hidden'));
        });

        menu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () { setState(false); });
        });

        var desktop = window.matchMedia('(min-width: 1024px)');
        desktop.addEventListener('change', function (e) {
            if (e.matches) setState(false);
        });
    });
}

/* =========================================================
   INDUSTRI PARTNER MARQUEE
   ========================================================= */
function initIndustriMarquee() {
    var track = document.querySelector('.industri-marquee-track');
    var wrap  = document.querySelector('.industri-marquee-wrap');
    if (!track || !wrap) return;

    var firstSet = track.querySelector('.industri-marquee-set');
    if (!firstSet) return;

    function fillTrack() {
        var setWidth = firstSet.getBoundingClientRect().width;
        if (setWidth <= 0) return;
        var needed = wrap.getBoundingClientRect().width + setWidth;
        var guard = 0;
        while (track.scrollWidth < needed && guard < 20) {
            var clone = firstSet.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.querySelectorAll('img').forEach(function (img) { img.alt = ''; });
            track.appendChild(clone);
            guard++;
        }
        track.style.setProperty('--industri-shift', setWidth + 'px');
    }

    fillTrack();

    var imgs    = track.querySelectorAll('img');
    var pending = 0;
    imgs.forEach(function (img) {
        if (!img.complete) {
            pending++;
            img.addEventListener('load', function () {
                pending--;
                if (pending <= 0) fillTrack();
            });
        }
    });

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(fillTrack, 150);
    });
}

/* =========================================================
   PAGE TRANSITION
   ========================================================= */
function initPageTransition() {
    var overlay = document.getElementById('pageTransitionOverlay');
    if (!overlay) return;

    // On first load — sweep the bands away
    overlay.classList.add('is-animated');
    requestAnimationFrame(function () {
        overlay.classList.add('is-hidden');
    });

    // On internal link click — sweep bands in, then navigate
    document.addEventListener('click', function (e) {
        var anchor = e.target.closest('a[href]');
        if (!anchor) return;

        var href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
        if (anchor.target === '_blank') return;

        try {
            var url = new URL(href, window.location.href);
            if (url.hostname !== window.location.hostname) return;
        } catch (_) {
            return;
        }

        e.preventDefault();
        overlay.classList.remove('is-hidden');
        setTimeout(function () {
            window.location.href = href;
        }, 420);
    });
}

/* =========================================================
   BERITA / ARTIKEL CATEGORY FILTER
   ========================================================= */
function initBeritaFilter() {
    var filterBtns  = document.querySelectorAll('.berita-filter-btn');
    var beritaCards = document.querySelectorAll('.berita-card[data-category]');
    var beritaEmpty = document.getElementById('beritaEmpty');
    if (!filterBtns.length || !beritaCards.length) return;

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');

            var filter       = btn.getAttribute('data-filter');
            var visibleCount = 0;

            beritaCards.forEach(function (card) {
                var cats = (card.getAttribute('data-category') || '').split(' ');
                var show = filter === 'all' || cats.indexOf(filter) !== -1;
                card.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            if (beritaEmpty) {
                beritaEmpty.classList.toggle('hidden', visibleCount !== 0);
            }
        });
    });
}

/* =========================================================
   PASSWORD TOGGLE
   ========================================================= */
function initPasswordToggle() {
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        var input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        button.addEventListener('click', function () {
            var isHidden  = input.type === 'password';
            input.type    = isHidden ? 'text' : 'password';
            button.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    });
}

/* =========================================================
   FORGOT PASSWORD HINT
   ========================================================= */
function initPasswordResetHint() {
    document.querySelectorAll('[data-password-reset-hint]').forEach(function (button) {
        var hint = document.getElementById(button.getAttribute('aria-controls'));
        if (!hint) return;

        button.addEventListener('click', function () {
            var isHidden = hint.hasAttribute('hidden');
            if (isHidden) hint.removeAttribute('hidden');
            else          hint.setAttribute('hidden', '');
            button.setAttribute('aria-expanded', String(isHidden));
        });
    });
}

/* =========================================================
   DEMO CREDENTIALS (dev only)
   ========================================================= */
function initDemoCredentials() {
    document.querySelectorAll('[data-demo-login]').forEach(function (button) {
        button.addEventListener('click', function () {
            var login    = document.getElementById('login');
            var password = document.getElementById('password');
            if (login)    login.value    = button.dataset.demoLogin    ?? '';
            if (password) password.value = button.dataset.demoPassword ?? '';
            document.querySelector('form button[type="submit"]')?.focus();
        });
    });
}

/* =========================================================
   AUTO-DISMISS FLASH BANNERS
   ========================================================= */
function initAutoDismissFlash() {
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (banner) {
        setTimeout(function () {
            banner.style.transition = 'opacity 400ms ease';
            banner.style.opacity    = '0';
            setTimeout(function () { banner.remove(); }, 400);
        }, 6000);
    });
}

/* =========================================================
   AI CHAT WIDGET
   ========================================================= */
function initAiChat() {
    var aiFab     = document.getElementById('aiChatFab');
    var aiReset   = document.getElementById('aiChatReset');
    var aiPanel   = document.getElementById('aiChatPanel');
    var aiClose   = document.getElementById('aiChatClose');
    var aiForm    = document.getElementById('aiChatForm');
    var aiInput   = document.getElementById('aiChatInput');
    var aiMessages = document.getElementById('aiChatMessages');

    if (!aiFab || !aiPanel) return;

    var aiGreeting = 'Halo! 👋 Aku asisten virtual SMK Negeri 2 Karanganyar. Ada yang bisa dibantu seputar PPDB, jurusan, PKL, atau produk unggulan sekolah?';

    function openPanel() {
        aiPanel.hidden = false;
        aiFab.setAttribute('aria-expanded', 'true');
        aiFab.setAttribute('aria-label', 'Tutup chat AI');
        if (aiReset) aiReset.classList.add('is-visible');
        setTimeout(function () { aiInput && aiInput.focus(); }, 150);
    }

    function closePanel() {
        aiPanel.hidden = true;
        aiFab.setAttribute('aria-expanded', 'false');
        aiFab.setAttribute('aria-label', 'Buka chat AI');
        if (aiReset) aiReset.classList.remove('is-visible');
    }

    aiFab.addEventListener('click', function () {
        aiPanel.hidden ? openPanel() : closePanel();
    });

    if (aiClose) aiClose.addEventListener('click', closePanel);

    function addMessage(text, who) {
        var bubble       = document.createElement('div');
        bubble.className = 'ai-chat-msg ai-chat-msg--' + who;
        bubble.textContent = text;
        aiMessages.appendChild(bubble);
        aiMessages.scrollTop = aiMessages.scrollHeight;
        return bubble;
    }

    function resetChat() {
        if (!aiMessages) return;
        aiMessages.innerHTML = '';
        addMessage(aiGreeting, 'bot');
        if (aiInput) { aiInput.value = ''; aiInput.focus(); }
    }

    if (aiReset) aiReset.addEventListener('click', resetChat);

    var aiFaq = [
        {
            keys:  ['ppdb', 'daftar', 'pendaftaran'],
            reply: 'Pendaftaran PPDB sudah dibuka! Cek alur, syarat, dan link pendaftaran online lengkap di bagian "PPDB" pada halaman ini.'
        },
        {
            keys:  ['jurusan', 'program keahlian'],
            reply: 'Kami punya 4 jurusan unggulan: Teknik Pemesinan, Teknik Pembuatan Kain, Teknik Ototronik, dan Rekayasa Perangkat Lunak. Detailnya ada di bagian "Jurusan Unggulan".'
        },
        {
            keys:  ['pkl', 'magang', 'karier', 'bkk', 'kerja'],
            reply: 'Info PKL, lowongan kerja, dan mitra industri bisa kamu lihat di bagian "PKL & Career Center".'
        },
        {
            keys:  ['produk', 'jasa', 'harga', 'katalog'],
            reply: 'Produk & jasa unggulan hasil karya siswa bisa kamu lihat di bagian "Produk Unggulan Sekolah", lengkap dengan deskripsi, fitur, dan harga.'
        },
        {
            keys:  ['alumni', 'lulusan'],
            reply: 'Lulusan kami banyak yang terserap kerja atau kuliah. Cerita alumni ada di bagian "Lulusan Terbaik".'
        },
        {
            keys:  ['kontak', 'telepon', 'email', 'alamat'],
            reply: 'Info kontak lengkap ada di bagian footer halaman ini, termasuk nomor telepon dan email sekolah.'
        },
    ];

    function getReply(message) {
        var lower = message.toLowerCase();
        for (var i = 0; i < aiFaq.length; i++) {
            for (var j = 0; j < aiFaq[i].keys.length; j++) {
                if (lower.indexOf(aiFaq[i].keys[j]) !== -1) return aiFaq[i].reply;
            }
        }
        return 'Terima kasih atas pertanyaanmu! Untuk info lebih detail, silakan jelajahi bagian PPDB, Jurusan, PKL & Career Center, atau Produk Unggulan Sekolah di halaman ini, ya.';
    }

    if (aiForm && aiInput) {
        aiForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var value = aiInput.value.trim();
            if (!value) return;

            addMessage(value, 'user');
            aiInput.value = '';

            var typing       = document.createElement('div');
            typing.className = 'ai-chat-msg ai-chat-msg--bot';
            typing.textContent = 'Mengetik...';
            aiMessages.appendChild(typing);
            aiMessages.scrollTop = aiMessages.scrollHeight;

            setTimeout(function () { typing.textContent = getReply(value); }, 600);
        });
    }
}

/* =========================================================
   GSAP ANIMATIONS + SCROLL REVEAL FALLBACK
   ========================================================= */
function initAnimations() {
    var prefersReducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
        : false;

    var hasGsap = typeof window.gsap !== 'undefined' && typeof window.ScrollTrigger !== 'undefined';

    if (hasGsap && !prefersReducedMotion) {
        document.documentElement.classList.add('gsap-ready');
        gsap.registerPlugin(ScrollTrigger);

        // `.gsap-ready .reveal, .gsap-ready .stagger-item` starts at opacity 0 in CSS,
        // so every one of those elements must be handed to a tween or it stays hidden.
        // Track what GSAP covers so orphans (e.g. a `.stagger-item` outside any
        // `.stagger-group`) can still be faded in below.
        var animated = [];

        // Reveal elements
        gsap.utils.toArray('.reveal').forEach(function (el) {
            animated.push(el);
            gsap.fromTo(el,
                { opacity: 0, y: 36 },
                {
                    opacity: 1, y: 0,
                    duration: 1.1,
                    ease: 'expo.out',
                    scrollTrigger: {
                        trigger: el,
                        start: 'top 88%',
                        toggleActions: 'play none none reverse',
                    },
                }
            );
        });

        // Stagger groups
        document.querySelectorAll('.stagger-group').forEach(function (group) {
            var items = group.querySelectorAll(':scope > .stagger-item, :scope > *');
            animated = animated.concat(Array.prototype.slice.call(items));
            gsap.fromTo(items,
                { opacity: 0, y: 40 },
                {
                    opacity: 1, y: 0,
                    duration: 1,
                    ease: 'expo.out',
                    stagger: 0.12,
                    scrollTrigger: {
                        trigger: group,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse',
                    },
                }
            );
        });

        // Safety net: reveal items no group picked up, so they can never stay invisible.
        var orphans = [];
        document.querySelectorAll('.reveal, .stagger-item').forEach(function (el) {
            if (animated.indexOf(el) === -1) orphans.push(el);
        });

        orphans.forEach(function (el) {
            gsap.fromTo(el,
                { opacity: 0, y: 28 },
                {
                    opacity: 1, y: 0,
                    duration: 0.9,
                    ease: 'expo.out',
                    scrollTrigger: {
                        trigger: el,
                        start: 'top 92%',
                        toggleActions: 'play none none reverse',
                    },
                }
            );
        });

        // Parallax layers
        gsap.utils.toArray('.parallax-layer').forEach(function (layer) {
            var speed   = parseFloat(layer.getAttribute('data-speed')) || 0.4;
            var section = layer.closest('.parallax-section') || layer.parentElement;
            gsap.to(layer, {
                yPercent: speed * 60,
                ease: 'none',
                scrollTrigger: {
                    trigger: section,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 0.6,
                },
            });
        });

        // Hero art parallax
        var heroArt = document.querySelector('.hero-art-parallax');
        if (heroArt) {
            gsap.to(heroArt, {
                yPercent: -12,
                ease: 'none',
                scrollTrigger: {
                    trigger: '#home',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: 0.6,
                },
            });
        }

        // Lulusan photo parallax
        var lulusanPhoto = document.querySelector('.lulusan-photo-parallax');
        if (lulusanPhoto) {
            gsap.set(lulusanPhoto, { scale: 1.15 });
            gsap.to(lulusanPhoto, {
                yPercent: -8,
                ease: 'none',
                scrollTrigger: {
                    trigger: '#lulusan-terbaik',
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 0.6,
                },
            });
        }

        // Number counters
        document.querySelectorAll('.counter').forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-target')) || 0;
            var suffix = el.getAttribute('data-suffix') || '';
            var proxy  = { val: 0 };

            ScrollTrigger.create({
                trigger: el,
                start: 'top 90%',
                once: true,
                onEnter: function () {
                    gsap.to(proxy, {
                        val: target,
                        duration: 1.6,
                        ease: 'power2.out',
                        onUpdate: function () {
                            el.textContent = Math.round(proxy.val) + suffix;
                        },
                    });
                },
            });
        });

    } else {
        // Fallback: IntersectionObserver
        var revealEls = document.querySelectorAll('.reveal, .stagger-item');
        if ('IntersectionObserver' in window && revealEls.length) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });
            revealEls.forEach(function (el) { io.observe(el); });
        } else {
            revealEls.forEach(function (el) { el.classList.add('is-visible'); });
        }

        // Counters — just set immediately
        document.querySelectorAll('.counter').forEach(function (el) {
            var target = el.getAttribute('data-target') || '0';
            var suffix = el.getAttribute('data-suffix') || '';
            el.textContent = target + suffix;
        });
    }
}

/* =========================================================
   FOOTER YEAR
   ========================================================= */
function initFooterYear() {
    var yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();
}

/* =========================================================
   INIT
   ========================================================= */
function init() {
    initFooterYear();
    initMobileNav();
    initIndustriMarquee();
    initPageTransition();
    initBeritaFilter();
    initPasswordToggle();
    initPasswordResetHint();
    initDemoCredentials();
    initAutoDismissFlash();
    initAiChat();
    initAnimations();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
