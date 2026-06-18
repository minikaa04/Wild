// js/main.js
// Global JavaScript: Modal, Mega Menü, Carousel, Toast, Auth, Tema, Kalp

'use strict';

/* ═══════════════════════════════
   MEGA MENÜ
═══════════════════════════════ */
const menuToggle   = document.getElementById('menu-toggle');
const megaMenu     = document.getElementById('mega-menu');
const menuOverlay  = document.getElementById('mega-menu-overlay');

function openMegaMenu()  { megaMenu.classList.add('open'); menuOverlay.classList.add('open'); document.body.style.overflow='hidden'; }
function closeMegaMenu() { megaMenu.classList.remove('open'); menuOverlay.classList.remove('open'); document.body.style.overflow=''; }

if (menuToggle) menuToggle.addEventListener('click', () => megaMenu.classList.contains('open') ? closeMegaMenu() : openMegaMenu());
if (menuOverlay) menuOverlay.addEventListener('click', closeMegaMenu);

function toggleSub(catId) {
    const sub = document.getElementById('sub-' + catId);
    if (!sub) return;
    sub.classList.toggle('open');
}

/* ═══════════════════════════════
   MODAL SİSTEMİ
═══════════════════════════════ */
function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
}

// ESC ile kapat
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
        document.body.style.overflow = '';
    }
});

// Overlay'a tıklayınca kapat
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
});

/* ═══════════════════════════════
   GİRİŞ / KAYIT SEKMELERİ
═══════════════════════════════ */
function switchTab(tabId) {
    const tabs   = ['tab-login','tab-register'];
    const panels = ['panel-login','panel-register'];

    tabs.forEach(t => {
        const el = document.getElementById(t);
        if (!el) return;
        el.style.color       = 'var(--text-muted)';
        el.style.borderBottom = '3px solid transparent';
        el.classList.remove('active');
    });

    const activeTab = document.getElementById(tabId);
    if (activeTab) {
        activeTab.style.color = 'var(--primary)';
        activeTab.style.borderBottom = '3px solid var(--primary)';
        activeTab.classList.add('active');
    }

    const panelId = tabId === 'tab-login' ? 'panel-login' : 'panel-register';
    panels.forEach(p => {
        const el = document.getElementById(p);
        if (el) el.style.display = (p === panelId) ? 'block' : 'none';
    });
}

// Sayfa yüklenince aktif sekmeyi vurgula
document.addEventListener('DOMContentLoaded', () => {
    const activeTab = document.querySelector('.modal-tab.active');
    if (activeTab) switchTab(activeTab.id);
});

/* ═══════════════════════════════
   GİRİŞ FORMU (AJAX)
═══════════════════════════════ */
async function handleLogin(e) {
    e.preventDefault();
    const btn   = document.getElementById('btn-login-submit');
    const email = document.getElementById('login-email').value.trim();
    const pass  = document.getElementById('login-password').value.trim();
    const errEl = document.getElementById('login-error');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;margin:0;"></span> Giriş yapılıyor...';

    try {
        const fd = new FormData();
        fd.append('action',   'login');
        fd.append('email',    email);
        fd.append('password', pass);

        const res  = await fetch(URL_ROOT + '/ajax/auth.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            closeModal('modal-login');
            showToast('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            errEl.style.display = 'block';
            errEl.textContent = '⚠️ ' + data.message;
            btn.disabled = false;
            btn.innerHTML = '<span>Giriş Yap</span>';
        }
    } catch(err) {
        errEl.style.display = 'block';
        errEl.textContent = '⚠️ Bağlantı hatası. Lütfen tekrar deneyin.';
        btn.disabled = false;
        btn.innerHTML = '<span>Giriş Yap</span>';
    }
}

/* ═══════════════════════════════
   KAYIT FORMU (AJAX)
═══════════════════════════════ */
async function handleRegister(e) {
    e.preventDefault();
    const btn     = document.getElementById('btn-register-submit');
    const errEl   = document.getElementById('register-error');
    const succEl  = document.getElementById('register-success');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;margin:0;"></span> Kaydediliyor...';

    try {
        const fd = new FormData();
        fd.append('action',     'register');
        fd.append('first_name', document.getElementById('reg-fname').value.trim());
        fd.append('last_name',  document.getElementById('reg-lname').value.trim());
        fd.append('email',      document.getElementById('reg-email').value.trim());
        fd.append('phone',      document.getElementById('reg-phone').value.trim());
        fd.append('password',   document.getElementById('reg-password').value.trim());

        const res  = await fetch('/ajax/auth.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            errEl.style.display  = 'none';
            succEl.style.display = 'block';
            succEl.textContent   = '🎉 ' + data.message;
            setTimeout(() => { closeModal('modal-login'); location.reload(); }, 1200);
        } else {
            succEl.style.display = 'none';
            errEl.style.display  = 'block';
            errEl.textContent    = '⚠️ ' + data.message;
            btn.disabled = false;
            btn.innerHTML = '<span>Üye Ol</span>';
        }
    } catch(err) {
        errEl.style.display = 'block';
        errEl.textContent   = '⚠️ Bağlantı hatası. Lütfen tekrar deneyin.';
        btn.disabled = false;
        btn.innerHTML = '<span>Üye Ol</span>';
    }
}

/* ═══════════════════════════════
   TOAST BİLDİRİMİ
═══════════════════════════════ */
function showToast(msg, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = msg;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3400);
}

