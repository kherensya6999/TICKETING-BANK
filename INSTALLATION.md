# Installation Guide - IT Security Ticketing System

## Prerequisites

- PHP 8.1 atau lebih tinggi
- Composer
- Node.js 18+ dan npm
- MySQL 8.0+
- XAMPP (untuk Windows)

## Backend Setup (Laravel)

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Database**
   Edit file `.env` dan set konfigurasi database:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ticketing_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Seed Database**
   ```bash
   php artisan db:seed
   ```

6. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

7. **Start Queue Worker** (di terminal terpisah)
   ```bash
   php artisan queue:work
   ```

8. **Start Laravel Server**
   ```bash
   php artisan serve
   ```

## Frontend Setup (React)

1. **Install Dependencies**
   ```bash
   npm install
   ```

2. **Setup Environment**
   Buat file `.env` di root:
   ```
   VITE_API_URL=http://localhost:8000/api
   ```

3. **Start Development Server**
   ```bash
   npm run dev
   ```

## Setup Cron Job (untuk SLA Monitoring)

Tambahkan ke crontab atau Task Scheduler Windows:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Atau untuk Windows, jalankan:
```bash
php artisan schedule:work
```

## Default Login

Setelah seeding, buat user admin manual di database atau gunakan seeder untuk membuat user default.

## Troubleshooting

1. **Permission Error**: Pastikan folder `storage` dan `bootstrap/cache` writable
2. **Queue Not Working**: Pastikan queue worker berjalan
3. **CORS Error**: Pastikan CORS sudah dikonfigurasi di Laravel
4. **Token Error**: Pastikan `APP_KEY` sudah di-generate
