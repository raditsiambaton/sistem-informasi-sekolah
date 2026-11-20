# PROJECT_CONTEXT.md
## Dokumen Acuan Arsitektur & Status Pengembangan
**Sistem Informasi & Monitoring Tahfiz — SMA IT Al-Fadhilah Pekanbaru**

---

## 1. Ringkasan Proyek, Aktor & Pembagian Tim

### Deskripsi Sistem

Proyek ini adalah dua sistem yang berjalan dalam satu codebase:

1. **Website Profil Sekolah (Publik)** — Halaman beranda, akademik, dan SPMB (Seleksi Penerimaan Murid Baru) SMA IT Al-Fadhilah Pekanbaru. Tidak memerlukan login. Diakses melalui `index.php`, `akademik.php`, dan `spmb.php`.

2. **Sistem Monitoring Tahfiz "Tahfiz Yuk"** — Platform manajemen hafalan Al-Qur'an berbasis peran (role-based) untuk memantau progres setoran, murajaah, dan target hafalan siswa secara real-time. Diakses melalui `modules/`.

### Aktor & Hak Akses

| Aktor | Role Session | Entry Point | Kapabilitas Utama |
|---|---|---|---|
| **Pengunjung Umum** | — (tidak login) | `index.php` | Baca profil sekolah, info SPMB, program akademik |
| **Admin** | `admin` | `modules/admin/index.php` | CRUD akun (ustadz & siswa), kelola halaqah, laporan global |
| **Ustadz/Guru** | `ustadz` atau `guru` | `modules/ustadz/index.php?tab=rekap` | Input setoran, evaluasi murajaah, tetapkan target hafalan, rekap halaqah |
| **Siswa** | `siswa` | `modules/siswa/index.php` | Pantau progres pribadi, riwayat setoran & murajaah (read-only) |

### Pembagian Tanggung Jawab Tim (GitHub)

| Wilayah | Penanggung Jawab | File Utama |
|---|---|---|
| **Modul Admin** `modules/admin/` | **Radit** | `modules/admin/index.php` + seluruh subfolder |
| **Modul Ustadz** `modules/ustadz/` | **Rival** | `modules/ustadz/index.php` + actions/ + data/ + tabs/ |
| **Modul Siswa** `modules/siswa/` | **Hafiz** | `modules/siswa/index.php` |
| Auth, Config, Assets, Public pages | Bersama / koordinasi | `config/`, `assets/`, `includes/`, `index.php`, `akademik.php`, `spmb.php` |

---

## 2. Tech Stack & Lingkungan Server

### Backend
- **PHP Native** (Procedural Modular, Server-Side Rendering) — tanpa framework berat agar setup XAMPP tim seminimal mungkin
- **PDO MySQL** dengan `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, dan prepared statements wajib di semua query
- **Singleton pattern** pada `koneksi_database()` di `config/database.php`
- **Auto-create database** — jika DB belum ada, `config/database.php` otomatis membuat database dan mengimpor `database.sql`

### Frontend
- **HTML5** + **CSS3** terpusat di `assets/css/tahfiz.css` (palet `#11756D` / `#F6F7F5`)
- **Bootstrap 5.3.3** (CDN) + **Bootstrap Icons 1.11.3** (CDN)
- **Google Fonts**: `Plus Jakarta Sans` (UI utama) + `Amiri` (teks Arab mushaf)
- **Vanilla JavaScript** inline per tab — tidak ada build tool/bundler
- CSS publik terpisah: `assets/css/public-style.css` (halaman profil) + `assets/css/profil.css`

### Database & Server
- **MySQL** (via XAMPP lokal), database: `sistem_informasi_sekolah`
- **Apache** web server (XAMPP)
- Koneksi: `localhost`, user `root`, password kosong (dev only)