/* ═══════════════════════════════
   HEADER DROPDOWN'LAR
═══════════════════════════════ */
function setupDropdown(btnId, dropId) {
    const btn  = document.getElementById(btnId);
    const drop = document.getElementById(dropId);
    if (!btn || !drop) return;

    btn.addEventListener('click', e => {
        e.stopPropagation();
        const isOpen = drop.classList.contains('open');
        document.querySelectorAll('.hdr-dropdown').forEach(d => d.classList.remove('open'));
        if (!isOpen) drop.classList.add('open');
    });
}
setupDropdown('btn-address', 'address-dropdown');
setupDropdown('btn-account', 'account-dropdown');

document.addEventListener('click', () => {
    document.querySelectorAll('.hdr-dropdown').forEach(d => d.classList.remove('open'));
});

/* ═══════════════════════════════
   ŞİFRE GÖSTER/GİZLE
═══════════════════════════════ */
function togglePwd(fieldId) {
    const f = document.getElementById(fieldId);
    if (!f) return;
    f.type = f.type === 'password' ? 'text' : 'password';
}

/* ═══════════════════════════════
   CAROUSEL (ANA SAYFA SLIDER)
═══════════════════════════════ */
function initCarousel(carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;

    const slides    = carousel.querySelector('.slides');
    const dotsWrap  = carousel.querySelector('.carousel-dots');
    const total     = carousel.querySelectorAll('.slide').length;
    let current     = 0;
    let autoTimer;

    // Dot'ları oluştur
    if (dotsWrap) {
        dotsWrap.innerHTML = '';
        for (let i = 0; i < total; i++) {
            const dot = document.createElement('span');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goTo(i));
            dotsWrap.appendChild(dot);
        }
    }

    function goTo(idx) {
        current = (idx + total) % total;
        slides.style.transform = `translateX(-${current * 100}%)`;
        if (dotsWrap) {
            dotsWrap.querySelectorAll('span').forEach((d, i) => d.classList.toggle('active', i === current));
        }
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    carousel.querySelector('.carousel-next')?.addEventListener('click', () => { clearInterval(autoTimer); next(); startAuto(); });
    carousel.querySelector('.carousel-prev')?.addEventListener('click', () => { clearInterval(autoTimer); prev(); startAuto(); });

    function startAuto() { autoTimer = setInterval(next, 5000); }
    startAuto();
}

/* ═══════════════════════════════
   KALP (FAVORİ) SİSTEMİ
═══════════════════════════════ */
async function toggleWishlist(btn, productId, isCloth) {
    // Giriş kontrolü
    const isLoggedIn = document.body.dataset.loggedIn === '1';
    if (!isLoggedIn) {
        openModal('modal-login');
        return;
    }

    // Giysi ise beden seçimi
    if (isCloth && !btn.classList.contains('active')) {
        openSizeModal(productId);
        return;
    }

    // Direkt favori ekle/çıkar
    await sendWishlistRequest(productId, null, btn);
}

