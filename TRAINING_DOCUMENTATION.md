# 📚 MODUL TRAINING: MEMBUAT API LAUNDRY DENGAN LARAVEL 12

## 🎯 **TUJUAN PEMBELAJARAN**
Setelah mengikuti training ini, peserta akan mampu:
1. Membuat REST API menggunakan Laravel 12
2. Mengimplementasi authentication dengan Laravel Sanctum
3. Membuat CRUD operations yang aman dan terstruktur
4. Menerapkan best practices dalam API development
5. Mengintegrasikan API dengan aplikasi mobile (Flutter)

---

## 📋 **OVERVIEW PROJECT**

### **Project Existing: Laundry Web Application**
- **Framework:** Laravel 9
- **Database:** MySQL
- **Fitur Utama:** 
  - User Management (Admin, Customer, Karyawan)
  - Transaksi Laundry
  - Manajemen Harga
  - Sistem Pembayaran
  - Notifikasi

### **Project Baru: Laundry API Application**
- **Framework:** Laravel 12
- **Purpose:** REST API untuk Mobile App (Flutter)
- **Database:** Shared dengan web application
- **Authentication:** Laravel Sanctum (Token-based)

---

## 🗂️ **STRUKTUR DATABASE ANALYSIS**

### **Tabel Utama:**
1. **users** - User management dengan role-based access
2. **transaksis** - Data transaksi laundry
3. **hargas** - Harga layanan per kilogram
4. **data_banks** - Informasi bank untuk pembayaran
5. **laundry_settings** - Pengaturan sistem
6. **notifications_settings** - Konfigurasi notifikasi

---

## 📝 **LOG PERUBAHAN STEP-BY-STEP**

### 🚀 **PHASE 1: PROJECT ANALYSIS & SETUP**

#### ✅ **Step 1: Analisis Project Existing** 
**Tanggal:** 2025-06-26  
**Durasi:** 15 menit  

**Yang Dilakukan:**
- Menganalisis composer.json project laundry web (Laravel 9)
- Menganalisis composer.json project laundry-api (Laravel 12)
- Mempelajari struktur database dari migration files
- Menganalisis model relationships

**File yang Diperiksa:**
- `/laundry/composer.json` - Dependencies Laravel 9
- `/laundry-api/composer.json` - Dependencies Laravel 12
- `/laundry/database/migrations/` - Database structure
- `/laundry/app/Models/` - Model relationships

**Temuan Penting:**
- Project web menggunakan Laravel 9 dengan PHP ^8.0.21
- Project API menggunakan Laravel 12 dengan PHP ^8.2
- Database utama: users, transaksis, hargas
- Authentication existing menggunakan Spatie Laravel Permission

**Keputusan Teknis:**
- Akan menggunakan Laravel Sanctum untuk API authentication
- Database schema akan disesuaikan dengan struktur existing
- API akan menggunakan Resource pattern untuk response

---

#### 📋 **Step 2: Membuat Dokumentasi Training**
**Tanggal:** 2025-06-26  
**Durasi:** 10 menit  

**Yang Dilakukan:**
- Membuat file `TRAINING_DOCUMENTATION.md`
- Menyusun struktur modul pembelajaran
- Mendefinisikan tujuan pembelajaran
- Membuat template untuk tracking progress

**File yang Dibuat:**
- `TRAINING_DOCUMENTATION.md` - Dokumentasi lengkap training

**Template yang Disiapkan:**
- Overview project dan analisis database
- Log perubahan step-by-step
- Template untuk setiap phase development
- Checklist untuk validation setiap step

---

### 🔧 **PHASE 2: PROJECT CONFIGURATION**

#### ✅ **Step 3: Setup Laravel Sanctum** 
**Tanggal:** 2025-06-26  
**Durasi:** 20 menit  
**Status:** Completed  

**Yang Dilakukan:**
- Install Laravel Sanctum package versi ^4.1
- Publish Sanctum configuration dan migration files
- Setup API guards di config/auth.php
- Konfigurasi middleware untuk API authentication
- Setup API routing dan struktur dasar

**File yang Dimodifikasi:**
- `composer.json` - Tambah laravel/sanctum ^4.1 dependency
- `config/sanctum.php` - Konfigurasi stateful domains dan token expiration
- `config/auth.php` - Tambah sanctum guard untuk API authentication  
- `bootstrap/app.php` - Aktifkan API routing dan middleware sanctum
- `routes/api.php` - Setup struktur route API dengan versioning

