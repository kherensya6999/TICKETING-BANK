# 🚀 QUICK START GUIDE

## URL yang Harus Dibuka di Chrome:

### 1. Frontend (Aplikasi Utama)
```
http://localhost:3000
```

### 2. Backend API (Testing)
```
http://localhost:8000/api
```

---

## ⚡ SETUP CEPAT (Lakukan Sekali Saja)

### STEP 1: Install Laravel Dependencies
```bash
composer install
```

### STEP 2: Setup Environment
```bash
# Copy file .env
copy .env.example .env

# Generate app key
php artisan key:generate
```

### STEP 3: Setup Database
1. Buka phpMyAdmin: http://localhost/phpmyadmin
2. Buat database baru: `ticketing_db`
3. Edit file `.env`:
   ```
   DB_DATABASE=ticketing_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### STEP 4: Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### STEP 5: Create Storage Link
```bash
php artisan storage:link
```

### STEP 6: Install Frontend Dependencies
```bash
npm install
```

---

## 🎯 MENJALANKAN APLIKASI

### Buka 3 Terminal/PowerShell:

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```
✅ Backend akan berjalan di: http://localhost:8000

**Terminal 2 - Queue Worker (PENTING!):**
```bash
php artisan queue:work
```
✅ Untuk auto-assignment dan notifications

**Terminal 3 - React Frontend:**
```bash
npm run dev
```
✅ Frontend akan berjalan di: http://localhost:3000

---

## 🌐 URL YANG DIBUKA DI CHROME:

### **URL UTAMA (Buka ini):**
```
http://localhost:3000
```

Ini adalah aplikasi ticketing system yang akan muncul.

### **URL Testing API:**
```
http://localhost:8000/api
```

Ini untuk test apakah backend berjalan (akan muncul JSON response).

---

## 🔐 LOGIN DEFAULT

Setelah seeding, buat user admin manual atau gunakan:
- Username: admin
- Password: (buat di database atau seeder)

---

## ⚠️ TROUBLESHOOTING

### Error: "Could not open input file: artisan"
→ Pastikan Anda di folder yang benar: `C:\xampp\htdocs\TICKETING`

### Error: Port 3000 sudah digunakan
→ Ganti port di `vite.config.js` atau tutup aplikasi lain yang pakai port 3000

### Error: Port 8000 sudah digunakan  
→ Ganti port: `php artisan serve --port=8001`

### Database connection error
→ Pastikan MySQL/XAMPP sudah running dan database sudah dibuat

---

## ✅ CHECKLIST SEBELUM BUKA BROWSER:

- [ ] Composer install sudah jalan
- [ ] npm install sudah jalan
- [ ] Database sudah dibuat
- [ ] Migrations sudah dijalankan
- [ ] php artisan serve sudah running (Terminal 1)
- [ ] php artisan queue:work sudah running (Terminal 2)
- [ ] npm run dev sudah running (Terminal 3)

**Setelah semua checklist ✅, baru buka:**
```
http://localhost:3000
```