### Integrasi Eksternal (Direncanakan / Sebagian)
- **API Al-Qur'an publik** — untuk teks Arab dan audio murattal Juz 1–5 & Juz 30 (saat ini tab Hafalan masih memakai `MUSHAF_MOCK` statis 5 surah: Adh-Dhuha, Al-Insyirah, At-Tin, Al-Alaq, Al-Qadr)

---

## 3. Struktur Hierarki Direktori & Pemetaan File

### Pohon Direktori Aktual

```
sistem-informasi-sekolah/
├── PROJECT_CONTEXT.md                   # Dokumen acuan arsitektur ini
├── index.php                            # Beranda publik: hero, statistik, visi-misi, program (221 baris)
├── akademik.php                         # Halaman program akademik publik (330 baris)
├── spmb.php                             # Halaman SPMB / pendaftaran siswa baru (513 baris)
├── database.sql                         # Skema DDL + seed data MySQL
│
├── config/
│   ├── database.php                     # Koneksi PDO singleton + auto-create DB (69 baris)
│   └── session.php                      # Helper sesi: sudah_login(), peran_pengguna(), wajib_login() (53 baris)
│
├── assets/
│   ├── css/
│   │   ├── tahfiz.css                   # Stylesheet terpusat modul Tahfiz (palet #11756D)
│   │   ├── public-style.css             # Stylesheet halaman publik
│   │   └── profil.css                   # Stylesheet halaman profil publik
│   └── images/
│       ├── logo-sekolah.png             # Logo SMA IT Al-Fadhilah
│       ├── logo-tahfiz-baru.png         # Logo aplikasi "Tahfiz Yuk" (dipakai sidebar & favicon)
│       ├── prestasi/                    # Folder foto prestasi (kosong, ada .gitkeep)
│       └── profil/
│           ├── hero-sekolah.jpg         # Gambar hero halaman beranda
│           └── visi-misi-sekolah.jpg    # Gambar seksi visi-misi
│
├── includes/
│   ├── header_public.php                # Header HTML publik (navbar + CDN links)
│   └── footer_public.php                # Footer HTML publik
│
│
└── modules/
    ├── auth/
    │   ├── index.html                   # Redirect ke login.php
    │   ├── login.php                    # Form login multi-role + verifikasi DB/hardcode fallback (463 baris)
    │   └── logout.php                   # Hancurkan sesi + redirect ke login (27 baris)
    │
    ├── admin/                           # ── Wilayah RADIT ──
    │   ├── index.php                    # Router admin: auth, routing tab, sidebar, modal (1620 baris)
    │   ├── actions/
    │   │   └── post_handler.php         # Pemroses POST admin: CRUD users, halaqah (422 baris)
    │   ├── data/
    │   │   └── queries.php              # Query SELECT users & halaqah (109 baris, guard ADMIN_INDEX)
    │   └── tabs/
    │       ├── rekap.php                # Tab Rekap: statistik halaqah & KPI global
    │       ├── pengguna.php             # Tab Pengguna: tabel daftar & form edit akun
    │       ├── halaqah.php              # Tab Halaqah: kelola nama & assignment halaqah
    │       ├── laporan.php              # Tab Laporan: ekspor atau ringkasan progres
    │       ├── profil.php               # Tab Profil Admin: biodata & kontak
    │       └── sandi.php                # Tab Sandi: ganti kata sandi admin
    │
    ├── ustadz/                          # ── Wilayah RIVAL ──
    │   ├── index.php                    # Router utama Ustadz: guard, query, sidebar, dispatcher (231 baris)
    │   ├── actions/
    │   │   └── post_handler.php         # Pemroses POST: 5 aksi (setoran, murajaah, target, password, biodata) (272 baris)
    │   ├── data/
    │   │   └── kurikulum.php            # Master Juz 1–5 & Juz 30, $urutan_opsi_target, fallback siswa (68 baris)
    │   └── tabs/
    │       ├── profil.php               # Tab Profil Saya: biodata ustadz + ganti sandi (96 baris)
    │       ├── hafalan.php              # Tab Hafalan: 2 kolom pilih siswa, surah target, modal setoran & mushaf (429 baris)
    │       ├── murajaah.php             # Tab Murajaah: 2 kolom pilih siswa, riwayat setoran, modal evaluasi (380 baris)
    │       ├── rekap.php                # Tab Rekap: 4 KPI, tabel progres siswa, pencarian, modal detail (237 baris)
    │       └── target.php               # Tab Target: 2 kolom pilih siswa, form target Juz/Surah + tanggal (338 baris)
    │
    └── siswa/                           # ── Wilayah HAFIZ ──
        └── index.php                    # Dashboard Siswa: progres pribadi, setoran, murajaah (901 baris)
```

