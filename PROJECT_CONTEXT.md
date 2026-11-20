# PROJECT_CONTEXT.md
## Dokumen Acuan Arsitektur & Status Pengembangan
**Sistem Informasi Sekolah & Monitoring Tahfiz "Tahfiz Yuk"**
**SMA IT Al-Fadhilah Pekanbaru**
*Diperbarui: November 2026*

---

## 1. Ringkasan Proyek, Aktor & Pembagian Wilayah Tim

### Deskripsi Sistem

Proyek ini adalah dua sistem yang berjalan dalam satu codebase:

1. **Website Profil Sekolah (Publik)** — Halaman beranda, program akademik, dan informasi SPMB (Seleksi Penerimaan Murid Baru) SMA IT Al-Fadhilah Pekanbaru. Tidak memerlukan login. Diakses melalui `index.php`, `akademik.php`, dan `spmb.php`.

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
| **Modul Admin** `modules/admin/` | **Radit** | `modules/admin/index.php` + `actions/` + `data/` + `tabs/` + `assets/css/admin.css` |
| **Modul Ustadz** `modules/ustadz/` | **Rival** | `modules/ustadz/index.php` + `actions/` + `data/` + `tabs/` |
| **Modul Siswa** `modules/siswa/` | **Hafiz** | `modules/siswa/index.php` + `actions/` + `data/` + `tabs/` + `assets/css/siswa.css` |
| Auth, Config, Assets, Public pages | **Bersama / Koordinasi** | `config/`, `assets/css/tahfiz.css`, `includes/`, `modules/auth/`, `database.sql`, `index.php`, `akademik.php`, `spmb.php` |

---

## 2. Tech Stack, Lingkungan Server & Integrasi Eksternal

### Backend

