<?php
// components/login_modal.php
// Tüm sayfalarda hazır duran Giriş / Kayıt Modal'ı
?>
<div class="modal-overlay" id="modal-login" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modal-login')" aria-label="Kapat">✕</button>

        <!-- Sekmeler -->
        <div style="display:flex;gap:0;margin-bottom:28px;border-bottom:2px solid var(--border);">
            <button class="modal-tab active" id="tab-login" onclick="switchTab('tab-login')"
                    style="flex:1;background:none;border:none;padding:12px;font-size:15px;font-weight:600;
                           cursor:pointer;border-bottom:3px solid transparent;transition:.2s;color:var(--text-muted);">
                Giriş Yap
            </button>
            <button class="modal-tab" id="tab-register" onclick="switchTab('tab-register')"
                    style="flex:1;background:none;border:none;padding:12px;font-size:15px;font-weight:600;
                           cursor:pointer;border-bottom:3px solid transparent;transition:.2s;color:var(--text-muted);">
                Üye Ol
            </button>
        </div>

        <!-- GİRİŞ FORMU -->
        <div id="panel-login">
            <h2 id="modal-title" style="font-size:20px;margin-bottom:6px;">Hoş geldiniz 👋</h2>
            <p style="color:var(--text-muted);font-size:13.5px;margin-bottom:22px;">Devam etmek için giriş yapın.</p>

            <div id="login-error" style="display:none;background:#ffebee;color:#c62828;padding:10px 14px;
                 border-radius:6px;margin-bottom:14px;font-size:13.5px;"></div>

            <form id="form-login" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label for="login-email">E-posta Adresi</label>
                    <input type="email" id="login-email" class="form-control" placeholder="ornek@email.com" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="login-password">Şifre</label>
                    <div style="position:relative;">
                        <input type="password" id="login-password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" onclick="togglePwd('login-password')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full" id="btn-login-submit">
                    <span>Giriş Yap</span>
                </button>
            </form>
            <p style="text-align:center;margin-top:16px;font-size:13.5px;color:var(--text-muted);">
                Hesabın yok mu?
                <a href="#" onclick="switchTab('tab-register')" style="color:var(--primary);font-weight:600;">Üye Ol</a>
            </p>
        </div>

        <!-- KAYIT FORMU -->
        <div id="panel-register" style="display:none;">
            <h2 style="font-size:20px;margin-bottom:6px;">Hesap Oluştur 🚀</h2>
            <p style="color:var(--text-muted);font-size:13.5px;margin-bottom:22px;">Hemen üye ol, avantajları keşfet!</p>

            <div id="register-error" style="display:none;background:#ffebee;color:#c62828;padding:10px 14px;
                 border-radius:6px;margin-bottom:14px;font-size:13.5px;"></div>
            <div id="register-success" style="display:none;background:#e8f5e9;color:#2e7d32;padding:10px 14px;
                 border-radius:6px;margin-bottom:14px;font-size:13.5px;"></div>

            <form id="form-register" onsubmit="handleRegister(event)">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label for="reg-fname">Ad</label>
                        <input type="text" id="reg-fname" class="form-control" placeholder="Adınız" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-lname">Soyad</label>
                        <input type="text" id="reg-lname" class="form-control" placeholder="Soyadınız" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reg-email">E-posta Adresi</label>
                    <input type="email" id="reg-email" class="form-control" placeholder="ornek@email.com" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="reg-phone">Telefon (İsteğe Bağlı)</label>
                    <input type="tel" id="reg-phone" class="form-control" placeholder="05XX XXX XX XX">
                </div>
                <div class="form-group">
                    <label for="reg-password">Şifre</label>
                    <div style="position:relative;">
                        <input type="password" id="reg-password" class="form-control" placeholder="En az 6 karakter" required minlength="6">
                        <button type="button" onclick="togglePwd('reg-password')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full" id="btn-register-submit">
                    <span>Üye Ol</span>
                </button>
            </form>
            <p style="text-align:center;margin-top:16px;font-size:13.5px;color:var(--text-muted);">
                Zaten hesabın var mı?
                <a href="#" onclick="switchTab('tab-login')" style="color:var(--primary);font-weight:600;">Giriş Yap</a>
            </p>
        </div>

    </div><!-- /modal-box -->
</div><!-- /modal-overlay -->

<!-- Beden Seçim Modal'ı (Kalp ikonuna tıklayınca) -->
<div class="modal-overlay" id="modal-size" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:400px;text-align:center;">
        <button class="modal-close" onclick="closeModal('modal-size')">✕</button>
        <h3 style="margin-bottom:6px;">Beden Seçin</h3>
        <p style="color:var(--text-muted);font-size:13.5px;margin-bottom:20px;">Favorilere eklemek için beden seçin:</p>
        <div id="size-options" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:20px;"></div>
        <input type="hidden" id="size-modal-product-id">
        <button class="btn btn-primary btn-full" onclick="confirmSizeWishlist()">❤️ Favorilere Ekle</button>
    </div>
</div>