### Tabel Spesifikasi File Kunci

| Path File | Peran & Tanggung Jawab | Relasi `require_once` | Input / Output Data |
|---|---|---|---|
| `config/session.php` | Inisialisasi sesi PHP + 3 helper fungsi | — | I: `$_SESSION` / O: `bool`, `string\|null` |
| `config/database.php` | Singleton PDO + auto-create DB | — | I: konstanta DB_* / O: `PDO\|null` |
| `modules/auth/login.php` | Form login, verifikasi DB/fallback, set `$_SESSION` | `config/session.php`, `config/database.php` | I: POST `username`, `password`, `role` / O: redirect |
| `modules/auth/logout.php` | Hancurkan `$_SESSION` + cookie | `config/session.php` | I: — / O: redirect ke `login.php` |
| `modules/ustadz/index.php` | Guard sesi, routing tab, query ringkas, layout HTML | `config/session.php`, `config/database.php`, `data/kurikulum.php`, `actions/post_handler.php`, `tabs/{tab}.php` | I: `$_GET['tab']`, `$_SESSION` / O: HTML halaman penuh |
| `modules/ustadz/actions/post_handler.php` | Proses 5 aksi POST ustadz | Di-require oleh `ustadz/index.php` | I: `$_POST` / O: `$notif_sukses`, `$notif_error`, `$page_tab` |
| `modules/ustadz/data/kurikulum.php` | Master kurikulum Juz 1–5 & 30, fallback `$santri_list_fallback` | Di-require oleh `ustadz/index.php` | I: — / O: `$kurikulum_target`, `$urutan_opsi_target`, `$santri_list_fallback` |
| `modules/ustadz/tabs/hafalan.php` | UI tab Hafalan (2 kolom) + MUSHAF_MOCK JS | Di-require oleh `ustadz/index.php` | I: `$santri_list` / O: HTML + `<script>` inline |
| `modules/ustadz/tabs/murajaah.php` | UI tab Murajaah (2 kolom) + modal evaluasi | Di-require oleh `ustadz/index.php` | I: `$santri_list`, `$riwayat_setoran` / O: HTML + `<script>` inline |
| `modules/ustadz/tabs/target.php` | UI tab Target (2 kolom) + JS validasi form | Di-require oleh `ustadz/index.php` | I: `$santri_list`, `$target_aktif_map`, `$kurikulum_target`, `$target_fokus_id`, `$target_old` / O: HTML + `<script>` |
| `modules/ustadz/tabs/rekap.php` | UI tab Rekap: KPI + tabel + modal detail | Di-require oleh `ustadz/index.php` | I: `$santri_list`, `$total_setoran_count`, `$total_murajaah_count` / O: HTML + `<script>` |
| `modules/ustadz/tabs/profil.php` | UI tab Profil: form biodata + ganti sandi | Di-require oleh `ustadz/index.php` | I: `$ustadz_name`, `$ustadz_kontak`, `$ustadz_nip`, `$halaqah_name` / O: HTML form |
| `modules/admin/index.php` | Router admin lengkap dengan modal inline | `config/*`, `actions/post_handler.php`, `data/queries.php`, `tabs/{tab}.php` | I: `$_GET['tab']`, `$_POST`, `$_SESSION` / O: HTML halaman penuh |
| `modules/admin/data/queries.php` | Query `users` & `halaqah` untuk admin | Di-require oleh `admin/index.php` (guard `ADMIN_INDEX`) | I: `$pdo` / O: `$users_list`, `$halaqah_list`, `$total_santri`, `$total_ustadz` |
| `modules/siswa/index.php` | Dashboard siswa: progres, setoran, murajaah, ganti sandi | `config/session.php`, `config/database.php` | I: `$_SESSION['user_id']`, `$_GET['tab']` / O: HTML halaman penuh |
| `assets/css/tahfiz.css` | Stylesheet terpusat semua modul Tahfiz | Di-link oleh semua modul via `<link>` | I: — / O: styling `.hafalan-*`, `.ustadz-*`, `.admin-*`, `.siswa-*` |
| `database.sql` | Skema DDL + seed data MySQL | Di-import oleh `config/database.php` saat auto-create | I: — / O: tabel `users`, `setoran_hafalan`, `murajaah`, `target_hafalan`, `halaqah` |

