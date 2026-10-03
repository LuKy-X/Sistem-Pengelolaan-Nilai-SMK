document.addEventListener("DOMContentLoaded", function() {
  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var OVERLAY_DURATION = 420;
  var HOLD_BEFORE_REVEAL = 120;
  var safetyTimer = null;

  var overlay = document.getElementById("pageTransitionOverlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.className = "page-transition-overlay is-hidden";
    overlay.setAttribute("aria-hidden", "true");
    var bandsHtml = "";
    for (var i = 0; i < 16; i++) bandsHtml += '<span class="page-transition-band"></span>';
    overlay.innerHTML = '<div class="page-transition-diagonal">' + bandsHtml + "</div>";
    document.body.appendChild(overlay);
  }

  function hideOverlayInstantly() {
    if (safetyTimer) {
      clearTimeout(safetyTimer);
      safetyTimer = null;
    }
    if (!overlay) return;
    overlay.classList.remove("is-animated");
    overlay.classList.add("is-hidden");
    void overlay.offsetHeight;
  }

  function animateOverlayAway() {
    if (safetyTimer) {
      clearTimeout(safetyTimer);
      safetyTimer = null;
    }
    if (!overlay) return;
    if (reduceMotion) {
      hideOverlayInstantly();
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

    try {
      sessionStorage.setItem("playPageTransition", "1");
    } catch (err) {}

    overlay.classList.add("is-animated");
    overlay.classList.remove("is-hidden");

    // Watchdog fallback: If navigation does not cause page unload within 1.8s (e.g. file download or aborted navigation)
    if (safetyTimer) clearTimeout(safetyTimer);
    safetyTimer = setTimeout(function() {
      hideOverlayInstantly();
      try {
        sessionStorage.removeItem("playPageTransition");
      } catch (e) {}
    }, 1800);

    window.setTimeout(function() {
      window.location.href = destinationUrl;
    }, OVERLAY_DURATION);
  }

  // Detect if a link is an export, download, or file attachment
  function isExportOrDownload(a) {
    if (!a) return false;
    if (a.hasAttribute("download")) return true;
    if (a.getAttribute("data-no-transition") === "true") return true;
    if (a.classList.contains("no-transition")) return true;

    var href = a.getAttribute("href");
    if (!href) return false;

    // Check URL patterns for exports and common file downloads
    var lowerHref = href.toLowerCase();
    if (lowerHref.indexOf("/export") !== -1 || lowerHref.indexOf("export=") !== -1) return true;
    if (/\.(xlsx|xls|csv|pdf|zip|rar|doc|docx|png|jpg|jpeg)$/i.test(lowerHref.split("?")[0])) return true;
    if (lowerHref.indexOf("blob:") === 0 || lowerHref.indexOf("data:") === 0) return true;

    return false;
  }

  // Extract high-level module from a pathname
  // e.g. /guru/gradebooks/2/edit -> /guru/gradebooks
  // /guru/gradebooks -> /guru/gradebooks
  // /guru/penilaian -> /guru/penilaian
  // /guru/dashboard or /guru -> /guru/dashboard
  // /login -> /login
  function getRouteModule(pathname) {
    var path = (pathname || "").toLowerCase().replace(/\/+$/, "") || "/";
    if (path === "/" || path === "") return "/";
    if (path === "/login" || path.indexOf("/login") === 0) return "/login";
    if (path === "/logout" || path.indexOf("/logout") === 0) return "/logout";

    var parts = path.split("/").filter(Boolean); // e.g. ['guru', 'gradebooks', '2']
    if (parts[0] === "guru") {
      if (parts.length === 1 || parts[1] === "dashboard") {
        return "/guru/dashboard";
      }
      return "/guru/" + parts[1]; // e.g. /guru/gradebooks, /guru/penilaian, /guru/assessments
    }

    if (parts.length >= 2) {
      return "/" + parts[0] + "/" + parts[1];
    }
    return "/" + parts[0];
  }

  function isInternalPageLink(a) {
    var href = a.getAttribute("href");
    if (!href) return false;
    if (href.indexOf("mailto:") === 0 || href.indexOf("tel:") === 0 || href.indexOf("javascript:") === 0) return false;
    if (a.target && a.target !== "" && a.target !== "_self") return false;
    if (isExportOrDownload(a)) return false;
    if (a.origin !== window.location.origin) return false;
    return true;
  }

  // Determine if a link navigation should trigger the page transition animation
  function shouldAnimateTransition(a) {
    if (!isInternalPageLink(a)) return false;
    if (isExportOrDownload(a)) return false;

    // Explicit override attributes
    if (a.getAttribute("data-no-transition") === "true") return false;
    if (a.getAttribute("data-transition") === "true") return true;

    var currentModule = getRouteModule(window.location.pathname);
    var targetModule = getRouteModule(a.pathname);

    // If destination is in the same module, DO NOT animate! (Sub-pages, tabs, details, edit, create)
    if (currentModule === targetModule) {
      return false;
    }

    // Navigating between different top-level modules (e.g. gradebooks -> penilaian, or /guru -> /login)
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

    // NEVER intercept export or file downloads
    if (isExportOrDownload(a)) {
      return;
    }

    // Only animate if transition is between major sections
    if (shouldAnimateTransition(a)) {
      e.preventDefault();
      coverAndNavigate(a.href);
    }
    // If not a major transition, let the browser perform standard instant navigation!
  });

  // Handle bfcache restore and browser Back / Forward buttons
  window.addEventListener("pageshow", function(event) {
    // If page is restored from Back-Forward cache (bfcache)
    if (event.persisted) {
      animateOverlayAway();
      try {
        sessionStorage.removeItem("playPageTransition");
      } catch (err) {}
      return;
    }

    // Normal load: check if we came from a major transition
    var shouldPlay = false;
    try {
      shouldPlay = (sessionStorage.getItem("playPageTransition") === "1");
      sessionStorage.removeItem("playPageTransition");
    } catch (err) {}

    if (shouldPlay && !reduceMotion) {
      window.setTimeout(animateOverlayAway, HOLD_BEFORE_REVEAL);
    } else {
      hideOverlayInstantly();
    }
  });

  window.addEventListener("popstate", function() {
    animateOverlayAway();
    try {
      sessionStorage.removeItem("playPageTransition");
    } catch (err) {}
  });

  window.addEventListener("pagehide", function() {
    if (safetyTimer) {
      clearTimeout(safetyTimer);
      safetyTimer = null;
    }
  });

  // Handle hash scrolling if present
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

  // Initial check: if no transition flag was set, ensure overlay is hidden immediately
  try {
    if (sessionStorage.getItem("playPageTransition") !== "1") {
      hideOverlayInstantly();
    }
  } catch (e) {
    hideOverlayInstantly();
  }
});
