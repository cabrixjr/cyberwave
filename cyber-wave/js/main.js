/* ================= MOBILE MENU ================= */
const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelector(".nav-links");

if (menuToggle) {
  menuToggle.addEventListener("click", () => {
    navLinks.style.display =
      navLinks.style.display === "flex" ? "none" : "flex";
  });
}

/* ================= PORTFOLIO FILTER ================= */
function filterProjects(category) {
  const projects = document.querySelectorAll(".project");
  projects.forEach(p => {
    p.style.display =
      category === "all" || p.classList.contains(category)
        ? "block"
        : "none";
  });
}

/* ================= SCROLL ANIMATIONS ================= */
const reveals = document.querySelectorAll(".reveal");

function revealOnScroll() {
  const trigger = window.innerHeight * 0.85;
  reveals.forEach(el => {
    const top = el.getBoundingClientRect().top;
    if (top < trigger) el.classList.add("active");
  });
}
window.addEventListener("scroll", revealOnScroll);
revealOnScroll();

/* ================= DARK / LIGHT MODE ================= */
const toggleTheme = document.querySelector("#themeToggle");

if (toggleTheme) {
  toggleTheme.addEventListener("click", () => {
    document.body.dataset.theme =
      document.body.dataset.theme === "light" ? "dark" : "light";
  });
}

/* ================= CURSOR GLOW (OPTIONAL) ================= */
const glow = document.createElement("div");
glow.className = "cursor-glow";
document.body.appendChild(glow);

document.addEventListener("mousemove", e => {
  glow.style.left = e.clientX + "px";
  glow.style.top = e.clientY + "px";
});
