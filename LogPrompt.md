Saya ditugaskan untuk membangun sebuah mini service mandiri bernama Order Service sebagai bagian dari pemenuhan tugas IAE. Service ini harus bisa "berbicara" dengan sistem lain menggunakan protokol
komunikasi modern.. dengan rubrikasi ketentuan sebagai berikut:
Kriteria Penilaian Bobot Indikator Keberhasilan
Fungsionalitas REST 40% Endpoint berjalan lancar di Docker, status code tepat (200, 201, 404),
dan JSON valid.
API Documentation 20% Swagger UI dapat diakses dan mencerminkan seluruh endpoint yang
tersedia.
GraphQL Implementation 20% Berhasil melakukan query data melalui Playground dengan skema
yang tepat
Security & Standard 10% Penggunaan API Key berfungsi

• Format Respon: Wajib menggunakan format JSON yang konsisten.
• Security: Implementasikan pengamanan sederhana menggunakan API Key
yang dikirimkan melalui Request Header.
• Documentation: Wajib menyediakan dokumentasi interaktif menggunakan
Swagger/OpenAPI

dengan probis pemenuhan pesanan (fulfillment)

Intan melihat detail order yang sudah dibuat
Intan mengecek ketersediaan barang ke gudang dengan mengambil data dari Inventory Service milik Dhika.
Jika barang tidak tersedia, maka order tidak dilanjutkan
Jika barang tersedia, maka order dilanjutkan ke tahap transaksi
Dhika sebagai bagian gudang memproses barang yang telah dipesan oleh customer.
Gudang melakukan Quality Control (QC) untuk memastikan barang sesuai pesanan, jumlahnya benar, dan kondisinya layak dikirim.
Setelah barang lolos QC, gudang menghubungi ekspedisi untuk melakukan pengiriman barang.
Meilisya sebagai bagian ekspedisi membuat data pengiriman berdasarkan order yang sudah diproses.
Ekspedisi mengirimkan barang dari gudang ke alamat customer.
Customer menerima barang.

dan end point order service (intan) 
GET /api/v1/orders = Melihat daftar semua order.
GET /api/v1/orders/{id}= Validasi/detail order 
POST /api/v1/orders= Memproses order ke tahap transaksi setelah stok tersedia  

dan dengan standar pengerjaan sebagai berikut
Standard Integration Contract (IAE-T2)
Setiap service individu wajib mematuhi standar teknis berikut agar dapat 
berinteraksi dalam ekosistem Enterprise:
1. Protokol & Format Data
Protokol: HTTP/1.1
Format Pesan: JSON (JavaScript Object Notation)
Charset: UTF-8
Content-Type: application/json
2. Standar Struktur Respon (Wrapper)
Semua API yang dibuat wajib membungkus (wrap) data dalam struktur yang 
konsisten agar mudah diproses oleh sistem lain:
Respon Berhasil (Success - 2xx):
JSON
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": { ... }, // Objek atau Array data utama
  "meta": {        // Opsional: Untuk pagination atau info 
tambahan
    "service_name": "Inventory-Service",
    "api_version": "v1"
  }
}
Respon Gagal (Error - 4xx/5xx):
JSON
{
  "status": "error",
  "message": "Detail pesan kesalahan (misal: Resource not 
found)",
  "errors": null // Opsional: Detail error validasi (array)
}
3. Keamanan (X-IAE-KEY)
Setiap endpoint harus diproteksi dengan API Key. Untuk Tugas 2, mahasiswa 
menggunakan mekanisme Header Authentication:
Header Key: X-IAE-KEY
Value: 102022400005 (Sebagai identitas sementara sebelum pindah 
ke SSO di Tugas 3).
4. Spesifikasi Endpoint (Minimum Viable API)
Setiap layanan wajib menyediakan minimal 3 jenis akses:
Collection: GET /api/v1/[resource] (Mengambil daftar data).
Resource: GET /api/v1/[resource]/{id} (Mengambil data spesifik).
Action: POST /api/v1/[resource] (Menambah data baru/memicu 
proses)

cara run swagger nya bagaimana? dan dibagian mana authorize value keynya?
query untuk graphqlnya bagaimana?

{
  "errors": [
    {
      "message": "Syntax Error: Unexpected Name \"graphql\"",
      "locations": [
        {
          "line": 1,
          "column": 1
        }
      ],
      "extensions": {
        "file": "/var/www/html/vendor/webonyx/graphql-php/src/Language/Parser.php",
        "line": 454
      }
    }
  ]
}
ini error bagian apa?
