# 🍳 RecipeBox

### Website Kumpulan dan Manajemen Resep Masakan

**RecipeBox** merupakan aplikasi berbasis web yang dirancang untuk membantu pengguna mencari, melihat, dan menyimpan berbagai resep masakan. Aplikasi ini juga menyediakan halaman administrasi untuk mengelola informasi resep secara terpusat.

Project RecipeBox dikembangkan sebagai tugas **Ujian Tengah Semester (UTS)** mata kuliah **Konstruksi dan Evolusi Perangkat Lunak (KEPL)** Tahun Akademik 2026/2027.

Pengembangan aplikasi menerapkan arsitektur frontend dan backend terpisah, REST API, autentikasi berbasis token, containerization menggunakan Docker, serta otomatisasi pengujian dan deployment melalui GitHub Actions.

---

## 1. Informasi Project

| Informasi | Keterangan |
|---|---|
| Nama Project | RecipeBox |
| Nama | Mardhika Murni Pramestika |
| NIM | 24/533524/SV/23914 |
| Mata Kuliah | Konstruksi dan Evolusi Perangkat Lunak (KEPL) |
| Jenis Tugas | Ujian Tengah Semester (UTS) |
| Tahun Akademik | 2026/2027 |
| Jenis Aplikasi | Website Manajemen Resep Masakan |
| Backend | Laravel REST API |
| Frontend | Vue.js 3 |
| Database | MySQL |

## 2. Deskripsi Aplikasi

RecipeBox merupakan website yang menyediakan kumpulan resep masakan dari berbagai kategori, seperti makanan utama, camilan, minuman, dessert, dan sarapan.

Pengguna dapat mencari resep berdasarkan nama, memfilter berdasarkan kategori, melihat bahan dan langkah pembuatan, serta menyimpan resep favorit.

Aplikasi memiliki dua role, yaitu **User** dan **Admin**, dengan hak akses yang berbeda. User berfokus pada pencarian dan penyimpanan resep, sedangkan Admin bertanggung jawab mengelola seluruh data resep melalui halaman administrasi.

## 3. Tujuan Pengembangan

Tujuan pengembangan RecipeBox adalah:

1. Membangun aplikasi web menggunakan Laravel sebagai backend REST API dan Vue.js sebagai frontend.
2. Mengimplementasikan autentikasi berbasis token menggunakan Laravel Sanctum.
3. Menerapkan operasi Create, Read, Update, dan Delete (CRUD) pada data resep.
4. Mengintegrasikan frontend, backend, dan database menggunakan Docker Compose.
5. Mengimplementasikan pengujian otomatis menggunakan GitHub Actions.
6. Menerapkan Continuous Deployment dengan penyimpanan Docker image melalui GitHub Container Registry (GHCR).
7. Menggunakan FrankenPHP sebagai web server untuk menjalankan backend Laravel.

## 4. Fitur Aplikasi

### 4.1 User

User merupakan pengguna yang mengakses website untuk mencari dan menyimpan resep masakan.

Fitur yang tersedia:

- Login menggunakan akun yang tersedia.
- Logout dari aplikasi.
- Melihat halaman beranda.
- Menjelajahi daftar resep masakan.
- Mencari resep berdasarkan judul.
- Memfilter resep berdasarkan kategori.
- Melihat detail resep.
- Melihat daftar bahan masakan.
- Melihat langkah-langkah pembuatan.
- Melihat informasi waktu memasak, jumlah porsi, dan tingkat kesulitan.
- Menambahkan resep ke daftar favorit.
- Melihat daftar resep favorit.
- Menghapus resep dari daftar favorit.

### 4.2 Admin

Admin merupakan pengguna yang memiliki hak akses untuk mengelola data resep.

Fitur yang tersedia:

- Login dan logout.
- Mengakses dashboard admin.
- Melihat statistik jumlah resep, kategori, dan pengguna.
- Melihat seluruh daftar resep.
- Menambahkan resep baru.
- Mengedit informasi resep.
- Menghapus resep.
- Menentukan kategori resep.
- Mengatur bahan, langkah pembuatan, waktu memasak, jumlah porsi, dan tingkat kesulitan.