---

## 4. Arsitektur Sistem & Alur Data

### Pola Arsitektur: Monolith Modular (PHP Native)

Satu codebase dengan isolasi akses berbasis `$_SESSION['user_role']`. Tidak ada API endpoint terpisah — semua rendering dilakukan server-side. Tiga modul terisolasi (`admin/`, `ustadz/`, `siswa/`) dengan guard di awal masing-masing `index.php`.

```
Browser ──GET/POST──► Apache ──► modules/{modul}/index.php
                                        │
                               ┌────────┼────────────┐
                               ▼        ▼             ▼
                         config/    data/        actions/
                         session +  kurikulum.   post_handler.
                         database   php          php (jika POST)
                               │
                               ▼
                         tabs/{page_tab}.php ──► HTML Response
```

### Alur Request-Response Detail: Modul Ustadz

```
1. Browser GET  modules/ustadz/index.php?tab=hafalan
        │
        ▼
2. config/session.php → wajib_login() → cek $_SESSION['user_role'] ∈ {ustadz, guru}
        │
        ▼
3. config/database.php → koneksi_database() → PDO singleton
        │
        ▼
4. data/kurikulum.php  → $kurikulum_target, $urutan_opsi_target, $santri_list_fallback
        │
        ▼
5. actions/post_handler.php (jika REQUEST_METHOD === POST)
   → switch($action): simpan_setoran | simpan_murajaah | simpan_target
                       update_password | update_biodata
   → PDO Prepared Statement → INSERT/UPDATE ke MySQL
   → set $notif_sukses / $notif_error / $page_tab
        │
        ▼
6. Query baca MySQL:
   SELECT users WHERE role='siswa'          → $santri_list
   SELECT setoran_hafalan ORDER BY id DESC  → $riwayat_setoran
   SELECT murajaah ORDER BY id DESC         → $riwayat_murajaah
   SELECT target_hafalan WHERE status=Aktif → $target_aktif_map
        │
        ▼
7. Render layout: Sidebar + Topbar + Alert notifikasi
        │
        ▼
8. Whitelist tab: ['profil','hafalan','murajaah','rekap','target']
   require_once "tabs/{$page_tab}.php"
        │
        ▼
9. HTML Response dikirim ke Browser
```

### Routing Tab Admin

Admin memakai whitelist `['rekap','pengguna','halaqah','laporan','profil','sandi']` dengan pola yang sama, tetapi file-file di `admin/` belum dipecah sepenuhnya — `admin/index.php` masih 1.620 baris (monolith, belum direfactor).

---

## 5. Skema Database MySQL & Relasi Tabel

### Database: `sistem_informasi_sekolah`

