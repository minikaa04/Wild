// js/product_mutator.js
// AJAX ürün varyasyon mutasyonu — sayfayı yenilemeden tüm verileri değiştirir

'use strict';

let selectedVariantId = null;

document.addEventListener('DOMContentLoaded', () => {
    // İlk varyasyonu aktif yap
    const firstThumb = document.querySelector('.thumb-btn');
    const firstSwatch = document.querySelector('.color-swatch');
    if (firstThumb)  firstThumb.style.borderColor  = 'var(--primary)';
    if (firstSwatch) firstSwatch.style.boxShadow   = '0 0 0 3px var(--primary)';

    // Gizli input'tan ilk varyant ID'yi al
    const hiddenVariant = document.getElementById('current-variant-id');
    if (hiddenVariant) selectedVariantId = hiddenVariant.value;
});

async function selectVariant(variantId) {
    if (selectedVariantId == variantId) return;

    // Thumbnail ve swatch seçim görselini güncelle
    document.querySelectorAll('.thumb-btn').forEach(t => {
        t.style.borderColor = 'transparent';
    });
    document.querySelectorAll('.color-swatch').forEach(s => {
        s.style.boxShadow = '0 0 0 1px var(--border)';
    });

    const activeThumb  = document.querySelector(`.thumb-btn[data-variant-id="${variantId}"]`);
    const activeSwatch = document.querySelector(`.color-swatch[data-variant-id="${variantId}"]`);
    if (activeThumb)  activeThumb.style.borderColor  = 'var(--primary)';
    if (activeSwatch) activeSwatch.style.boxShadow   = '0 0 0 3px var(--primary)';

    // Görsel geçiş animasyonu
    const mainImg = document.getElementById('main-product-img');
    if (mainImg) { mainImg.style.opacity = '0.3'; }

    try {
        const res  = await fetch(`/wild/ajax/get_product_variant.php?variant_id=${variantId}`);
        const data = await res.json();

        if (!data.success) return;

        // ─── DOM Mutasyonları ───────────────────
        // 1. Ana görsel
        if (mainImg) {
            mainImg.src = data.main_image;
            mainImg.onload = () => { mainImg.style.opacity = '1'; };
            mainImg.onerror = () => {
                mainImg.src = 'https://via.placeholder.com/600x800?text=Görsel+Yok';
                mainImg.style.opacity = '1';
            };
        }

        // 2. Fiyat (panel + sidebar)
        const priceEl   = document.getElementById('price-display');
        const sidePrice = document.getElementById('sidebar-price');
        if (priceEl)   priceEl.textContent   = data.price_fmt + ' ₺';
        if (sidePrice) sidePrice.textContent = data.price_fmt + ' ₺';

        // 3. SKU
        const skuEl = document.getElementById('sku-val');
        if (skuEl) skuEl.textContent = data.sku;

        // 4. Seçili renk adı
        const colorLabel = document.getElementById('selected-color');
        if (colorLabel) colorLabel.textContent = data.color_name;

        // 5. Ürün başlığı
        const titleEl = document.getElementById('product-title');
        if (titleEl) {
            titleEl.textContent = data.brand ? `${data.brand} – ${data.title}` : data.title;
        }

        // 6. Gizli variant ID'yi güncelle
        const hiddenVar = document.getElementById('current-variant-id');
        if (hiddenVar) hiddenVar.value = variantId;
        selectedVariantId = variantId;

    } catch (err) {
        console.error('Varyasyon yüklenemedi:', err);
        if (mainImg) mainImg.style.opacity = '1';
    }
}
