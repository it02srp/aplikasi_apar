# Rencana Pengembangan Aplikasi Inspeksi Hidrant
**PT. Sinar Rimba Pasifik — Plant 2000**
**Versi:** 2.0.0
**Tanggal:** 05 Oktober 2026
**Author:** IT Developer — Baginda Jourji

> **Catatan:** Aplikasi ini dikembangkan sebagai project Laravel terpisah namun mengikuti **seluruh pola arsitektur, stack, dan konvensi** dari aplikasi APAR Management (`apar-srp.srpid.net`) yang sudah berjalan, agar konsisten dan tidak bentrok.

---

## 1. Latar Belakang

Saat ini pencatatan inspeksi hidrant dilakukan secara manual menggunakan dua file Excel:

- `CHEK HYDRANT 2026.xlsx` — tracking ketersediaan komponen per area (Nozel, Selang, Kopling, Pompa)
- `laporan_checklist_bulanan_hydrant.xlsx` — checklist inspeksi bulanan dengan 11 item pemeriksaan detail per unit

Proses manual ini memiliki kelemahan yang sama dengan pencatatan APAR sebelum digitalisasi:

- Tidak ada riwayat (history) yang terstruktur dan mudah diakses
- Rentan terhadap kehilangan data dan human error
- Tidak ada dashboard monitoring kondisi seluruh hidrant secara real-time
- Tidak terintegrasi dengan sistem yang sudah ada (APAR Management)

---

## 2. Tujuan

- Digitalisasi pencatatan master data dan inspeksi hidrant
- Menyediakan dashboard monitoring kondisi hidrant secara real-time
- Menyimpan riwayat inspeksi bulanan per unit (11 item pemeriksaan standar)
- Menghasilkan laporan inspeksi bulanan dan rekap tahunan (Jan–Des)
- UI/UX **identik** dengan APAR Management (layout, sidebar, warna, komponen)

---

## 3. Ruang Lingkup

| Fitur | Keterangan | Prioritas |
|---|---|---|
| Master Data Hidrant | CRUD data unit hidrant | Tinggi |
| Auto-generate Kode | Format `HYD-XXX`, pola sama dengan APAR | Tinggi |
| Checklist Inspeksi Bulanan | 11 item pemeriksaan per unit per bulan | Tinggi |
| Riwayat Inspeksi | History per unit hidrant per tahun | Tinggi |
| Dashboard | Summary kondisi & status seluruh unit | Sedang |
| Export Excel | Rekap bulanan & tahunan (.xlsx, Maatwebsite Excel) | Sedang |
| Export PDF | Laporan cetak per periode | Sedang |
| PWA | Progressive Web App — bisa dipasang di HP (sama seperti APAR) | Rendah |
| QR Scan | QR per unit hidrant untuk akses publik detail | Rendah |

---

## 4. Arsitektur & Stack Teknologi

**Identik dengan APAR Management** — tidak ada perbedaan stack:

```
Framework : Laravel 11
Frontend  : Blade Template + Tailwind CSS (via CDN — bukan npm/build)
Database  : SQLite (development) / MySQL (production)
Server    : srpid.net infrastructure
Domain    : hydrant-srp.srpid.net (usulan)
Auth      : Session-based, SESSION_DRIVER=database
Password  : BCRYPT_ROUNDS=12
Excel     : Maatwebsite Laravel Excel
```

> **Tailwind via CDN** — sama persis dengan APAR, tidak menggunakan build process (Vite/npm).

---

## 5. Autentikasi & Otorisasi

### 5.1 Tabel `users` — Struktur Identik dengan APAR

Aplikasi hydrant menggunakan **struktur tabel `users` yang sama**:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED PK | |
| `username` | VARCHAR UNIQUE | Login identifier (bukan email) |
| `password` | VARCHAR | Bcrypt hashed (rounds=12) |
| `role` | ENUM('superadmin','admin') | Default: `admin` |
| `remember_token` | VARCHAR(100) nullable | |
| `created_at` / `updated_at` | TIMESTAMP | |

