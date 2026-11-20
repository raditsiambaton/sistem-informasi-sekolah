<?php
/**
 * modules/siswa/tabs/profil.php
 * Tab "Profil Pribadi & Keamanan Akun" — Dashboard Siswa
 * Guard: hanya boleh di-require dari modules/siswa/index.php
 */

if (!defined('SISWA_INDEX')) exit;
?>

<div class="mb-3">
    <h1 class="profil-page-title mb-1">Profil Pribadi</h1>
    <p class="profil-page-subtitle mb-0">Informasi diri dan keamanan akun siswa Anda.</p>
</div>

<!-- 1. HERO IDENTITY CARD -->
<div class="card border-0 mb-4" style="background: #FFFFFF; border: 1px solid #E2E8F0 !important; border-radius: 16px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

        <!-- Avatar & Identitas Siswa -->
        <div class="d-flex align-items-center gap-3">
            <div class="flex-shrink-0"
                 style="width: 64px; height: 64px; border-radius: 14px; background: #E6F4F1; color: #11756D; font-weight: 700; font-size: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(17, 117, 109, 0.1);">
                <?= strtoupper(substr($siswa_name, 0, 1) . substr(explode(' ', $siswa_name)[1] ?? '', 0, 1)) ?>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="font-size: 20px; color: #0F172A; line-height: 1.3;"><?= htmlspecialchars($siswa_name) ?></h3>
                <div style="font-size: 14px; font-weight: 500; color: #64748B; margin-top: 2px;">NIS: <?= htmlspecialchars($siswa_nis) ?></div>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                    <span class="badge rounded-pill fw-semibold" style="background: #E6F4F1; color: #11756D; font-size: 12px; padding: 5px 12px;">
                        <i class="bi bi-diagram-3-fill me-1"></i>Halaqah <?= htmlspecialchars($halaqah_name) ?>
                    </span>
                    <span class="badge rounded-pill fw-semibold" style="background: #F1F5F9; color: #475569; font-size: 12px; padding: 5px 12px;">
                        <i class="bi bi-person-badge-fill me-1"></i>Ust. Rival Alvian Melfandy
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistik Singkat Capaian Siswa -->
        <div class="d-flex gap-2 text-center align-items-center mt-3 mt-md-0">
            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                <div class="fw-bold" style="font-size: 18px; color: #11756D;">2 Juz</div>
                <small class="text-muted fw-semibold" style="font-size: 11px;">Mutqin</small>
            </div>
            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                <div class="fw-bold" style="font-size: 18px; color: #D97706;">60%</div>
                <small class="text-muted fw-semibold" style="font-size: 11px;">Target</small>
            </div>
            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                <div class="fw-bold" style="font-size: 18px; color: #0F172A;">90</div>
                <small class="text-muted fw-semibold" style="font-size: 11px;">Rata-rata</small>
            </div>
        </div>

    </div>
</div>

<!-- 2. DUA KOLOM FORMULIR PENGATURAN -->
<div class="profil-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: stretch;">

    <!-- KOLOM KIRI: DATA BIODATA & HALAQAH SISWA -->
    <div class="profil-card d-flex flex-column">
        <h2 class="profil-card-title">
            <i class="bi bi-person-circle text-success"></i>Data Biodata &amp; Halaqah Siswa
        </h2>

        <form action="?tab=profil" method="POST" class="d-flex flex-column flex-grow-1">
            <input type="hidden" name="action" value="update_biodata">

            <div class="profil-field">
                <label class="profil-label">Nama Lengkap</label>
                <input type="text" class="profil-input" value="<?= htmlspecialchars($siswa_name) ?>" readonly>
            </div>

            <div class="profil-field">
                <label class="profil-label">Nomor Induk Siswa (NIS)</label>
                <input type="text" class="profil-input" value="<?= htmlspecialchars($siswa_nis) ?>" readonly>
            </div>

            <div class="profil-field">
                <label class="profil-label">Nomor Kontak / WhatsApp</label>
                <input type="tel" name="no_hp" class="profil-input form-control"
                       value="<?= htmlspecialchars($data_siswa['no_hp'] ?? '') ?>"
                       placeholder="Contoh: 081234567890" maxlength="20"
                       style="background: #FFFFFF; border: 1px solid #CBD5E1;">
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="profil-field mb-0">
                        <label class="profil-label">Kelompok Halaqah</label>
                        <input type="text" class="profil-input" value="Halaqah <?= htmlspecialchars($halaqah_name) ?>" readonly>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="profil-field mb-0">
                        <label class="profil-label">Ustadz Pembimbing</label>
                        <input type="text" class="profil-input" value="Ust. Rival Alvian Melfandy" readonly>
                    </div>
                </div>
            </div>

            <div class="profil-actions mt-auto pt-2">
                <button type="submit" class="profil-btn profil-btn-primary">
                    <i class="bi bi-floppy"></i>Simpan Biodata Siswa
                </button>
            </div>
        </form>
    </div>

    <!-- KOLOM KANAN: PENGATURAN KATA SANDI SISWA -->
    <div class="profil-card d-flex flex-column">
        <h2 class="profil-card-title">
            <i class="bi bi-key-fill text-warning"></i>Ganti Kata Sandi
        </h2>

        <form action="?tab=profil" method="POST" class="d-flex flex-column flex-grow-1">
            <input type="hidden" name="action" value="ganti_password">

            <div class="profil-field">
                <label class="profil-label">Kata Sandi Saat Ini</label>
                <input type="password" name="password_lama" class="profil-input" placeholder="Masukkan kata sandi lama" required>
            </div>

            <div class="profil-field">
                <label class="profil-label">Kata Sandi Baru</label>
                <input type="password" name="password_baru" class="profil-input" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="profil-field">
                <label class="profil-label">Ulangi Kata Sandi Baru</label>
                <input type="password" name="password_konfirmasi" class="profil-input" placeholder="Konfirmasi sandi baru" required>
            </div>

            <div class="p-2.5 rounded-3 bg-light border mb-3 small text-muted" style="font-size: 12px;">
                <i class="bi bi-info-circle me-1" style="color: #11756D;"></i>Jangan bagikan kata sandi Anda kepada orang lain agar data hafalan tetap aman.
            </div>

            <div class="profil-actions mt-auto">
                <button type="submit" class="profil-btn profil-btn-primary w-100">
                    <i class="bi bi-shield-lock me-1"></i>Perbarui Sandi Siswa
                </button>
            </div>
        </form>
    </div>

</div>