- **PHP Native** (Procedural Modular, Server-Side Rendering) — tanpa framework berat agar setup XAMPP tim seminimal mungkin
- **PDO MySQL** dengan `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `ATTR_EMULATE_PREPARES = false`, dan prepared statements wajib di semua query
- **Singleton pattern** pada `koneksi_database()` di `config/database.php`
- **Auto-create database** — jika DB belum ada (PDO error code 1049), `config/database.php` otomatis membuat database dan mengimpor `database.sql`
- **Konstanta guard** melindungi file pendukung: `ADMIN_INDEX` (admin), `SISWA_INDEX` (siswa) — file `actions/`, `data/`, dan `tabs/` memeriksa guard di awal dan mengembalikan 403 Forbidden jika diakses langsung

### Frontend

- **HTML5** + **CSS3** modular terpisah: `tahfiz.css` (terpusat Tahfiz), `admin.css` (admin), `siswa.css` (siswa), `public-style.css` + `profil.css` (publik)
- **Bootstrap 5.3.3** (CDN) + **Bootstrap Icons 1.11.3** (CDN)
- **Google Fonts**: `Plus Jakarta Sans` (UI utama) + `Amiri` (teks Arab mushaf)
- **Vanilla JavaScript** inline per tab — tidak ada build tool/bundler
- Cache-busting via `?v=<?= time() ?>` pada semua link CSS

### Database & Web Server

- **MySQL** (via XAMPP lokal), database: `sistem_informasi_sekolah`
- **Apache** web server (XAMPP)
- Koneksi: `localhost`, user `root`, password kosong (dev only)

### Integrasi Eksternal (Direncanakan / Sebagian)

- **API Al-Qur'an publik** — untuk teks Arab dan audio murattal Juz 1–5 & Juz 30. Saat ini tab Hafalan Ustadz masih memakai `MUSHAF_MOCK` statis 5 surah (Adh-Dhuha, Al-Insyirah, At-Tin, Al-Alaq, Al-Qadr). Rencana: migrasi ke endpoint `api.alquran.cloud` atau `equran.id` untuk teks Arab, transliterasi Latin, terjemahan Indonesia, dan URL audio murattal.

---

## 3. Struktur Hierarki Direktori & Pemetaan File Mendalam

### Pohon Direktori Aktual

```
sistem-informasi-sekolah/
├── PROJECT_CONTEXT.md                   # Dokumen acuan arsitektur ini
├── index.php                            # Beranda publik: hero, statistik, visi-misi (194 baris)
├── akademik.php                         # Halaman program akademik publik (291 baris)
├── spmb.php                             # Halaman SPMB / pendaftaran siswa baru (468 baris)
├── database.sql                         # Skema DDL + seed data MySQL (101 baris)
│
├── config/
│   ├── database.php                     # Koneksi PDO singleton + auto-create DB (61 baris)
│   └── session.php                      # Helper sesi: sudah_login(), peran_pengguna(), wajib_login() (48 baris)
│
├── assets/
│   ├── css/
│   │   ├── tahfiz.css                   # Stylesheet terpusat modul Tahfiz (palet #11756D, 1648 baris)
│   │   ├── admin.css                    # Stylesheet modul Admin: sidebar, topbar, KPI, tabel, badge (718 baris)
│   │   ├── siswa.css                    # Stylesheet modul Siswa: sidebar, wrapper, KPI, responsivitas (223 baris)
│   │   ├── public-style.css             # Stylesheet halaman publik (266 baris)
│   │   └── profil.css                   # Stylesheet halaman profil publik (52 baris)
│   └── images/
│       ├── logo-sekolah.png             # Logo SMA IT Al-Fadhilah
│       ├── logo-tahfiz-baru.png         # Logo aplikasi "Tahfiz Yuk" (sidebar & favicon)
│       ├── prestasi/                    # Folder foto prestasi (kosong, ada .gitkeep)
│       └── profil/
│           ├── hero-sekolah.jpg         # Gambar hero halaman beranda
│           └── visi-misi-sekolah.jpg    # Gambar seksi visi-misi
│
├── includes/
│   ├── header_public.php                # Header HTML publik: navbar + CDN links (109 baris)
│   └── footer_public.php                # Footer HTML publik (116 baris)
│
└── modules/
    ├── auth/
    │   ├── index.html                   # Redirect ke login.php
    │   ├── login.php                    # Form login multi-role + verifikasi DB/fallback (413 baris)
    │   └── logout.php                   # Hancurkan sesi + redirect ke login (21 baris)
    │
    ├── admin/                           # ── Wilayah RADIT ──
    │   ├── index.php                    # Router admin: guard, routing tab, sidebar, dispatcher (219 baris)
    │   ├── actions/
    │   │   └── post_handler.php         # 7 aksi POST: CRUD users & halaqah, update_profil, update_password (386 baris)
    │   ├── data/
    │   │   └── queries.php              # Query users & halaqah, hitung metrik, label breadcrumb (96 baris, guard ADMIN_INDEX)
    │   └── tabs/
    │       ├── rekap.php                # Tab Rekap: statistik & KPI global (161 baris)
    │       ├── pengguna.php             # Tab Pengguna: tabel + modal tambah/edit + JS (251 baris)
    │       ├── halaqah.php              # Tab Halaqah: tabel + modal tambah/edit + JS counter & search (375 baris)
    │       ├── laporan.php              # Tab Laporan: ringkasan progres (71 baris)
    │       └── profil.php               # Tab Profil: biodata + form ubah kata sandi (2 kolom) (84 baris)
    │
    ├── ustadz/                          # ── Wilayah RIVAL ──
    │   ├── index.php                    # Router utama: guard, query, sidebar, dispatcher (208 baris)
    │   ├── actions/
    │   │   └── post_handler.php         # 5 aksi POST: simpan_setoran, simpan_murajaah, simpan_target, update_password, update_biodata (254 baris)
    │   ├── data/
    │   │   └── kurikulum.php            # Master Juz 1–5 & 30, $urutan_opsi_target, fallback $santri_list_fallback (63 baris)
    │   └── tabs/
    │       ├── profil.php               # Tab Profil Saya: biodata + ganti sandi (84 baris)
    │       ├── hafalan.php              # Tab Hafalan: 2 kolom siswa, surah target, modal setoran & mushaf, input angka ayat (614 baris)
    │       ├── murajaah.php             # Tab Murajaah: 2 kolom siswa, riwayat setoran, modal evaluasi (336 baris)
    │       ├── rekap.php                # Tab Rekap: 4 KPI, tabel progres, pencarian, modal detail (229 baris)
    │       └── target.php              # Tab Target: 2 kolom siswa, form target Juz/Surah + tanggal (308 baris)
    │
    └── siswa/                           # ── Wilayah HAFIZ ──
        ├── index.php                    # Router Siswa: guard, whitelist tab, sidebar, dispatcher (172 baris)
        ├── actions/
        │   └── post_handler.php         # 2 aksi POST: update_biodata & ganti_password (79 baris, guard SISWA_INDEX)
        ├── data/
        │   └── queries.php              # Query profil & riwayat (setoran+murajaah), helper kualitas (123 baris, guard SISWA_INDEX)
        └── tabs/
            ├── progres.php              # Tab Progres Hafalan: 3 KPI, kartu target, tabel setoran terakhir (142 baris)
            ├── riwayat.php              # Tab Riwayat: tabel gabungan, panel catatan ustadz, modal detail (300 baris)
            └── profil.php               # Tab Profil Pribadi: hero card, form biodata, form ganti sandi (126 baris)
