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

#### ⏳ **Step 4: Database Migration Setup**
**Status:** Pending  
**Estimasi:** 30 menit  

**Yang Akan Dilakukan:**
- Buat migration untuk tabel users
- Buat migration untuk tabel transaksis  
- Buat migration untuk tabel hargas
- Buat migration untuk tabel data_banks
- Setup foreign key relationships

---

#### ⏳ **Step 5: Model Creation & Relationships**
**Status:** Pending  
**Estimasi:** 25 menit  

**Yang Akan Dilakukan:**
- Buat model User dengan HasApiTokens trait
- Buat model Transaction dengan relationships
- Buat model Price dengan relationships
- Buat model DataBank dengan relationships
- Setup proper fillable dan hidden attributes

---

### 🔐 **PHASE 3: AUTHENTICATION SYSTEM**

#### ⏳ **Step 6: API Authentication Controller**
**Status:** Pending  
**Estimasi:** 35 menit  

**Yang Akan Dilakukan:**
- Buat AuthController untuk login/logout
- Implementasi registration untuk customer
- Setup validation rules
- Buat API responses yang konsisten

---

#### ⏳ **Step 7: Protected Routes & Middleware**
**Status:** Pending  
**Estimasi:** 20 menit  

**Yang Akan Dilakukan:**
- Setup sanctum middleware untuk protected routes
- Buat role-based middleware
- Konfigurasi CORS untuk Flutter app
- Setup rate limiting

---

### 📊 **PHASE 4: CORE API ENDPOINTS**

#### ⏳ **Step 8: Transaction API Controller**
**Status:** Pending  
**Estimasi:** 45 menit  

**Yang Akan Dilakukan:**
- Buat TransactionController dengan CRUD operations
- Implementasi validation rules
- Setup authorization untuk user roles
- Buat API Resources untuk response formatting

---

#### ⏳ **Step 9: Price Management API**
**Status:** Pending  
**Estimasi:** 30 menit  

**Yang Akan Dilakukan:**
- Buat PriceController untuk admin
- Implementasi CRUD untuk harga layanan
- Setup validation dan authorization
- Buat resource responses

---

#### ⏳ **Step 10: User Profile API**
**Status:** Pending  
**Estimasi:** 25 menit  

**Yang Akan Dilakukan:**
- Buat UserController untuk profile management
- Implementasi update profile
- Setup file upload untuk foto profile
- Buat customer list untuk admin

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