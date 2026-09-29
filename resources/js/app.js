import './bootstrap';

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');

    if (form && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});

const mediaInput = document.getElementById('media-file');
const mediaDropZone = document.getElementById('media-drop-zone');
const mediaFileName = document.getElementById('media-file-name');

if (mediaInput && mediaDropZone && mediaFileName) {
    const showMediaFileName = () => {
        mediaFileName.textContent = mediaInput.files?.[0]?.name || 'Arrastra un archivo aquí o selecciónalo.';
    };

    mediaInput.addEventListener('change', showMediaFileName);
    ['dragenter', 'dragover'].forEach((eventName) => mediaDropZone.addEventListener(eventName, (event) => {
        event.preventDefault();
        mediaDropZone.classList.add('border-cyan/80', 'bg-cyan/[.05]');
    }));
    ['dragleave', 'drop'].forEach((eventName) => mediaDropZone.addEventListener(eventName, (event) => {
        event.preventDefault();
        mediaDropZone.classList.remove('border-cyan/80', 'bg-cyan/[.05]');
    }));
    mediaDropZone.addEventListener('drop', (event) => {
        if (!event.dataTransfer?.files?.length) return;
        mediaInput.files = event.dataTransfer.files;
        showMediaFileName();
    });
}

const adminSidebar = document.querySelector('[data-admin-sidebar]');
const adminMenuToggle = document.querySelector('[data-admin-menu-toggle]');
const adminMenuBackdrop = document.querySelector('[data-admin-menu-backdrop]');

if (adminSidebar && adminMenuToggle && adminMenuBackdrop) {
    const desktopViewport = window.matchMedia('(min-width: 64rem)');
    const setAdminMenuOpen = (isOpen) => {
        document.documentElement.classList.toggle('admin-menu-open', isOpen);
        adminSidebar.classList.toggle('-translate-x-full', !isOpen);
        adminMenuBackdrop.classList.toggle('hidden', !isOpen);
        adminMenuToggle.setAttribute('aria-expanded', String(isOpen));
        adminMenuToggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
        adminMenuToggle.querySelector('span').textContent = isOpen ? '×' : '☰';

        if (isOpen) adminSidebar.querySelector('a')?.focus();
        else adminMenuToggle.focus();
    };

    adminMenuToggle.addEventListener('click', () => {
        setAdminMenuOpen(adminMenuToggle.getAttribute('aria-expanded') !== 'true');
    });
    adminMenuBackdrop.addEventListener('click', () => setAdminMenuOpen(false));
    adminSidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setAdminMenuOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (adminMenuToggle.getAttribute('aria-expanded') !== 'true') return;

        if (event.key === 'Escape') {
            setAdminMenuOpen(false);
            return;
        }

        if (event.key === 'Tab') {
            const focusable = [...adminSidebar.querySelectorAll('a[href], button:not([disabled])')];
            const first = focusable[0];
            const last = focusable.at(-1);

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });
    desktopViewport.addEventListener('change', (event) => {
        if (event.matches) setAdminMenuOpen(false);
    });
}

const socialNetworkSelect = document.querySelector('#network');
const socialIconPreview = document.querySelector('[data-social-icon-preview] span[aria-hidden="true"]');

if (socialNetworkSelect && socialIconPreview) {
    const socialIconSymbols = {
        facebook: 'f',
        instagram: '◎',
        linkedin: 'in',
        youtube: '▶',
        tiktok: '♪',
        x: '𝕏',
        whatsapp: '☎',
        other: '↗',
    };

    const updateSocialIcon = () => {
        socialIconPreview.textContent = socialIconSymbols[socialNetworkSelect.value] ?? socialIconSymbols.other;
    };

    socialNetworkSelect.addEventListener('change', updateSocialIcon);
    updateSocialIcon();
}

const heroCarousel = document.querySelector('[data-hero-carousel]');

if (heroCarousel) {
    const slides = [...heroCarousel.querySelectorAll('[data-hero-slide]')];
    const indicators = [...heroCarousel.querySelectorAll('[data-carousel-indicator]')];

    if (slides.length > 1) {
        let activeIndex = 0;
        let timer;
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const showSlide = (index) => {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === activeIndex;
                slide.hidden = !active;
                slide.setAttribute('aria-hidden', String(!active));
            });

            indicators.forEach((indicator, indicatorIndex) => {
                indicator.setAttribute('aria-current', String(indicatorIndex === activeIndex));
            });
        };

        const stopRotation = () => window.clearInterval(timer);
        const startRotation = () => {
            stopRotation();

            if (!reducedMotion && !document.hidden && !heroCarousel.matches(':hover') && !heroCarousel.contains(document.activeElement)) {
                timer = window.setInterval(() => showSlide(activeIndex + 1), 7000);
            }
        };

        heroCarousel.querySelector('[data-carousel-previous]')?.addEventListener('click', () => {
            showSlide(activeIndex - 1);
            startRotation();
        });
        heroCarousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => {
            showSlide(activeIndex + 1);
            startRotation();
        });
        indicators.forEach((indicator) => indicator.addEventListener('click', () => {
            showSlide(Number(indicator.dataset.carouselIndicator));
            startRotation();
        }));

        heroCarousel.addEventListener('pointerenter', stopRotation);
        heroCarousel.addEventListener('pointerleave', startRotation);
        heroCarousel.addEventListener('focusin', stopRotation);
        heroCarousel.addEventListener('focusout', (event) => {
            if (!heroCarousel.contains(event.relatedTarget)) startRotation();
        });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopRotation();
            else startRotation();
        });

        startRotation();
    }
}
