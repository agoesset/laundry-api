# 📖 **LAUNDRY API DOCUMENTATION**

## 🔗 **Base URL**
```
http://localhost:8000/api/v1
```

## 🛡️ **Authentication**
API menggunakan Laravel Sanctum token-based authentication.

**Header Authorization:**
```
Authorization: Bearer {your-token}
```

---

## 📋 **Response Format**

### **Success Response:**
```json
{
    "success": true,
    "message": "Pesan sukses",
    "data": {
        // data response
    }
}
```

### **Error Response:**
```json
{
    "success": false,
    "message": "Pesan error",
    "errors": {
        "field": ["Error message"]
    }
}
```

---

## 🔐 **AUTHENTICATION ENDPOINTS**

### **1. Login**
**POST** `/auth/login`

**Request Body:**
```json
{
    "email": "admin@laundry.com",
    "password": "password123",
    "device_name": "mobile-app"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin Laundry",
            "email": "admin@laundry.com",
            "role": "Admin",
            "point": 0,
            "foto_url": null,
            "no_telp": "08123456789",
            "alamat": "Jl. Admin No. 1"
        },
        "token": "1|laravel_sanctum_xxx",
        "token_type": "Bearer"
    }
}
```

### **2. Register Customer**
**POST** `/auth/register`

**Request Body:**
```json
{
    "name": "Customer Baru",
    "email": "customer@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "no_telp": "081234567890",
    "alamat": "Jl. Customer No. 123",
    "device_name": "mobile-app"
}
```

### **3. Check Email Availability**
**POST** `/auth/check-email`

**Request Body:**
```json
{
    "email": "test@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "available": true,
        "message": "Email tersedia"
    }
}
```

### **4. Logout**
**POST** `/auth/logout` *(Auth Required)*

