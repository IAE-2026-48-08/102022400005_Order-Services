# Laravel Order Service (Intan) - IAE Assignment 2

Repositori layanan **Order Service (Intan)** berbasis **Laravel 11 (PHP 8.3)**. Proyek ini didesain sebagai bagian tugas individu terpisah untuk **IAE Assignment 2**. Layanan ini mendukung komunikasi hibrida **REST API** (didokumentasikan via Swagger L5) dan **GraphQL API** (menggunakan Lighthouse & GraphiQL Playground), serta diamankan menggunakan validasi API Key **`X-IAE-KEY`** via Request Header.

Layanan ini dirancang untuk berkomunikasi dengan **Inventory Service (Dhika)** menggunakan HTTP Client untuk mengecek ketersediaan barang di gudang sebelum melanjutkan order ke tahap transaksi.

---

## 📋 Rubrikasi & Indikator Keberhasilan Tugas

- **Fungsionalitas REST (40%)**: Mendukung 3 endpoint REST Order Service yang berjalan lancar di Docker dengan status code yang tepat (`200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`) dan format JSON yang konsisten.
- **API Documentation (20%)**: Dokumentasi interaktif Swagger UI tersedia, dapat diakses penuh, dan mencakup semua endpoint.
- **GraphQL Implementation (20%)**: Query data & Mutation dapat dilakukan melalui Playground/GraphiQL dengan skema terstruktur (Lighthouse) yang fleksibel memilih field.
- **Security & Standard (10%)**: Seluruh akses REST & GraphQL dilindungi dengan validasi API Key pada Header (**`X-IAE-KEY`**).

---

## 🏗️ Struktur Proyek Laravel

```
order-fulfillment-service/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php         # Base Controller Laravel
│   │   │   └── OrderController.php     # REST Order Service (Intan)
│   │   └── Middleware/
│   │       └── ApiKeyMiddleware.php   # Middleware Keamanan Header X-IAE-KEY
│   ├── GraphQL/
│   │   ├── Queries/
│   │   │   └── FulfillmentQuery.php   # Resolver Kueri GraphQL Lighthouse
│   │   └── Mutations/
│   │       └── FulfillmentMutation.php# Resolver Mutasi GraphQL Lighthouse
│   └── Services/
│       └── DatabaseService.php        # Database Semu & Laravel HTTP Client
├── bootstrap/
│   └── app.php                        # Registrasi rute API & Middleware alias
├── config/
│   ├── l5-swagger.php                 # Konfigurasi Swagger L5
│   └── lighthouse.php                 # Konfigurasi Lighthouse GraphQL
├── graphql/
│   └── schema.graphql                 # Skema GraphQL (Lighthouse Type Defs)
├── routes/
│   ├── api.php                        # Rute REST API (/api/v1/*)
│   └── web.php                        # Redirects web
├── Dockerfile                         # PHP 8.3-FPM + Nginx Dockerfile
├── docker-compose.yml                 # Docker Compose konfigurasi
├── ai-prompt-history.md               # File riwayat AI Prompting (Wajib Tugas)
└── README.md                          # Dokumentasi & Panduan Uji (Berkas ini)
```

---

## 🔑 Informasi Keamanan (API Key)

Seluruh permintaan ke API (REST dan GraphQL) **wajib** menyertakan API Key berikut pada Request Header:
- **Header Key**: `X-IAE-KEY`
- **Header Value**: `102022400005` *(NIM Mahasiswa)*

Jika API Key tidak dicantumkan, sistem mengembalikan status `401 Unauthorized`. Jika API Key salah, sistem mengembalikan status `403 Forbidden`.

---

## ⚡ Cara Menjalankan Aplikasi

### Opsi A: Menggunakan Docker Compose (Direkomendasikan)
Pastikan Docker Desktop aktif di sistem Anda, kemudian buka terminal di direktori proyek dan jalankan:
```bash
docker-compose up --build -d
```
Kontainer PHP 8.3-FPM + Nginx akan di-build dan berjalan di latar belakang pada port `3000`.

### Opsi B: Berjalan di Mesin Lokal (Tanpa Docker)
1. Salin `.env.example` menjadi `.env` jika belum ada, lalu generate application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
2. Kompilasi ulang Swagger OpenAPI:
   ```bash
   php artisan l5-swagger:generate
   ```
