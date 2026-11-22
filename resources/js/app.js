import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    initFloatingPillNavbar();
    initElegantScrollReveals();
    initStudentWizard();
});

/**
 * Graceful Scroll-Triggered Reveal (IntersectionObserver) with subtle stagger
 */
function initElegantScrollReveals() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const targets = document.querySelectorAll('main section, main .honey-card, main .honey-card-strong, main .espresso-card');
    if (!targets.length || !('IntersectionObserver' in window)) return;

    const seen = new WeakSet();
    const observer = new IntersectionObserver((entries) => {
        let batchIndex = 0;
        entries.forEach((entry) => {
            if (entry.isIntersecting && !seen.has(entry.target)) {
                seen.add(entry.target);
                const delay = Math.min(batchIndex * 65, 260);
                entry.target.style.transitionDelay = `${delay}ms`;
                entry.target.classList.add('is-visible');
                batchIndex++;
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });

    targets.forEach((el) => {
        // Avoid double-wrapping nested cards inside already-observed sections
        if (el.parentElement && el.parentElement.closest('.reveal-ready')) return;
        const rect = el.getBoundingClientRect();
        el.classList.add('reveal-ready');
        if (rect.top < window.innerHeight * 0.92) {
            requestAnimationFrame(() => el.classList.add('is-visible'));
        } else {
            observer.observe(el);
        }
    });
}

/**
 * Expandable Floating Pill Navbar (Icon Orb -> Dynamic Horizontal Expansion):
 * - Default state: compact centered circular icon orb (is-shrunk) — NOT permanently stuck open
 * - Hover or click on the icon orb: smoothly expands horizontally revealing full WHOI.BUZZ logo & SVG icon links
 * - Mouse leave (when not pinned), outside click, or Escape: smoothly shrinks back into the centered icon orb
 */
function initFloatingPillNavbar() {
    const shell = document.getElementById('whoibuzz-floating-pill');
    const orbTrigger = document.getElementById('pill-orb-trigger');
    const mobileToggle = document.getElementById('pill-mobile-toggle');
    const mobileMenu = document.getElementById('pill-mobile-menu');

    if (!shell || !orbTrigger) return;

    let isHovered = false;
    let isPinnedOpen = false;
    let leaveTimer = null;

    const updatePillState = () => {
        const shouldExpand = isHovered || isPinnedOpen;
        shell.classList.toggle('is-shrunk', !shouldExpand);
        orbTrigger.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');

        if (!shouldExpand && mobileMenu) {
            mobileMenu.classList.remove('is-open');
            if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'false');
        }
    };

    // Hover expands the Floating Pill horizontally
    shell.addEventListener('mouseenter', () => {
        if (window.matchMedia('(hover: hover)').matches) {
            if (leaveTimer) clearTimeout(leaveTimer);
            isHovered = true;
            updatePillState();
        }
    });

    shell.addEventListener('mouseleave', () => {
        if (window.matchMedia('(hover: hover)').matches) {
            leaveTimer = setTimeout(() => {
                isHovered = false;
                updatePillState();
            }, 240);
        }
    });

    // Clicking the circular icon orb pins it expanded, or collapses it back to the icon orb
    orbTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        if (shell.classList.contains('is-shrunk') || !isPinnedOpen) {
            isPinnedOpen = true;
        } else {
            isPinnedOpen = false;
            isHovered = false;
        }
        updatePillState();
    });

    // Mobile menu toggle
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = mobileMenu.classList.toggle('is-open');
            mobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            isPinnedOpen = open;
            updatePillState();
        });
    }

    // Collapse back to icon orb on outside click or Escape
    document.addEventListener('click', (e) => {
        if (!shell.contains(e.target) && (!mobileMenu || !mobileMenu.contains(e.target))) {
            isPinnedOpen = false;
            isHovered = false;
            updatePillState();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            isPinnedOpen = false;
            isHovered = false;
            updatePillState();
        }
    });

    // Always start collapsed as the compact Floating Pill Icon Orb
    updatePillState();
}