> **Tidak ada role `inspektor` atau `viewer`** — mengikuti sistem yang sudah berjalan di APAR.

### 5.2 Middleware

Menggunakan middleware `CheckRole.php` dengan pola yang sama:

```php
// Middleware yang sudah ada di APAR, dibuat ulang di project hydrant
// app/Http/Middleware/CheckRole.php
```

### 5.3 Hirarki Akses

```
Publik (tanpa login)
 ├── GET /scan              — Halaman scan QR hydrant
 └── GET /hydrant/{code}   — Detail hydrant (read-only)

Admin (role: admin atau superadmin)
 ├── GET /dashboard
 ├── CRUD Hydrant (/hydrant)
 ├── Input checklist inspeksi (/inspeksi)
 ├── Hapus record inspeksi (/inspeksi/{id})
 └── Export Excel / PDF

Superadmin saja
 └── Manajemen User — CRUD /users
```

### 5.4 Login / Logout Flow

Identik dengan APAR:

```
POST /login
  → Auth::attempt(['username', 'password'], $remember)
  → session()->regenerate()
  → redirect()->intended(route('dashboard'))

POST /logout
  → Auth::logout()
  → session()->invalidate()
  → session()->regenerateToken()
  → redirect /login
```

### 5.5 User Seeder Default

```
username : adminsrp
password : srppastibisa   ← GANTI SEGERA DI PRODUCTION
role     : superadmin
```

---

## 6. Database Schema

### 6.1 Relasi Antar Tabel

```
users
 └── (1:N) hydrant_inspections.inspected_by  [set null on delete]

hydrants
 └── (1:N) hydrant_inspections.hydrant_id    [cascade delete]
```

### 6.2 Tabel `hydrants` (Master Data)

```sql
CREATE TABLE hydrants (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code              VARCHAR(20)  NOT NULL UNIQUE,   -- HYD-001, HYD-002, ...
    location          VARCHAR(255) NOT NULL,           -- Depan Kantor Personalia
    hose_length       TINYINT UNSIGNED NOT NULL,       -- 20 atau 30 (meter)
    condition         ENUM('Good', 'Needs Attention', 'Damaged')
                      NOT NULL DEFAULT 'Good',         -- Enum label konsisten dg APAR
    responsible_person VARCHAR(100) NULL,
    notes             TEXT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_code (code),
    INDEX idx_condition (condition)
);
```

> **Naming convention:** menggunakan bahasa Inggris (`code`, `location`, `condition`) agar konsisten dengan tabel `apars` di APAR Management.

> **Tidak ada `is_active` / soft delete** — mengikuti pola APAR yang menggunakan hard delete.

### 6.3 Tabel `hydrant_inspections` (Checklist Inspeksi Bulanan)

```sql
CREATE TABLE hydrant_inspections (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hydrant_id            BIGINT UNSIGNED NOT NULL,
    inspected_by          BIGINT UNSIGNED NULL,        -- FK users.id (set null on delete)
    periode               CHAR(7)      NOT NULL,       -- Format: YYYY-MM (contoh: 2026-03)
    inspected_at          DATE         NOT NULL,       -- Tanggal inspeksi (konsisten dg APAR)

    -- 11 Item Pemeriksaan (OK / NOT OK) — konsisten dengan enum di apar_inspections
    item_01_kondisi_box       ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_02_akses_bebas       ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_03_nozzle            ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_04_selang            ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_05_valve             ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_06_coupling          ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_07_kunci             ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_08_pillar            ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_09_tekanan           ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_10_hose_rack         ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',
    item_11_pompa             ENUM('OK', 'NOT OK') NOT NULL DEFAULT 'OK',

    notes                 TEXT NULL,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (hydrant_id) REFERENCES hydrants(id) ON DELETE CASCADE,
    FOREIGN KEY (inspected_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_hydrant_periode (hydrant_id, periode),
    INDEX idx_periode (periode),
    INDEX idx_inspected_at (inspected_at)
);
```

> **ENUM `'OK'` / `'NOT OK'`** — huruf kapital, konsisten dengan `kondisi_*` di tabel `apar_inspections`.

### 6.4 Urutan Migrasi

