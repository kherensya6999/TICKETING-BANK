# 🔐 SETUP ADMIN REGISTRATION CODE

## Konfigurasi Admin Code

Untuk keamanan, admin registration memerlukan **Admin Code** khusus.

### Cara Setup:

1. **Buka file `.env`** di root project
2. **Tambahkan atau edit** baris berikut:

```env
ADMIN_REGISTRATION_CODE=BANKSUMUT2026ADMIN
```

3. **Ganti dengan code yang aman** (disarankan menggunakan kombinasi huruf, angka, dan karakter khusus)
4. **Simpan file**
5. **Restart server** jika sedang running

### Default Code:
```
BANKSUMUT2026ADMIN
```

### Rekomendasi:
- Gunakan code yang panjang (minimal 16 karakter)
- Kombinasi huruf besar, huruf kecil, angka, dan karakter khusus
- Jangan share code ini ke user biasa
- Ganti secara berkala untuk keamanan

### Contoh Code yang Aman:
```
BS2026!@#ADMIN_SECURE_CODE_XYZ
```

---

## Cara Menggunakan:

1. **User mengakses halaman register**
2. **Pilih role: ADMIN**
3. **Masukkan Admin Code** yang sudah dikonfigurasi
4. **Jika code valid**, account akan dibuat dengan status aktif
5. **Jika code invalid**, akan muncul error

---

## Keamanan:

- Admin code disimpan di environment variable (tidak di code)
- Failed attempts akan di-log untuk monitoring
- Disarankan untuk menggunakan code yang berbeda di production
