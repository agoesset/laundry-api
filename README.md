# Laundry API

API untuk aplikasi mobile laundry menggunakan Laravel 12 dan Laravel Sanctum.

## 📋 Features

- **Authentication** dengan Laravel Sanctum (Personal Access Tokens)
- **Role-based Access Control** (Admin, Karyawan, Customer)
- **Transaction Management** dengan status flow validation
- **Price Management** untuk berbagai jenis layanan
- **Customer Management** dengan point system
- **Auto-generated API Documentation** dengan Scramble

## 🚀 Requirements

- PHP 8.4+
- MySQL 8.0+
- Composer 2.0+
- Node.js 18+ (optional, untuk frontend assets)

## 📦 Installation

1. Clone repository:
```bash
git clone https://github.com/yourusername/laundry-api.git
cd laundry-api
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure database di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_laundry_api
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations dan seeders:
```bash
php artisan migrate --seed
```

7. Start development server:
```bash
php artisan serve
```

## 📖 API Documentation

API documentation tersedia di: http://localhost:8000/docs/api

### Default Users (dari Seeder):
- **Admin**: `admin@laundry.com` / `password123`
- **Karyawan**: `karyawan@laundry.com` / `password123`
- **Customer**: `customer@example.com` / `password123`

## 🧪 Testing

### Run All Tests:
```bash
php artisan test
```

### Quick API Test:
```bash
./test-api.sh
```

### Manual Testing dengan cURL:

1. Login:
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@laundry.com","password":"password123","device_name":"test"}'
```

2. Use Token:
```bash
curl -X GET http://localhost:8000/api/v1/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📚 API Endpoints

### Authentication
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/register` - Register customer
- `POST /api/v1/auth/logout` - Logout current session
- `POST /api/v1/auth/logout-all` - Logout all sessions
- `GET /api/v1/auth/profile` - Get user profile
- `PUT /api/v1/auth/update-password` - Update password
- `POST /api/v1/auth/check-email` - Check email availability

### Prices
- `GET /api/v1/prices` - Get all active prices
- `GET /api/v1/prices/{id}` - Get price detail
- `POST /api/v1/prices` - Create price (Admin/Karyawan)
- `PUT /api/v1/prices/{id}` - Update price (Admin/Karyawan)
- `DELETE /api/v1/prices/{id}` - Delete price (Admin/Karyawan)
- `GET /api/v1/prices/jenis-list` - Get service types

### Transactions
- `GET /api/v1/transactions` - Get transactions
- `GET /api/v1/transactions/{id}` - Get transaction detail
- `POST /api/v1/transactions` - Create transaction (Admin/Karyawan)
- `PUT /api/v1/transactions/{id}` - Update transaction (Admin/Karyawan)
- `DELETE /api/v1/transactions/{id}` - Delete transaction (Admin)
- `GET /api/v1/transactions/summary` - Get summary (Admin/Karyawan)

### Profile & User Management
- `PUT /api/v1/profile/update` - Update profile
- `POST /api/v1/profile/photo` - Upload photo
- `DELETE /api/v1/profile/photo` - Delete photo
- `GET /api/v1/profile/points-history` - Get points history (Customer)
- `GET /api/v1/customers` - Get customers (Admin/Karyawan)
- `POST /api/v1/customers` - Create customer (Admin/Karyawan)
- `GET /api/v1/customers/{id}` - Get customer detail (Admin/Karyawan)
- `PUT /api/v1/customers/{id}/status` - Update status (Admin)

## 🛡️ Security

- Authentication menggunakan Laravel Sanctum Personal Access Tokens
- Role-based access control (Admin, Karyawan, Customer)
- Request validation dengan Form Requests
- Rate limiting untuk API endpoints
- CORS configured untuk mobile apps

## 📱 Flutter Integration

Headers untuk setiap request:
```dart
Map<String, String> headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': 'Bearer $token',
};
```

## 🔧 Configuration

### Laundry Settings (dari Seeder):
- **Minimum Order**: Rp 10,000
- **Max Discount**: 20%
- **Working Hours**: 08:00 - 20:00
- **Working Days**: Monday - Saturday

### Available Services:
1. Cuci Kering - Rp 5,000/kg (1 hari)
2. Cuci Setrika - Rp 7,000/kg (2 hari)
3. Cuci Lipat - Rp 6,000/kg (1 hari)
4. Dry Clean - Rp 15,000/kg (3 hari)
5. Express - Rp 10,000/kg (1 hari)

## 📄 License

This project is licensed under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

For support, email support@laundry-api.com or create an issue in this repository.