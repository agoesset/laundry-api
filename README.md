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

## 🚀 Production Deployment

### Nginx Configuration
File konfigurasi Nginx untuk production deployment:

```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    server_name laundry-api.aguss.id;
    return 301 https://$server_name$request_uri;
}

# HTTPS Configuration
server {
    listen 443 ssl http2;
    server_name laundry-api.aguss.id;
    root /var/www/html/laundry-api/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/laundry-api.aguss.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/laundry-api.aguss.id/privkey.pem;
    
    # SSL Security Settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php;
    charset utf-8;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param HTTPS on;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Environment Configuration
Untuk production, pastikan setting berikut di `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://laundry-api.aguss.id

# Database production settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_laundry_api
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password
```

### Scramble Configuration
File `config/scramble.php` telah dikonfigurasi untuk production:

```php
'servers' => [
    'Production' => 'https://laundry-api.aguss.id/api',
],
```

Gate access untuk API docs di production:
```php
// In AppServiceProvider
Gate::define('viewApiDocs', function () {
    return true; // Allow all users to view API docs
});
```

### Deployment Commands
```bash
# Cache configurations
php artisan config:cache
php artisan route:cache

# Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart services
sudo systemctl reload nginx
sudo systemctl restart php8.4-fpm
```

## 📖 API Documentation

### Development:
API documentation tersedia di: http://localhost:8000/docs/api

### Production:
API documentation tersedia di: https://laundry-api.aguss.id/docs/api

**Note**: Dokumentasi menggunakan Scramble dan dapat diakses di environment production dengan gate access yang telah dikonfigurasi.

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

#### Development:
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

#### Production:
1. Login:
```bash
curl -X POST https://laundry-api.aguss.id/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@laundry.com","password":"password123","device_name":"test"}'
```

2. Use Token:
```bash
curl -X GET https://laundry-api.aguss.id/api/v1/auth/profile \
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