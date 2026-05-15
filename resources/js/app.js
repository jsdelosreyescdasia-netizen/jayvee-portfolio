document.querySelector('[data-nav-toggle]')?.addEventListener('click', () => {
    document.querySelector('[data-nav-links]')?.classList.toggle('is-open');
});

const siteHeader = document.querySelector('.site-header');

if (siteHeader) {
    const updateHeaderState = () => {
        siteHeader.classList.toggle('is-scrolled', window.scrollY > 24);
    };

    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });
}

const navLinks = [...document.querySelectorAll('[data-nav-section]')];
const sections = [...document.querySelectorAll('[data-scroll-section]')];

if (navLinks.length && sections.length) {
    const activateNav = (sectionName) => {
        navLinks.forEach((link) => {
            link.classList.toggle('is-active', link.dataset.navSection === sectionName);
        });
    };

    const updateActiveSection = () => {
        const navOffset = 130;
        const current = sections.reduce((active, section) => {
            const top = section.getBoundingClientRect().top;

            return top <= navOffset ? section : active;
        }, sections[0]);

        activateNav(current.dataset.scrollSection);
    };

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            document.querySelector('[data-nav-links]')?.classList.remove('is-open');
            activateNav(link.dataset.navSection);
        });
    });

    updateActiveSection();
    window.addEventListener('scroll', updateActiveSection, { passive: true });
    window.addEventListener('resize', updateActiveSection);
}

const revealTargets = [
    '.section-heading',
    '.about-detail__mark',
    '.about-detail__copy',
    '.vision-grid article',
    '.home-client-card',
    '.home-brand-card',
    '.home-product-card',
    '.home-service-card',
    '.item-card',
    '.gallery-card',
    '.contact-card',
    '.contact-layout > *',
    '.contact-cta > *',
    '.review-card',
    '.footer-inner > *',
].join(',');

const revealItems = [...document.querySelectorAll(revealTargets)];
const revealMedia = [...document.querySelectorAll([
    '.about-detail__mark img',
    '.home-card__media img',
    '.card-media img',
    '.gallery-card img',
    '.service-feature__media img',
    '.contact-cta__image img',
    '.contact-card img',
    '.footer-brand img',
].join(','))];
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if ((revealItems.length || revealMedia.length) && !prefersReducedMotion) {
    revealItems.forEach((item, index) => {
        item.classList.add('reveal-item');
        item.style.transitionDelay = `${Math.min(index % 4, 3) * 80}ms`;
    });

    revealMedia.forEach((item, index) => {
        item.classList.add('reveal-media');
        item.style.transitionDelay = `${Math.min(index % 3, 2) * 90}ms`;
    });

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -12% 0px',
        threshold: 0.12,
    });

    [...revealItems, ...revealMedia].forEach((item) => revealObserver.observe(item));
} else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
    revealMedia.forEach((item) => item.classList.add('is-visible'));
}

const heroSlider = document.querySelector('[data-hero-slider]');

if (heroSlider) {
    const slides = [...heroSlider.querySelectorAll('[data-hero-slide]')];
    const dots = [...heroSlider.querySelectorAll('[data-hero-dot]')];
    const previous = heroSlider.querySelector('[data-hero-prev]');
    const next = heroSlider.querySelector('[data-hero-next]');
    let activeIndex = 0;
    let timer;

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('is-active', slideIndex === activeIndex);
        });

        dots.forEach((dot, dotIndex) => {
            dot.classList.toggle('is-active', dotIndex === activeIndex);
        });
    };

    const start = () => {
        if (slides.length < 2) {
            return;
        }

        timer = window.setInterval(() => showSlide(activeIndex + 1), 5500);
    };

    const restart = () => {
        window.clearInterval(timer);
        start();
    };

    previous?.addEventListener('click', () => {
        showSlide(activeIndex - 1);
        restart();
    });

    next?.addEventListener('click', () => {
        showSlide(activeIndex + 1);
        restart();
    });

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            showSlide(Number(dot.dataset.heroDot));
            restart();
        });
    });

    document.addEventListener('visibilitychange', () => {
        window.clearInterval(timer);

        if (!document.hidden) {
            start();
        }
    });

    showSlide(0);
    start();
}

const galleryFilters = document.querySelector('[data-gallery-filters]');
const galleryGrid = document.querySelector('[data-gallery-grid]');

if (galleryFilters && galleryGrid) {
    const filterButtons = [...galleryFilters.querySelectorAll('[data-gallery-filter]')];
    const galleryCards = [...galleryGrid.querySelectorAll('[data-gallery-category]')];
    const emptyState = document.querySelector('[data-gallery-empty]');

    const filterGallery = (category) => {
        let visibleCount = 0;

        galleryCards.forEach((card) => {
            const isVisible = card.dataset.galleryCategory === category;
            card.classList.toggle('is-hidden', !isVisible);

            if (isVisible) {
                visibleCount += 1;
            }
        });

        filterButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.galleryFilter === category);
        });

        if (emptyState) {
            emptyState.hidden = visibleCount > 0;
        }
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => filterGallery(button.dataset.galleryFilter));
    });

    filterGallery(filterButtons[0]?.dataset.galleryFilter ?? 'store set-up');
}

const lightbox = document.querySelector('[data-lightbox]');

if (lightbox) {
    const image = lightbox.querySelector('[data-lightbox-image]');
    const caption = lightbox.querySelector('[data-lightbox-caption]');
    const close = lightbox.querySelector('[data-lightbox-close]');

    const closeLightbox = () => {
        lightbox.hidden = true;
        image.src = '';
    };

    document.querySelectorAll('[data-lightbox-src]').forEach((button) => {
        button.addEventListener('click', () => {
            image.src = button.dataset.lightboxSrc;
            image.alt = button.dataset.lightboxTitle;
            caption.textContent = button.dataset.lightboxTitle;
            lightbox.hidden = false;
        });
    });

    close?.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !lightbox.hidden) {
            closeLightbox();
        }
    });
}