#### Tabel `users` *(Tabel Utama Pengguna)*
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AUTO_INCREMENT | Primary key |
| `username` | VARCHAR | Username login (unique) |
| `password` | VARCHAR | Hash bcrypt (`password_hash`) |
| `nama_lengkap` | VARCHAR | Nama lengkap pengguna |
| `role` | VARCHAR | `admin` / `ustadz` / `guru` / `siswa` |
| `nip_nis` | VARCHAR | NIP (ustadz/admin) atau NIS (siswa) |
| `halaqah` | VARCHAR | Nama halaqah pengguna |
| `no_hp` | VARCHAR(20), NULL | Nomor kontak (di-alter saat runtime jika belum ada) |
| `status` | VARCHAR | `aktif` / `nonaktif` |

#### Tabel `setoran_hafalan` *(Log Setoran per Sesi)*
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AUTO_INCREMENT | Primary key |
| `siswa_id` | INT, FK → users.id | ID siswa |
| `nama_siswa` | VARCHAR | Cache nama siswa |
| `ustadz_id` | INT, FK → users.id | ID ustadz |
| `nama_ustadz` | VARCHAR | Cache nama ustadz |
| `surah` | VARCHAR | Nama surah |
| `ayat_awal` | INT | Ayat awal setoran |
| `ayat_akhir` | INT | Ayat akhir setoran |
| `nilai` | INT | Nilai numerik (65–95) |
| `kualitas` | VARCHAR | Label kualitas (Lancar, Kurang Lancar, dll.) |
| `catatan` | TEXT | Catatan ustadz |
| `tanggal_setor` | DATE | Tanggal setoran |

#### Tabel `murajaah` *(Log Sesi Pengulangan)*
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AUTO_INCREMENT | Primary key |
| `siswa_id` | INT, FK → users.id | ID siswa |
| `nama_siswa` | VARCHAR | Cache nama siswa |
| `ustadz_id` | INT, FK → users.id | ID ustadz |
| `nama_ustadz` | VARCHAR | Cache nama ustadz |
| `surah` | VARCHAR | Surah yang diulang |
| `ayat_range` | VARCHAR | Rentang ayat (mis. "Ayat 1–10") |
| `status_mutqin` | VARCHAR | Hasil evaluasi (Sangat Mutqin, Lancar, dll.) |
| `tanggal_murajaah` | DATE | Tanggal murajaah |

#### Tabel `target_hafalan` *(Target Aktif per Siswa)*
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AUTO_INCREMENT | Primary key |
| `siswa_id` | INT, FK → users.id | ID siswa |
| `ustadz_id` | INT, FK → users.id | ID ustadz yang menetapkan |
| `surah_awal` | VARCHAR(180) | Batas awal hafalan (label dari `$kurikulum_target`) |
| `surah_akhir` | VARCHAR(180) | Batas akhir hafalan |
| `tanggal_mulai` | DATE | Tanggal mulai target |
| `tanggal_akhir` | DATE | Deadline target |
| `status` | VARCHAR(20) | `Aktif` / `Nonaktif` (saat set target baru, lama di-nonaktifkan) |
| `created_at` | TIMESTAMP | Waktu pembuatan |

#### Tabel `halaqah` *(Master Halaqah)*
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK | Primary key |
| `nama` | VARCHAR | Nama halaqah (mis. "Al-Furqan") |
| *(kolom lain)* | — | Di-query oleh `admin/data/queries.php` |

### Catatan Skema
- Kolom `no_hp` di tabel `users` di-alter secara runtime (ada `ALTER TABLE ... ADD COLUMN` di kode PHP) — perlu ditambahkan ke `database.sql` agar konsisten.
- Tidak ada foreign key constraint eksplisit di DDL (hanya konvensi penamaan `siswa_id`, `ustadz_id`).
- Tabel `target_hafalan` di-create secara runtime dari `modules/ustadz/index.php` jika belum ada — perlu dipindah ke `database.sql`.

---

## 6. Matriks Status Pengembangan