3. Jalankan server pembangunan lokal Laravel:
   ```bash
   php artisan serve --port=3000
   ```

---

## 🌐 Endpoint Utama

Aplikasi menyediakan halaman navigasi terpadu saat Anda mengakses:
- **Swagger REST UI**: [http://localhost:3000/api/documentation](http://localhost:3000/api/documentation)
- **GraphiQL Playground**: [http://localhost:3000/graphiql](http://localhost:3000/graphiql)

---

## 📖 Alur Pengujian Bisnis (Probis) Menggunakan REST API

Berikut adalah skenario pengujian 3 langkah terurut untuk menguji Order Service (Intan) baik menggunakan simulasi (Mock) maupun integrasi riil ke kontainer Dhika.

### Langkah 1: Melihat Daftar Pesanan Awal (Intan)
Melihat daftar order yang sudah dibuat di sistem (berstatus `PENDING`).
* **Request**:
  ```bash
  curl -X GET http://localhost:3000/api/v1/orders \
    -H "X-IAE-KEY: 102022400005"
  ```
* **Respons Berhasil (200 OK - Standard Integration Contract)**:
  ```json
  {
    "status": "success",
    "message": "Operation successful",
    "data": [
      {
        "id": "ORD-001",
        "customerId": "CUST-INTAN-01",
        "items": [
          {"itemId": "ITEM-001", "name": "Buku Pemrograman JS Modern", "quantity": 2, "price": 150000}
        ],
        "totalAmount": 300000,
        "status": "PENDING",
        "createdAt": "2026-05-30T10:15:36+07:00"
      }
      // ... data lainnya
    ],
    "meta": {
      "service_name": "Order-Service",
      "api_version": "v1"
    }
  }
  ```

---

### Langkah 2: Mengambil Detail & Validasi Order Tertentu
Mendapatkan informasi detail untuk spesifik order `ORD-001`.
* **Request**:
  ```bash
  curl -X GET http://localhost:3000/api/v1/orders/ORD-001 \
    -H "X-IAE-KEY: 102022400005"
  ```
* **Respons Berhasil (200 OK)**:
  Mengembalikan representasi JSON pesanan tunggal berformat standard.

---

### Langkah 3: Memproses Order ke Tahap Transaksi (HTTP Client Stock Check)
Saat memproses order `ORD-001`, Order Service akan melakukan panggilan HTTP ke Inventory Service Dhika untuk memastikan ketersediaan stok barang.
* **Request**:
  ```bash
  curl -X POST http://localhost:3000/api/v1/orders \
    -H "X-IAE-KEY: 102022400005" \
    -H "Content-Type: application/json" \
    -d "{\"orderId\": \"ORD-001\"}"
  ```
* **Respons Berhasil (200 OK)**:
  ```json
  {
    "status": "success",
    "message": "Operation successful. Order ORD-001 berhasil dilanjutkan ke tahap transaksi. Stok barang berhasil dikurangi.",
    "data": {
      "id": "ORD-001",
      "customerId": "CUST-INTAN-01",
      "items": [...],
      "totalAmount": 300000,
      "status": "TRANSACTION",
      "createdAt": "2026-05-30T10:15:36+07:00"
    },
    "meta": {
      "service_name": "Order-Service",
      "api_version": "v1"
    }
  }
  ```

---

## 📊 Pengujian GraphQL API

Gunakan GraphiQL Client terintegrasi di browser Anda pada alamat [http://localhost:3000/graphiql](http://localhost:3000/graphiql). 

> [!IMPORTANT]
> **Penting**: Pastikan untuk menambahkan HTTP Header `{"X-IAE-KEY": "102022400005"}` di kolom panel "Headers" di pojok kiri bawah Playground agar permintaan Anda tidak ditolak oleh middleware Laravel ApiKey.

### 1. Kueri Fleksibel Terpadu (Query Ambil Data REST API)
```graphql
query AmbilDataFulfillment {
  orders {
    id
    customerId
    status
    totalAmount
    items {
      name
      quantity
    }
  }
}
```

### 2. Mutation: Memproses Order (Step 3 GraphQL)
```graphql
mutation ProsesOrderTransaksi {
  processOrder(orderId: "ORD-001") {
    id
    status
    totalAmount
  }
}
```