**Konfigurasi Penting:**
- Token expiration: 24 jam (1440 menit)
- Stateful domains: termasuk 10.0.2.2:8000 untuk Android emulator
- API throttling: menggunakan throttle middleware
- Route versioning: `/api/v1/` prefix untuk semua endpoints

**Commands yang Dijalankan:**
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

**Commit Message:**
```
feat: Setup Laravel Sanctum untuk API authentication

- Install Laravel Sanctum ^4.1 untuk Laravel 12
- Publish config sanctum.php dan migration files
- Tambah sanctum guard di config/auth.php
- Aktifkan API routing dengan middleware sanctum
- Setup route structure dengan versioning /api/v1/
- Konfigurasi token expiration 24 jam
- Tambah support untuk Android emulator (10.0.2.2:8000)
```

---

#### ✅ **Step 4: Database Migration Setup**
**Tanggal:** 2025-06-26  
**Durasi:** 30 menit  
**Status:** Completed  

**Yang Dilakukan:**
- Buat migration untuk tabel users dengan struktur lengkap
- Buat migration untuk tabel prices (hargas) dengan foreign key
- Buat migration untuk tabel transactions (transaksis) dengan relasi lengkap  
- Buat migration untuk tabel bank_accounts (data_banks)
- Buat migration untuk tabel laundry_settings
- Setup foreign key relationships dan indexes
- Jalankan migrasi database

**File Migration yang Dibuat:**
- `2025_06_26_165234_create_users_table.php` - Tabel users dengan role dan profile
- `2025_06_26_165305_create_prices_table.php` - Tabel harga layanan laundry
- `2025_06_26_165324_create_transactions_table.php` - Tabel transaksi lengkap
- `2025_06_26_165349_create_bank_accounts_table.php` - Tabel rekening bank
- `2025_06_26_165406_create_laundry_settings_table.php` - Tabel pengaturan sistem

**Struktur Database:**
- **users**: Authentication, profile, role (Admin/Customer/Karyawan)
- **prices**: Harga layanan per kg dengan estimasi hari
- **transactions**: Transaksi laundry dengan status tracking
- **bank_accounts**: Info rekening untuk pembayaran
- **laundry_settings**: Pengaturan operasional dan notifikasi
- **personal_access_tokens**: Token Sanctum untuk API

**Fitur Database:**
- Foreign key constraints untuk data integrity
- Indexes untuk optimasi query
- Comments pada setiap field untuk dokumentasi
- Enum values untuk status tracking
- Decimal precision untuk harga dan berat
- JSON field untuk working_days
- Composite indexes untuk laporan

**Commands yang Dijalankan:**
```bash
php artisan make:migration create_users_table
php artisan make:migration create_prices_table
php artisan make:migration create_transactions_table
php artisan make:migration create_bank_accounts_table
php artisan make:migration create_laundry_settings_table
php artisan migrate:fresh
```

---

#### ✅ **Step 5: Model Creation & Relationships**
**Tanggal:** 2025-06-26  
**Durasi:** 25 menit  
**Status:** Completed  

**Yang Dilakukan:**
- Update model User dengan HasApiTokens trait dan relationships lengkap
- Buat model Price dengan relationship ke User dan Transaction
- Buat model Transaction dengan relationships dan helper methods
- Buat model BankAccount dengan logic primary account
- Buat model LaundrySetting dengan pengaturan operasional

**File Model yang Dibuat/Diupdate:**
- `app/Models/User.php` - Tambah HasApiTokens, relationships, scopes, dan helper methods
- `app/Models/Price.php` - Model harga layanan dengan format rupiah
- `app/Models/Transaction.php` - Model transaksi dengan auto-generate invoice
- `app/Models/BankAccount.php` - Model rekening bank dengan masked number
- `app/Models/LaundrySetting.php` - Model pengaturan dengan working days logic

**Fitur Model yang Diimplementasi:**
1. **User Model:**
   - Laravel Sanctum integration dengan HasApiTokens
   - Relationships: prices, transactions, bankAccounts, laundrySettings
   - Scopes: byRole(), active()
   - Helper methods: isAdmin(), isCustomer(), isKaryawan()
   - Accessor: getFotoUrlAttribute()

