document.addEventListener("DOMContentLoaded", function() {
  // ===== Hamburger & Nav =====
  const hamburger = document.querySelector('.hamburger');
  const navMenu = document.querySelector('.nav-right');
  const closeBtn = document.querySelector('.close-btn');

  if (hamburger && navMenu) {
    hamburger.addEventListener('click', function(e) {
      e.stopPropagation();
      navMenu.classList.toggle('active');
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        navMenu.classList.remove('active');
      });
    }

    document.addEventListener('click', function(e) {
      if (!navMenu.contains(e.target) && !hamburger.contains(e.target)) {
        navMenu.classList.remove('active');
      }
    });
  }

  // ===== Dropdown =====
  const dropdown = document.querySelector(".dropdown");
  if (dropdown) {
    const btn = dropdown.querySelector(".btn-login");
    const menu = dropdown.querySelector(".dropdown-content");

    if (btn && menu) {
      // Klik tombol → toggle dropdown
      btn.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropdown.classList.toggle("open");
      });

      // Klik di luar → tutup dropdown
      document.addEventListener("click", function(e) {
        if (!dropdown.contains(e.target)) {
          dropdown.classList.remove("open");
        }
      });

      // ESC untuk menutup
      document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") dropdown.classList.remove("open");
      });

      // Hover di desktop (optional)
      if (window.matchMedia("(hover: hover) and (pointer: fine)").matches) {
        dropdown.addEventListener("mouseenter", () => {
          // tetap bisa di klik
        });
        dropdown.addEventListener("mouseleave", () => {
          if (!dropdown.classList.contains("open")) {
            // nothing
          }
        });
      }
    }
  }
});
