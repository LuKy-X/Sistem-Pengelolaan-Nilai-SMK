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

   Every answer comes from `POST /tanya-ai`, which reads the live
   CMS tables server-side. The widget owns presentation only:
   open/close, optimistic echo, typing indicator, suggestion chips
   and a tiny safe renderer for the `**bold**` / newline subset the
   backend emits (no innerHTML, so a CMS value can never inject
   markup into the page).
   ========================================================= */
function initAiChat() {
    var panel     = document.getElementById('aiChatPanel');
    var fab       = document.getElementById('aiChatFab');
    var resetBtn  = document.getElementById('aiChatReset');
    var resetTop  = document.getElementById('aiChatResetTop');
    var closeBtn  = document.getElementById('aiChatClose');
    var form      = document.getElementById('aiChatForm');
    var input     = document.getElementById('aiChatInput');
    var list      = document.getElementById('aiChatMessages');
    var suggestionBar = document.getElementById('aiChatSuggestions');

    if (!panel || !fab || !list || !form || !input) return;

    var replyUrl  = panel.dataset.replyUrl;
    var csrfToken = panel.dataset.csrf;
    var busy      = false;

    var MIN_MESSAGE_LENGTH = 2;
    var MIN_TYPING_MS = 420;

    /* ---------- rendering helpers ---------- */

    function scrollToLatest() {
        list.scrollTop = list.scrollHeight;
    }

    function bubbleClass(who) {
        return 'ai-chat-bubble ai-chat-bubble--' + who;
    }

    var BOT_ICON = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="4" y="7" width="16" height="12" rx="4"/><path d="M8 7V5a4 4 0 018 0v2"/></svg>';

    /**
     * Build a message row. `reply` may contain newlines and `**bold**`
     * markers; everything is appended as text nodes, never as HTML.
     */
    function addMessage(text, who, links) {
        var row = document.createElement('div');
        row.className = 'ai-chat-row ai-chat-row--' + who;

        if (who === 'bot') {
            var avatar = document.createElement('span');
            avatar.className = 'ai-chat-row__avatar';
            avatar.innerHTML = BOT_ICON;
            row.appendChild(avatar);
        }

        var bubble = document.createElement('div');
        bubble.className = bubbleClass(who);

        String(text).split('\n').forEach(function (line, index) {
            if (index > 0) bubble.appendChild(document.createElement('br'));
            appendRichLine(bubble, line);
        });

        if (links && links.length) {
            var linkWrap = document.createElement('div');
            linkWrap.className = 'ai-chat-links';

            links.forEach(function (link) {
                var anchor = document.createElement('a');
                anchor.href = link.url;
                anchor.textContent = link.label;
                anchor.rel = 'noopener';
                linkWrap.appendChild(anchor);
            });

            bubble.appendChild(linkWrap);
        }

        row.appendChild(bubble);
        list.appendChild(row);
        scrollToLatest();

        return row;
    }

    /** Split on `**bold**` and append text/strong nodes only. */
    function appendRichLine(target, line) {
        var parts = line.split(/\*\*/);

        parts.forEach(function (part, index) {
            if (part === '') return;

            if (index % 2 === 1) {
                var strong = document.createElement('strong');
                strong.textContent = part;
                target.appendChild(strong);
            } else {
                target.appendChild(document.createTextNode(part));
            }
        });
    }

    function showTyping() {
        var row = document.createElement('div');
        row.className = 'ai-chat-row ai-chat-row--bot';
        row.dataset.typing = 'true';

        var avatar = document.createElement('span');
        avatar.className = 'ai-chat-row__avatar';
        avatar.innerHTML = BOT_ICON;
        row.appendChild(avatar);

        var bubble = document.createElement('div');
        // `ai-chat-typing` is what drives the three bouncing dots in CSS.
        bubble.className = bubbleClass('bot') + ' ai-chat-typing';
        bubble.setAttribute('aria-label', 'Asisten sedang mengetik');

        [0, 1, 2].forEach(function () {
            bubble.appendChild(document.createElement('span'));
        });

        row.appendChild(bubble);
        list.appendChild(row);
        scrollToLatest();

        return row;
    }

    function removeTyping(node) {
        if (node && node.parentNode) node.parentNode.removeChild(node);
    }

    function setBusy(value) {
        busy = value;
        var send = form.querySelector('button[type="submit"]');
        if (send) {
            send.disabled = value;
            send.setAttribute('aria-label', value ? 'Menunggu jawaban' : 'Kirim pertanyaan');
        }
        input.disabled = value;
    }

    /* ---------- suggestion chips ---------- */

    function renderSuggestions(items) {
        if (!suggestionBar) return;

        suggestionBar.innerHTML = '';

        (items || []).forEach(function (label) {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'ai-chat-suggestion';
            chip.textContent = label;
            chip.addEventListener('click', function () {
                if (busy) return;
                ask(label);
            });
            suggestionBar.appendChild(chip);
        });

        suggestionBar.hidden = !items || !items.length;
    }

    /* ---------- network ---------- */

    function post(url, payload) {
        var body = new FormData();
        body.append('_token', csrfToken);
        Object.keys(payload).forEach(function (key) { body.append(key, payload[key]); });

        return fetch(url, {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                if (!response.ok) {
                    var message = (data.errors && data.errors.message && data.errors.message[0])
                        || (data.message || 'Maaf, layanan sedang tidak tersedia.');

                    throw new Error(message);
                }

                return data;
            });
        });
    }

    /** Keep the dots on screen long enough to read, even on a fast reply. */
    function minimumDelay(startedAt) {
        var elapsed = Date.now() - startedAt;

        return elapsed < MIN_TYPING_MS
            ? new Promise(function (resolve) { window.setTimeout(resolve, MIN_TYPING_MS - elapsed); })
            : Promise.resolve();
    }

    function ask(question) {
        if (busy) return;

        var value = String(question).trim();

        // Guard the length here so a stray keystroke never becomes a round trip
        // that comes back as a validation error.
        if (value.length < MIN_MESSAGE_LENGTH) {
            addMessage('Tulis pertanyaan yang sedikit lebih lengkap ya, minimal ' + MIN_MESSAGE_LENGTH + ' huruf.', 'bot');
            input.focus();
            scrollToLatest();
            return;
        }

        addMessage(value, 'user');
        input.value = '';

        var typing = showTyping();
        var startedAt = Date.now();

        setBusy(true);
        renderSuggestions([]);

        post(replyUrl, { message: value })
            .then(function (data) {
                return minimumDelay(startedAt).then(function () { return data; });
            })
            .then(function (data) {
                removeTyping(typing);
                addMessage(data.reply || 'Maaf, aku belum bisa menjawab itu.', 'bot', data.links);
                renderSuggestions(data.suggestions);
            })
            .catch(function (error) {
                return minimumDelay(startedAt).then(function () { throw error; });
            })
            .catch(function (error) {
                removeTyping(typing);
                addMessage(error.message || 'Koneksi ke server terputus. Coba beberapa saat lagi.', 'bot');
                renderSuggestions([]);
            })
            .then(function () {
                setBusy(false);
                input.focus();
                scrollToLatest();
            });
    }

    /* ---------- lifecycle ---------- */

    function greet() {
        list.innerHTML = '';

        // Render a local greeting immediately so the panel never opens empty,
        // then let the backend replace it with the real opening message.
        addMessage('Menghubungkan ke data sekolah...', 'bot');

        fetch(panel.dataset.openingUrl, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        })
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (data) {
                if (!data || !data.reply) return;
                list.innerHTML = '';
                addMessage(data.reply, 'bot');
                renderSuggestions(data.suggestions);
            })
            .catch(function () {
                if (!list.querySelector('.ai-chat-bubble')) {
                    list.innerHTML = '';
                    addMessage('Halo! Saya asisten virtual sekolah. Silakan tulis pertanyaanmu.', 'bot');
                }
            });
    }

    function openPanel() {
        if (!panel.hidden) return;

        panel.hidden = false;
        fab.setAttribute('aria-expanded', 'true');
        fab.setAttribute('aria-label', 'Tutup chat AI');
        if (resetBtn) resetBtn.classList.add('is-visible');

        if (!list.children.length) greet();

        window.setTimeout(function () { input.focus(); }, 180);
    }

    function closePanel() {
        if (panel.hidden) return;

        panel.hidden = true;
        fab.setAttribute('aria-expanded', 'false');
        fab.setAttribute('aria-label', 'Buka chat AI');
        if (resetBtn) resetBtn.classList.remove('is-visible');
    }

    fab.addEventListener('click', function () { panel.hidden ? openPanel() : closePanel(); });
    if (closeBtn) closeBtn.addEventListener('click', closePanel);
    if (resetBtn) resetBtn.addEventListener('click', greet);
    if (resetTop) resetTop.addEventListener('click', greet);

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var value = input.value.trim();
        if (!value || busy) return;

        ask(value);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.hidden) closePanel();
    });
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