### Modul Ustadz (Wilayah: Rival)

| Sub-Tab / Komponen | Frontend UI | Backend MySQL | Langkah Selanjutnya |
|---|---|---|---|
| **Router & Layout** (`index.php`) | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Tab Profil** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Tab Hafalan** — Panel siswa & surah | ✅ Selesai | ⚠️ Sebagian (daftar siswa dari DB, status surah dari JS state) | Integrasikan status surah dengan query `setoran_hafalan` aktual |
| **Tab Hafalan** — MUSHAF_MOCK | ✅ Selesai (5 surah hardcode) | ❌ Belum API | Ganti `MUSHAF_MOCK` statis dengan panggilan API Al-Qur'an publik (endpoint: `api.alquran.cloud` atau `equran.id`) |
| **Tab Murajaah** — Panel siswa & riwayat | ✅ Selesai | ⚠️ Sebagian (data dari `$riwayat_setoran`) | Query per-siswa sudah ada; uji dengan data DB penuh |
| **Tab Murajaah** — Modal evaluasi | ✅ Selesai | ✅ UPDATE + INSERT | Terapkan Post-Redirect-Get (PRG) agar tidak re-submit saat refresh |
| **Tab Target** | ✅ Selesai | ✅ Terkoneksi DB | Tampilkan riwayat semua target lama (bukan hanya aktif) |
| **Tab Rekap** — KPI & tabel | ✅ Selesai | ⚠️ Fallback `$rekap_data` statis | Ganti `$rekap_data` hardcode dengan query agregasi SQL (`COUNT`, `MAX`, progress per siswa) |
| **Tab Rekap** — Modal detail | ✅ Selesai | ⚠️ Data dari array `$rekap_data` statis | Sambungkan ke query live per `siswa_id` |

### Modul Admin (Wilayah: Radit)

| Sub-Tab / Komponen | Frontend UI | Backend MySQL | Langkah Selanjutnya |
|---|---|---|---|
| **Tab Rekap** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Tab Pengguna** (CRUD) | ✅ Selesai | ✅ Terkoneksi DB | Tambah validasi duplikasi NIS/username |
| **Tab Halaqah** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Tab Laporan** | ⚠️ Sebagian | ❌ Belum | Implementasi ekspor PDF/Excel |
| **Tab Profil & Sandi** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Arsitektur** | ⚠️ Monolith (1.620 baris) | — | Pertimbangkan refactor seperti pola ustadz (tabs/ + actions/ + data/) |

### Modul Siswa (Wilayah: Hafiz)

| Sub-Tab / Komponen | Frontend UI | Backend MySQL | Langkah Selanjutnya |
|---|---|---|---|
| **Dashboard/Progres** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Riwayat Setoran** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Riwayat Murajaah** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Ganti Sandi** | ✅ Selesai | ✅ Terkoneksi DB | — |
| **Arsitektur** | ⚠️ Monolith (901 baris) | — | Pertimbangkan refactor ke pola modular |

### Halaman Publik

| Halaman | Frontend UI | Backend | Langkah Selanjutnya |
|---|---|---|---|
| `index.php` (Beranda) | ✅ Selesai | ❌ Data statis | Koneksikan statistik ke DB |
| `akademik.php` | ✅ Selesai | ❌ Data statis | — |
| `spmb.php` | ✅ Selesai | ❌ Form belum tersambung DB | Simpan pendaftar ke tabel `spmb_pendaftar` |

---

## 7. Konvensi Kode & Aturan Kolaborasi Git

### Isolasi Wilayah File

**Dilarang keras** menyentuh file di luar wilayah modul sendiri tanpa koordinasi eksplisit di chat/meeting:

