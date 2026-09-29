document.addEventListener("DOMContentLoaded", function() {
  var yearEl = document.getElementById("year");
  if (yearEl) {
    yearEl.textContent = (new Date).getFullYear();
  }
  var menuBtn = document.getElementById("menuBtn");
  var mobileMenu = document.getElementById("mobileMenu");
  var iconMenu = document.getElementById("iconMenu");
  var iconClose = document.getElementById("iconClose");
  if (menuBtn && mobileMenu && iconMenu && iconClose) {
    menuBtn.addEventListener("click", function() {
      var isOpen = !mobileMenu.classList.contains("hidden");
      mobileMenu.classList.toggle("hidden");
      iconMenu.classList.toggle("hidden");
      iconClose.classList.toggle("hidden");
      menuBtn.setAttribute("aria-expanded", String(!isOpen));
    });
    mobileMenu.querySelectorAll("a").forEach(function(a) {
      a.addEventListener("click", function() {
        mobileMenu.classList.add("hidden");
        iconMenu.classList.remove("hidden");
        iconClose.classList.add("hidden");
      });
    });
  }
  (function setupIndustriMarquee() {
    var track = document.querySelector(".industri-marquee-track");
    var wrap = document.querySelector(".industri-marquee-wrap");
    if (!track || !wrap) return;
    var firstSet = track.querySelector(".industri-marquee-set");
    if (!firstSet) return;
    function fillTrack() {
      var setWidth = firstSet.getBoundingClientRect().width;
      if (setWidth <= 0) return;
      var needed = wrap.getBoundingClientRect().width + setWidth;
      var guard = 0;
      while (track.scrollWidth < needed && guard < 20) {
        var clone = firstSet.cloneNode(true);
        clone.setAttribute("aria-hidden", "true");
        clone.querySelectorAll("img").forEach(function(img) {
          img.alt = "";
        });
        track.appendChild(clone);
        guard++;
      }
      track.style.setProperty("--industri-shift", setWidth + "px");
    }
    fillTrack();
    var imgs = track.querySelectorAll("img");
    var pending = 0;
    imgs.forEach(function(img) {
      if (!img.complete) {
        pending++;
        img.addEventListener("load", function() {
          pending--;
          if (pending <= 0) fillTrack();
        });
      }
    });
    var resizeTimer;
    window.addEventListener("resize", function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(fillTrack, 150);
    });
  })();
  var prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var hasGsap = typeof window.gsap !== "undefined" && typeof window.ScrollTrigger !== "undefined";
  if (hasGsap && !prefersReducedMotion) {
    document.documentElement.classList.add("gsap-ready");
    gsap.registerPlugin(ScrollTrigger);
    gsap.utils.toArray(".reveal").forEach(function(el) {
      gsap.fromTo(el, {
        opacity: 0,
        y: 36
      }, {
        opacity: 1,
        y: 0,
        duration: 1.1,
        ease: "expo.out",
        scrollTrigger: {
          trigger: el,
          start: "top 88%",
          toggleActions: "play none none reverse"
        }
      });
    });
    document.querySelectorAll(".stagger-group").forEach(function(group) {
      var items = group.querySelectorAll(":scope > .stagger-item, :scope > *");
      gsap.fromTo(items, {
        opacity: 0,
        y: 40
      }, {
        opacity: 1,
        y: 0,
        duration: 1,
        ease: "expo.out",
        stagger: .12,
        scrollTrigger: {
          trigger: group,
          start: "top 85%",
          toggleActions: "play none none reverse"
        }
      });
    });
    gsap.utils.toArray(".parallax-layer").forEach(function(layer) {
      var speed = parseFloat(layer.getAttribute("data-speed")) || .4;
      var section = layer.closest(".parallax-section") || layer.parentElement;
      gsap.to(layer, {
        yPercent: speed * 60,
        ease: "none",
        scrollTrigger: {
          trigger: section,
          start: "top bottom",
          end: "bottom top",
          scrub: .6
        }
      });
    });
    var heroArt = document.querySelector(".hero-art-parallax");
    if (heroArt) {
      gsap.to(heroArt, {
        yPercent: -12,
        ease: "none",
        scrollTrigger: {
          trigger: "#home",
          start: "top top",
          end: "bottom top",
          scrub: .6
        }
      });
    }
    var lulusanPhoto = document.querySelector(".lulusan-photo-parallax");
    if (lulusanPhoto) {
      gsap.set(lulusanPhoto, {
        scale: 1.15
      });
      gsap.to(lulusanPhoto, {
        yPercent: -8,
        ease: "none",
        scrollTrigger: {
          trigger: "#lulusan-terbaik",
          start: "top bottom",
          end: "bottom top",
          scrub: .6
        }
      });
    }
    document.querySelectorAll(".counter").forEach(function(el) {
      var target = parseFloat(el.getAttribute("data-target")) || 0;
      var suffix = el.getAttribute("data-suffix") || "";
      var proxy = {
        val: 0
      };
      ScrollTrigger.create({
        trigger: el,
        start: "top 90%",
        once: true,
        onEnter: function() {
          gsap.to(proxy, {
            val: target,
            duration: 1.6,
            ease: "power2.out",
            onUpdate: function() {
              el.textContent = Math.round(proxy.val) + suffix;
            }
          });
        }
      });
    });
  } else {
    var revealEls = document.querySelectorAll(".reveal, .stagger-item");
    if ("IntersectionObserver" in window && revealEls.length) {
      var io = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("show");
            io.unobserve(entry.target);
          }
        });
      }, {
        threshold: .15
      });
      revealEls.forEach(function(el) {
        io.observe(el);
      });
    } else {
      revealEls.forEach(function(el) {
        el.classList.add("show");
      });
    }
    document.querySelectorAll(".counter").forEach(function(el) {
      var target = el.getAttribute("data-target") || "0";
      var suffix = el.getAttribute("data-suffix") || "";
      el.textContent = target + suffix;
    });
  }
  var filterBtns = document.querySelectorAll(".berita-filter-btn");
  var beritaCards = document.querySelectorAll(".berita-card");
  var beritaEmpty = document.getElementById("beritaEmpty");
  if (filterBtns.length && beritaCards.length) {
    filterBtns.forEach(function(btn) {
      btn.addEventListener("click", function() {
        filterBtns.forEach(function(b) {
          b.classList.remove("is-active");
        });
        btn.classList.add("is-active");
        var filter = btn.getAttribute("data-filter");
        var visibleCount = 0;
        beritaCards.forEach(function(card) {
          var cats = (card.getAttribute("data-category") || "").split(" ");
          var show = filter === "all" || cats.indexOf(filter) !== -1;
          card.style.display = show ? "" : "none";
          if (show) visibleCount++;
        });
        if (beritaEmpty) {
          beritaEmpty.classList.toggle("hidden", visibleCount !== 0);
        }
      });
    });
  }
  var aiFab = document.getElementById("aiChatFab");
  var aiReset = document.getElementById("aiChatReset");
  var aiPanel = document.getElementById("aiChatPanel");
  var aiClose = document.getElementById("aiChatClose");
  var aiForm = document.getElementById("aiChatForm");
  var aiInput = document.getElementById("aiChatInput");
  var aiMessages = document.getElementById("aiChatMessages");
  var aiGreeting = "Halo! 👋 Aku asisten virtual SMK Negeri 2 Karanganyar. Ada yang bisa dibantu seputar PPDB, jurusan, PKL, atau produk unggulan sekolah?";
  function aiOpenPanel() {
    aiPanel.hidden = false;
    aiFab.setAttribute("aria-expanded", "true");
    aiFab.setAttribute("aria-label", "Tutup chat AI");
    if (aiReset) {
      aiReset.classList.add("is-visible");
    }
    setTimeout(function() {
      aiInput && aiInput.focus();
    }, 150);
  }
  function aiClosePanel() {
    aiPanel.hidden = true;
    aiFab.setAttribute("aria-expanded", "false");
    aiFab.setAttribute("aria-label", "Buka chat AI");
    if (aiReset) {
      aiReset.classList.remove("is-visible");
    }
  }
  if (aiFab && aiPanel) {
    aiFab.addEventListener("click", function() {
      if (aiPanel.hidden) {
        aiOpenPanel();
      } else {
        aiClosePanel();
      }
    });
  }
  if (aiClose) {
    aiClose.addEventListener("click", aiClosePanel);
  }
  function aiAddMessage(text, who) {
    var bubble = document.createElement("div");
    bubble.className = "ai-chat-msg ai-chat-msg--" + who;
    bubble.textContent = text;
    aiMessages.appendChild(bubble);
    aiMessages.scrollTop = aiMessages.scrollHeight;
    return bubble;
  }
  function aiResetChat() {
    if (!aiMessages) return;
    aiMessages.innerHTML = "";
    aiAddMessage(aiGreeting, "bot");
    if (aiInput) {
      aiInput.value = "";
      aiInput.focus();
    }
  }
  if (aiReset) {
    aiReset.addEventListener("click", aiResetChat);
  }
  var aiFaq = [ {
    keys: [ "ppdb", "daftar", "pendaftaran" ],
    reply: 'Pendaftaran PPDB 2026/2027 sudah dibuka! Cek alur, syarat, dan link pendaftaran online lengkap di bagian "PPDB" pada halaman ini.'
  }, {
    keys: [ "jurusan", "program keahlian" ],
    reply: 'Kami punya 4 jurusan unggulan: Teknik Pemesinan, Teknik Pembuatan Kain, Teknik Ototronik, dan Rekayasa Perangkat Lunak. Detailnya ada di bagian "Jurusan Unggulan".'
  }, {
    keys: [ "pkl", "magang", "karier", "bkk", "kerja" ],
    reply: 'Info PKL, lowongan kerja, dan mitra industri bisa kamu lihat di bagian "PKL & Career Center".'
  }, {
    keys: [ "produk", "jasa", "harga", "katalog" ],
    reply: 'Produk & jasa unggulan hasil karya siswa bisa kamu lihat di bagian "Produk Unggulan Sekolah", lengkap dengan deskripsi, fitur, dan harga.'
  }, {
    keys: [ "alumni", "lulusan" ],
    reply: '96% lulusan kami terserap kerja atau kuliah dalam 6 bulan. Cerita alumni ada di bagian "Lulusan Terbaik".'
  }, {
    keys: [ "kontak", "telepon", "email", "alamat" ],
    reply: "Kamu bisa hubungi kami di 0271-6498171 atau email smkn2kra97@gmail.com. Info selengkapnya ada di footer halaman ini."
  } ];
  function aiGetReply(message) {
    var lower = message.toLowerCase();
    for (var i = 0; i < aiFaq.length; i++) {
      for (var j = 0; j < aiFaq[i].keys.length; j++) {
        if (lower.indexOf(aiFaq[i].keys[j]) !== -1) {
          return aiFaq[i].reply;
        }
      }
    }
    return "Terima kasih atas pertanyaanmu! Untuk info lebih detail, silakan jelajahi bagian PPDB, Jurusan, PKL & Career Center, atau Produk Unggulan Sekolah di halaman ini, ya.";
  }
  if (aiForm && aiInput) {
    aiForm.addEventListener("submit", function(e) {
      e.preventDefault();
      var value = aiInput.value.trim();
      if (!value) return;
      aiAddMessage(value, "user");
      aiInput.value = "";
      var typing = document.createElement("div");
      typing.className = "ai-chat-msg ai-chat-msg--bot";
      typing.textContent = "Mengetik...";
      aiMessages.appendChild(typing);
      aiMessages.scrollTop = aiMessages.scrollHeight;
      setTimeout(function() {
        typing.textContent = aiGetReply(value);
      }, 600);
    });
  }
});
