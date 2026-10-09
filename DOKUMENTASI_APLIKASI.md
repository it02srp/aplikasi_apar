# Dokumentasi Aplikasi APAR Management — PT Sinar Rimba Pasifik

> **Stack:** Laravel 11 · SQLite/MySQL · Blade + Tailwind CSS (CDN) · Maatwebsite Excel · PWA

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Arsitektur & Struktur Direktori](#2-arsitektur--struktur-direktori)
3. [Database Schema](#3-database-schema)
4. [Alur Autentikasi & Otorisasi](#4-alur-autentikasi--otorisasi)
5. [Flow Utama Aplikasi](#5-flow-utama-aplikasi)
6. [Modul & Fitur Detail](#6-modul--fitur-detail)
7. [Keamanan](#7-keamanan)
8. [Export & Import Excel](#8-export--import-excel)
9. [PWA & QR Scan](#9-pwa--qr-scan)
10. [Konfigurasi Lingkungan](#10-konfigurasi-lingkungan)

---

## 1. Gambaran Umum

Aplikasi **APAR Management** adalah sistem manajemen Alat Pemadam Api Ringan (APAR) berbasis web untuk PT Sinar Rimba Pasifik. Fungsinya mencakup:

- Pendataan unit APAR (lokasi, jenis, kapasitas, tanggal produksi/kadaluarsa)
- Pemantauan status kadaluarsa secara real-time
- Pencatatan **pemeriksaan berkala** (kondisi fisik 6 komponen)
- Pencatatan **riwayat maintenance** (5 jenis tindakan)
- Notifikasi visual untuk APAR yang lewat/hampir jadwal inspeksi
- Scan QR code untuk akses publik ke detail APAR
- Export data ke Excel (4 sheet) dan import massal dari Excel
- Support PWA (Progressive Web App) — bisa dipasang di HP

---

## 2. Arsitektur & Struktur Direktori

```
aplikasi_apar/
├── app/
│   ├── Console/Commands/
│   │   └── GeneratePwaIcons.php       # Command artisan generate icon PWA
│   ├── Exports/
│   │   ├── AparDataExport.php         # Orchestrator export (4 sheet)
│   │   ├── AparAllSheet.php           # Sheet: semua APAR
│   │   ├── AparAlertSheet.php         # Sheet: APAR alert (kadaluarsa/hampir)
│   │   ├── AparMaintenanceSheet.php   # Sheet: jadwal maintenance
│   │   ├── AparSummarySheet.php       # Sheet: ringkasan statistik
│   │   ├── AparInspectionExport.php   # Export pemeriksaan (multi-sheet per APAR)
│   │   ├── AparInspectionSheet.php    # Sheet per APAR untuk pemeriksaan
│   │   └── AparTemplateExport.php     # Template Excel untuk import
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php     # Login / Logout
│   │   │   ├── AparController.php     # CRUD APAR + inspeksi + maintenance
│   │   │   └── UserController.php     # Manajemen user (superadmin only)
│   │   └── Middleware/
│   │       └── CheckRole.php          # Role-based access control
│   ├── Imports/
│   │   └── AparImport.php             # Import APAR dari Excel
│   ├── Models/
│   │   ├── User.php
│   │   ├── Apar.php
│   │   ├── AparInspection.php
│   │   └── AparMaintenance.php
│   └── Providers/
│       └── AppServiceProvider.php     # Boot: Paginator pakai Tailwind
├── database/
│   ├── migrations/                    # 9 file migrasi (lihat seksi 3)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php             # Seed superadmin default
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php              # Layout admin (sidebar + navbar)
│   │   └── guest.blade.php            # Layout publik (tanpa sidebar)
│   ├── auth/login.blade.php
│   ├── dashboard.blade.php
│   ├── scan.blade.php                 # Halaman scan QR (publik)
│   ├── apar/
│   │   ├── index.blade.php            # Daftar APAR + filter
│   │   ├── create.blade.php           # Form tambah APAR
│   │   ├── edit.blade.php             # Form edit APAR
│   │   ├── show.blade.php             # Detail APAR (publik, 3 tab)
│   │   ├── inspection.blade.php       # Daftar pemeriksaan berkala (admin)
│   │   ├── maintenance.blade.php      # Daftar maintenance + alert (admin)
│   │   ├── print.blade.php            # Cetak QR 1 APAR
│   │   └── print-all.blade.php        # Cetak QR semua APAR
│   └── users/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
└── routes/web.php                     # Semua route aplikasi
```

---

## 3. Database Schema

### Relasi Antar Tabel

```
users
 └── (1:N) apar_maintenances.performed_by  [set null on delete]
 └── (1:N) apar_inspections.inspected_by   [set null on delete]

apars
 └── (1:N) apar_maintenances.apar_id       [cascade delete]
 └── (1:N) apar_inspections.apar_id        [cascade delete]
```

---

### Tabel: `users`

| Kolom            | Tipe                           | Keterangan                  |
|------------------|--------------------------------|-----------------------------|
| `id`             | BIGINT UNSIGNED PK             | Auto-increment              |
| `username`       | VARCHAR UNIQUE                 | Login identifier (bukan email) |
| `password`       | VARCHAR                        | Bcrypt hashed (rounds=12)   |
| `role`           | ENUM('superadmin','admin')     | Default: `admin`            |
| `remember_token` | VARCHAR(100) nullable          | Cookie "ingat saya"         |
| `created_at`     | TIMESTAMP                      |                             |
| `updated_at`     | TIMESTAMP                      |                             |

---

### Tabel: `apars`

| Kolom                   | Tipe                                        | Keterangan                         |
|-------------------------|---------------------------------------------|------------------------------------|
| `id`                    | BIGINT UNSIGNED PK                          |                                    |
| `code`                  | VARCHAR UNIQUE                              | Format: `APAR-001`, auto-generate  |
| `location`              | VARCHAR                                     | Deskripsi lokasi (wajib)           |
| `building`              | VARCHAR nullable                            | Nama gedung                        |
| `floor`                 | VARCHAR(100) nullable                       | Lantai                             |
| `room`                  | VARCHAR(100) nullable                       | Ruangan                            |
| `type`                  | VARCHAR(100)                                | Jenis APAR, ucwords-normalized     |
| `capacity`              | DECIMAL(8,2)                                | Nilai kapasitas                    |
| `capacity_unit`         | ENUM('kg','liter')                          |                                    |
| `manufacture_date`      | DATE                                        | Tanggal produksi                   |
| `expiry_date`           | DATE                                        | Tanggal kadaluarsa                 |
| `last_inspection_date`  | DATE nullable                               | Auto-update saat simpan inspeksi/maintenance |
| `next_inspection_date`  | DATE nullable                               | Jadwal inspeksi berikutnya         |
| `condition`             | ENUM('Good','Needs Attention','Replace')    | Default: `Good`                    |
| `responsible_person`    | VARCHAR nullable                            | Penanggung jawab                   |
| `notes`                 | TEXT nullable                               |                                    |
| `is_maintenance`        | BOOLEAN                                     | Default: `false`                   |
| `maintenance_started_at`| TIMESTAMP nullable                          | Waktu mulai maintenance mode       |
| `created_at`            | TIMESTAMP                                   |                                    |
| `updated_at`            | TIMESTAMP                                   |                                    |

**Status APAR (computed, bukan kolom DB):**
- `expired` → `expiry_date < today`
- `near_expiry` → `today <= expiry_date <= today + 30 hari`
- `good` → tidak keduanya

---

### Tabel: `apar_maintenances`

| Kolom                  | Tipe                                                                              | Keterangan                    |
|------------------------|-----------------------------------------------------------------------------------|-------------------------------|
| `id`                   | BIGINT UNSIGNED PK                                                                |                               |
| `apar_id`              | FK → apars.id (cascade delete)                                                    |                               |
| `maintenance_date`     | DATE                                                                              | Tanggal tindakan (wajib)      |
| `next_inspection_date` | DATE nullable                                                                     | Jadwal inspeksi berikutnya    |
| `maintenance_type`     | ENUM('Inspeksi Rutin','Pengisian Ulang','Penggantian Komponen','Perbaikan','Lainnya') |                            |
| `technician`           | VARCHAR nullable                                                                  | Nama teknisi/petugas          |
| `notes`                | TEXT nullable                                                                     |                               |
| `performed_by`         | FK → users.id (set null on delete) nullable                                       | User yang mencatat            |
| `created_at`           | TIMESTAMP                                                                         |                               |
| `updated_at`           | TIMESTAMP                                                                         |                               |

---

### Tabel: `apar_inspections`

| Kolom                | Tipe                    | Keterangan                              |
|----------------------|-------------------------|-----------------------------------------|
| `id`                 | BIGINT UNSIGNED PK      |                                         |
| `apar_id`            | FK → apars.id (cascade) |                                         |
| `inspected_at`       | TIMESTAMP               | Waktu pemeriksaan (otomatis = now)      |
| `inspected_by`       | FK → users.id (set null) nullable |                               |
| `kondisi_handle`     | ENUM('OK','NOT OK') nullable | 1. Handle                          |
| `kondisi_selang`     | ENUM('OK','NOT OK') nullable | 2. Selang                          |
| `kondisi_pin_kunci`  | ENUM('OK','NOT OK') nullable | 3. Pin Kunci                       |
| `kondisi_indikator`  | ENUM('OK','NOT OK') nullable | 4. Indikator tekanan               |
| `kondisi_tabung`     | ENUM('OK','NOT OK') nullable | 5. Tabung                          |
| `kondisi_masa_apar`  | ENUM('OK','NOT OK') nullable | 6. Masa APAR (tidak kadaluarsa)    |
| `notes`              | TEXT nullable           |                                         |
| `created_at`         | TIMESTAMP               |                                         |
| `updated_at`         | TIMESTAMP               |                                         |

---

### Tabel-tabel Sistem Laravel

| Tabel                    | Fungsi                                        |
|--------------------------|-----------------------------------------------|
| `sessions`               | Penyimpanan sesi database                     |
| `cache` / `cache_locks`  | Cache database                                |
| `jobs` / `failed_jobs`   | Queue jobs (tidak aktif dipakai saat ini)     |
| `password_reset_tokens`  | Token reset password (tidak diimplementasi)   |
| `migrations`             | Riwayat migrasi Laravel                       |

---

### Urutan Migrasi

| File Migrasi                                                   | Perubahan                                             |
|----------------------------------------------------------------|-------------------------------------------------------|
| `0001_01_01_000000_create_users_table`                         | Buat: `users`, `sessions`, `password_reset_tokens`    |
| `0001_01_01_000001_create_cache_table`                         | Buat: `cache`, `cache_locks`                          |
| `0001_01_01_000002_create_jobs_table`                          | Buat: `jobs`, `job_batches`, `failed_jobs`            |
| `2024_01_01_000003_create_apars_table`                         | Buat: `apars` (dengan `type` ENUM awal)               |
| `2024_01_02_000001_create_apar_maintenances_table`             | Buat: `apar_maintenances`                             |
| `2024_01_02_000002_add_next_inspection_to_apar_maintenances`   | Tambah kolom `next_inspection_date` ke maintenances   |
| `2026_06_17_061830_add_maintenance_status_to_apars_table`      | Tambah `is_maintenance`, `maintenance_started_at`     |
| `2026_06_17_061833_create_apar_inspections_table`              | Buat: `apar_inspections`                              |
| `2026_06_22_000001_change_type_to_string_in_apars_table`       | Ubah `type` ENUM → VARCHAR(100), normalisasi data     |

---

## 4. Alur Autentikasi & Otorisasi

### Login Flow

```
Browser → GET /login  (middleware: guest)
       → tampil view auth/login

POST /login
  → Validate: username (required), password (required)
  → Auth::attempt(['username', 'password'], $remember)
     ├── Gagal  → back() + error 'Username atau password salah.'
     └── Sukses → session()->regenerate()
                → redirect()->intended(route('dashboard'))
```

### Logout Flow

```
POST /logout  (middleware: auth)
  → Auth::logout()
  → session()->invalidate()        ← hancurkan sesi lama
  → session()->regenerateToken()   ← cegah CSRF token reuse
  → redirect /login
```

### Middleware & Role

| Middleware         | Diperiksa                   | Aksi jika gagal          |
|--------------------|-----------------------------|--------------------------|
| `auth`             | User belum login            | Redirect `/login`        |
| `guest`            | User sudah login            | Redirect `dashboard`     |
| `role:superadmin`  | Role bukan superadmin       | `abort(403)`             |

**Hirarki Akses:**

```
Publik (tanpa login)
 ├── GET /scan              — Halaman scan QR
 └── GET /apar/{code}       — Detail APAR (read-only)

Admin (role: admin atau superadmin)
 ├── GET /dashboard
 ├── CRUD APAR (/apar)
 ├── Tambah/hapus pemeriksaan berkala (/inspeksi)
 ├── Tambah/hapus maintenance (/maintenance)
 ├── Toggle status maintenance APAR
 ├── Import/Export Excel
 └── Print QR

Superadmin saja
 └── Manajemen User — CRUD /users
```

---

## 5. Flow Utama Aplikasi

### 5.1 Dashboard

```
GET /dashboard
  → Hitung dari DB:
     total        = COUNT(apars)
     expired      = COUNT WHERE expiry_date < today
     nearExpiry   = COUNT WHERE today <= expiry_date <= today+30
     good         = total - expired - nearExpiry
     recentApars  = 5 APAR terbaru diperbarui (ORDER BY updated_at DESC)
  → view: 4 kartu statistik + tabel recent APAR
```

### 5.2 CRUD APAR

```
LIST   GET /apar
         Filter: search (code/location LIKE), condition, type
         Paginate 15/halaman dengan query string
         Dropdown jenis: Apar::distinct()->pluck('type')

CREATE GET /apar/create
         nextCode = Apar::generateCode()  [APAR-{id+1 padded 3 digit}]
       POST /apar
         Validasi semua field (lihat AparController::store)
         type di-normalize: ucwords(strtolower(trim()))
         Simpan → redirect apar.index

EDIT   GET /apar/{code}/edit
       PUT /apar/{code}
         Cari APAR by code (bukan id)
         Sama seperti store, tanpa unique code check
         Simpan → redirect apar.index

DELETE DELETE /apar/{code}
         Hard delete (cascade ke inspections + maintenances)
         Redirect apar.index
```

### 5.3 Pemeriksaan Berkala

```
DARI HALAMAN DETAIL APAR (POST /apar/{code}/inspection):
  → Validasi 6 kondisi: required|in:OK,NOT OK
  → AparInspection::create(inspected_at=now, inspected_by=Auth::id())
  → Update apars.last_inspection_date = today
  → Redirect /apar/{code}

DARI HALAMAN ADMIN (POST /inspeksi):
  → Tambahan validasi: apar_code (exists:apars,code)
  → Logic sama, redirect /inspeksi

LIST   GET /inspeksi
         Filter: search, date_from, date_to, year
         Paginate 25/halaman

HAPUS  DELETE /apar/inspection/{id}
         ?from=list  → redirect /inspeksi
         default     → redirect /apar/{code}

EXPORT POST /inspeksi/export
         Input: array of codes[]
         Output: .xlsx (1 sheet per APAR dipilih)
```

### 5.4 Maintenance

```
DARI HALAMAN DETAIL APAR (POST /apar/{code}/maintenance):
  → Validasi: maintenance_date, next_inspection_date (after:maintenance_date),
              maintenance_type (5 pilihan), technician?, notes?
  → AparMaintenance::create(performed_by=Auth::id())
  → Update apars.last_inspection_date + next_inspection_date
  → Redirect /maintenance

DARI HALAMAN ADMIN (POST /maintenance):
  → Tambahan validasi: apar_code (exists:apars,code)
  → Logic sama

LIST   GET /maintenance
         Filter: search, type, date_from, date_to, year
         Paginate 25/halaman
         Alert: overdueInspection (next_inspection_date < today)
         Alert: upcomingInspection (today <= next_inspection_date <= today+30)

HAPUS  DELETE /apar/maintenance/{id}
         ?from=list  → redirect /maintenance
         default     → redirect /apar/{code}

TOGGLE STATUS  POST /apar/{code}/toggle-maintenance
         is_maintenance = true  → set false, maintenance_started_at = null
         is_maintenance = false → set true,  maintenance_started_at = now()
```

### 5.5 Scan QR → Detail APAR

```
GET /scan  (publik)
  → Kamera aktif via html5-qrcode@2.3.8
  → Scan QR berhasil:
       QR berisi URL penuh  → window.location.href = URL
       QR berisi kode saja  → window.location.href = /apar/{kode}
  → Input manual: ketik kode → /apar/{kode}

GET /apar/{code}  (publik, route diletakkan SETELAH route /apar/* agar tidak konflik)
  → Apar::where('code', $code)
        ->with(['maintenances.performer', 'inspections.inspector'])
        ->firstOrFail()
  → 3 Tab di view:
       Tab 1 "Info APAR"    — data unit + toggle maintenance (admin only)
       Tab 2 "Pemeriksaan"  — form tambah (admin) + riwayat + filter tahun
       Tab 3 "Maintenance"  — form tambah (admin) + riwayat + filter tahun
  → State tab disimpan di sessionStorage per kode APAR
```

---

## 6. Modul & Fitur Detail

### Model: Apar — Method Penting

| Method / Attribute        | Jenis    | Keterangan                                               |
|---------------------------|----------|----------------------------------------------------------|
| `getStatusAttribute()`    | Computed | Return: `'expired'`, `'near_expiry'`, `'good'`           |
| `isExpired()`             | Method   | `expiry_date < today`                                    |
| `isNearExpiry()`          | Method   | Tidak expired && `expiry_date <= today+30`               |
| `isGood()`                | Method   | Tidak expired && tidak near expiry                       |
| `generateCode()`          | Static   | Ambil id terbesar, ekstrak angka akhir, +1, pad 3 digit  |
| `maintenances()`          | HasMany  | Order: maintenance_date DESC, id DESC                    |
| `latestMaintenance()`     | HasOne   | Maintenance terbaru saja                                 |
| `inspections()`           | HasMany  | Order: inspected_at DESC, id DESC                        |

### Model: AparInspection

| Method          | Keterangan                                           |
|-----------------|------------------------------------------------------|
| `isAllOk()`     | True jika semua 6 komponen bernilai `'OK'`           |
| `inspector()`   | BelongsTo User via kolom `inspected_by`              |
| `apar()`        | BelongsTo Apar                                       |

### User Seeder (default)

```
username : adminsrp
password : srppastibisa   ← GANTI SEGERA DI PRODUCTION
role     : superadmin
```

---

## 7. Keamanan

### Perlindungan yang Sudah Ada

| Ancaman                  | Mekanisme                                                           |
|--------------------------|---------------------------------------------------------------------|
| CSRF                     | `@csrf` directive di semua form POST/PUT/DELETE                     |
| SQL Injection            | Eloquent ORM dengan parameter binding (tidak ada raw query          |
| Brute-force password     | Bcrypt dengan rounds=12                                             |
| Session fixation         | `session()->regenerate()` setelah login sukses                      |
| Session hijacking        | `session()->invalidate()` saat logout                               |
| CSRF token reuse         | `session()->regenerateToken()` saat logout                          |
| Unauthorized access      | Middleware `auth` + `role:superadmin`                               |
| XSS output               | Blade `{{ }}` auto-escape HTML entities                             |
| Mass assignment          | `$fillable` eksplisit di semua Model                                |
| File upload berbahaya    | Validasi `mimes:xlsx,xls\|max:5120` pada import                     |
| Self-delete akun         | `UserController::destroy()` cek `$user->id === Auth::id()`          |
| Password exposure        | `$hidden = ['password', 'remember_token']` di Model User            |

### Catatan Keamanan (Perlu Perhatian)

1. **Password default seeder** (`srppastibisa`) — WAJIB diganti segera setelah deploy.
2. **`APP_DEBUG=true`** di `.env` — WAJIB di-set `false` di production; jika debug=true, stack trace detail tampil ke browser.
3. **`APP_KEY`** di `.env` — tidak boleh dikompromit/expose.
4. **Rate limiting login** — belum ada. Tambahkan `RateLimiter` di `Kernel.php` untuk hardening.
5. **Route publik `/apar/{code}`** — read-only, data yang tampil tidak sensitif (tidak ada data personal atau finansial).
6. **Password tanpa email** — tidak ada mekanisme reset password (tabel `password_reset_tokens` ada tapi tidak diimplementasi).
7. **Session di database** — lebih aman dari file-based di shared hosting.

---

## 8. Export & Import Excel

### Export Data APAR (`GET /apar/export/data`)

File `.xlsx` dengan **4 sheet**:

| Sheet              | Kelas                  | Isi                                               |
|--------------------|------------------------|---------------------------------------------------|
| Data APAR          | `AparAllSheet`         | Semua unit APAR dengan color-coding kondisi/status |
| Alert              | `AparAlertSheet`       | APAR kadaluarsa dan hampir kadaluarsa             |
| Jadwal Maintenance | `AparMaintenanceSheet` | Rekap jadwal inspeksi                             |
| Ringkasan          | `AparSummarySheet`     | Statistik total, per jenis, per kondisi           |

### Export Pemeriksaan (`POST /inspeksi/export`)

- Pilih APAR dengan checkbox di halaman `/inspeksi`
- 1 sheet per APAR, berisi semua riwayat pemeriksaan berkala

### Import APAR (`POST /apar/import`)

**Header template Excel** (`GET /apar/import/template`):

```
kode_apar | lokasi | gedung | lantai | ruangan | jenis | kapasitas | satuan |
tanggal_produksi | tanggal_kadaluarsa | inspeksi_terakhir | inspeksi_berikutnya |
kondisi | penanggung_jawab | catatan
```

**Logic import per baris:**
1. Skip baris kosong (`SkipsEmptyRows`)
2. Validasi kolom wajib: lokasi, jenis, kapasitas, satuan, tanggal_produksi, tanggal_kadaluarsa, kondisi
3. Validasi enum: satuan (`kg`/`liter`), kondisi (`Good`/`Needs Attention`/`Replace`)
4. Auto-generate kode jika `kode_apar` kosong
5. Skip baris jika kode sudah ada (tidak overwrite)
6. Parse tanggal: serial Excel → `DD/MM/YYYY` → format Carbon bebas
7. Normalisasi `type`: `ucwords(strtolower(trim()))`
8. Kembalikan counter: `imported`, `skipped`, `errors[]`

---

## 9. PWA & QR Scan

### Progressive Web App

- `manifest.json` di `/public` — nama: "APAR SRP", tema: `#15803d`
- Service Worker: `/public/sw.js` — cache aset untuk akses offline
- Install banner muncul otomatis 2 detik setelah load jika browser mendukung `beforeinstallprompt`
- User bisa dismiss (status disimpan di `localStorage`)
- Icon: `/public/icons/icon.svg`, `/public/icons/icon-192.png`

### QR Code

- **Generate:** Halaman print (`/apar/{code}/print`) — QR berisi URL `{APP_URL}/apar/{code}`
- **Print massal:** `/apar/print-all` — semua APAR dalam satu halaman
- **Scan:** `/scan` menggunakan `html5-qrcode@2.3.8` (CDN)
  - Kamera: `facingMode: "environment"` (kamera belakang)
  - FPS: 10, area scan: 220×220 px
  - QR berisi URL penuh → navigate langsung; berisi kode saja → navigate ke `/apar/{kode}`

---

## 10. Konfigurasi Lingkungan

```env
# App
APP_NAME=Laravel
APP_ENV=local          # Ganti ke: production
APP_DEBUG=true         # Ganti ke: false
APP_URL=http://localhost

# Database (saat ini SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=srp_apar
# Untuk MySQL production: ubah ke mysql + isi DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120    # 120 menit
CACHE_STORE=database
QUEUE_CONNECTION=database

# Auth
BCRYPT_ROUNDS=12
```

---

## Ringkasan Flow Keseluruhan

```
[Pengguna Lapangan]                         [Admin / Superadmin]
        |                                            |
        v                                            v
   GET /scan (publik)                          POST /login
   Kamera QR scan                          Auth::attempt()
        |                                  session regenerate
        v                                            |
   GET /apar/{code} (publik)                         v
   ┌────────────────────┐           ┌────────────────────────────────┐
   │  Info APAR         │           │         Dashboard               │
   │  Riwayat inspeksi  │           │  total / expired / near / good  │
   │  Riwayat maint.    │           │  5 APAR terbaru diperbarui      │
   │  (read-only)       │           └──────────┬─────────────────────┘
   └─────────┬──────────┘                      |
             |                     ┌───────────┼────────────────┐
             | (jika login)        v           v                v
             v                Daftar APAR  History         Pemeriksaan
       Tambah inspeksi        + filter     Maintenance     Berkala
       Tambah maintenance     + search     + alert         + filter
       Toggle maintenance     + paginate   overdue/        + export
                                  |        upcoming        per APAR
                             Import/Export      |
                             Excel         Tambah/Hapus
                                  |        record
                             Print QR
                             (1 / semua)

                             [Superadmin saja]
                             Manajemen User
                             CRUD /users
```