| Anggota | Boleh Edit | Butuh Koordinasi |
|---|---|---|
| **Rival** | `modules/ustadz/**` | `config/`, `assets/css/tahfiz.css`, `includes/` |
| **Radit** | `modules/admin/**` | `config/`, `assets/css/tahfiz.css`, `database.sql` |
| **Hafiz** | `modules/siswa/**` | `config/`, `database.sql` |
| **Semua** | `modules/auth/**`, `index.php`, `akademik.php`, `spmb.php` | Koordinasi sebelum push |

### Standar Istilah UI vs Kode

| Konteks | Kata yang Dipakai | Contoh |
|---|---|---|
| **Teks UI** (label, judul, badge, pesan, placeholder) | **"Siswa"** | `Pilih siswa`, `Riwayat Setoran Siswa` |
| **Variabel PHP internal** | Tetap `santri_*` | `$santri_list`, `$santri_nama` |
| **Parameter URL** | Tetap `santri=` | `?santri=Muhammad+Fadhlan` |
| **Kolom database** | Tetap `nama_siswa`, `siswa_id` | `INSERT INTO ... nama_siswa` |
| **Atribut `name` form** | Tetap `santri_nama` | `name="santri_nama"` |

**Alasan**: Variabel internal dan parameter URL sudah tertanam di banyak titik kode. Mengubahnya butuh refactor menyeluruh dan berpotensi regresi. Perubahan dilakukan bertahap dengan keamanan.

### Standar Desain UI

- **Warna primer**: `#11756D` (Teal-Green) — wajib dipakai di sidebar, button, badge aktif, border highlight
- **Latar halaman**: `#F6F7F5` / `#F8FAFC`
- **Kartu**: background `#FFFFFF`, `border-radius: 16px`, `border: 1px solid #E2E8F0`
- **Tata letak 2 kolom**: wajib untuk tab `hafalan`, `murajaah`, dan `target` (class `.hafalan-layout`)
- **Font UI**: `Plus Jakarta Sans` / **Font Arab**: `Amiri`
- **Semua class CSS baru** ditambahkan di `assets/css/tahfiz.css`, bukan inline `<style>` di file PHP

### Standar Keamanan & Kode

- **Wajib PDO Prepared Statements** untuk semua query yang melibatkan input pengguna
- **Wajib `htmlspecialchars()`** pada semua output variabel ke HTML
- **Hindari `$_REQUEST`** — gunakan `$_POST` atau `$_GET` secara eksplisit
- **Post-Redirect-Get (PRG)** — perlu diterapkan pada `post_handler.php` untuk mencegah re-submit saat refresh (saat ini belum diimplementasikan di semua aksi)
- **`modules/ustadz/index.php` harus tetap di bawah 250 baris** — semua logika baru masuk ke `tabs/`, `actions/`, atau `data/`

### Alur Commit yang Disarankan

```
feat(ustadz): Tambahkan fitur X di tab hafalan
fix(admin): Perbaiki validasi form tambah pengguna
refactor(siswa): Pecah index.php menjadi modular
style(css): Update warna badge di tahfiz.css
db: Tambahkan kolom X ke database.sql
```

---

## 8. Decision Log (Catatan Keputusan Teknis)

### DL-001 · PHP Native tanpa Framework
**Keputusan**: Menggunakan PHP Native procedural modular, bukan Laravel/CodeIgniter.  
**Alasan**: Tim belum familiar dengan framework. PHP Native memungkinkan setup langsung di XAMPP lokal tanpa Composer, `artisan`, atau build step. Semua anggota tim dapat membuka dan mengedit file langsung.  
**Trade-off**: Lebih verbose, tidak ada ORM, validasi form manual.

---

### DL-002 · Refactor `ustadz/index.php` dari Monolith ke Modular
**Keputusan**: Memecah file `modules/ustadz/index.php` yang semula >2.000 baris menjadi arsitektur `actions/` + `data/` + `tabs/` dengan dispatcher whitelist.  
**Alasan**: File >2.000 baris tidak bisa di-review atau di-debug secara efisien. Konflik Git antar tab sering terjadi karena semua kode di satu file.  
**Hasil**: `index.php` turun dari 2.067 → 231 baris. Setiap tab independen dan bisa diedit tanpa memengaruhi tab lain.  
**Pelajaran**: Modul admin (1.620 baris) dan siswa (901 baris) sebaiknya mengikuti pola yang sama.

