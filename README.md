# 🍳 RecipeBox

### Website Kumpulan dan Manajemen Resep Masakan

RecipeBox adalah aplikasi web untuk mencari, melihat, dan menyimpan resep masakan favorit. Aplikasi ini juga menyediakan halaman Admin untuk mengelola resep, kategori, dan informasi yang ditampilkan kepada pengguna.

Proyek ini dibuat untuk Ujian Tengah Semester (UTS) mata kuliah **Konstruksi dan Evolusi Perangkat Lunak (KEPL)** tahun akademik **2026/2027**. RecipeBox menggunakan frontend Vue.js 3, REST API Laravel, database MySQL, dan Docker Compose. Backend dijalankan dengan FrankenPHP, sedangkan proses pengujian dan deployment image Docker menggunakan GitHub Actions.

## 1. Informasi Proyek

| Informasi | Keterangan |
|---|---|
| Nama proyek | RecipeBox |
| Nama mahasiswa | Mardhika Murni Pramestika |
| NIM | 24/533524/SV/23914 |
| Mata kuliah | Konstruksi dan Evolusi Perangkat Lunak (KEPL) |
| Jenis tugas | Ujian Tengah Semester (UTS) |
| Tahun akademik | 2026/2027 |
| Jenis aplikasi | Website manajemen resep masakan |
| Backend | Laravel 12 REST API dan Laravel Sanctum |
| Frontend | Vue.js 3, Vue Router, dan Axios |
| Database | MySQL 8.4 |
| Server backend | FrankenPHP |
| Server frontend | Nginx |
| Repository | [GitHub RecipeBox](https://github.com/Mardhika20/uts-pl-24-533524-SV-23914) |

## 2. Deskripsi Aplikasi

RecipeBox menyediakan resep dari berbagai kategori, seperti makanan utama, camilan, minuman, dessert, dan sarapan. Pengguna bisa melihat bahan, langkah pembuatan, waktu memasak, jumlah porsi, tingkat kesulitan, serta menyimpan resep ke daftar favorit.

Aplikasi memiliki dua role, yaitu **User** dan **Admin**. User dapat menjelajahi dan menyimpan resep favorit, sedangkan Admin dapat menambahkan, mengubah, dan menghapus data resep melalui halaman administrasi.

## 3. Tujuan Pengembangan

1. Membuat REST API menggunakan Laravel dengan autentikasi token Laravel Sanctum.
2. Membuat tampilan aplikasi menggunakan Vue.js 3 dan menghubungkannya ke Laravel melalui Axios.
3. Menerapkan fitur CRUD resep serta pembatasan hak akses Admin dan User.
4. Menjalankan frontend, backend, dan MySQL dalam container terpisah menggunakan Docker Compose.
5. Menggunakan FrankenPHP sebagai server aplikasi Laravel.
6. Menjalankan pengujian otomatis menggunakan GitHub Actions (CI).
7. Membangun dan mengunggah image Docker ke GitHub Container Registry (GHCR) melalui workflow CD.

## 4. Fitur Aplikasi

### 4.1 Fitur User

- Registrasi, login, dan logout.
- Melihat halaman beranda dan daftar resep.
- Mencari resep berdasarkan nama dan memfilter berdasarkan kategori.
- Melihat detail resep, bahan, dan langkah pembuatannya.
- Melihat informasi waktu memasak, porsi, dan tingkat kesulitan.
- Menambahkan resep ke favorit, melihat favorit, dan menghapus favorit.

### 4.2 Fitur Admin

- Login dan logout.
- Melihat dashboard dan ringkasan data aplikasi.
- Melihat daftar dan detail resep.
- Menambahkan resep baru, termasuk informasi dan gambar resep.
- Mengubah serta menghapus resep.
- Menentukan kategori, bahan, langkah, waktu memasak, porsi, dan tingkat kesulitan.

### 4.3 Hak Akses

- **Admin:** dapat mengelola resep melalui endpoint yang dilindungi middleware Admin.
- **User:** dapat melihat resep dan mengelola daftar favoritnya.
- Endpoint yang memerlukan login menggunakan token Laravel Sanctum.

## 5. Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| Laravel 12 | Backend REST API |
| Laravel Sanctum | Autentikasi menggunakan token |
| Vue.js 3 | Antarmuka aplikasi |
| Vue Router | Navigasi antarmenu dan halaman |
| Axios | Mengirim permintaan HTTP ke API Laravel |
| MySQL 8.4 | Menyimpan data pengguna, kategori, resep, dan favorit |
| Docker dan Docker Compose | Membangun serta menjalankan layanan dalam container |
| FrankenPHP | Server untuk menjalankan Laravel |
| Caddyfile | Pengaturan server dan log HTTP FrankenPHP |
| Nginx | Menyajikan frontend Vue.js serta meneruskan permintaan API |
| Git dan GitHub | Version control dan pengelolaan repository |
| GitHub Actions | Pengujian otomatis dan workflow deployment |
| GitHub Container Registry (GHCR) | Penyimpanan image Docker |
| Postman | Pengujian REST API |

## 6. Arsitektur Aplikasi

Frontend, backend, dan database berjalan sebagai tiga service terpisah di Docker Compose.

```text
                Pengguna / Browser
                        |
                        v
              Frontend Vue.js
                 (Nginx)
                        |
                 HTTP / Axios
                        |
                        v
             Backend Laravel API
                 (FrankenPHP)
                        |
                 Query Database
                        |
                        v
                   MySQL
```

Alur data dimulai saat pengguna melakukan aktivitas pada Vue.js, misalnya membuka daftar resep atau menambahkan resep baru. Axios mengirim permintaan API melalui frontend/Nginx ke backend Laravel. Laravel memeriksa token, memvalidasi data, lalu membaca atau mengubah data di MySQL. Hasilnya dikirim kembali dalam bentuk respons JSON untuk ditampilkan pada halaman Vue.js.

Di dalam jaringan Docker Compose, nama service seperti `backend` dan `db` digunakan untuk komunikasi antarkontainer.

## 7. Struktur Database

Tabel utama yang digunakan RecipeBox adalah:

| Tabel | Fungsi |
|---|---|
| `users` | Menyimpan akun serta role Admin atau User |
| `categories` | Menyimpan daftar kategori resep |
| `recipes` | Menyimpan judul, deskripsi, bahan, langkah, waktu, porsi, tingkat kesulitan, gambar, dan kategori resep |
| `favorites` | Menyimpan hubungan antara pengguna dan resep yang difavoritkan |
| `personal_access_tokens` | Menyimpan token autentikasi Laravel Sanctum |

Pada tabel `recipes`, kolom utama yang digunakan mencakup `id`, `user_id`, `category_id`, `title`, `description`, `ingredients`, `instructions`, `cooking_time`, `servings`, `difficulty`, `image_url`, `created_at`, dan `updated_at`.

Relasi resep dan kategori menggunakan konsep *one-to-many*: satu kategori dapat memiliki banyak resep, sedangkan satu resep termasuk dalam satu kategori. Tabel favorit menghubungkan akun pengguna dengan resep yang disimpan.

## 8. REST API RecipeBox

### 8.1 Autentikasi

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| POST | `/api/register` | Membuat akun User baru | Publik |
| POST | `/api/login` | Login dan mendapatkan token | Publik |
| POST | `/api/logout` | Menghapus token aktif | Login |
| GET | `/api/me` | Melihat informasi pengguna | Login |

### 8.2 Resep

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/recipes` | Menampilkan resep | Login |
| GET | `/api/recipes/{id}` | Menampilkan detail resep | Login |
| POST | `/api/recipes` | Menambahkan resep | Admin |
| PUT | `/api/recipes/{id}` | Mengubah resep | Admin |
| DELETE | `/api/recipes/{id}` | Menghapus resep | Admin |

### 8.3 Kategori

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/categories` | Menampilkan daftar kategori | Login |

### 8.4 Favorit

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/favorites` | Menampilkan favorit pengguna | Login |
| POST | `/api/favorites/{recipeId}` | Menambahkan favorit | Login |
| DELETE | `/api/favorites/{recipeId}` | Menghapus favorit | Login |

### 8.5 Dashboard Admin

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/admin/dashboard` | Menampilkan ringkasan data | Admin |

API menggunakan JSON untuk pertukaran data. Pada pengujian, beberapa status HTTP yang digunakan antara lain `200 OK`, `201 Created`, `403 Forbidden`, `404 Not Found`, dan `422 Unprocessable Content`. Permintaan tanpa autentikasi seharusnya menggunakan `401 Unauthorized`.

**Catatan pengujian:** Pada salah satu pengujian endpoint `/api/recipes` tanpa token setelah migrasi ke FrankenPHP, respons berisi `Unauthenticated` tetapi header menunjukkan `200 OK`. Penanganan HTTP status untuk kasus tersebut masih perlu ditinjau agar sesuai dengan status `401 Unauthorized` yang diharapkan.

## 9. Halaman Website

### Halaman User

1. Login dan Registrasi.
2. Beranda.
3. Jelajahi Resep (pencarian dan filter).
4. Detail Resep.
5. Favorit Saya.

### Halaman Admin

1. Dashboard Admin.
2. Kelola Resep.
3. Tambah Resep.
4. Edit Resep.
5. Detail dan Hapus Resep.

Pengguna diarahkan ke halaman yang sesuai setelah login berdasarkan role akun yang digunakan.

## 10. Struktur Repository

Berikut gambaran file dan folder penting di repository RecipeBox:

```text
uts-pl-24-533524-SV-23914/
├── backend/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── tests/
│   ├── Caddyfile
│   ├── Dockerfile
│   ├── .dockerignore
│   ├── composer.json
│   └── composer.lock
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   ├── router/
│   │   ├── services/
│   │   └── views/
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── .dockerignore
│   ├── package.json
│   └── package-lock.json
├── postman/
│   └── RecipeBox.postman_collection.json
├── .github/
│   └── workflows/
│       └── ci.yml
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

> Struktur ini menunjukkan file utama yang digunakan. Beberapa folder atau file pendukung lain dapat tersedia di repository.

## 11. Strategi Branch GitHub

| Branch | Kegunaan |
|---|---|
| `main` | Branch utama aplikasi |
| `dev` | Tempat integrasi dan pengujian perubahan |
| `feature/laravel-api` | Pengembangan backend REST API |
| `feature/vue-frontend` | Pengembangan antarmuka Vue.js |
| `feature/github-cd` | Pengembangan workflow CI/CD serta integrasi Docker dan FrankenPHP |

Perubahan dikerjakan pada branch fitur, dibuatkan Pull Request menuju `dev`, kemudian digabungkan ke `main` setelah pemeriksaan berhasil. Aturan perlindungan branch digunakan untuk membatasi merge langsung dan mewajibkan pemeriksaan CI.

## 12. Continuous Integration (CI)

CI menggunakan workflow GitHub Actions di `.github/workflows/ci.yml` dan dijalankan saat Pull Request menuju `dev` atau `main`.

Dua job utama yang digunakan:

- **Backend Test:** menjalankan `php artisan test` untuk menguji fitur Laravel.
- **Frontend Build:** menjalankan `npm ci` dan `npm run build` untuk memastikan aplikasi Vue.js dapat dibangun.

Pull Request diperiksa melalui GitHub Actions sebelum perubahan digabungkan ke branch yang dilindungi.

## 13. Continuous Deployment (CD)

Workflow CD membangun dan mengunggah image Docker ketika perubahan masuk ke branch `main` sesuai kondisi workflow GitHub Actions.

Tahapannya:

1. Mengambil kode proyek dari repository.
2. Menjalankan pemeriksaan yang diperlukan.
3. Membangun image Docker backend dan frontend.
4. Memberikan tag image berdasarkan commit SHA, bukan tag `latest`.
5. Mengunggah image ke GitHub Container Registry (GHCR).

Pengujian pada Pull Request tidak menjalankan job Deploy GHCR, sedangkan deployment pada branch `main` telah diuji melalui GitHub Actions. Informasi rahasia workflow disimpan melalui GitHub Actions Secrets.

## 14. Docker dan Docker Compose

RecipeBox menggunakan tiga service berikut:

| Service | Teknologi | Port host | Fungsi |
|---|---|---|---|
| `frontend` | Vue.js dan Nginx | `8080` | Menampilkan website |
| `backend` | Laravel dan FrankenPHP | `8000` | Melayani REST API |
| `db` | MySQL 8.4 | Tidak dipublikasikan | Menyimpan database |

Backend menggunakan image dasar `dunglas/frankenphp:1.4-php8.3` dengan konfigurasi server pada `backend/Caddyfile`. Frontend menggunakan proses build Vue.js yang hasilnya disajikan melalui Nginx.

### 14.1 Persiapan

Pastikan Git, Docker Desktop, dan Docker Compose tersedia. Clone repository:

```bash
git clone https://github.com/Mardhika20/uts-pl-24-533524-SV-23914.git
cd uts-pl-24-533524-SV-23914
```

Buat file `.env` di folder utama dengan menyalin `.env.example`, lalu isi konfigurasi database sesuai kebutuhan:

```env
DB_DATABASE=recipebox
DB_USERNAME=recipebox_user
DB_PASSWORD=isi_password_database_lokal
DB_ROOT_PASSWORD=isi_password_root_mysql_lokal
```

Nilai di atas hanya contoh pengisian. Jangan menuliskan password asli pada README atau mengunggah `.env` ke GitHub. Konfigurasi Laravel seperti `APP_KEY` juga harus tersedia sesuai kebutuhan lingkungan backend.

### 14.2 Build dan Menjalankan Aplikasi

Dari folder utama proyek, jalankan:

```bash
docker compose up -d --build
```

Periksa status ketiga service:

```bash
docker compose ps
```

Setelah container berjalan, aplikasi dapat diakses melalui:

- **Frontend RecipeBox:** http://localhost:8080
- **Backend Laravel:** http://localhost:8000

Pada lingkungan database yang masih baru, jalankan migrasi dan seeder sesuai kebutuhan:

```bash
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan db:seed --class=UserSeeder
```

### 14.3 Perintah Docker Compose Lainnya

```bash
# Melihat log backend
docker compose logs backend --tail=50

# Memeriksa status container
docker compose ps

# Menghentikan dan menghapus container tanpa menghapus named volume
docker compose down

# Menjalankan kembali service
docker compose up -d
```

**Penting:** Hindari `docker compose down -v` jika ingin mempertahankan data MySQL, karena opsi `-v` menghapus named volume yang digunakan Compose.

### 14.4 Penyimpanan Data MySQL

Database MySQL menggunakan named volume `mysql_data` yang dipasang ke `/var/lib/mysql`. Volume Docker ini bernama `recipebox_mysql_data` pada proyek lokal yang diuji. Data resep tetap tersedia setelah container dihentikan atau dibuat ulang selama volume tersebut tidak dihapus.

**Catatan gambar resep:** Named volume `mysql_data` hanya menyimpan database, bukan file gambar yang diunggah ke folder storage Laravel. Jika ingin file upload tetap tersedia ketika container backend dibuat ulang, penyimpanan gambar perlu disiapkan secara terpisah, misalnya menggunakan volume atau penyimpanan eksternal.

## 15. Pengujian Aplikasi

### 15.1 Laravel Feature Test

Pengujian otomatis dilakukan pada fitur autentikasi, CRUD, validasi, hak akses, serta beberapa fitur tambahan seperti favorit.

```bash
docker compose exec backend php artisan test
```

### 15.2 Postman

Postman digunakan untuk menguji:

- Registrasi, login, dan logout.
- Akses API dengan dan tanpa token.
- Operasi CRUD resep.
- Penanganan validasi dan data yang tidak ditemukan.
- Pembatasan hak akses Admin dan User.
- Endpoint kategori serta fitur favorit.

Postman Collection disimpan di folder `postman/` dalam format JSON. Token, password, dan informasi sensitif tidak boleh dicantumkan dalam file yang dipublikasikan.

### 15.3 Pengujian Frontend dan Integrasi

Pengujian melalui browser mencakup fitur login, tampil resep, tambah, ubah, hapus, pencarian, kategori, dan favorit. Aktivitas API dapat dilihat melalui tab Network pada Developer Tools browser untuk memastikan frontend Vue.js dapat berkomunikasi dengan Laravel.

### 15.4 Verifikasi FrankenPHP

Proses utama backend dan log container telah diperiksa untuk memastikan Laravel menggunakan FrankenPHP. Log menunjukkan pesan `FrankenPHP started` dan `handled request` untuk endpoint `/api/recipes`.

Contoh pemeriksaan:

```bash
docker compose logs backend --tail=50
```

## 16. Keamanan Aplikasi

- Password pengguna disimpan menggunakan hashing.
- Autentikasi API menggunakan Laravel Sanctum.
- Hak akses Admin dan User diperiksa pada backend.
- Input divalidasi sebelum diproses.
- File `.env` diabaikan oleh Git menggunakan `.gitignore`.
- File `.env.example` disediakan tanpa password asli.
- Rahasia workflow disimpan melalui GitHub Actions Secrets.
- Postman Collection perlu dipastikan tidak memuat token aktif atau kredensial asli sebelum dipublikasikan.

## 17. Kendala dan Solusi Selama Pengembangan

Beberapa kendala yang ditemui selama pengerjaan proyek:

| Kendala | Penanganan |
|---|---|
| Gambar resep tidak muncul | Memeriksa file upload dan memperbaiki symbolic link `public/storage` agar mengarah ke folder storage Laravel |
| Perubahan direktori backend setelah menggunakan FrankenPHP | Menyesuaikan direktori aplikasi dan konfigurasi Caddyfile |
| Dockerfile gagal di-build ketika menambahkan ekstensi PHP | Memperbaiki penulisan baris instalasi ekstensi pada Dockerfile |
| Laravel membutuhkan ekstensi PHP `intl` untuk perintah pemeriksaan database | Menambahkan `intl` pada instalasi ekstensi PHP dan membangun ulang backend |
| Status respons autentikasi tidak sesuai yang diharapkan pada salah satu pengujian | Dicatat untuk pemeriksaan lanjutan pada penanganan status HTTP Laravel |

## 18. Dokumentasi UTS

Laporan UTS disusun dalam format PDF dan mencakup:

1. Pembuatan repository GitHub, clone, dan pengelolaan branch.
2. Implementasi Laravel REST API, Sanctum, CRUD, dan pengujian Postman.
3. Pengembangan frontend Vue.js beserta integrasi Axios.
4. Pull Request, branch protection, dan Continuous Integration.
5. Continuous Deployment ke GHCR.
6. Dockerfile, Docker Compose, jaringan antarlayanan, dan named volume MySQL.
7. Penggunaan serta verifikasi FrankenPHP.
8. Screenshot proses pengerjaan, pengujian, kendala, dan solusi.

## 19. Informasi Mahasiswa

- **Nama:** Mardhika Murni Pramestika
- **NIM:** 24/533524/SV/23914
- **Mata kuliah:** Konstruksi dan Evolusi Perangkat Lunak (KEPL)
- **Jenis tugas:** Ujian Tengah Semester (UTS)
- **Tahun akademik:** 2026/2027

## 20. Status Proyek

**Implementasi utama RecipeBox telah selesai untuk kebutuhan UTS.**

Fitur utama, integrasi frontend dan backend, koneksi database MySQL, Docker Compose, pengujian API, CI/CD, dan penggunaan FrankenPHP sudah diterapkan serta diuji. Dokumentasi ini menggambarkan hasil pengerjaan proyek RecipeBox, dengan catatan bahwa beberapa detail teknis masih dapat disempurnakan setelah pengujian.

---

**RecipeBox — Discover, Cook, and Save Your Favorite Recipes.**