```
0001_01_01_000000_create_users_table
0001_01_01_000001_create_cache_table
0001_01_01_000002_create_jobs_table
2026_10_05_000001_create_hydrants_table
2026_10_05_000002_create_hydrant_inspections_table
```

---

## 7. Model & Logic

### 7.1 Model: `Hydrant`

```php
// app/Models/Hydrant.php

class Hydrant extends Model
{
    protected $fillable = [
        'code', 'location', 'hose_length', 'condition',
        'responsible_person', 'notes',
    ];

    // Relasi
    public function inspections(): HasMany
    {
        return $this->hasMany(HydrantInspection::class)->orderByDesc('inspected_at')->orderByDesc('id');
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(HydrantInspection::class)->latestOfMany('inspected_at');
    }

    // Auto-generate kode — pola SAMA dengan Apar::generateCode()
    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->code, 4)) + 1 : 1;
        return 'HYD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // Computed attribute — pola sama dengan Apar::getStatusAttribute()
    public function getStatusAttribute(): string
    {
        $latest = $this->latestInspection;
        if (!$latest) return 'no_inspection';
        return match($this->condition) {
            'Good'           => 'good',
            'Needs Attention'=> 'needs_attention',
            'Damaged'        => 'damaged',
            default          => 'good',
        };
    }
}
```

### 7.2 Model: `HydrantInspection`

```php
// app/Models/HydrantInspection.php

class HydrantInspection extends Model
{
    protected $fillable = [
        'hydrant_id', 'inspected_by', 'periode', 'inspected_at',
        'item_01_kondisi_box', 'item_02_akses_bebas', 'item_03_nozzle',
        'item_04_selang', 'item_05_valve', 'item_06_coupling',
        'item_07_kunci', 'item_08_pillar', 'item_09_tekanan',
        'item_10_hose_rack', 'item_11_pompa',
        'notes',
    ];

    // Identik dengan AparInspection::isAllOk()
    public function isAllOk(): bool
    {
        return collect([
            $this->item_01_kondisi_box, $this->item_02_akses_bebas,
            $this->item_03_nozzle,      $this->item_04_selang,
            $this->item_05_valve,       $this->item_06_coupling,
            $this->item_07_kunci,       $this->item_08_pillar,
            $this->item_09_tekanan,     $this->item_10_hose_rack,
            $this->item_11_pompa,
        ])->every(fn($v) => $v === 'OK');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function hydrant(): BelongsTo
    {
        return $this->belongsTo(Hydrant::class);
    }
}
```

### 7.3 Logic Update Kondisi Otomatis

Dipanggil di `HydrantController` setelah inspeksi disimpan — **tidak menggunakan service class terpisah**, konsisten dengan pola APAR yang langsung di controller:

```php
// Dalam HydrantController::storeInspection()

$jumlahNotOk = collect([
    $inspection->item_01_kondisi_box, $inspection->item_02_akses_bebas,
    $inspection->item_03_nozzle,      $inspection->item_04_selang,
    $inspection->item_05_valve,       $inspection->item_06_coupling,
    $inspection->item_07_kunci,       $inspection->item_08_pillar,
    $inspection->item_09_tekanan,     $inspection->item_10_hose_rack,
    $inspection->item_11_pompa,
])->filter(fn($v) => $v === 'NOT OK')->count();

// Item kritis: Tekanan (09) dan Pompa (11)
$itemKritisBermasalah = $inspection->item_09_tekanan === 'NOT OK'
                     || $inspection->item_11_pompa   === 'NOT OK';

$kondisiBaru = match(true) {
    $jumlahNotOk === 0                              => 'Good',
    $jumlahNotOk <= 2 && !$itemKritisBermasalah    => 'Needs Attention',
    default                                         => 'Damaged',
};

$hydrant->update(['condition' => $kondisiBaru]);
```

---

## 8. Struktur Direktori

Mengikuti struktur `aplikasi_apar/` secara langsung:

```
aplikasi_hydrant/
├── app/
│   ├── Exports/
│   │   ├── HydrantDataExport.php          # Orchestrator export
│   │   ├── HydrantAllSheet.php            # Sheet: semua hydrant
│   │   ├── HydrantInspectionSheet.php     # Sheet: riwayat inspeksi per unit
│   │   └── HydrantSummarySheet.php        # Sheet: ringkasan statistik
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php         # Login / Logout (identik dg APAR)
│   │   │   ├── HydrantController.php      # CRUD Hydrant + inspeksi
│   │   │   └── UserController.php         # Manajemen user (superadmin only)
│   │   └── Middleware/
│   │       └── CheckRole.php              # Role-based access (identik dg APAR)
│   ├── Models/
│   │   ├── User.php
│   │   ├── Hydrant.php
│   │   └── HydrantInspection.php
│   └── Providers/
│       └── AppServiceProvider.php         # Boot: Paginator pakai Tailwind
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php                 # Seed superadmin default
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                  # Layout admin (sidebar + navbar) — clone dari APAR
│   │   └── guest.blade.php                # Layout publik
│   ├── auth/login.blade.php
│   ├── dashboard.blade.php
│   ├── scan.blade.php                     # QR scan publik
│   ├── hydrant/
│   │   ├── index.blade.php                # Daftar hydrant + filter
│   │   ├── create.blade.php               # Form tambah hydrant
│   │   ├── edit.blade.php                 # Form edit hydrant
│   │   ├── show.blade.php                 # Detail hydrant (publik, 2 tab)
│   │   ├── inspection.blade.php           # Form & daftar checklist inspeksi
│   │   ├── print.blade.php                # Cetak QR 1 hydrant
│   │   └── print-all.blade.php            # Cetak QR semua hydrant
│   └── users/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
└── routes/web.php
```

---

## 9. Routes

```php
// routes/web.php

// Publik
Route::get('/scan', [HydrantController::class, 'scan'])->name('scan');
Route::get('/hydrant/{code}', [HydrantController::class, 'show'])->name('hydrant.show');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HydrantController::class, 'dashboard'])->name('dashboard');

    // Master Data Hydrant
    Route::get('/hydrant',                [HydrantController::class, 'index'])->name('hydrant.index');
    Route::get('/hydrant/create',         [HydrantController::class, 'create'])->name('hydrant.create');
    Route::post('/hydrant',               [HydrantController::class, 'store'])->name('hydrant.store');
    Route::get('/hydrant/{code}/edit',    [HydrantController::class, 'edit'])->name('hydrant.edit');
    Route::put('/hydrant/{code}',         [HydrantController::class, 'update'])->name('hydrant.update');
    Route::delete('/hydrant/{code}',      [HydrantController::class, 'destroy'])->name('hydrant.destroy');
    Route::get('/hydrant/{code}/print',   [HydrantController::class, 'print'])->name('hydrant.print');
    Route::get('/hydrant/print-all',      [HydrantController::class, 'printAll'])->name('hydrant.print-all');

    // Inspeksi
    Route::get('/inspeksi',               [HydrantController::class, 'inspectionIndex'])->name('inspeksi.index');
    Route::post('/hydrant/{code}/inspeksi', [HydrantController::class, 'storeInspection'])->name('hydrant.inspeksi.store');
    Route::post('/inspeksi',              [HydrantController::class, 'storeInspectionAdmin'])->name('inspeksi.store');
    Route::delete('/inspeksi/{id}',       [HydrantController::class, 'destroyInspection'])->name('inspeksi.destroy');

    // Export
    Route::get('/hydrant/export/data',    [HydrantController::class, 'exportData'])->name('hydrant.export');
    Route::post('/inspeksi/export',       [HydrantController::class, 'exportInspection'])->name('inspeksi.export');

    // User Management (superadmin only)
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('/users', UserController::class)->except(['show']);
    });
});
```

---

## 10. Dashboard

Identik dengan pola dashboard APAR:

```php
// GET /dashboard
$total         = Hydrant::count();
$good          = Hydrant::where('condition', 'Good')->count();
$needsAttention = Hydrant::where('condition', 'Needs Attention')->count();
$damaged       = Hydrant::where('condition', 'Damaged')->count();

$currentPeriode = now()->format('Y-m');
$sudahInspeksi  = HydrantInspection::where('periode', $currentPeriode)->distinct('hydrant_id')->count();
$belumInspeksi  = $total - $sudahInspeksi;

$recentHydrants = Hydrant::with('latestInspection')->orderByDesc('updated_at')->take(5)->get();
```

**4 kartu statistik:** Total | Good | Needs Attention | Damaged
**2 kartu inspeksi bulan ini:** Sudah Diinspeksi | Belum Diinspeksi
**Tabel:** 5 hydrant terbaru diperbarui

---

## 11. Form & UX

### 11.1 Form Tambah Hydrant (create.blade.php)

Mengikuti layout form APAR (`apar/create.blade.php`) — 3 seksi:

**IDENTITAS HYDRANT**
- `Kode Hydrant` — `Hydrant::generateCode()`, read-only, tampil sebagai preview
- `Kondisi` — dropdown: Good / Needs Attention / Damaged

**SPESIFIKASI**
- `Panjang Selang` — dropdown: 20 m / 30 m
- `Catatan` — textarea opsional

**INFORMASI LOKASI**
- `Lokasi` — text input (contoh: "Depan Kantor Personalia")
- `Penanggung Jawab` — nama PIC

### 11.2 Halaman Detail Hydrant (show.blade.php)

Mengikuti pola `apar/show.blade.php` — **2 tab** (APAR punya 3 tab):

- **Tab 1 "Info Hydrant"** — data unit, kondisi, lokasi
- **Tab 2 "Riwayat Inspeksi"** — form input checklist (admin) + tabel riwayat + filter tahun

State tab disimpan di `sessionStorage` per kode hydrant — identik dengan APAR.

### 11.3 Form Checklist Inspeksi

Di halaman detail (Tab 2) dan halaman `/inspeksi`:

**Header:**
- `Periode (Bulan-Tahun)` — month input `YYYY-MM`
- `Tanggal Inspeksi` — date picker

**11 Item Pemeriksaan** — radio button atau toggle ✓/✗ per item, default semua `OK`:

| No | Item Pemeriksaan |
|---|---|
| 1 | Kondisi fisik Box Hydrant bersih & terawat |
| 2 | Akses menuju Box Hydrant bebas / tidak terhalang barang |
| 3 | Nozzle dalam kondisi baik, bersih, & tidak retak/patah |
| 4 | Selang pemadam (Fire Hose) rapi & tidak bocor/robek |
| 5 | Kran/Katup (Valve) utama berfungsi baik & tidak macet |
| 6 | Coupling/Sambungan selang presisi & karet seal utuh |
| 7 | Kunci pembuka/Tuas hydrant tersedia di tempatnya |
| 8 | Hydrant Pillar tidak berkarat & tidak bocor |
| 9 | Tekanan air stabil pada pressure gauge (Min. 4.42 Bar / 100 PSI) |
| 10 | Kondisi fisik Hose Rack baik & fungsional |
| 11 | Pompa otomatis (Jockey, Electric, Diesel) standby & normal |

**Catatan** — textarea opsional

---

## 12. Export Excel

Menggunakan **Maatwebsite Laravel Excel** — paket yang sama dengan APAR:

| Sheet | Kelas | Isi |
|---|---|---|
| Data Hydrant | `HydrantAllSheet` | Semua unit hydrant dengan color-coding kondisi |
| Ringkasan | `HydrantSummarySheet` | Statistik total, per kondisi, inspeksi bulan ini |

**Export Inspeksi** (`POST /inspeksi/export`):
- Pilih hydrant dengan checkbox
- 1 sheet per hydrant dipilih, berisi riwayat inspeksi
- Format laporan tahunan: rekap Jan–Des per unit (11 item per kolom bulan)

---

## 13. Keamanan

Mengikuti **seluruh mekanisme keamanan APAR** tanpa pengurangan:

| Ancaman | Mekanisme |
|---|---|
| CSRF | `@csrf` di semua form POST/PUT/DELETE |
| SQL Injection | Eloquent ORM, parameter binding, tidak ada raw query |
| Brute-force | Bcrypt rounds=12 |
| Session fixation | `session()->regenerate()` setelah login |
| Session hijacking | `session()->invalidate()` saat logout |
| CSRF token reuse | `session()->regenerateToken()` saat logout |
| Unauthorized access | Middleware `auth` + `role:superadmin` |
| XSS | Blade `{{ }}` auto-escape |
| Mass assignment | `$fillable` eksplisit di semua Model |
| Self-delete akun | Cek `$user->id === Auth::id()` di `UserController::destroy()` |

**Catatan tambahan:**
- Rate limiting login: belum ada (sama seperti APAR) — tambahkan `RateLimiter` untuk hardening
- Route publik `/hydrant/{code}` — read-only, tidak ada data sensitif

---

## 14. Konfigurasi Environment

Sama persis dengan APAR:

```env
APP_NAME=Hydrant SRP
APP_ENV=local          # Ganti ke: production
APP_DEBUG=true         # Ganti ke: false
APP_URL=http://localhost

DB_CONNECTION=sqlite   # Dev: SQLite / Prod: MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=srp_hydrant
# DB_USERNAME=...
# DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

BCRYPT_ROUNDS=12
```

---

## 15. Aturan Bisnis (Business Rules)

1. **Kode hydrant tidak bisa diedit** setelah tersimpan (identik dengan `code` APAR).
2. **Satu unit hydrant hanya boleh diinspeksi satu kali per periode** (`UNIQUE hydrant_id + periode`).
3. **Delete inspeksi:** Admin bisa hapus record inspeksi (hard delete) — identik dengan APAR.
4. **Delete hydrant:** Hard delete, cascade ke `hydrant_inspections` — identik dengan APAR.
5. **Kondisi hydrant diupdate otomatis** setelah inspeksi disimpan:
   - Semua 11 item = OK → `Good`
   - 1–2 item NOT OK, bukan item kritis → `Needs Attention`
   - Item kritis (Tekanan/Pompa) NOT OK **atau** ≥3 item NOT OK → `Damaged`
6. **Kondisi ENUM:** `'Good'` / `'Needs Attention'` / `'Damaged'` — label identik pola APAR (`'Good'` / `'Needs Attention'` / `'Replace'`), kecuali `'Replace'` diganti `'Damaged'` karena hidrant tidak "diganti" melainkan "diperbaiki".

---

## 16. Rencana Pengerjaan (Timeline)

| Phase | Deskripsi | Target |
|---|---|---|
| **Phase 1** | Setup project Laravel 11, clone layout APAR, database migration, seeder | Minggu 1 |
| **Phase 2** | Master data Hydrant — CRUD + auto-generate kode `HYD-XXX` | Minggu 2 |
| **Phase 3** | Form checklist 11 item, simpan inspeksi, update kondisi otomatis | Minggu 3–4 |
| **Phase 4** | Halaman detail (2 tab), riwayat inspeksi + filter tahun, dashboard | Minggu 5 |
| **Phase 5** | Export Excel (data + inspeksi), QR code, print | Minggu 6 |
| **Phase 6** | UAT, hardening, deployment ke `hydrant-srp.srpid.net` | Minggu 7–8 |

---

## 17. Referensi

| Sumber | Keterangan |
|---|---|
| `apar-srp.srpid.net` | Aplikasi referensi — seluruh pola diikuti |
| `DOKUMENTASI_APLIKASI.md` | Dokumentasi teknis APAR Management |
| `CHEK HYDRANT 2026.xlsx` | Data existing: daftar area + komponen |
| `laporan_checklist_bulanan_hydrant.xlsx` | Template 11 item pemeriksaan standar |
| `hydrant-srp.srpid.net` | Domain target deployment |

---

*Dokumen ini merupakan living document dan akan diperbarui seiring perkembangan pengerjaan.*
*Versi 2.0.0 — disesuaikan penuh dengan arsitektur APAR Management (Laravel 11, Tailwind CDN, role superadmin/admin, hard delete, konvensi naming).*
