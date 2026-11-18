<?php
/**
 * tabs/profil.php
 * Tampilan tab Profil & Kata Sandi ustadz.
 * Variabel yang dipakai: $ustadz_name, $ustadz_kontak, $ustadz_nip, $halaqah_name
 */
?>
<div class="mb-4">
    <h1 class="profil-page-title mb-1">Profil Pribadi</h1>
    <p class="profil-page-subtitle mb-0">Kelola informasi biodata asatidz dan pembaruan kata sandi akun.</p>
</div>

<div class="profil-grid">
    <!-- Kartu Kiri: Informasi Akun -->
    <div class="profil-card">
        <h2 class="profil-card-title">
            <i class="bi bi-person-circle text-success"></i>Informasi Akun
        </h2>
        <form action="?tab=profil" method="POST">
            <input type="hidden" name="action" value="update_biodata">

            <div class="profil-field">
                <label class="profil-label" for="namaLengkap">Nama Lengkap</label>
                <input type="text" id="namaLengkap" class="profil-input" value="<?= htmlspecialchars($ustadz_name) ?>" readonly>
            </div>

            <div class="profil-field">
                <label class="profil-label" for="kontakUstadz">Nomor Kontak</label>
                <input type="tel" id="kontakUstadz" name="kontak" class="profil-input form-control"
                    value="<?= htmlspecialchars($ustadz_kontak) ?>"
                    placeholder="Contoh: 0812xxxxxxxx" maxlength="20"
                    style="background: #FFFFFF; border: 1px solid #CBD5E1;">
            </div>

            <div class="profil-field">
                <label class="profil-label" for="nipUstadz">Nomor Induk Pegawai (NIP)</label>
                <input type="text" id="nipUstadz" class="profil-input" value="<?= htmlspecialchars($ustadz_nip) ?>" readonly>
            </div>

            <div class="profil-field">
                <label class="profil-label" for="halaqahUstadz">Halaqah Binaan</label>
                <input type="text" id="halaqahUstadz" class="profil-input"
                    value="Halaqah <?= htmlspecialchars($halaqah_name) ?>" readonly>
                <small class="profil-hint">Penugasan halaqah dikelola oleh Admin</small>
            </div>

            <div class="profil-actions">
                <button type="submit" class="profil-btn profil-btn-primary">
                    <i class="bi bi-floppy"></i>Simpan Perubahan
                </button>
                <button type="reset" class="profil-btn profil-btn-reset">
                    <i class="bi bi-x-circle"></i>Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Kartu Kanan: Ganti Password -->
    <div class="profil-card">
        <h2 class="profil-card-title">
            <i class="bi bi-key-fill text-warning"></i>Ganti Password
        </h2>
        <form action="?tab=profil" method="POST">
            <input type="hidden" name="action" value="update_password">

            <div class="profil-field">
                <label class="profil-label" for="passLama">Kata Sandi Saat Ini</label>
                <input type="password" id="passLama" name="password_lama" class="profil-input"
                    placeholder="Masukkan kata sandi lama" required>
            </div>

            <div class="profil-field">
                <label class="profil-label" for="passBaru">Kata Sandi Baru</label>
                <input type="password" id="passBaru" name="password_baru" class="profil-input"
                    placeholder="Minimal 6 karakter" required>
            </div>

            <div class="profil-field">
                <label class="profil-label" for="passKonfirmasi">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="passKonfirmasi" name="password_konfirmasi" class="profil-input"
                    placeholder="Ulangi kata sandi baru" required>
            </div>

            <div class="p-2 rounded-3 bg-light border mb-3 small text-muted" style="font-size: 12px;">
                <i class="bi bi-info-circle me-1" style="color: #11756D;"></i>
                Pastikan kata sandi Anda kuat dan tidak mudah ditebak demi keamanan nilai hafalan santri.
            </div>

            <div class="profil-actions mt-0">
                <button type="submit" class="profil-btn profil-btn-primary w-100">
                    <i class="bi bi-shield-lock"></i>Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>
