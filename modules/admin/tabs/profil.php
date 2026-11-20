<?php
/**
 * modules/admin/tabs/profil.php
 * TAB 5: Profil Saya
 *
 * Variabel yang digunakan (disiapkan oleh index.php via data/queries.php):
 *   $admin_name, $admin_initials
 *
 * CATATAN PERUBAHAN:
 *   - Kartu "Informasi Sistem" (Versi, Teknologi, Total Pengguna, Total Halaqah,
 *     Level Akses) telah dihapus sesuai instruksi.
 *   - Kartu/tautan "Ubah Kata Sandi" dipertahankan.
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
    <div class="profil-card">
        <!-- Avatar besar -->
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

    <!-- Panel kanan: hanya tautan Ubah Kata Sandi -->
    <div>
        <div class="mt-0 p-3 rounded-3" style="background:var(--em-50);border:1px solid var(--em-100);">
            <div class="d-flex align-items-center gap-2 mb-1" style="font-size:13px;font-weight:700;color:var(--em-800);">
                <i class="bi bi-key-fill"></i> Ubah Kata Sandi
            </div>
            <p style="font-size:12.5px;color:var(--slate-5);margin:0 0 10px;">Untuk keamanan, ubah kata sandi secara berkala.</p>
            <a href="?tab=sandi" class="btn-outline-em btn-em-sm">
                <i class="bi bi-arrow-right-circle"></i> Pergi ke Halaman Sandi
            </a>
        </div>
    </div>
</div>
