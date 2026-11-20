<?php
/**
 * modules/admin/tabs/profil.php
 * TAB 5: Profil Saya — tata letak 2 kolom (Informasi Akun + Keamanan & Kata Sandi)
 *
 * Form ubah kata sandi digabung ke kartu kanan.
 * Variabel yang digunakan: $admin_name, $admin_initials, $csrf_token
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
}
?>
<div class="page-heading">
    <h1>Profil Saya</h1>
    <p>Kelola identitas akun administrator. Perubahan nama akan tersimpan ke database.</p>
</div>

<div class="profil-wrap">

    <!-- ── Kolom Kiri: Informasi Akun ─────────────────────── -->
    <div class="profil-card">
        <!-- Avatar -->
        <div class="d-flex align-items-center gap-3 mb-4 pb-4" style="border-bottom:1px solid var(--slate-2);">
            <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--em-600),var(--em-800));display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;">
                <?= htmlspecialchars($admin_initials) ?>
            </div>
            <div>
                <div class="fw-bold" style="font-size:16px;"><?= htmlspecialchars($admin_name) ?></div>
                <div style="font-size:12.5px;color:var(--em-600);font-weight:600;">Super Administrator</div>
                <div style="font-size:12px;color:var(--slate-5);">SMA IT Al-Fadhilah Pekanbaru</div>
            </div>
        </div>

        <h3 class="profil-card-title"><i class="bi bi-person-circle ic"></i> Informasi Akun</h3>
        <form action="?tab=profil" method="POST">
            <input type="hidden" name="action" value="update_profil">
            <div class="profil-field">
                <label class="profil-label">Nama Administrator</label>
                <input type="text" name="nama_lengkap" class="profil-input fw-semibold" value="<?= htmlspecialchars($admin_name) ?>" required>
            </div>
            <div class="profil-field">
                <label class="profil-label">NIP / ID Pengenal</label>
                <input type="text" class="profil-input" value="198700" readonly>
                <span class="profil-hint">* ID Root Administrator terproteksi oleh sistem.</span>
            </div>
            <div class="profil-field">
                <label class="profil-label">Nomor Kontak Unit IT / TU</label>
                <input type="text" class="profil-input" value="(0761) 840-7782">
            </div>
            <div class="profil-field">
                <label class="profil-label">Alamat Surel Resmi</label>
                <input type="email" class="profil-input" value="admin@alfadhilah.sch.id">
            </div>
            <button type="submit" class="btn-em w-100 mt-2" style="justify-content:center;">
                <i class="bi bi-check-circle"></i> Simpan Biodata Administrator
            </button>
        </form>
    </div>

    <!-- ── Kolom Kanan: Keamanan & Kata Sandi ─────────────── -->
    <div class="profil-card">
        <h3 class="profil-card-title"><i class="bi bi-key-fill ic"></i> Keamanan &amp; Kata Sandi</h3>

        <div class="info-box">
            <i class="bi bi-shield-exclamation" style="font-size:16px;flex-shrink:0;"></i>
            <span>Kata sandi Super Administrator memiliki hak penuh atas konfigurasi sistem. Jangan bagikan kepada siapapun.</span>
        </div>

        <form action="?tab=profil" method="POST">
            <input type="hidden" name="action" value="update_password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
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
