document.addEventListener("DOMContentLoaded", function() {

  /* ============================
     HAMBURGER MENU
  ============================ */
  const hamburger = document.querySelector('.hamburger');
  const navMenu = document.querySelector('.nav-right');
  const closeBtn = document.querySelector('.close-btn');

  if (hamburger && navMenu) {
    hamburger.addEventListener('click', function(e) {
      e.stopPropagation();
      navMenu.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
      if (!navMenu.contains(e.target) && !hamburger.contains(e.target)) {
        navMenu.classList.remove('active');
      }
    });

    closeBtn?.addEventListener('click', function() {
      navMenu.classList.remove('active');
    });
  }

/* ============================
   FAQ ACCORDION (FIXED)
============================ */
const faqItems = document.querySelectorAll(".faq-item");

faqItems.forEach(item => {
  const header = item.querySelector(".faq-header");
  const answer = item.querySelector(".faq-answer");

  // Set default hidden
  answer.style.maxHeight = "0px";
  answer.style.overflow = "hidden";

  header.addEventListener("click", function (e) {
    e.stopPropagation();

    // Tutup semua FAQ lain
    faqItems.forEach(i => {
      if (i !== item) {
        i.classList.remove("active");
        i.querySelector(".faq-answer").style.maxHeight = "0px";
      }
    });

    // Buka/tutup yg diklik
    item.classList.toggle("active");
    answer.style.maxHeight = item.classList.contains("active")
      ? answer.scrollHeight + "px"
      : "0px";
  });
});

  /* ============================
     POPUP LOGIN
  ============================ */
  const popup = document.getElementById("loginPopup");
  const cancelBtn = document.querySelector(".popup-btn-cancel");

  function showLoginPopup(message = null) {
    if (!popup) return;

    const title = popup.querySelector("h2");
    const text  = popup.querySelector("p");

    title.textContent = message ? "Pemberitahuan" : "Anda belum login";
    text.textContent  = message ?? "Silakan login terlebih dahulu untuk membaca artikel ini.";

    popup.style.display = "flex";
    document.body.classList.add("popup-active");
  }

  function closePopup() {
    popup.style.display = "none";
    document.body.classList.remove("popup-active");
  }

  cancelBtn?.addEventListener("click", closePopup);



  /* ============================
     CARD KLIK → BUKA ARTIKEL
  ============================ */
  document.querySelectorAll('.article-card').forEach(card => {
    card.addEventListener('click', function() {
      const id = this.dataset.id;
      window.location.href = `komentar.php?id=${id}&from=beranda`;
    });
  });



  /* ============================
     SEARCH BAR
  ============================ */
  const searchForm = document.querySelector(".search-form");

  searchForm?.addEventListener("submit", function(e) {
    e.preventDefault();
    const q = document.querySelector("input[name='search']").value.trim().toLowerCase();
    if (q === "") return;

    if (q === "artikel") return (window.location.href = "artikel.php");
    if (q === "forum") return (window.location.href = "forum.php");

    fetch("redirect.php?q=" + encodeURIComponent(q))
      .then(res => res.json())
      .then(data => {
        if (data.status === "found") window.location.href = data.redirect;
        else showLoginPopup("Tidak ditemukan. Coba kata kunci lain.");
      });
  });

});
