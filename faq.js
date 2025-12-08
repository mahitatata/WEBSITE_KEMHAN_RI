<<<<<<< HEAD
document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add("faq-page");

    /* ======================
       MOBILE NAV
    ====================== */
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".faq-page .nav-right");
    const closeBtn = document.querySelector(".close-btn");

    if (hamburger) {
        hamburger.onclick = e => {
            e.stopPropagation();
            navMenu.classList.add("active");
        };

        closeBtn.onclick = () => navMenu.classList.remove("active");

        document.addEventListener("click", e => {
            if (!navMenu.contains(e.target) && !hamburger.contains(e.target)) {
                navMenu.classList.remove("active");
            }
        });
    }


/* ===============================
   ✅ FAQ ACCORDION UNIVERSAL (BERANDA + FAQS)
=============================== */
document.querySelectorAll(".faq-item").forEach(item => {
  const header =
    item.querySelector(".faq-header") ||   // untuk beranda
    item.querySelector(".faq-question");   // untuk halaman FAQ

  const answer = item.querySelector(".faq-answer");

  if (!header || !answer) return;

  // default tertutup (sinkron untuk dua halaman)
  answer.style.opacity = "0";
  answer.style.maxHeight = "0px";
  answer.style.overflow = "hidden";

header.addEventListener("click", function (e) {
  e.stopPropagation();

  // tutup faq lain
  document.querySelectorAll(".faq-item").forEach(i => {
    if (i !== item) {
      i.classList.remove("active");
      const otherAnswer = i.querySelector(".faq-answer");
      if (otherAnswer) {
        otherAnswer.style.maxHeight = "0px";
        otherAnswer.style.opacity = "0";
      }
    }
  });

  // ⬅️ WAJIB ADA
  item.classList.toggle("active");

  // animasi buka / tutup
  if (item.classList.contains("active")) {
    answer.style.opacity = "1";

    requestAnimationFrame(() => {
        answer.style.maxHeight = answer.scrollHeight + "px";
    });

} else {

    answer.style.maxHeight = "0px";
    answer.style.opacity = "0";

}
});

});

    /* ======================
       LIMIT FEATURED CHECKBOX
       (sementara masih dipakai)
    ====================== */
    const boxes = document.querySelectorAll('input[name="featured[]"]');

    if (boxes.length) {
        boxes.forEach(box => {
            box.addEventListener("change", () => {
                const checked = [...boxes].filter(i => i.checked);

                if (checked.length > 3) {
                    box.checked = false;
                    alert("Maksimal hanya 3 FAQ ditampilkan di Home!");
                }
            });
        });
    }
});

/* ======================
       FAQ EDIT
    ====================== */
document.querySelectorAll(".btn-edit").forEach(btn => {
    btn.addEventListener("click", function () {
        document.getElementById("editId").value = this.dataset.id;
        document.getElementById("editQuestion").value = this.dataset.q;
        document.getElementById("editAnswer").value = this.dataset.a;

        document.getElementById("editModal").style.display = "flex";
    });
});

document.getElementById("cancelEdit").addEventListener("click", function () {
    document.getElementById("editModal").style.display = "none";
});

// =====================
// OPEN EDIT MODAL
// =====================
document.querySelectorAll(".btn-edit").forEach(btn => {
    btn.addEventListener("click", () => {
        let id = btn.dataset.id;
        let q = btn.dataset.q;
        let a = btn.dataset.a;

        document.getElementById("editId").value = id;
        document.getElementById("editQuestion").value = q;
        document.getElementById("editAnswer").value = a;

        document.getElementById("editModal").classList.add("show");
    });
});

// =====================
// CANCEL EDIT
// =====================
document.getElementById("cancelEdit").addEventListener("click", () => {
    document.getElementById("editModal").classList.remove("show");
});