2. **Price Model:**
   - Relationship dengan User dan Transaction
   - Type casting untuk decimal harga
   - Scopes: active(), byJenis(), orderByPrice()
   - Helper: getFormattedHargaAttribute() untuk format rupiah
   - Helper: getEstimasiSelesai() untuk tanggal selesai

3. **Transaction Model:**
   - Auto-generate invoice dengan format LND-YYYYMMDD-0001
   - Relationships: customer, user, price
   - Multiple scopes untuk filtering
   - Boot method untuk auto-fill fields
   - Accessor untuk status text Indonesia

4. **BankAccount Model:**
   - Auto-manage primary account (hanya 1 per user)
   - Masked account number untuk security
   - Scopes: active(), primary(), byBank()
   - Boot method untuk validasi primary

5. **LaundrySetting Model:**
   - JSON field untuk working_days
   - Hidden fields untuk API tokens
   - Helper: isOpen() check jam operasional
   - Helper: invoice number management
   - Boot method untuk single active setting

**Best Practices yang Diterapkan:**
- Detailed PHPDoc comments untuk setiap method
- Type hinting untuk relationships dan return values
- Proper fillable dan hidden attributes
- Scopes untuk reusable queries
- Helper methods untuk business logic
- Boot methods untuk auto-fill dan validation
- Accessor pattern untuk computed attributes

**Commands yang Dijalankan:**
```bash
php artisan make:model Price
php artisan make:model Transaction
php artisan make:model BankAccount
php artisan make:model LaundrySetting
```

---

### 🔐 **PHASE 3: AUTHENTICATION SYSTEM**

#### ✅ **Step 6: API Authentication Controller**
**Tanggal:** 2025-06-26  
**Durasi:** 35 menit  
**Status:** Completed  

**Yang Dilakukan:**
- Buat AuthController dengan authentication flow lengkap
- Implementasi login dengan device_name untuk multi-device
- Implementasi register khusus untuk Customer
- Setup validation rules dalam bahasa Indonesia
- Buat API response format yang konsisten
- Tambah endpoint check email availability
- Buat UserSeeder untuk testing

**File yang Dibuat/Dimodifikasi:**
- `app/Http/Controllers/Api/AuthController.php` - Authentication controller
- `routes/api.php` - Authentication routes
- `database/seeders/UserSeeder.php` - Test users seeder

**Endpoints yang Dibuat:**
1. **Public Endpoints:**
   - `POST /api/v1/auth/login` - Login user
   - `POST /api/v1/auth/register` - Register customer baru
   - `POST /api/v1/auth/check-email` - Check email availability

2. **Protected Endpoints (require token):**
   - `POST /api/v1/auth/logout` - Logout current device
   - `POST /api/v1/auth/logout-all` - Logout all devices
   - `GET /api/v1/auth/profile` - Get user profile
   - `PUT /api/v1/auth/update-password` - Update password

**Authentication Features:**
- Token-based authentication dengan Laravel Sanctum
- Device management (token per device)
- Auto-delete old token saat login device yang sama
- Password confirmation untuk register
- Current password validation untuk update
- Status user validation (Active/Inactive)
- Role-based data loading (bank accounts untuk Admin/Karyawan)

