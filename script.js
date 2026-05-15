const year = document.querySelector("#year");
const buttons = document.querySelectorAll(".filter-button");
const cards = document.querySelectorAll(".project-card");
const lightbox = document.querySelector("#screenshot-lightbox");
const lightboxImage = lightbox?.querySelector("img");
const lightboxCaption = lightbox?.querySelector("p");
const lightboxClose = lightbox?.querySelector(".lightbox-close");
const screenshotImages = document.querySelectorAll(".lightbox-image");
const revealItems = document.querySelectorAll(
  ".section, .stats article, .project-card, .project-detail, .about-grid article, .skill-list span, .timeline article, .contact"
);

year.textContent = new Date().getFullYear();

if ("IntersectionObserver" in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.14 }
  );

  revealItems.forEach((item, index) => {
    item.classList.add("reveal");
    item.style.setProperty("--reveal-delay", `${Math.min(index % 6, 5) * 70}ms`);
    observer.observe(item);
  });
} else {
  revealItems.forEach((item) => item.classList.add("is-visible"));
}

buttons.forEach((button) => {
  button.addEventListener("click", () => {
    const filter = button.dataset.filter;

    buttons.forEach((item) => item.classList.remove("active"));
    button.classList.add("active");

    cards.forEach((card) => {
      const shouldShow = filter === "all" || card.dataset.category === filter;
      card.classList.toggle("is-hidden", !shouldShow);
    });
  });
});

const closeLightbox = () => {
  if (!lightbox || !lightboxImage || !lightboxCaption) return;

  lightbox.classList.remove("is-open");
  lightbox.setAttribute("aria-hidden", "true");
  lightboxImage.src = "";
  lightboxImage.alt = "";
  lightboxCaption.textContent = "";
};

screenshotImages.forEach((image) => {
  image.addEventListener("click", () => {
    if (!lightbox || !lightboxImage || !lightboxCaption) return;

    lightboxImage.src = image.currentSrc || image.src;
    lightboxImage.alt = image.alt;
    lightboxCaption.textContent = image.closest("figure")?.querySelector("figcaption")?.textContent || image.alt;
    lightbox.classList.add("is-open");
    lightbox.setAttribute("aria-hidden", "false");
  });
});

lightboxClose?.addEventListener("click", closeLightbox);

lightbox?.addEventListener("click", (event) => {
  if (event.target === lightbox) closeLightbox();
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") closeLightbox();
});
