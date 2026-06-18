// js/checkout.js
// Checkout sayfası: validasyon, kart formatlama

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkout-form');
    if (!form) return;

    form.addEventListener('submit', e => {
        const isLoggedIn = document.body.dataset.loggedIn === '1';

        if (!isLoggedIn) {
            e.preventDefault();
            openModal('modal-login');
            return;
        }

        const city    = document.getElementById('addr-city');
        const address = document.getElementById('addr-full');
        let valid = true;

        if (!city?.value.trim()) {
            city.classList.add('is-invalid');
            city.focus();
            valid = false;
        }
        if (!address?.value.trim()) {
            address.classList.add('is-invalid');
            if (valid) address.focus();
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            showToast('⚠️ Lütfen teslimat adresini doldurun.', 'warning');
            document.getElementById('addr-city').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});

// Kart numarasını formatlama
function formatCardNumber(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}