/**
 * Progressive 3-Step Certificate Wizard + AI Smart Certificate Parser
 */
function initStudentWizard() {
    const form = document.getElementById('certificate-wizard-form');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('[data-wizard-step]'));
    const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
    const fileInput = document.getElementById('wizard-file-input');
    const parseBtn = document.getElementById('ai-parse-trigger');
    const parseStatus = document.getElementById('ai-parse-status');
    const hintInput = document.getElementById('wizard-hint-text');

    let currentStep = 1;

    const showStep = (stepNum) => {
        currentStep = stepNum;
        steps.forEach((el) => {
            el.classList.toggle('hidden', Number(el.dataset.wizardStep) !== stepNum);
        });
        indicators.forEach((el) => {
            const idx = Number(el.dataset.stepIndicator);
            el.classList.toggle('bg-[#F2AA00]', idx === stepNum);
            el.classList.toggle('text-[#4C2500]', idx === stepNum);
            el.classList.toggle('bg-[#4C2500]/10', idx !== stepNum);
            el.classList.toggle('text-[#4C2500]/70', idx !== stepNum);
        });
    };

    form.querySelectorAll('[data-next-step]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const next = Number(btn.dataset.nextStep);
            if (next === 2 && fileInput && (!fileInput.files || fileInput.files.length === 0)) {
                alert('Unggah file sertifikat (PDF/JPG/PNG, maks 2 MB) terlebih dahulu di Langkah 1.');
                return;
            }
            showStep(next);
        });
    });

    form.querySelectorAll('[data-prev-step]').forEach((btn) => {
        btn.addEventListener('click', () => showStep(Number(btn.dataset.prevStep)));
    });

    if (parseBtn && fileInput) {
        parseBtn.addEventListener('click', async () => {
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Pilih file sertifikat (PDF/JPG/PNG) terlebih dahulu agar AI dapat mengekstraknya.');
                return;
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const fd = new FormData();
            fd.append('certificate_file', fileInput.files[0]);
            if (hintInput && hintInput.value) fd.append('hint_text', hintInput.value);

            parseBtn.disabled = true;
            parseBtn.textContent = 'Menganalisis via Laravel AI SDK...';
            if (parseStatus) {
                parseStatus.classList.remove('hidden');
                parseStatus.innerHTML = '<span class="text-[#CD7000] font-semibold">Menjalankan Structured Extraction & terjemahan SKPI dwibahasa...</span>';
            }

            try {
                const res = await fetch(parseBtn.dataset.parseUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: fd,
                });
                const json = await res.json();
                if (!res.ok || !json.ok) {
                    throw new Error(json.message || 'Gagal mengekstrak sertifikat.');
                }

                const p = json.parsed;
                const setVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el && val !== null && val !== undefined) el.value = val;
                };

                setVal('field-title-id', p.title_id);
                setVal('field-title-en', p.title_en);
                setVal('field-organizer', p.organizer);
                setVal('field-role', p.role_in_activity);
                setVal('field-date', p.activity_date);
                setVal('field-verify-url', p.external_verify_url || '');
                setVal('field-category-id', p.category_id);
                setVal('field-ai-confidence', p.confidence);

                if (parseStatus) {
                    parseStatus.innerHTML = `
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-full bg-[#4C2500] text-[#F9E7A9] font-bold">AI Confidence ${p.confidence}%</span>
                            <span class="px-2.5 py-1 rounded-full bg-[#F2AA00] text-[#4C2500] font-bold">Rekomendasi Tier ${p.trust_tier}</span>
                            <span class="text-[#4C2500] font-medium">${p.provider}</span>
                        </div>`;
                }

                showStep(2);
            } catch (err) {
                if (parseStatus) {
                    parseStatus.innerHTML = `<span class="text-red-700 font-semibold">${err.message}</span>`;
                }
            } finally {
                parseBtn.disabled = false;
                parseBtn.textContent = 'Ekstrak Otomatis dengan AI & Lanjut';
            }
        });
    }

    showStep(1);
}