// =====================
// SAVE EDIT (AJAX)
// =====================
document.querySelector(".btn-save").addEventListener("click", () => {
    let id = document.getElementById("editId").value;
    let q = document.getElementById("editQuestion").value;
    let a = document.getElementById("editAnswer").value;

    fetch("update_faq.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&question=${encodeURIComponent(q)}&answer=${encodeURIComponent(a)}`
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {

            // UPDATE TEXT DI LAYAR
            let item = document.querySelector(`.faq-item[data-id="${id}"]`);

            item.querySelector(".faq-question").textContent = res.question;
            item.querySelector(".faq-answer").innerHTML = res.answer;

            // UPDATE DATA DI TOMBOL EDIT
            let editBtn = item.querySelector(".btn-edit");
            editBtn.dataset.q = res.question;
            editBtn.dataset.a = a;

            // TUTUP MODAL
            document.getElementById("editModal").classList.remove("show");
        }
    });
});

/* ======================
   FEATURE TOGGLE (⭐)
====================== */
document.querySelectorAll(".btn-feature").forEach(btn => {
    btn.addEventListener("click", function () {

        let id = this.dataset.id;

        fetch("feature_faq.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(res => {

            // Jika sudah 3 → munculkan popup modern
            if (res.status === "limit") {
                Swal.fire({
                    icon: "warning",
                    title: "Batas Tercapai",
                    text: "Maksimal hanya 3 FAQ bisa ditampilkan!",
                    confirmButtonColor: "#b30000",
                    confirmButtonText: "Mengerti"
                });
                return;
            }

            if (res.status === "success") {
                this.innerHTML = res.featured == 1 ? "⭐" : "☆";
            }
        });
    });
});

/* ===== OPTIONAL: tampilkan warning kalau sudah 3 ===== */
function updateFeaturedCount() {
    let stars = document.querySelectorAll(".btn-feature");
    let count = 0;

    stars.forEach(s => {
        if (s.innerHTML.includes("⭐")) count++;
    });

    console.log("Featured selected:", count);
}

document.querySelectorAll(".btn-feature").forEach(btn => {
    if (btn.innerText.trim() === "⭐") {
        btn.classList.add("active");
    }
});

=======
document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add("faq-page");

    /* ======================
       MOBILE NAV
    ====================== */
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".faq-page .nav-right");
    const closeBtn = document.querySelector(".close-btn");

    if (hamburger) {
        hamburger.onclick = e => {
            e.stopPropagation();
            navMenu.classList.add("active");
        };

        closeBtn.onclick = () => navMenu.classList.remove("active");

        document.addEventListener("click", e => {
            if (!navMenu.contains(e.target) && !hamburger.contains(e.target)) {
                navMenu.classList.remove("active");
            }
        });
    }


/* ===============================
   ✅ FAQ ACCORDION UNIVERSAL (BERANDA + FAQS)
=============================== */
document.querySelectorAll(".faq-item").forEach(item => {
  const header =
    item.querySelector(".faq-header") ||   // untuk beranda
    item.querySelector(".faq-question");   // untuk halaman FAQ

  const answer = item.querySelector(".faq-answer");

  if (!header || !answer) return;

  // default tertutup (sinkron untuk dua halaman)
  answer.style.opacity = "0";
  answer.style.maxHeight = "0px";
  answer.style.overflow = "hidden";

header.addEventListener("click", function (e) {
  e.stopPropagation();

  // tutup faq lain
  document.querySelectorAll(".faq-item").forEach(i => {
    if (i !== item) {
      i.classList.remove("active");
      const otherAnswer = i.querySelector(".faq-answer");
      if (otherAnswer) {
        otherAnswer.style.maxHeight = "0px";
        otherAnswer.style.opacity = "0";
      }
    }
  });

  // ⬅️ WAJIB ADA
  item.classList.toggle("active");

  // animasi buka / tutup
  if (item.classList.contains("active")) {
    answer.style.opacity = "1";

    requestAnimationFrame(() => {
        answer.style.maxHeight = answer.scrollHeight + "px";
    });

} else {

    answer.style.maxHeight = "0px";
    answer.style.opacity = "0";

}
});

});

    /* ======================
       LIMIT FEATURED CHECKBOX
       (sementara masih dipakai)
    ====================== */
    const boxes = document.querySelectorAll('input[name="featured[]"]');

    if (boxes.length) {
        boxes.forEach(box => {
            box.addEventListener("change", () => {
                const checked = [...boxes].filter(i => i.checked);

                if (checked.length > 3) {
                    box.checked = false;
                    alert("Maksimal hanya 3 FAQ ditampilkan di Home!");
                }
            });
        });
    }
});