**Response Format Standar:**
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {...},
        "token": "1|laravel_sanctum_xxx",
        "token_type": "Bearer"
    }
}
```

**Validation Messages:**
- Semua error message dalam bahasa Indonesia
- Custom validation messages untuk UX yang lebih baik
- ValidationException untuk error handling yang konsisten

**Test Users yang Dibuat:**
| Email | Password | Role | Status |
|-------|----------|------|--------|
| admin@laundry.com | password123 | Admin | Active |
| karyawan@laundry.com | password123 | Karyawan | Active |
| customer@example.com | password123 | Customer | Active |
| inactive@example.com | password123 | Customer | Inactive |

**Security Practices:**
- Password hashing dengan bcrypt
- Token expiration 24 jam (configurable)
- Logout all devices saat ganti password
- Hidden sensitive fields in response
- Email uniqueness validation

---

#### ✅ **Step 7: Buat API Controllers untuk CRUD Operations**
**Tanggal:** 2025-06-26  
**Durasi:** 45 menit  
**Status:** Completed  

**Yang Dilakukan:**
- Buat TransactionController dengan CRUD lengkap
- Buat PriceController untuk manajemen harga
- Buat UserController untuk profile dan customer management
- Implementasi role-based permissions
- Tambah business logic dan validations

**File Controller yang Dibuat:**
- `app/Http/Controllers/Api/TransactionController.php`
- `app/Http/Controllers/Api/PriceController.php`
- `app/Http/Controllers/Api/UserController.php`

**Features TransactionController:**
1. **index()** - List transaksi dengan filter & pagination
   - Customer hanya lihat transaksi sendiri
   - Admin/Karyawan lihat semua transaksi
   - Filter: status_order, status_payment, date range, search
2. **store()** - Buat transaksi baru (Admin/Karyawan only)
   - Validasi minimum order dari settings
   - Validasi discount maksimal
   - Auto-generate invoice number
3. **show()** - Detail transaksi dengan permission check
4. **update()** - Update status transaksi
   - Status flow validation (tidak bisa mundur)
   - Auto-update customer points saat Done
5. **destroy()** - Soft delete (Admin only)
6. **summary()** - Statistics & reporting

**Features PriceController:**
1. **index()** - List harga dengan filter active/inactive
   - Customer hanya lihat yang active
   - Option group by jenis layanan
2. **store()** - Tambah harga baru (Admin only)
   - Check duplicate jenis layanan
3. **show()** - Detail harga dengan estimasi selesai
4. **update()** - Update harga (Admin only)
   - Validasi jika sudah digunakan di transaksi
5. **destroy()** - Delete harga (Admin only)
   - Prevent delete jika sudah digunakan
6. **getJenisList()** - List unique jenis layanan

**Features UserController:**
1. **updateProfile()** - Update profile data user
2. **updatePhoto()** - Upload foto profile (max 2MB)
3. **deletePhoto()** - Hapus foto profile
4. **getCustomers()** - List customer (Admin/Karyawan)
   - With transaction count
   - Search & filter
5. **getCustomerDetail()** - Detail customer dengan transaksi
6. **createCustomer()** - Buat customer baru (Admin/Karyawan)
7. **updateCustomerStatus()** - Active/Inactive (Admin only)
8. **getPointsHistory()** - History points customer

**Business Logic yang Diimplementasi:**
- Permission checking per role (Admin, Karyawan, Customer)
- Status flow validation untuk transaksi
- Points calculation: 1 point per Rp 10.000
- Minimum order validation dari settings
- Discount validation dengan max percentage
- File upload handling untuk foto profile
- Soft delete protection untuk data integrity

**Security Practices:**
- Role-based access control
- Transaction wrapping dengan DB::beginTransaction()
- File validation untuk upload
- Prevent delete data yang sudah digunakan
- Permission check di setiap method

**Response Consistency:**
- Semua response menggunakan format standar
- Success/error messages dalam bahasa Indonesia
- Proper HTTP status codes
- Detailed validation messages

---

### 📊 **PHASE 4: CORE API ENDPOINTS**

#### ✅ **Step 8: Setup API Routes dan Middleware**
**Tanggal:** 2025-06-26  
**Durasi:** 20 menit  
**Status:** Completed  

**Yang Dilakukan:**
- Setup semua API routes untuk controllers yang sudah dibuat
- Konfigurasi route groups dengan authentication middleware
- Buat sample data (PriceSeeder) untuk testing
- Test endpoints dengan curl untuk validasi
- Update rate limiting configuration

**Routes yang Dibuat:**

**1. Public Routes (no auth required):**
```
POST /api/v1/auth/login
POST /api/v1/auth/register  
POST /api/v1/auth/check-email
```

**2. Protected Routes (auth:sanctum required):**

**Authentication Routes:**
```
POST /api/v1/auth/logout
POST /api/v1/auth/logout-all
GET  /api/v1/auth/profile
PUT  /api/v1/auth/update-password
```

**Profile Routes:**
```
PUT    /api/v1/profile/update
POST   /api/v1/profile/photo
DELETE /api/v1/profile/photo
GET    /api/v1/profile/points-history
```

**Transaction Routes:**
```
GET    /api/v1/transactions
POST   /api/v1/transactions
GET    /api/v1/transactions/summary
GET    /api/v1/transactions/{id}
PUT    /api/v1/transactions/{id}
DELETE /api/v1/transactions/{id}
```

**Price Routes:**
```
GET    /api/v1/prices
GET    /api/v1/prices/jenis-list
POST   /api/v1/prices
GET    /api/v1/prices/{id}
PUT    /api/v1/prices/{id}
DELETE /api/v1/prices/{id}
```

**Customer Routes:**
```
GET    /api/v1/customers
POST   /api/v1/customers
GET    /api/v1/customers/{id}
PUT    /api/v1/customers/{id}/status
```

**Sample Data yang Dibuat:**
- 5 jenis layanan laundry (Cuci Kering, Cuci Setrika, dll)
- Harga mulai dari Rp 5.000 - Rp 15.000 per kg
- Estimasi pengerjaan 1-3 hari

**API Testing:**
- Login authentication: ✅ Working
- Get prices endpoint: ✅ Working  
- Get transactions (empty): ✅ Working
- Token-based authentication: ✅ Working
- Rate limiting: ✅ Configured

**Route Protection:**
- Semua CRUD operations memerlukan authentication
- Role-based permissions di-handle di controller level
- Customer hanya bisa akses data mereka sendiri
- Admin/Karyawan bisa akses semua data

**Middleware Stack:**
- Laravel Sanctum untuk token authentication
- Rate limiting (60 req/min per user)
- CORS untuk cross-origin requests
- Throttling untuk security

---

### 🛡️ **PHASE 5: SECURITY & VALIDATION**

#### ⏳ **Step 11: Request Validation**
**Status:** Pending  
**Estimasi:** 30 menit  

**Yang Akan Dilakukan:**
- Buat Form Request classes
- Implementasi custom validation rules
- Setup error handling yang konsisten
- Buat validation messages dalam bahasa Indonesia

---

#### ⏳ **Step 12: Error Handling & Logging**
**Status:** Pending  
**Estimasi:** 25 menit  

**Yang Akan Dilakukan:**
- Setup custom exception handler
- Implementasi API error responses
- Setup logging untuk debugging
- Buat monitoring system

---

### 📖 **PHASE 6: DOCUMENTATION & TESTING**

#### ⏳ **Step 13: API Testing**
**Status:** Pending  
**Estimasi:** 40 menit  

**Yang Akan Dilakukan:**
- Buat Feature tests untuk setiap endpoint
- Setup test database
- Implementasi authentication testing
- Setup continuous integration

---

#### ⏳ **Step 14: API Documentation**
**Status:** Pending  
**Estimasi:** 30 menit  

**Yang Akan Dilakukan:**
- Install dan setup Swagger/OpenAPI
- Dokumentasi semua endpoints
- Buat example requests dan responses
- Setup Postman collection

---

## 🏆 **CHECKLIST VALIDATION**

### **Setiap Step Harus Memenuhi:**
- [ ] Kode berjalan tanpa error
- [ ] Semua test passing
- [ ] Documentation updated
- [ ] Git commit dengan message yang jelas
- [ ] Code review checklist completed

### **Quality Gates:**
- [ ] Code mengikuti PSR-12 coding standards
- [ ] Semua function memiliki docblock comments
- [ ] Tidak ada hardcoded values
- [ ] Error handling implemented properly
- [ ] Security best practices applied

---

## 📚 **RESOURCES & REFERENCES**

### **Laravel 12 Documentation:**
- [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum)
- [API Resources](https://laravel.com/docs/12.x/eloquent-resources)
- [Validation](https://laravel.com/docs/12.x/validation)

### **Best Practices:**
- RESTful API Design
- JWT vs Token Authentication
- API Versioning Strategies
- Error Handling Patterns

---

## 🎓 **LEARNING OUTCOMES**

**Setelah menyelesaikan training ini, peserta akan memiliki:**
1. Working knowledge Laravel 12 API development
2. Understanding of authentication patterns
3. Experience dengan testing dan documentation
4. Portfolio project yang dapat digunakan
5. Skills untuk maintain dan scale API

---

**Catatan:** Dokumentasi ini akan terus diupdate setiap ada perubahan atau penambahan step baru.

**Last Updated:** 2025-06-26  
**Trainer:** Claude AI Assistant  
**Project:** Laundry API Development Training