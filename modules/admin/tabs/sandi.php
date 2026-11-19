<?php
/**
 * modules/admin/tabs/sandi.php
 * TAB 6: Ubah Kata Sandi Administrator
 *
 * Tidak memerlukan variabel khusus dari data/queries.php.
 * Form action dan field names dipertahankan persis seperti semula.
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
}
?>
<div class="page-heading">
    <h1>Ubah Kata Sandi</h1>
    <p>Perbarui kata sandi akun Administrator. Minimal 6 karakter.</p>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-5">
        <div class="profil-card">
            <h3 class="profil-card-title"><i class="bi bi-key-fill ic"></i> Keamanan &amp; Kata Sandi</h3>

            <div class="info-box">
                <i class="bi bi-shield-exclamation" style="font-size:16px;flex-shrink:0;"></i>
                <span>Kata sandi Super Administrator memiliki hak penuh atas konfigurasi sistem. Jangan bagikan kepada siapapun.</span>
            </div>

            <form action="?tab=sandi" method="POST">
                <input type="hidden" name="action" value="update_password">
                <div class="profil-field">
                    <label class="profil-label">Kata Sandi Saat Ini</label>
                    <input type="password" name="password_lama" class="profil-input" placeholder="Masukkan kata sandi lama" required autocomplete="current-password">
                </div>
                <div class="profil-field">
                    <label class="profil-label">Kata Sandi Baru</label>
                    <input type="password" name="password_baru" class="profil-input" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                </div>
                <div class="profil-field">
                    <label class="profil-label">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_konfirmasi" class="profil-input" placeholder="Ketik ulang sandi baru" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-em w-100 mt-2" style="justify-content:center;">
                    <i class="bi bi-shield-lock"></i> Perbarui Sandi Administrator
                </button>
            </form>
        </div>
    </div>
</div>
