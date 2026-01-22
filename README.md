# PayGate API

API Integrasi Payment Gateway.
Proyek ini menyediakan layanan backend untuk memproses pembayaran digital secara aman dan efisien.

## 🚀 Fitur

- **Secure Payment**: Pemrosesan transaksi yang aman dengan validasi server-side.
- **Payment Gateway Integration**: Mendukung pembayaran via PayPal.
- **Asynchronous Processing**: Penanganan proses pasca-transaksi di latar belakang.

## 🛠 Prasyarat

- PHP 8.2+
- Composer
- Database Server

## ⚙️ Instalasi

1. **Clone repository**

    ```bash
    git clone https://github.com/zaenalrfn/paygate-api.git
    ```

2. **Install Dependencies**

    ```bash
    composer install
    ```

3. **Konfigurasi**
   Salin file konfigurasi contoh dan sesuaikan dengan lingkungan server Anda.

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Pastikan kredensial database dan payment gateway telah diisi dengan benar pada file `.env`.

4. **Setup Database**
   Jalankan migrasi untuk menyiapkan struktur database.

    ```bash
    php artisan migrate
    php artisan db:seed --class=ProductSeeder
    ```

5. **Jalankan Aplikasi**
    ```bash
    php artisan serve
    ```

## 📖 Penggunaan API

### Buat Pesanan

Untuk memulai transaksi, kirim permintaan POST ke endpoint pembuatan pesanan dengan menyertakan daftar item.

- **Method**: `POST`
- **Endpoint**: `/api/paypal/create`
- **Body**: JSON berisi `items` dan `currency`.

### Pembayaran Berhasil

Sistem akan secara otomatis menangani callback dari payment gateway setelah pembayaran disetujui oleh pengguna.

---

Dibuat untuk keperluan integrasi sistem pembayaran.