## 5. Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| Laravel | Membangun backend REST API |
| Laravel Sanctum | Autentikasi berbasis token |
| Vue.js 3 | Membangun antarmuka frontend |
| Vue Router | Mengatur navigasi halaman |
| Axios | Menghubungkan frontend dengan REST API |
| MySQL | Menyimpan data aplikasi |
| Docker | Menjalankan aplikasi dalam container |
| Docker Compose | Mengelola beberapa container |
| FrankenPHP | Menjalankan backend Laravel |
| Nginx | Menyajikan frontend Vue.js |
| Git dan GitHub | Version control dan kolaborasi pengembangan |
| GitHub Actions | Continuous Integration dan Continuous Deployment |
| GitHub Container Registry | Menyimpan Docker image |
| Postman | Menguji endpoint REST API |

## 6. Arsitektur Aplikasi

RecipeBox menggunakan arsitektur frontend dan backend yang terpisah.

```text
                Pengguna
                   |
                   v
          Frontend Vue.js
             (Nginx)
                   |
                   | HTTP Request / Axios
                   v
          Backend Laravel API
             (FrankenPHP)
                   |
                   | Query Database
                   v
                MySQL
```

Frontend bertugas menampilkan antarmuka aplikasi dan mengirimkan permintaan HTTP menggunakan Axios.

Backend Laravel menerima permintaan, memvalidasi data, memeriksa autentikasi dan hak akses pengguna, kemudian berkomunikasi dengan database MySQL.

Backend mengembalikan respons dalam format JSON yang selanjutnya ditampilkan oleh frontend.

Pada lingkungan Docker, ketiga layanan akan dijalankan dalam container terpisah menggunakan Docker Compose.

## 7. Struktur Database

RecipeBox dirancang menggunakan empat tabel utama.

### 7.1 Tabel `users`

Menyimpan informasi akun pengguna.

| Kolom | Keterangan |
|---|---|
| id | Primary key |
| name | Nama pengguna |
| email | Email pengguna |
| password | Password yang telah di-hash |
| role | Hak akses admin atau user |
| created_at | Waktu pembuatan data |
| updated_at | Waktu perubahan data |

### 7.2 Tabel `categories`

Menyimpan kategori resep.

| Kolom | Keterangan |
|---|---|
| id | Primary key |
| name | Nama kategori |
| slug | Identitas unik kategori |
| created_at | Waktu pembuatan data |
| updated_at | Waktu perubahan data |

Contoh kategori:

- Makanan Utama
- Camilan
- Minuman
- Dessert
- Sarapan

### 7.3 Tabel `recipes`

Menyimpan informasi resep masakan.

| Kolom | Keterangan |
|---|---|
| id | Primary key |
| user_id | ID admin pembuat resep |
| category_id | ID kategori |
| title | Judul resep |
| description | Deskripsi resep |
| ingredients | Daftar bahan masakan |
| instructions | Langkah-langkah pembuatan |
| cooking_time | Waktu memasak dalam menit |
| servings | Jumlah porsi |
| difficulty | Tingkat kesulitan |
| image_url | URL gambar resep |
| created_at | Waktu pembuatan data |
| updated_at | Waktu perubahan data |

Kolom `ingredients` dan `instructions` direncanakan menggunakan tipe JSON.

### 7.4 Tabel `favorites`

Menyimpan hubungan antara pengguna dan resep favorit.

| Kolom | Keterangan |
|---|---|
| id | Primary key |
| user_id | ID pengguna |
| recipe_id | ID resep favorit |
| created_at | Waktu penambahan favorit |

Setiap pasangan pengguna dan resep hanya dapat tersimpan satu kali.

Selain tabel utama tersebut, Laravel dapat menggunakan tabel tambahan untuk kebutuhan framework, termasuk penyimpanan token autentikasi.

## 8. Rancangan REST API

REST API digunakan sebagai penghubung antara frontend Vue.js dan backend Laravel.

### 8.1 Autentikasi

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| POST | `/api/login` | Login dan mendapatkan token | Publik |
| POST | `/api/logout` | Menghapus token login | Login |
| GET | `/api/me` | Melihat informasi akun | Login |

### 8.2 Resep

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/recipes` | Melihat daftar resep | Login |
| GET | `/api/recipes/{id}` | Melihat detail resep | Login |
| POST | `/api/recipes` | Menambahkan resep | Admin |
| PUT | `/api/recipes/{id}` | Mengubah resep | Admin |
| DELETE | `/api/recipes/{id}` | Menghapus resep | Admin |

### 8.3 Kategori

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/categories` | Melihat kategori resep | Login |

