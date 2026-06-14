1. berdasarkan capaian teknis dan luaran dari tugas 3, berikan penjelasan bagaimana cara kerja dan alur implementasi dari Federated SSO, SOAP XML Client, AMQP Publisher. setelah itu jelaskan jadi step step pengerjaannya seperti apa, apa yang harus aku lakukan terlebih dahulu untuk mengerjakan tugas iae ini. jelaskan dengan runtut dan jelas, stepnya dengan spesifik dan rinci.

2. selanjutnya jelaskan cara pengerjaan Modul 2: SOAP XML Client 40% Kode SOAP Client berhasil melakukan transformasi data JSON ke format XML
Envelope kaku dan menyimpan ReceiptNumber dari Dosen.
Modul 3: AMQP Publisher 20% Aplikasi berhasil mengirimkan event notification dalam bentuk JSON ke RabbitMQ
Dosen tanpa memicu error.

3. kan aku habis masukin kode di centrall integration php itu yang soap, trus yang amqp publsiher gimana caranya

4. {
    "status": "error",
    "message": "Unauthorized: Token JWT tidak valid atau kedaluwarsa. cURL error 6: Could not resolve host: iae-sso.virtualfri.id (DNS server returned general failure) (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://iae-sso.virtualfri.id/api/v1/auth/jwks",
    "errors": null
}
ada error saat run di swagger, gini terus dari tadi dicoba

5. Response body: { "status": "error", "message": "Gagal memproses transaksi: Otorisasi token M2M kosong.", "errors": null } ini error kenapa

6. trus kenapa yang post order barusan aku coba ga muncul di board rabbitmq