---

### DL-003 · Mekanisme Murajaah Berbasis Evaluasi Baris Setoran
**Keputusan**: Modal evaluasi murajaah mengambil data dari baris `setoran_hafalan` yang ada, dengan dropdown Ayat Awal dan Ayat Akhir dikunci (`disabled`) agar ustadz tidak bisa mengubah rentang ayat yang sudah disetorkan.  
**Alasan**: Murajaah adalah *pengulangan* hafalan yang sudah ada — rentang ayatnya sudah fixed. Ustadz hanya mengevaluasi kelancaran dan menambahkan catatan.  
**Implementasi**: `UPDATE setoran_hafalan SET kualitas = :kualitas WHERE id = :id` + `INSERT INTO murajaah` dalam satu handler POST.

---

### DL-004 · MUSHAF_MOCK Statis (Sementara)
**Keputusan**: Tab Hafalan saat ini menggunakan data mushaf hardcode (`MUSHAF_MOCK`) untuk 5 surah: Adh-Dhuha, Al-Insyirah, At-Tin, Al-Alaq, Al-Qadr.  
**Alasan**: Integrasi API belum dilakukan karena membutuhkan koordinasi endpoint dan penanganan error jaringan.  
**Rencana**: Ganti dengan pemanggilan ke API Al-Qur'an publik (seperti `api.alquran.cloud/v1/surah/{nomor}`) untuk mendapatkan teks Arab, transliterasi Latin, terjemahan Indonesia, dan URL audio murattal Juz 1–5 & Juz 30 secara dinamis.

---

### DL-005 · Penggantian Istilah "Santri" → "Siswa" di UI
**Keputusan**: Semua teks yang tampil ke pengguna (label, judul, badge, pesan) menggunakan kata **"Siswa"**. Variabel PHP internal (`$santri_list`), parameter URL (`?santri=`), kolom DB (`nama_siswa`), dan atribut form (`name="santri_nama"`) tetap tidak diubah.  
**Alasan**: Penyesuaian terminologi agar konsisten dengan nomenklatur sekolah formal. Perubahan variabel internal ditunda karena berisiko regresi lintas file dan butuh regression test menyeluruh.

---

### DL-006 · `auto-create database` di `config/database.php`
**Keputusan**: `config/database.php` mendeteksi jika database belum ada (PDO error code 1049) dan otomatis membuat database serta mengimpor `database.sql`.  
**Alasan**: Memudahkan setup anggota tim baru — cukup clone repo, jalankan XAMPP, buka browser. Tidak perlu import manual via phpMyAdmin.  
**Catatan**: Tabel `target_hafalan` saat ini di-create dari `modules/ustadz/index.php` secara runtime. Perlu dipindah ke `database.sql` untuk konsistensi.

---

### DL-007 · CSS Terpusat di `tahfiz.css`
**Keputusan**: Semua styling untuk modul Tahfiz disatukan di `assets/css/tahfiz.css`. File PHP tidak boleh mengandung blok `<style>` inline (kecuali nilai dinamis yang tidak bisa dilakukan via class).  
**Alasan**: Sebelumnya `ustadz/index.php` mengandung >700 baris CSS inline yang menyebabkan konflik Git saat dua orang mengedit file yang sama. Pemindahan ke file CSS eksternal juga mengaktifkan cache browser.  
**Implementasi**: Link di `<head>` menggunakan `tahfiz.css?v=<?= time() ?>` untuk cache-busting.

---

*Dokumen ini diperbarui setiap kali ada perubahan arsitektur signifikan. Simpan di root repositori dan pastikan selalu in-sync dengan kode aktual.*