### 8.4 Favorit

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/favorites` | Melihat resep favorit | User |
| POST | `/api/favorites/{recipeId}` | Menambahkan resep favorit | User |
| DELETE | `/api/favorites/{recipeId}` | Menghapus resep favorit | User |

### 8.5 Dashboard Admin

| Method | Endpoint | Fungsi | Akses |
|---|---|---|---|
| GET | `/api/admin/dashboard` | Melihat statistik aplikasi | Admin |

Seluruh endpoint selain login direncanakan menggunakan autentikasi Laravel Sanctum.

Respons API akan menggunakan format JSON dengan HTTP status code yang sesuai, seperti:

- `200 OK`: permintaan berhasil.
- `201 Created`: data berhasil ditambahkan.
- `401 Unauthorized`: pengguna belum terautentikasi.
- `403 Forbidden`: pengguna tidak memiliki hak akses.
- `404 Not Found`: data tidak ditemukan.
- `422 Unprocessable Content`: data yang dikirim tidak memenuhi validasi.

## 9. Rancangan Halaman Website

### 9.1 Halaman User

1. **Login** — halaman autentikasi pengguna.
2. **Beranda** — menampilkan pengenalan RecipeBox dan pilihan resep.
3. **Jelajahi Resep** — menampilkan daftar resep, pencarian, dan filter kategori.
4. **Detail Resep** — menampilkan informasi lengkap resep.
5. **Favorit Saya** — menampilkan resep yang disimpan pengguna.

### 9.2 Halaman Admin

1. **Dashboard** — menampilkan ringkasan statistik aplikasi.
2. **Kelola Resep** — menampilkan daftar resep beserta tindakan pengelolaan.
3. **Tambah Resep** — formulir untuk membuat resep baru.
4. **Edit Resep** — formulir untuk memperbarui resep.

Halaman Login digunakan bersama oleh Admin dan User. Setelah login berhasil, pengguna diarahkan sesuai role masing-masing.

## 10. Struktur Repository

Struktur repository yang direncanakan:

```text
uts-pl-24-533524-SV-23914/
|
├── backend/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── tests/
│   ├── Dockerfile
│   ├── .dockerignore
│   ├── composer.json
│   └── composer.lock
│
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   ├── router/
│   │   ├── services/
│   │   └── views/
│   ├── Dockerfile
│   ├── .dockerignore
│   ├── package.json
│   └── package-lock.json
│
├── postman/
│   └── RecipeBox.postman_collection.json
│
├── .github/
│   └── workflows/
│       └── ci.yml
│
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

Struktur folder akan dilengkapi secara bertahap selama pengembangan aplikasi.

## 11. Strategi Pengembangan Git

Pengembangan RecipeBox menggunakan beberapa branch untuk memisahkan pekerjaan.

| Branch | Fungsi |
|---|---|
| `main` | Menyimpan versi utama aplikasi |
| `dev` | Integrasi dan pengujian hasil pengembangan |
| `feature/laravel-api` | Pengembangan backend Laravel |
| `feature/vue-frontend` | Pengembangan frontend Vue.js |

Alur pengembangan:

```text
feature/laravel-api ──┐
                      ├──> dev ──> main
feature/vue-frontend ─┘
```

Penggabungan perubahan dilakukan melalui Pull Request.

Branch protection akan digunakan untuk mewajibkan Pull Request dan memastikan pemeriksaan CI berhasil sebelum perubahan digabungkan ke branch yang dilindungi.

## 12. Continuous Integration (CI)

Continuous Integration direncanakan menggunakan GitHub Actions melalui file:

`.github/workflows/ci.yml`

Workflow CI memiliki minimal dua job utama.

**Backend Test**

Menjalankan pengujian otomatis Laravel menggunakan:

```bash
php artisan test
```

**Frontend Build**

Memasang dependensi dan membangun frontend menggunakan:

```bash
npm ci
npm run build
```

CI dijalankan untuk memeriksa perubahan kode sebelum proses penggabungan branch.

## 13. Continuous Deployment (CD)

Continuous Deployment digunakan untuk membangun dan mengirim Docker image ke GitHub Container Registry.

Proses CD direncanakan berjalan hanya ketika terdapat perubahan yang masuk ke branch `main`.