function openSizeModal(productId) {
    const sizes = ['XS','S','M','L','XL','XXL'];
    const wrap  = document.getElementById('size-options');
    document.getElementById('size-modal-product-id').value = productId;
    if (!wrap) return;

    wrap.innerHTML = sizes.map(s =>
        `<button onclick="selectSize(this,'${s}')"
            style="min-width:52px;padding:10px;border:2px solid var(--border);border-radius:8px;
                   background:var(--surface);cursor:pointer;font-size:14px;font-weight:600;
                   transition:.2s;" class="size-btn">${s}</button>`
    ).join('');

    openModal('modal-size');
}

function selectSize(el, size) {
    document.querySelectorAll('.size-btn').forEach(b => {
        b.style.borderColor = 'var(--border)';
        b.style.background  = 'var(--surface)';
        b.style.color       = 'var(--text)';
    });
    el.style.borderColor = 'var(--primary)';
    el.style.background  = 'var(--primary-light)';
    el.style.color       = 'var(--primary)';
    el.dataset.selected  = 'true';
}

async function confirmSizeWishlist() {
    const productId  = document.getElementById('size-modal-product-id').value;
    const selectedBtn = document.querySelector('.size-btn[data-selected="true"]');
    if (!selectedBtn) { showToast('Lütfen bir beden seçin.', 'warning'); return; }

    closeModal('modal-size');
    const btn = document.querySelector(`.btn-heart[data-product-id="${productId}"]`);
    await sendWishlistRequest(productId, selectedBtn.textContent, btn);
}

async function sendWishlistRequest(productId, size, btn) {
    try {
        const fd = new FormData();
        fd.append('action',     'toggle');
        fd.append('product_id', productId);
        if (size) fd.append('size', size);

        const res  = await fetch(URL_ROOT + '/ajax/wishlist.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            if (btn) btn.classList.toggle('active', data.added);
            showToast(data.added ? '❤️ Favorilere eklendi!' : '💔 Favorilerden çıkarıldı.', data.added ? 'success' : 'info');
        }
    } catch(err) {
        showToast('Bir hata oluştu.', 'error');
    }
}

/* ═══════════════════════════════
   SEPETE EKLE (AJAX)
═══════════════════════════════ */
async function addToCart(productId, variantId, size) {
    try {
        const fd = new FormData();
        fd.append('action',     'add');
        fd.append('product_id', productId);
        if (variantId) fd.append('variant_id', variantId);
        if (size)      fd.append('size',       size);

        const res  = await fetch(URL_ROOT + '/ajax/cart_actions.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            showToast('🛒 Ürün sepete eklendi!', 'success');
            // Sepet rozetini güncelle
            const badge = document.querySelector('.cart-badge');
            if (badge) badge.textContent = data.cart_count;
            else {
                const cartIcon = document.querySelector('#btn-cart .hdr-icon');
                if (cartIcon) {
                    const newBadge = document.createElement('span');
                    newBadge.className   = 'cart-badge';
                    newBadge.textContent = data.cart_count;
                    cartIcon.appendChild(newBadge);
                }
            }
        } else {
            showToast('⚠️ ' + (data.message || 'Hata oluştu.'), 'error');
        }
    } catch(err) {
        showToast('Bağlantı hatası.', 'error');
    }
}

/* ═══════════════════════════════
   TEMA SİSTEMİ
═══════════════════════════════ */
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.cookie = `wild_theme=${theme};path=/;max-age=31536000`;
}

/* ═══════════════════════════════
   FORM VALİDASYON (HIGHLIGHT)
═══════════════════════════════ */
function validateRequired(formEl) {
    let valid = true;
    formEl.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            field.addEventListener('input', () => field.classList.remove('is-invalid'), { once: true });
            valid = false;
        }
    });
    return valid;
}

/* ═══════════════════════════════
   SAYFA YÜKLENİNCE BAŞLAT
═══════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    initCarousel('hero-carousel');
});
