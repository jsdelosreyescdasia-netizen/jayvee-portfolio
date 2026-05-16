const year = document.querySelector("#year");
const buttons = document.querySelectorAll(".filter-button");
const cards = document.querySelectorAll(".project-card");
const lightbox = document.querySelector("#screenshot-lightbox");
const lightboxImage = lightbox?.querySelector("img");
const lightboxCaption = lightbox?.querySelector("p");
const lightboxClose = lightbox?.querySelector(".lightbox-close");
const screenshotImages = document.querySelectorAll(".lightbox-image");
const projectModal = document.querySelector("#project-modal");
const projectModalTitle = document.querySelector("#modal-title");
const projectModalType = document.querySelector("#modal-type");
const projectModalDescription = document.querySelector("#modal-description");
const projectModalMeta = document.querySelector("#modal-meta");
const projectModalActions = document.querySelector("#modal-actions");
const projectModalClose = projectModal?.querySelector(".modal-close");
const projectOpenButtons = document.querySelectorAll("[data-project-open]");
const revealItems = document.querySelectorAll(
  ".section, .stats article, .project-card, .project-detail, .about-grid article, .tech-stack article, .skill-list span, .timeline article, .contact"
);

const projects = {
  cms: {
    type: "Laravel CMS Website",
    title: "Company Website CMS",
    description:
      "A content-managed company website built with Laravel, Blade, and MySQL. It gives admins a practical way to update pages, service content, contact details, and public website information without changing code.",
    role: "Developer, CMS structure, admin pages, content flow",
    features: "Public pages, admin authentication, content editing, contact handling",
    tech: "Laravel, PHP, Blade, MySQL, SCSS",
    proof: "Live demo and screen-recorded CMS walkthrough",
    links: [
      ["Live Demo", "https://web-production-a45aa.up.railway.app/"],
      ["View Video", "#company-cms-video"],
    ],
  },
  dtr: {
    type: "Laravel Attendance System",
    title: "Daily Time Record System",
    description:
      "A DTR system for recording employee attendance and organizing daily work activity. It includes employee time tracking, task logging, leave requests, and admin monitoring screens.",
    role: "Developer, attendance workflow, admin dashboard",
    features: "Clock in/out, task logs, leave requests, employee monitoring",
    tech: "Laravel, PHP, MySQL, Blade",
    proof: "Live demo and redacted screenshots",
    links: [
      ["Live Demo", "http://cdasiadtrsystem.page.gd/login"],
      ["Screenshots", "#dtr-system"],
    ],
  },
  shop: {
    type: "Laravel E-Commerce Website",
    title: "JSD Shop",
    description:
      "An e-commerce website for browsing products, adding items to cart, checking out, tracking orders, and managing products and stock through a Laravel CMS.",
    role: "Developer, storefront, order flow, product CMS",
    features: "Product catalog, cart, checkout, order tracking, stock management",
    tech: "Laravel, PHP, MySQL, Blade, SCSS",
    proof: "Live demo and ecommerce screen recording",
    links: [
      ["Live Demo", "https://web-production-47122.up.railway.app/"],
      ["View Video", "#jsd-shop-video"],
    ],
  },
  report: {
    type: "Laravel Report System",
    title: "Daily Report System",
    description:
      "A reporting platform for collecting daily employee outputs and reviewing weekly or monthly summaries. It supports search, employee report views, table summaries, and CSV export.",
    role: "Developer, report tables, search, export workflow",
    features: "Daily reports, weekly summaries, monthly summaries, CSV export",
    tech: "Laravel, PHP, MySQL, Blade",
    proof: "Live demo and redacted screenshots",
    links: [
      ["Live Demo", "https://app-production-aed1.up.railway.app/login"],
      ["Screenshots", "#daily-report-system"],
    ],
  },
};

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

const closeProjectModal = () => {
  if (!projectModal) return;

  projectModal.classList.remove("is-open");
  projectModal.setAttribute("aria-hidden", "true");
};

const openProjectModal = (projectKey) => {
  const project = projects[projectKey];
  if (
    !project ||
    !projectModal ||
    !projectModalTitle ||
    !projectModalType ||
    !projectModalDescription ||
    !projectModalMeta ||
    !projectModalActions
  ) {
    return;
  }

  projectModalType.textContent = project.type;
  projectModalTitle.textContent = project.title;
  projectModalDescription.textContent = project.description;
  projectModalMeta.innerHTML = [
    ["My Role", project.role],
    ["Main Features", project.features],
    ["Tech Stack", project.tech],
    ["Project Proof", project.proof],
  ]
    .map(([label, value]) => `<div><strong>${label}</strong><span>${value}</span></div>`)
    .join("");
  projectModalActions.innerHTML = project.links
    .map(([label, href]) => {
      const isExternal = href.startsWith("http");
      return `<a href="${href}" ${isExternal ? 'target="_blank" rel="noreferrer"' : ""}>${label}</a>`;
    })
    .join("");

  projectModal.classList.add("is-open");
  projectModal.setAttribute("aria-hidden", "false");
  projectModalClose?.focus();
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

projectOpenButtons.forEach((button) => {
  button.addEventListener("click", () => openProjectModal(button.dataset.projectOpen));
});

lightboxClose?.addEventListener("click", closeLightbox);
projectModalClose?.addEventListener("click", closeProjectModal);

lightbox?.addEventListener("click", (event) => {
  if (event.target === lightbox) closeLightbox();
});

projectModal?.addEventListener("click", (event) => {
  if (event.target === projectModal) closeProjectModal();
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeLightbox();
    closeProjectModal();
  }
});