Tahapan CD:

1. Mengambil kode terbaru dari repository.
2. Menjalankan pemeriksaan yang diperlukan.
3. Membangun Docker image backend.
4. Membangun Docker image frontend.
5. Memberikan tag berdasarkan commit SHA.
6. Mengirim kedua image ke GitHub Container Registry.

Informasi sensitif seperti `APP_KEY` dan `DB_PASSWORD` tidak disimpan langsung di repository.

## 14. Docker dan Docker Compose

RecipeBox direncanakan menggunakan tiga service Docker Compose.

| Service | Teknologi | Fungsi |
|---|---|---|
| `db` | MySQL | Menyimpan database aplikasi |
| `backend` | Laravel dan FrankenPHP | Menyediakan REST API |
| `frontend` | Vue.js dan Nginx | Menampilkan aplikasi |

Aplikasi akan dijalankan melalui perintah:

```bash
docker compose up -d --build
```

Untuk memeriksa container:

```bash
docker compose ps
```

Untuk menghentikan container:

```bash
docker compose down
```

Database akan menggunakan Docker named volume agar data tetap tersimpan ketika container dihentikan dan dijalankan kembali.

Konfigurasi lingkungan akan menggunakan file `.env` lokal yang tidak diunggah ke GitHub. Contoh variabel konfigurasi disediakan melalui `.env.example`.

## 15. Pengujian Aplikasi

Pengujian RecipeBox direncanakan meliputi:

### Laravel Feature Test

Pengujian otomatis terhadap fungsi backend, termasuk autentikasi, validasi, dan akses endpoint.

Minimal dua Feature Test akan dibuat dan dijalankan.

### Postman

Postman digunakan untuk menguji REST API, termasuk:

- Login dan logout.
- Akses endpoint menggunakan token.
- Menampilkan data resep.
- Menambahkan resep.
- Mengubah resep.
- Menghapus resep.
- Menguji validasi data.
- Menguji pembatasan akses berdasarkan role.

Postman Collection akan disimpan di dalam folder `postman/` pada repository.

### Pengujian Integrasi

Pengujian dilakukan untuk memastikan frontend Vue.js dapat mengakses backend Laravel yang berjalan dalam container berbeda dan menyimpan perubahan ke database MySQL.

### Pengujian Persistensi

Pengujian dilakukan untuk memastikan data tetap tersimpan setelah container dihentikan menggunakan `docker compose down` dan dijalankan kembali.

## 16. Keamanan Aplikasi

Beberapa mekanisme keamanan yang direncanakan:

- Autentikasi menggunakan Laravel Sanctum.
- Password disimpan dalam bentuk hash.
- Endpoint API dilindungi menggunakan token.
- Hak akses Admin dan User dibatasi pada backend.
- Validasi data dilakukan sebelum penyimpanan.
- Kredensial database tidak ditulis langsung di source code.
- File `.env` tidak dimasukkan ke repository.
- Konfigurasi rahasia untuk workflow menggunakan GitHub Actions Secrets.

## 17. Dokumentasi UTS

Dokumentasi pengerjaan disusun dalam bentuk laporan PDF yang memuat:

1. Persiapan repository GitHub.
2. Implementasi Laravel REST API.
3. Implementasi frontend Vue.js.
4. Penggunaan branch dan Pull Request.
5. Konfigurasi GitHub Actions CI.
6. Konfigurasi Continuous Deployment ke GHCR.
7. Pembuatan Dockerfile.
8. Implementasi Docker Compose.
9. Penggunaan FrankenPHP.
10. Hasil pengujian dan screenshot.
11. Kendala selama pengembangan dan solusi yang diterapkan.

## 18. Informasi Mahasiswa

**Nama:** Mardhika Murni Pramestika  
**NIM:** 24/533524/SV/23914  
**Mata Kuliah:** Konstruksi dan Evolusi Perangkat Lunak (KEPL)  
**Jenis Tugas:** Ujian Tengah Semester (UTS)  
**Tahun Akademik:** 2026/2027

## 19. Status Project

**Dalam tahap pengembangan.**

Fitur, struktur repository, konfigurasi Docker, pengujian, dan workflow CI/CD akan dilengkapi secara bertahap sesuai progres implementasi.

---

**RecipeBox — Discover, Cook, and Save Your Favorite Recipes.**
