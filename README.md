# SIMAK Mahasiswa & Siswa PKL
Sistem Informasi Manajemen Kehadiran & Kegiatan untuk **Mahasiswa** dan **Siswa PKL**, berbasis PHP native (CRUD) + MySQL.

## Fitur per Role

**Operator** (akses penuh)
- CRUD data peserta (mahasiswa & siswa PKL — termasuk foto), data dosen/pembimbing (NIDN/NIDK), akun pengguna staf
- **Satu-satunya pihak yang dapat mengunggah sertifikat**
- Rekap absensi (dengan status Tepat Waktu/Terlambat/Pulang Cepat), rekap kegiatan harian, rekap sertifikat

**Dosen / Pembimbing**
- Melihat & mengelola data peserta bimbingannya (mahasiswa & siswa PKL)
- Melihat absensi dan kegiatan harian peserta bimbingan

**Portal Publik Peserta** (`portal.php` — sebelum login, cukup NIM/NISN)
- Absen masuk & keluar (dengan patokan jam otomatis: Mahasiswa masuk 08:00, Siswa PKL masuk 07:10, pulang 16:00)
- Input kegiatan harian (tanggal otomatis hari ini)
- Lihat & unduh sertifikat yang sudah diunggahkan operator
- Rekap absensi pribadi (total hadir, tepat waktu, terlambat)

<<<<<<< HEAD
## Tema
Warna utama aplikasi: `#613b18` (diset di `assets/css/style.css` lewat CSS variable `--brand`).
=======
## Antarmuka: Material 3 Expressive

Sejak versi ini aplikasi **tidak lagi memakai Bootstrap**. Seluruh tampilan dibangun di atas design
system Material 3 (Material You) sendiri yang ada di `assets/css/material3.css` — satu file, tanpa
build step, tanpa framework JavaScript.

**Yang ada di dalamnya**
- *Color tokens* hasil turunan seed `#613b18`: primary, secondary, tertiary, error, ditambah
  success & warning untuk status kehadiran, lengkap dengan pasangan `container` / `on-*`.
- *Type scale* Material 3 (display, headline, title, body, label) memakai Roboto Flex.
- *Shape scale* khas Expressive: sudut lebih besar dan berani (kartu 28px, tombol pill, dialog 28px).
- Komponen: app bar, navigation drawer, tombol (filled / tonal / outlined / text / elevated), FAB,
  icon button, kartu, kartu statistik, text field, select, segmented button, chip, label status,
  data table, dialog, banner.
- Detail interaksi Material 3: *state layer* saat hover/fokus dan *shape morph* — sudut tombol
  menyempit sesaat ketika ditekan.

**Aksesibilitas** — fokus keyboard selalu terlihat, target sentuh minimal 44px, kontras warna
mengikuti pasangan token Material 3, dan `prefers-reduced-motion` dihormati.

**JavaScript** — `assets/js/material3.js` (sekitar 2 KB) hanya menangani drawer di layar kecil,
dialog, dan menutup banner. Tidak ada dependensi.

### Ikon dan font

Ikon memakai **Material Symbols Rounded**, font memakai **Roboto Flex**, keduanya dimuat dari
Google Fonts. Agar tetap tampil tanpa internet, unduh versi lokalnya:

```bash
npm install material-symbols @fontsource-variable/roboto-flex
```

Lalu salin ke `assets/vendor/` dan ganti dua baris `<link ...fonts.googleapis.com...>` di
`includes/header.php`, `login.php`, `portal.php`, dan `portal_dashboard.php` dengan file lokal tersebut.
Bila font ikon gagal dimuat, yang tampil adalah nama ikonnya (misalnya "search"), bukan kotak kosong.

### Mengubah warna tema

Semua warna berasal dari satu blok token di bagian atas `assets/css/material3.css`. Untuk mengganti
warna aplikasi, ubah nilai-nilai di dalam `:root` — tidak perlu menyentuh file PHP mana pun.
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)

## Struktur Folder
```
simak-mahasiswa/
├── config/database.php        # koneksi DB, BASE_URL, path upload, patokan jam
├── database/schema.sql        # skema tabel
├── database/seed.php          # buat akun staf & data contoh (jalankan sekali)
├── includes/                  # auth, helper, header/sidebar/footer
├── operator/                  # halaman khusus operator
├── dosen/                     # halaman khusus dosen
├── portal.php                 # landing/portal publik (input NIM/NISN)
├── portal_dashboard.php       # dashboard peserta setelah masuk portal
├── portal_logout.php          # keluar dari sesi portal
├── login.php / logout.php / index.php   # login staf (operator/dosen)
├── uploads/foto/               # foto peserta
├── uploads/sertifikat/         # file sertifikat
<<<<<<< HEAD
├── assets/vendor/              # Bootstrap & Bootstrap Icons lokal (bukan CDN)
=======
├── assets/css/material3.css    # design system Material 3 (menggantikan Bootstrap)
├── assets/js/material3.js      # interaksi drawer, dialog, banner
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
```

## Instalasi

1. Salin project ke folder web server (misal `htdocs/aplikasi-manajemen-mahasiswa`).
2. Import `database/schema.sql` ke MySQL.
3. Sesuaikan `config/database.php`: `DB_HOST`/`DB_USER`/`DB_PASS`, `BASE_URL`, dan patokan jam (`JAM_MASUK_MAHASISWA`, `JAM_MASUK_SISWA_PKL`, `JAM_PULANG_STANDAR`) jika perlu diubah.
<<<<<<< HEAD
4. Pastikan folder `assets/vendor/bootstrap/` dan `assets/vendor/bootstrap-icons/` sudah berisi file Bootstrap hasil `npm install` (lihat instruksi terpisah bila belum).
=======
4. Tidak ada dependensi yang perlu di-build. Bila server tanpa akses internet, ikuti bagian "Ikon dan font" di atas untuk memakai font lokal.
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
5. Buka `database/seed.php` di browser **satu kali** untuk membuat akun staf contoh dan 2 data peserta contoh, lalu **hapus file itu**.
6. Login staf di `login.php` (operator1 / dosen1, password `password123`), atau buka `portal.php` untuk mencoba Portal Publik peserta (NIM `2110511001` atau NISN `0051234567`).

## Catatan Keamanan
- Password staf di-hash (`password_hash`), semua query pakai prepared statements (PDO), form dilindungi CSRF token.
- Folder `uploads/foto/` dan `uploads/sertifikat/` diberi `.htaccess` untuk mencegah eksekusi file yang diunggah.
- Portal peserta sengaja tanpa password (cukup NIM/NISN) agar mudah diakses — pertimbangkan menambahkan captcha/rate-limit jika disebar ke jaringan publik luas.
