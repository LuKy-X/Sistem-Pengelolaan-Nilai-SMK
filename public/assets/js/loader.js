document.addEventListener("DOMContentLoaded", function() {
  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var OVERLAY_DURATION = 400;
  var HOLD_BEFORE_REVEAL = 350;
  var overlay = document.getElementById("pageTransitionOverlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.className = "page-transition-overlay";
    overlay.setAttribute("aria-hidden", "true");
    var bandsHtml = "";
    for (var i = 0; i < 16; i++) bandsHtml += '<span class="page-transition-band"></span>';
    overlay.innerHTML = '<div class="page-transition-diagonal">' + bandsHtml + "</div>";
    document.body.appendChild(overlay);
  }
  function revealOverlay() {
    if (reduceMotion) {
      overlay.remove();
      return;
    }
    requestAnimationFrame(function() {
      overlay.classList.add("is-animated");
      requestAnimationFrame(function() {
        overlay.classList.add("is-hidden");
      });
    });
  }
  function coverAndNavigate(destinationUrl) {
    if (reduceMotion) {
      window.location.href = destinationUrl;
      return;
    }
    overlay.classList.add("is-animated");
    overlay.classList.remove("is-hidden");
    window.setTimeout(function() {
      window.location.href = destinationUrl;
    }, OVERLAY_DURATION);
  }
  function isInternalPageLink(a) {
    var href = a.getAttribute("href");
    if (!href) return false;
    if (href.indexOf("mailto:") === 0 || href.indexOf("tel:") === 0 || href.indexOf("javascript:") === 0) return false;
    if (a.target && a.target !== "" && a.target !== "_self") return false;
    if (a.hasAttribute("download")) return false;
    if (a.origin !== window.location.origin) return false;
    return true;
  }
  function closeMobileMenuInstantly() {
    var mMenu = document.getElementById("mobileMenu");
    if (!mMenu) return;
    mMenu.style.transition = "none";
    mMenu.classList.add("hidden");
    void mMenu.offsetHeight;
    requestAnimationFrame(function() {
      mMenu.style.transition = "";
    });
    var mMenuBtn = document.getElementById("menuBtn");
    var mIconMenu = document.getElementById("iconMenu");
    var mIconClose = document.getElementById("iconClose");
    if (mMenuBtn) mMenuBtn.setAttribute("aria-expanded", "false");
    if (mIconMenu) mIconMenu.classList.remove("hidden");
    if (mIconClose) mIconClose.classList.add("hidden");
  }
  document.addEventListener("click", function(e) {
    var a = e.target.closest("a");
    if (!a) return;
    var rawHref = a.getAttribute("href");
    if (!rawHref) return;
    if (rawHref === "#") {
      e.preventDefault();
      return;
    }
    if (rawHref.charAt(0) === "#") {
      var target = document.getElementById(rawHref.slice(1));
      if (target) {
        e.preventDefault();
        closeMobileMenuInstantly();
        target.scrollIntoView({
          behavior: reduceMotion ? "auto" : "smooth",
          block: "start"
        });
      }
      return;
    }
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (isInternalPageLink(a)) {
      e.preventDefault();
      coverAndNavigate(a.href);
    }
  });
  if (window.location.hash) {
    var landingTarget = document.getElementById(window.location.hash.slice(1));
    if (landingTarget) {
      window.setTimeout(function() {
        landingTarget.scrollIntoView({
          behavior: reduceMotion ? "auto" : "smooth",
          block: "start"
        });
        history.replaceState(null, "", window.location.pathname + window.location.search);
      }, reduceMotion ? 0 : HOLD_BEFORE_REVEAL + OVERLAY_DURATION + 50);
    }
  }
  window.setTimeout(revealOverlay, reduceMotion ? 0 : HOLD_BEFORE_REVEAL);
});