**Response:**
```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

### **5. Logout All Devices**
**POST** `/auth/logout-all` *(Auth Required)*

### **6. Get Profile**
**GET** `/auth/profile` *(Auth Required)*

### **7. Update Password**
**PUT** `/auth/update-password` *(Auth Required)*

**Request Body:**
```json
{
    "current_password": "oldpassword",
    "password": "newpassword",
    "password_confirmation": "newpassword"
}
```

---

## 💰 **PRICE ENDPOINTS**

### **1. Get All Prices**
**GET** `/prices` *(Auth Required)*

**Query Parameters:**
- `show_all=1` - Admin only, show inactive prices
- `jenis=Cuci Kering` - Filter by service type
- `sort_by=harga` - Sort by field
- `sort_order=asc` - Sort order
- `group_by_jenis=true` - Group by service type

**Response:**
```json
{
    "success": true,
    "message": "Data harga berhasil diambil",
    "data": [
        {
            "id": 1,
            "jenis": "Cuci Kering",
            "kg": "1 kg",
            "harga": 5000,
            "hari": 1,
            "status": "Active",
            "user": {
                "id": 1,
                "name": "Admin",
                "email": "admin@laundry.com"
            }
        }
    ]
}
```

### **2. Create Price** *(Admin Only)*
**POST** `/prices`

**Request Body:**
```json
{
    "jenis": "Cuci Express",
    "kg": "1 kg",
    "harga": 8000,
    "hari": 1,
    "status": "Active"
}
```

### **3. Get Price Detail**
**GET** `/prices/{id}` *(Auth Required)*

### **4. Update Price** *(Admin Only)*
**PUT** `/prices/{id}`

### **5. Delete Price** *(Admin Only)*
**DELETE** `/prices/{id}`

### **6. Get Service Types List**
**GET** `/prices/jenis-list` *(Auth Required)*

**Response:**
```json
{
    "success": true,
    "message": "List jenis layanan berhasil diambil",
    "data": [
        "Cuci Kering",
        "Cuci Setrika",
        "Cuci Lipat",
        "Dry Clean",
        "Express"
    ]
}
```

---

## 📝 **TRANSACTION ENDPOINTS**

### **1. Get Transactions**
**GET** `/transactions` *(Auth Required)*

**Query Parameters:**
- `status_order=Process` - Filter by order status
- `status_payment=Success` - Filter by payment status
- `date_from=2025-01-01` - Filter by date range
- `date_to=2025-12-31` - Filter by date range
- `search=LND-001` - Search by invoice or customer
- `per_page=10` - Pagination
- `sort_by=created_at` - Sort field
- `sort_order=desc` - Sort order

**Response:**
```json
{
    "success": true,
    "message": "Data transaksi berhasil diambil",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "invoice": "LND-20250626-0001",
                "customer": {
                    "id": 2,
                    "name": "Customer Name",
                    "email": "customer@example.com"
                },
                "user": {
                    "id": 1,
                    "name": "Admin"
                },
                "price": {
                    "id": 1,
                    "jenis": "Cuci Kering",
                    "harga": 5000
                },
                "berat": 3.5,
                "total_harga": 17500,
                "diskon": 0,
                "harga_akhir": 17500,
                "catatan": "Catatan transaksi",
                "status_order": "Process",
                "status_payment": "Pending",
                "created_at": "2025-06-26T10:00:00.000000Z"
            }
        ],
        "per_page": 10,
        "total": 1
    }
}
```

### **2. Create Transaction** *(Admin/Karyawan Only)*
**POST** `/transactions`

**Request Body:**
```json
{
    "customer_id": 2,
    "price_id": 1,
    "berat": 2.5,
    "catatan": "Cuci bersih",
    "diskon": 10,
    "status_order": "Process",
    "status_payment": "Pending"
}
```

### **3. Get Transaction Detail**
**GET** `/transactions/{id}` *(Auth Required)*

### **4. Update Transaction** *(Admin/Karyawan Only)*
**PUT** `/transactions/{id}`

**Request Body:**
```json
{
    "status_order": "Done",
    "status_payment": "Success",
    "catatan": "Updated note"
}
```

### **5. Delete Transaction** *(Admin Only)*
**DELETE** `/transactions/{id}`

### **6. Get Transaction Summary** *(Admin/Karyawan Only)*
**GET** `/transactions/summary`

**Response:**
```json
{
    "success": true,
    "data": {
        "total_transactions": 150,
        "total_revenue": 2500000,
        "pending_transactions": 25,
        "completed_transactions": 125,
        "monthly_revenue": [
            {"month": "2025-01", "revenue": 500000},
            {"month": "2025-02", "revenue": 750000}
        ]
    }
}
```

---

## 👤 **USER PROFILE ENDPOINTS**

### **1. Update Profile**
**PUT** `/profile/update` *(Auth Required)*

**Request Body:**
```json
{
    "name": "Updated Name",
    "no_telp": "081234567890",
    "alamat": "Updated Address",
    "theme": "dark"
}
```

### **2. Upload Profile Photo**
**POST** `/profile/photo` *(Auth Required)*

**Request Body:** *(Form Data)*
```
foto: (file) max 2MB, jpg/jpeg/png
```

### **3. Delete Profile Photo**
**DELETE** `/profile/photo` *(Auth Required)*

### **4. Get Points History** *(Customer Only)*
**GET** `/profile/points-history`

**Response:**
```json
{
    "success": true,
    "data": {
        "current_points": 25,
        "history": [
            {
                "invoice": "LND-20250626-0001",
                "date": "26/06/2025",
                "amount": 50000,
                "points_earned": 5
            }
        ]
    }
}
```

---

## 👥 **CUSTOMER MANAGEMENT** *(Admin/Karyawan Only)*

### **1. Get Customers**
**GET** `/customers`

**Query Parameters:**
- `status=Active` - Filter by status
- `search=john` - Search by name/email/phone
- `sort_by=name` - Sort field
- `per_page=10` - Pagination

### **2. Create Customer**
**POST** `/customers`

**Request Body:**
```json
{
    "name": "New Customer",
    "email": "newcustomer@example.com",
    "password": "password123",
    "no_telp": "081234567890",
    "alamat": "Customer Address"
}
```

### **3. Get Customer Detail**
**GET** `/customers/{id}`

### **4. Update Customer Status** *(Admin Only)*
**PUT** `/customers/{id}/status`

**Request Body:**
```json
{
    "status": "Inactive"
}
```

---

## 📊 **BUSINESS RULES**

### **Order Status Flow:**
```
Process → Done → Delivery
```
*(Status tidak bisa mundur)*

### **Payment Status:**
- `Pending` - Belum dibayar
- `Success` - Sudah dibayar  
- `Failed` - Gagal bayar

### **Points System:**
- Customer mendapat 1 point untuk setiap Rp 10.000 yang dibayar
- Points diberikan saat `status_order = Done` dan `status_payment = Success`

### **Validation Rules:**
- **Minimum Order:** Rp 10.000 (configurable via settings)
- **Maximum Discount:** 20% (configurable via settings)
- **Weight Range:** 0.1 kg - 100 kg
- **Service Days:** 1-30 hari

---

## ⚙️ **SYSTEM SETTINGS**

Settings dikonfigurasi via `laundry_settings` table:
- `minimum_order`: Minimal order dalam rupiah
- `max_discount_percent`: Maksimal diskon dalam persen
- `working_days`: Hari kerja (JSON array)
- `opening_time` & `closing_time`: Jam operasional

---

## 🔒 **ROLE PERMISSIONS**

### **Admin:**
- Full access ke semua endpoints
- Dapat CRUD semua data
- Dapat mengubah status customer
- Dapat melihat summary dan reports

### **Karyawan:**
- Dapat CRUD transaksi
- Dapat melihat semua data customer dan transaksi
- Tidak dapat menghapus data
- Tidak dapat mengubah harga

### **Customer:**
- Hanya dapat melihat data pribadi
- Dapat update profile sendiri
- Dapat melihat transaksi sendiri saja
- Dapat melihat history points

---

## 📱 **ERROR CODES**

| HTTP Code | Description |
|-----------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## 🧪 **TESTING**

API telah ditest dengan comprehensive test suite:

```bash
# Run all tests
php artisan test

# Run specific test group
php artisan test --filter="Authentication API"
php artisan test --filter="Price API"
php artisan test --filter="Transaction API"
```

**Test Coverage:**
- ✅ Authentication & Authorization
- ✅ CRUD Operations
- ✅ Validation & Error Handling
- ✅ Business Rules
- ✅ Role-based Access Control
- ✅ Edge Cases & Security

---

## 📝 **NOTES**

1. **Rate Limiting:** 60 requests per minute per user
2. **Token Expiration:** 24 hours (configurable)
3. **File Upload:** Max 2MB for profile photos
4. **Database:** MySQL with proper indexes
5. **Environment:** Supports development & production modes

**Created:** 2025-06-26  
**Version:** 1.0.0  
**Framework:** Laravel 12