```

### Tabel Spesifikasi File Kunci

| Path File | Peran & Tanggung Jawab | Relasi `require_once` / Guard | Input / Output Data |
|---|---|---|---|
| `config/session.php` | Inisialisasi sesi PHP + helper `sudah_login()`, `peran_pengguna()`, `wajib_login()` | — | I: `$_SESSION` / O: `bool`, `string\|null` |
| `config/database.php` | Singleton PDO (`koneksi_database()`) + auto-create DB dari `database.sql` | — | I: konstanta `DB_*` / O: `PDO\|null` |
| `modules/auth/login.php` | Form login, verifikasi DB/fallback, set `$_SESSION` multi-role | `config/session.php`, `config/database.php` | I: POST `username`, `password`, `role` / O: redirect ke dashboard peran |
| `modules/auth/logout.php` | Hancurkan `$_SESSION` + cookie sesi, redirect ke login | `config/session.php` | I: — / O: redirect ke `login.php` |
| `modules/admin/index.php` | Guard sesi admin, routing tab whitelist, layout sidebar + topbar, dispatcher tab | `config/*`, `actions/post_handler.php`, `data/queries.php`, `tabs/{tab}.php` | I: `$_GET['tab']`, `$_POST`, `$_SESSION` / O: HTML halaman penuh |
| `modules/admin/actions/post_handler.php` | Proses 7 aksi POST admin: CRUD pengguna & halaqah, `update_profil`, `update_password` | Guard `ADMIN_INDEX` — di-require oleh `admin/index.php` | I: `$_POST`, `$csrf_token` / O: `$success_msg`, `$error_msg` |
| `modules/admin/data/queries.php` | Query `users` & `halaqah`, hitung `$total_santri`, `$total_ustadz`, `$tab_labels` | Guard `ADMIN_INDEX` — di-require oleh `admin/index.php` | I: `$pdo` / O: `$users_list`, `$halaqah_list`, `$ustadz_list`, `$siswa_list` |
| `modules/admin/tabs/rekap.php` | Dashboard rekap: 4 KPI global, grafik batang capaian halaqah | Di-require oleh `admin/index.php` | I: `$users_list`, `$halaqah_list` / O: HTML tab |
| `modules/admin/tabs/pengguna.php` | Tabel pengguna + modal tambah/edit + JS isi modal edit | Di-require oleh `admin/index.php` | I: `$users_list` / O: HTML + modal Bootstrap + `<script>` |
| `modules/admin/tabs/halaqah.php` | Tabel halaqah + modal tambah/edit + JS counter & search siswa | Di-require oleh `admin/index.php` | I: `$halaqah_list`, `$siswa_list`, `$ustadz_list` / O: HTML + modal Bootstrap + `<script>` |
| `modules/admin/tabs/laporan.php` | Ringkasan laporan tahfiz (placeholder) | Di-require oleh `admin/index.php` | I: — / O: HTML tab |
| `modules/admin/tabs/profil.php` | Biodata admin + form ubah kata sandi (2 kolom terintegrasi) | Di-require oleh `admin/index.php` | I: `$admin_name`, `$admin_initials`, `$csrf_token` / O: HTML form |
| `modules/ustadz/index.php` | Guard sesi ustadz/guru, routing tab whitelist, query ringkas, layout HTML, dispatcher | `config/*`, `data/kurikulum.php`, `actions/post_handler.php`, `tabs/{tab}.php` | I: `$_GET['tab']`, `$_SESSION` / O: HTML halaman penuh |
| `modules/ustadz/actions/post_handler.php` | Proses 5 aksi POST: `simpan_setoran`, `simpan_murajaah`, `simpan_target`, `update_password`, `update_biodata` | Di-require oleh `ustadz/index.php` | I: `$_POST` / O: `$notif_sukses`, `$notif_error`, `$page_tab` |
| `modules/ustadz/data/kurikulum.php` | Master kurikulum Juz 1–5 & 30, `$urutan_opsi_target`, fallback `$santri_list_fallback` | Di-require oleh `ustadz/index.php` | I: — / O: `$kurikulum_target`, `$urutan_opsi_target`, `$santri_list_fallback` |
| `modules/ustadz/tabs/hafalan.php` | UI 2 kolom: pilih siswa, daftar surah target, modal setoran (input angka ayat), modal mushaf (MUSHAF_MOCK) | Di-require oleh `ustadz/index.php` | I: `$santri_list` / O: HTML + `<script>` inline |
| `modules/ustadz/tabs/murajaah.php` | UI 2 kolom: pilih siswa, riwayat setoran per baris, modal evaluasi murajaah (ayat terkunci) | Di-require oleh `ustadz/index.php` | I: `$santri_list`, `$riwayat_setoran` / O: HTML + `<script>` inline |
| `modules/ustadz/tabs/target.php` | UI 2 kolom: pilih siswa, form tetapkan target Juz/Surah + tanggal, kartu target aktif | Di-require oleh `ustadz/index.php` | I: `$santri_list`, `$target_aktif_map`, `$kurikulum_target`, `$target_fokus_id`, `$target_old` / O: HTML + `<script>` |
| `modules/ustadz/tabs/rekap.php` | 4 KPI halaqah, tabel progres siswa, pencarian real-time, modal detail riwayat | Di-require oleh `ustadz/index.php` | I: `$santri_list`, `$total_setoran_count`, `$total_murajaah_count` / O: HTML + `<script>` |
| `modules/ustadz/tabs/profil.php` | Form biodata ustadz (readonly) + form ganti sandi | Di-require oleh `ustadz/index.php` | I: `$ustadz_name`, `$ustadz_kontak`, `$ustadz_nip`, `$halaqah_name` / O: HTML form |
| `modules/siswa/index.php` | Guard sesi siswa, routing tab whitelist, layout sidebar + topbar, dispatcher | `config/*`, `actions/post_handler.php`, `data/queries.php`, `tabs/{tab}.php` | I: `$_GET['tab']`, `$_POST`, `$_SESSION` / O: HTML halaman penuh |
| `modules/siswa/actions/post_handler.php` | Proses 2 aksi POST: `update_biodata` (no_hp) & `ganti_password` | Guard `SISWA_INDEX` — di-require oleh `siswa/index.php` | I: `$_POST`, `$siswa_id` / O: `$notif_sukses`, `$notif_error`, `$page_tab` |
| `modules/siswa/data/queries.php` | Query `users` (profil) & `setoran_hafalan`+`murajaah` (riwayat), `$data_siswa`, helper kualitas | Guard `SISWA_INDEX` — di-require oleh `siswa/index.php` | I: `$siswa_id`, `$siswa_name`, `$pdo` / O: `$siswa_setoran`, `$siswa_murajaah`, `$data_siswa` |
| `modules/siswa/tabs/progres.php` | Tab Progres Hafalan: 3 KPI, kartu target aktif, tabel setoran terakhir | Di-require oleh `siswa/index.php` | I: `$siswa_name`, `$halaqah_name`, `$siswa_setoran` / O: HTML tab |
| `modules/siswa/tabs/riwayat.php` | Tab Riwayat: tabel gabungan setoran+murajaah, panel catatan ustadz, modal detail | Di-require oleh `siswa/index.php` | I: `$siswa_setoran`, `$siswa_murajaah` / O: HTML + modal Bootstrap + `<script>` |
| `modules/siswa/tabs/profil.php` | Tab Profil Pribadi: hero identity card, form biodata, form ganti sandi | Di-require oleh `siswa/index.php` | I: `$siswa_name`, `$siswa_nis`, `$halaqah_name`, `$data_siswa` / O: HTML form |
| `assets/css/tahfiz.css` | Stylesheet terpusat semua modul Tahfiz: `.hafalan-*`, `.ustadz-*`, `.siswa-*`, `.profil-*` | Di-link semua modul Tahfiz via `<link>` | I: — / O: class CSS reusable antar modul |
| `assets/css/admin.css` | Stylesheet modul Admin: sidebar `.sb-*`, topbar, KPI, tabel `.tbl`, badge, button `.btn-em`, modal | Di-link oleh `admin/index.php` via `<link>` | I: — / O: class CSS khusus admin |
| `assets/css/siswa.css` | Stylesheet modul Siswa: sidebar `.sb-*`, content wrapper, KPI card, hamburger, responsivitas | Di-link oleh `siswa/index.php` via `<link>` | I: — / O: class CSS khusus siswa |
| `database.sql` | Skema DDL (5 tabel) + seed data (3 akun + 1 halaqah) | Di-import otomatis oleh `config/database.php` saat auto-create DB | I: — / O: tabel `users`, `halaqah`, `setoran_hafalan`, `murajaah`, `konten_informasi` |

---

## 4. Arsitektur Sistem & Alur Request-Response

### Pola Arsitektur: Monolith Modular Seragam

Ketiga aktor (`admin`, `ustadz`, `siswa`) kini menggunakan pola arsitektur identik: `index.php` router ramping (<250 baris) + `actions/post_handler.php` + `data/queries|kurikulum.php` + `tabs/{tab}.php` dengan dispatcher whitelist. Tidak ada API endpoint terpisah — semua rendering dilakukan server-side.

#### Diagram Arsitektur Lintas Modul

```
Browser ──GET/POST──► Apache ──► modules/{aktor}/index.php
                                        │
                               ┌────────┼────────────┐
                               ▼        ▼             ▼
                         config/    data/        actions/
                         session +  queries/     post_handler.
                         database   kurikulum   php (jika POST)
                               │
                               ▼
                         tabs/{page_tab}.php ──► HTML Response
```

#### Alur Detail Request-Response Modul Ustadz

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

#### Ringkasan Whitelist Routing Tab Ketiga Aktor

| Aktor | Whitelist Tab | Tab Default |
|---|---|---|
| **Admin** | `['rekap', 'pengguna', 'halaqah', 'laporan', 'profil']` | `rekap` |
| **Ustadz** | `['rekap', 'hafalan', 'murajaah', 'target', 'profil']` | `rekap` |
| **Siswa** | `['progres', 'riwayat', 'profil']` | `progres` |

Semua modul mengarahkan tab tidak valid kembali ke tab default. Admin mengarahkan akses lama `?tab=sandi` ke `?tab=profil`.

---

## 5. Skema Database MySQL, Relasi Tabel & Catatan Migrasi ERD

### Database: `sistem_informasi_sekolah`

#### Tabel `users` *(Tabel Utama Pengguna)*

| Kolom | Tipe | Key | Keterangan |
|---|---|---|---|
| `id` | INT, AUTO_INCREMENT | PK | Primary key |
| `username` | VARCHAR(50) | UNIQUE | Username login |
| `password` | VARCHAR(255) | — | Hash bcrypt (`password_hash`) |
| `nama_lengkap` | VARCHAR(100) | — | Nama lengkap pengguna |
| `no_hp` | VARCHAR(20), NULL | — | Nomor kontak (di-alter saat runtime jika belum ada) |
| `role` | ENUM('admin','ustadz','siswa') | — | Peran pengguna |
| `nip_nis` | VARCHAR(30), NULL | — | NIP (ustadz/admin) atau NIS (siswa) |
| `halaqah` | VARCHAR(50), NULL | — | Nama halaqah pengguna |
| `status` | ENUM('aktif','nonaktif') | — | Status akun |
| `created_at` | TIMESTAMP | — | Waktu pembuatan |

#### Tabel `halaqah` *(Master Kelompok Halaqah)*

| Kolom | Tipe | Key | Keterangan |
|---|---|---|---|
| `id` | INT, AUTO_INCREMENT | PK | Primary key |
| `kode_halaqah` | VARCHAR(20) | UNIQUE | Kode halaqah (mis. HLQ-01) |
| `nama_halaqah` | VARCHAR(50) | — | Nama halaqah (mis. Al-Furqan) |
| `ustadz_id` | INT, NULL | FK → users.id | ID ustadz pembimbing |
| `nama_ustadz` | VARCHAR(100) | — | Cache nama ustadz |
| `target_juz` | VARCHAR(50) | — | Target hafalan juz |
| `created_at` | TIMESTAMP | — | Waktu pembuatan |

#### Tabel `setoran_hafalan` *(Log Setoran per Sesi)*

| Kolom | Tipe | Key | Keterangan |
|---|---|---|---|
| `id` | INT, AUTO_INCREMENT | PK | Primary key |
| `siswa_id` | INT | FK → users.id | ID siswa |
| `nama_siswa` | VARCHAR(100) | — | Cache nama siswa |
| `ustadz_id` | INT | FK → users.id | ID ustadz |
| `nama_ustadz` | VARCHAR(100) | — | Cache nama ustadz |
| `surah` | VARCHAR(50) | — | Nama surah |
| `ayat_awal` | INT | — | Ayat awal setoran |
| `ayat_akhir` | INT | — | Ayat akhir setoran |
| `kualitas` | VARCHAR(50) | — | Label kualitas (Lancar, Kurang Lancar, Tidak Lancar) |
| `nilai` | INT, DEFAULT 85 | — | Nilai numerik (65–95) |
| `catatan` | TEXT, NULL | — | Catatan ustadz |
| `tanggal_setor` | DATE | — | Tanggal setoran |
| `created_at` | TIMESTAMP | — | Waktu pembuatan |

#### Tabel `murajaah` *(Log Sesi Pengulangan)*

| Kolom | Tipe | Key | Keterangan |
|---|---|---|---|
| `id` | INT, AUTO_INCREMENT | PK | Primary key |
| `siswa_id` | INT | FK → users.id | ID siswa |
| `nama_siswa` | VARCHAR(100) | — | Cache nama siswa |
| `ustadz_id` | INT | FK → users.id | ID ustadz |
| `nama_ustadz` | VARCHAR(100) | — | Cache nama ustadz |
| `surah` | VARCHAR(50) | — | Surah yang diulang |
| `ayat_range` | VARCHAR(50) | — | Rentang ayat (mis. "Ayat 1–10") |
| `status_mutqin` | VARCHAR(50) | — | Hasil evaluasi (Sangat Mutqin, Lancar, dll.) |
| `tanggal_murajaah` | DATE | — | Tanggal murajaah |
| `created_at` | TIMESTAMP | — | Waktu pembuatan |

#### Tabel `konten_informasi` *(Konten Publik)*

| Kolom | Tipe | Key | Keterangan |
|---|---|---|---|
| `id` | INT, AUTO_INCREMENT | PK | Primary key |
| `judul` | VARCHAR(200) | — | Judul konten |
| `kategori` | ENUM('Pengumuman','Berita','Agenda') | — | Kategori konten |
| `isi` | TEXT | — | Isi konten |
| `penulis` | VARCHAR(100), DEFAULT 'Administrator' | — | Penulis |
| `tanggal_terbit` | DATE | — | Tanggal terbit |
| `created_at` | TIMESTAMP | — | Waktu pembuatan |

#### Tabel `target_hafalan` *(Dibuat Runtime — belum ada di database.sql)*

| Kolom | Tipe | Key | Keterangan |
|---|---|---|---|
| `id` | INT, AUTO_INCREMENT | PK | Primary key |
| `siswa_id` | INT | FK → users.id | ID siswa |
| `ustadz_id` | INT | FK → users.id | ID ustadz yang menetapkan |
| `surah_awal` | VARCHAR(180) | — | Batas awal hafalan (label dari `$kurikulum_target`) |
| `surah_akhir` | VARCHAR(180) | — | Batas akhir hafalan |
| `tanggal_mulai` | DATE | — | Tanggal mulai target |
| `tanggal_akhir` | DATE | — | Deadline target |
| `status` | VARCHAR(20), DEFAULT 'Aktif' | — | `Aktif` / `Nonaktif` |
| `created_at` | TIMESTAMP | — | Waktu pembuatan |

### Catatan Teknis Migrasi ERD

- **Auto-create DB**: `config/database.php` mendeteksi error 1049 (Unknown database) dan otomatis membuat database serta mengimpor `database.sql`. Mekanisme ini hanya jalan jika DB belum ada sama sekali — jika struktur tabel berubah, DB lama harus di-drop manual via phpMyAdmin.
- **DDL/ALTER runtime di PHP**: Kolom `no_hp` pada `users` di-alter saat runtime (`ALTER TABLE users ADD COLUMN no_hp`) di `modules/siswa/index.php` dan `modules/ustadz/actions/post_handler.php`. Tabel `target_hafalan` di-create dari `modules/ustadz/index.php` dengan `CREATE TABLE IF NOT EXISTS`. Kedua DDL ini perlu dipindahkan seluruhnya ke `database.sql` agar konsisten.
- **Kolom cache nama**: `nama_siswa` dan `nama_ustadz` pada `setoran_hafalan` dan `murajaah` menyimpan salinan nama. Jika ERD baru dinormalisasi, kolom ini perlu diganti dengan `JOIN users` untuk menghindari data tidak sinkron.

---

## 6. Matriks Status Pengembangan (Frontend UI vs Backend MySQL/API)

### Modul Ustadz (Wilayah: Rival)

| Komponen / Sub-Tab | Status Frontend UI | Status Backend MySQL / API | Langkah Integrasi Selanjutnya |
|---|---|---|---|
| **Router & Layout** (`index.php`) | Selesai | Terkoneksi DB | — |
| **Tab Rekap** — KPI & tabel | Selesai | Fallback `$rekap_data` statis | Ganti `$rekap_data` hardcode dengan query agregasi SQL (`COUNT`, `MAX`, progress per siswa) |
| **Tab Rekap** — Modal detail | Selesai | Data dari array `$rekap_data` statis | Sambungkan ke query live per `siswa_id` |
| **Tab Hafalan** — Panel siswa & surah | Selesai | Sebagian (daftar siswa dari DB, status surah dari JS state) | Integrasikan status surah dengan query `setoran_hafalan` aktual |
| **Tab Hafalan** — Input angka ayat | Selesai (input number + validasi batas) | Terkoneksi DB (validasi `$ayat_awal >= 1`, `$ayat_akhir >= $ayat_awal`) | — |
| **Tab Hafalan** — MUSHAF_MOCK | Selesai (5 surah hardcode) | Belum API | Ganti `MUSHAF_MOCK` statis dengan API Al-Qur'an publik (`api.alquran.cloud` atau `equran.id`) |
| **Tab Murajaah** — Panel siswa & riwayat | Selesai | Sebagian (data dari `$riwayat_setoran`) | Query per-siswa sudah ada; uji dengan data DB penuh |
| **Tab Murajaah** — Modal evaluasi | Selesai (ayat terkunci disabled) | Terkoneksi DB (UPDATE + INSERT) | Terapkan Post-Redirect-Get (PRG) agar tidak re-submit saat refresh |
| **Tab Target** | Selesai | Terkoneksi DB | Tampilkan riwayat semua target lama (bukan hanya aktif) |
| **Tab Profil** | Selesai (biodata + sandi 2 kolom) | Terkoneksi DB | — |

### Modul Admin (Wilayah: Radit)

| Komponen / Sub-Tab | Status Frontend UI | Status Backend MySQL / API | Langkah Integrasi Selanjutnya |
|---|---|---|---|
| **Router & Layout** (`index.php`) | Selesai | Terkoneksi DB | — |
| **Tab Rekap** | Selesai | Terkoneksi DB | — |
| **Tab Pengguna** (CRUD) | Selesai (modal terpisah di tab) | Terkoneksi DB | Tambah validasi duplikasi NIS/username |
| **Tab Halaqah** | Selesai (modal + JS counter & search) | Terkoneksi DB | — |
| **Tab Laporan** | Sebagian | Belum | Implementasi ekspor PDF/Excel |
| **Tab Profil (Biodata & Sandi terintegrasi)** | Selesai (2 kolom: biodata + form sandi) | Terkoneksi DB | — |
| **Arsitektur** | Modular (219 baris, CSS & modal terpisah) | — | — |

### Modul Siswa (Wilayah: Hafiz)

| Komponen / Sub-Tab | Status Frontend UI | Status Backend MySQL / API | Langkah Integrasi Selanjutnya |
|---|---|---|---|
| **Router & Layout** (`index.php`) | Selesai (sidebar seragam teal #11756D) | Terkoneksi DB | — |
| **Tab Progres** — KPI & target | Selesai (3 KPI, kartu target, progress bar teal) | Sebagian (KPI masih sebagian statis) | Integrasikan KPI (total juz, capaian target) dengan query DB aktual |
| **Tab Progres** — Setoran terakhir | Selesai (tabel 5 baris) | Terkoneksi DB | — |
| **Tab Riwayat** — Tabel gabungan | Selesai (setoran + murajaah, badge kualitatif) | Terkoneksi DB | — |
| **Tab Riwayat** — Modal detail | Selesai (modal + JS) | Terkoneksi DB | — |
| **Tab Riwayat** — Penilaian kualitatif | Selesai (badge Lancar/Kurang Lancar/Tidak Lancar) | Sebagian (konversi dari nilai DB) | Pastikan helper `resolveKualitas()` konsisten |
| **Tab Profil** | Selesai (hero card + biodata + sandi) | Terkoneksi DB | — |
| **Arsitektur** | Modular (seragam dengan ustadz/) | — | Pertimbangkan koneksi KPI ke query agregasi SQL |

### Halaman Publik

| Halaman | Status Frontend UI | Status Backend | Langkah Integrasi Selanjutnya |
|---|---|---|---|
| `index.php` (Beranda) | Selesai | Data statis | Koneksikan statistik ke DB |
| `akademik.php` | Selesai | Data statis | — |
| `spmb.php` | Selesai | Form belum tersambung DB | Simpan pendaftar ke tabel `spmb_pendaftar` |

---

## 7. Konvensi Kode, Standar UI/UX & Aturan Kolaborasi Git

### Isolasi Wilayah File Tim

**Dilarang keras** menyentuh file di luar wilayah modul sendiri tanpa koordinasi eksplisit di chat/meeting:

| Anggota | Boleh Edit | Butuh Koordinasi |
|---|---|---|
| **Rival** | `modules/ustadz/**` | `config/`, `assets/css/tahfiz.css`, `includes/` |
| **Radit** | `modules/admin/**` + `assets/css/admin.css` | `config/`, `assets/css/tahfiz.css`, `database.sql` |
| **Hafiz** | `modules/siswa/**` + `assets/css/siswa.css` | `config/`, `database.sql` |
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

### Standar Desain & Navigasi Seragam

- **Palet utama**: Teal-Green `#11756D`, aksen `#0D5C56`, soft tint `#E6F4F1`, latar kanvas `#F6F7F5`, kartu putih `border-radius: 16px`.
- **Penyeragaman sidebar ketiga aktor**: `MENU UTAMA` untuk operasional harian, `AKUN SAYA` di bagian bawah untuk `Profil Saya` yang menyatukan biodata & ubah sandi dalam 2 kolom, serta tombol merah `Keluar Sesi` (`#DC2626`).
- **Penilaian kualitatif**: `Lancar`, `Kurang Lancar`, `Tidak Lancar` menggantikan skor angka di ruang Siswa.
- **Input angka ayat berbatas**: Pada tab Hafalan Ustadz, input `type="number"` dengan `min="1"` dan `max="{totalAyat}"` surah terkait, plus validasi real-time.
- **Larangan `<style>` inline**: Semua CSS baru wajib masuk ke `tahfiz.css`, `admin.css`, atau `siswa.css`. Link CSS di `<head>` menggunakan `?v=<?= time() ?>` untuk cache-busting.

### Standar Keamanan & Kode

- **Wajib PDO Prepared Statements** untuk semua query yang melibatkan input pengguna
- **Wajib `htmlspecialchars()`** pada semua output variabel ke HTML
- **Hindari `$_REQUEST`** — gunakan `$_POST` atau `$_GET` secara eksplisit
- **Perlindungan CSRF** — admin menggunakan `csrf_token` (`bin2hex(random_bytes(32))`) yang diverifikasi via `hash_equals()` pada setiap form POST
- **Post-Redirect-Get (PRG)** — perlu diterapkan pada `post_handler.php` untuk mencegah re-submit saat refresh (saat ini belum diimplementasikan di semua aksi)
- **`modules/{aktor}/index.php` harus tetap di bawah 250 baris** — semua logika baru masuk ke `tabs/`, `actions/`, atau `data/`

### Konvensi Pesan Commit

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

**Dampak**: Lebih verbose, tidak ada ORM, validasi form manual. Trade-off ini diterima demi kemudahan onboarding.

---

### DL-002 · Refactoring Monolith menjadi Pola Modular Seragam

**Keputusan**: Memecah file `index.php` yang monolith (>2.000 baris pada modul Ustadz, ~1.620 baris pada modul Admin, ~901 baris pada modul Siswa) menjadi arsitektur `actions/` + `data/` + `tabs/` dengan dispatcher whitelist.

**Alasan**: File >1.000 baris tidak bisa di-review atau di-debug secara efisien. Konflik Git antar tab sering terjadi karena semua kode di satu file.

**Hasil**: Ketiga modul kini seragam:
- Modul Ustadz: `index.php` 208 baris
- Modul Admin: `index.php` 219 baris
- Modul Siswa: `index.php` 172 baris

Setiap tab independen dan bisa diedit tanpa memengaruhi tab lain. Pola modular ini berhasil diadopsi ke seluruh aktor.

---

### DL-003 · Mekanisme Murajaah Berbasis Evaluasi Baris Setoran

**Keputusan**: Modal evaluasi murajaah mengambil data dari baris `setoran_hafalan` yang ada, dengan dropdown Ayat Awal dan Ayat Akhir dikunci (`disabled`) agar ustadz tidak bisa mengubah rentang ayat yang sudah disetorkan.

**Alasan**: Murajaah adalah pengulangan hafalan yang sudah ada — rentang ayatnya sudah fixed. Ustadz hanya mengevaluasi kelancaran dan menambahkan catatan.

**Implementasi**: `UPDATE setoran_hafalan SET kualitas = :kualitas WHERE id = :id` + `INSERT INTO murajaah` dalam satu handler POST.

---

### DL-004 · MUSHAF_MOCK Statis (Sementara)

**Keputusan**: Tab Hafalan Ustadz saat ini menggunakan data mushaf hardcode (`MUSHAF_MOCK`) untuk 5 surah: Adh-Dhuha, Al-Insyirah, At-Tin, Al-Alaq, Al-Qadr.

**Alasan**: Integrasi API belum dilakukan karena membutuhkan koordinasi endpoint dan penanganan error jaringan.

**Rencana**: Ganti dengan pemanggilan ke API Al-Qur'an publik (seperti `api.alquran.cloud/v1/surah/{nomor}`) untuk mendapatkan teks Arab, transliterasi Latin, terjemahan Indonesia, dan URL audio murattal Juz 1–5 & Juz 30 secara dinamis.

---

### DL-005 · Penyeragaman Terminologi "Santri" menjadi "Siswa" di UI

**Keputusan**: Semua teks yang tampil ke pengguna (label, judul, badge, pesan) menggunakan kata "Siswa". Variabel PHP internal (`$santri_list`), parameter URL (`?santri=`), kolom DB (`nama_siswa`), dan atribut form (`name="santri_nama"`) tetap tidak diubah.

**Alasan**: Penyesuaian terminologi agar konsisten dengan nomenklatur sekolah formal. Perubahan variabel internal ditunda karena berisiko regresi lintas file dan butuh regression test menyeluruh.

---

### DL-006 · Mekanisme Auto-Create Database

**Keputusan**: `config/database.php` mendeteksi jika database belum ada (PDO error code 1049) dan otomatis membuat database serta mengimpor `database.sql`.

**Alasan**: Memudahkan setup anggota tim baru — cukup clone repo, jalankan XAMPP, buka browser. Tidak perlu import manual via phpMyAdmin.

**Catatan**: Tabel `target_hafalan` saat ini di-create dari `modules/ustadz/index.php` secara runtime. Kolom `no_hp` juga di-alter saat runtime. Kedua DDL ini perlu dipindahkan ke `database.sql` untuk konsistensi.

---

### DL-007 · Pemisahan Stylesheet ke File Eksternal dengan Cache-Busting

**Keputusan**: Semua styling dipisahkan ke file CSS eksternal: `tahfiz.css` (terpusat Tahfiz), `admin.css` (modul Admin), `siswa.css` (modul Siswa). File PHP tidak boleh mengandung blok `<style>` inline.

**Alasan**: Sebelumnya `admin/index.php` dan `ustadz/index.php` mengandung ratusan baris CSS inline yang menyebabkan konflik Git saat dua orang mengedit file yang sama. Pemindahan ke file CSS eksternal juga mengaktifkan cache browser.

**Implementasi**: Link di `<head>` menggunakan `?v=<?= time() ?>` untuk cache-busting.

---

### DL-008 · Penyeragaman Palet Warna, Penilaian Kualitatif Siswa & Validasi Rentang Ayat

**Keputusan**:

1. Menyeragamkan identitas visual utama dengan palet Teal-Green `#11756D`, aksen `#0D5C56`, soft tint `#E6F4F1`, serta latar `#F6F7F5` di seluruh portal aktor (Admin, Ustadz, Siswa).
2. Menyatukan menu `Profil Saya` dan `Ubah Kata Sandi` ke satu halaman dengan tata letak 2 kolom di semua aktor, serta menyeragamkan struktur sidebar (`MENU UTAMA` + `AKUN SAYA`).
3. Mengganti tampilan angka numerik (skor nilai 0–100) pada ruang Siswa (tab Progres dan Riwayat) menjadi penilaian kualitatif berbasis badge status: `Lancar`, `Kurang Lancar`, dan `Tidak Lancar`, dengan konversi otomatis dari nilai historis DB.
4. Mengganti elemen dropdown rentang ayat pada form input setoran tab Hafalan Ustadz dengan input angka (`type="number"`) yang divalidasi secara interaktif berbatas nilai minimal 1 hingga batas maksimal total ayat surah yang dipilih.

**Alasan**: Mengurangi beban psikologis siswa dari persaingan angka numerik murni, menciptakan bahasa desain yang kohesif antar-dashboard, menyatukan pengelolaan akun dalam satu halaman, dan mencegah kesalahan input ayat yang melebihi jumlah ayat riil surah Al-Qur'an.

---

*Dokumen ini diperbarui setiap kali ada perubahan arsitektur signifikan. Simpan di root repositori dan pastikan selalu in-sync dengan kode aktual.*