/* ======================
       FAQ EDIT
    ====================== */
document.querySelectorAll(".btn-edit").forEach(btn => {
    btn.addEventListener("click", function () {
        document.getElementById("editId").value = this.dataset.id;
        document.getElementById("editQuestion").value = this.dataset.q;
        document.getElementById("editAnswer").value = this.dataset.a;

        document.getElementById("editModal").style.display = "flex";
    });
});

document.getElementById("cancelEdit").addEventListener("click", function () {
    document.getElementById("editModal").style.display = "none";
});

// =====================
// OPEN EDIT MODAL
// =====================
document.querySelectorAll(".btn-edit").forEach(btn => {
    btn.addEventListener("click", () => {
        let id = btn.dataset.id;
        let q = btn.dataset.q;
        let a = btn.dataset.a;

        document.getElementById("editId").value = id;
        document.getElementById("editQuestion").value = q;
        document.getElementById("editAnswer").value = a;

        document.getElementById("editModal").classList.add("show");
    });
});

// =====================
// CANCEL EDIT
// =====================
document.getElementById("cancelEdit").addEventListener("click", () => {
    document.getElementById("editModal").classList.remove("show");
});

// =====================
// SAVE EDIT (AJAX)
// =====================
document.querySelector(".btn-save").addEventListener("click", () => {
    let id = document.getElementById("editId").value;
    let q = document.getElementById("editQuestion").value;
    let a = document.getElementById("editAnswer").value;

    fetch("update_faq.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&question=${encodeURIComponent(q)}&answer=${encodeURIComponent(a)}`
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {

            // UPDATE TEXT DI LAYAR
            let item = document.querySelector(`.faq-item[data-id="${id}"]`);

            item.querySelector(".faq-question").textContent = res.question;
            item.querySelector(".faq-answer").innerHTML = res.answer;

            // UPDATE DATA DI TOMBOL EDIT
            let editBtn = item.querySelector(".btn-edit");
            editBtn.dataset.q = res.question;
            editBtn.dataset.a = a;

            // TUTUP MODAL
            document.getElementById("editModal").classList.remove("show");
        }
    });
});

/* ======================
   FEATURE TOGGLE (⭐)
====================== */
document.querySelectorAll(".btn-feature").forEach(btn => {
    btn.addEventListener("click", function () {

        let id = this.dataset.id;

        fetch("feature_faq.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(res => {

            // Jika sudah 3 → munculkan popup modern
            if (res.status === "limit") {
                Swal.fire({
                    icon: "warning",
                    title: "Batas Tercapai",
                    text: "Maksimal hanya 3 FAQ bisa ditampilkan!",
                    confirmButtonColor: "#b30000",
                    confirmButtonText: "Mengerti"
                });
                return;
            }

            if (res.status === "success") {
                this.innerHTML = res.featured == 1 ? "⭐" : "☆";
            }
        });
    });
});

/* ===== OPTIONAL: tampilkan warning kalau sudah 3 ===== */
function updateFeaturedCount() {
    let stars = document.querySelectorAll(".btn-feature");
    let count = 0;

    stars.forEach(s => {
        if (s.innerHTML.includes("⭐")) count++;
    });

    console.log("Featured selected:", count);
}

document.querySelectorAll(".btn-feature").forEach(btn => {
    if (btn.innerText.trim() === "⭐") {
        btn.classList.add("active");
    }
});

>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268
