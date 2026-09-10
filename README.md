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

## Tema
Warna utama aplikasi: `#613b18` (diset di `assets/css/style.css` lewat CSS variable `--brand`).

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
├── assets/vendor/              # Bootstrap & Bootstrap Icons lokal (bukan CDN)
```

## Instalasi

1. Salin project ke folder web server (misal `htdocs/aplikasi-manajemen-mahasiswa`).
2. Import `database/schema.sql` ke MySQL.
3. Sesuaikan `config/database.php`: `DB_HOST`/`DB_USER`/`DB_PASS`, `BASE_URL`, dan patokan jam (`JAM_MASUK_MAHASISWA`, `JAM_MASUK_SISWA_PKL`, `JAM_PULANG_STANDAR`) jika perlu diubah.
4. Pastikan folder `assets/vendor/bootstrap/` dan `assets/vendor/bootstrap-icons/` sudah berisi file Bootstrap hasil `npm install` (lihat instruksi terpisah bila belum).
5. Buka `database/seed.php` di browser **satu kali** untuk membuat akun staf contoh dan 2 data peserta contoh, lalu **hapus file itu**.
6. Login staf di `login.php` (operator1 / dosen1, password `password123`), atau buka `portal.php` untuk mencoba Portal Publik peserta (NIM `2110511001` atau NISN `0051234567`).

## Catatan Keamanan
- Password staf di-hash (`password_hash`), semua query pakai prepared statements (PDO), form dilindungi CSRF token.
- Folder `uploads/foto/` dan `uploads/sertifikat/` diberi `.htaccess` untuk mencegah eksekusi file yang diunggah.
- Portal peserta sengaja tanpa password (cukup NIM/NISN) agar mudah diakses — pertimbangkan menambahkan captcha/rate-limit jika disebar ke jaringan publik luas.
