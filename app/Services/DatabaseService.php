<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class DatabaseService
{
    private static $cacheKey = 'order_service_db_state_2026';

    /**
     * Mengambil state data saat ini dari Laravel Cache.
     */
    public static function getState()
    {
        if (!Cache::has(self::$cacheKey)) {
            self::seed();
        }
        return Cache::get(self::$cacheKey);
    }

    /**
     * Menyimpan state data ke Laravel Cache.
     */
    public static function saveState($state)
    {
        Cache::put(self::$cacheKey, $state);
    }

    /**
     * Mengisi data awal (seed data) yang representatif hanya untuk pesanan (Order).
     */
    public static function seed()
    {
        $state = [
            'orders' => [
                [
                    'id' => 'ORD-001',
                    'customerId' => 'CUST-INTAN-01',
                    'items' => [
                        ['itemId' => 'ITEM-001', 'name' => 'Buku Pemrograman', 'quantity' => 2, 'price' => 150000]
                    ],
                    'totalAmount' => 300000,
                    'status' => 'PENDING', // PENDING, TRANSACTION, FAILED_STOCK_UNAVAILABLE, SHIPPED, DELIVERED
                    'createdAt' => now()->toIso8601String()
                ],
                [
                    'id' => 'ORD-002',
                    'customerId' => 'CUST-INTAN-02',
                    'items' => [
                        ['itemId' => 'ITEM-002', 'name' => 'Buku Latsol UTBK', 'quantity' => 1, 'price' => 120000]
                    ],
                    'totalAmount' => 120000,
                    'status' => 'PENDING',
                    'createdAt' => now()->toIso8601String()
                ],
                [
                    'id' => 'ORD-003',
                    'customerId' => 'CUST-INTAN-03',
                    'items' => [
                        ['itemId' => 'ITEM-003', 'name' => 'Buku Cerita Nabi', 'quantity' => 1, 'price' => 180000]
                    ],
                    'totalAmount' => 180000,
                    'status' => 'PENDING',
                    'createdAt' => now()->toIso8601String()
                ],
                [
                    'id' => 'ORD-004',
                    'customerId' => 'CUST-INTAN-04',
                    'items' => [
                        ['itemId' => 'ITEM-004', 'name' => 'Kamus Bahasa Inggris', 'quantity' => 1, 'price' => 150000],
                        ['itemId' => 'ITEM-005', 'name' => 'Buku Tulis Premium Pack', 'quantity' => 1, 'price' => 100000],
                        ['itemId' => 'ITEM-006', 'name' => 'Buku Panduan Lolos Tes CPNS', 'quantity' => 1, 'price' => 130000],
                        ['itemId' => 'ITEM-007', 'name' => 'Novel Matahari - Tere Liye', 'quantity' => 1, 'price' => 120000]
                    ],
                    'totalAmount' => 500000,
                    'status' => 'PENDING',
                    'createdAt' => now()->toIso8601String()
                ],
                [
                    'id' => 'ORD-005',
                    'customerId' => 'CUST-INTAN-05',
                    'items' => [
                        ['itemId' => 'ITEM-004', 'name' => 'Kamus Bahasa Inggris', 'quantity' => 1, 'price' => 150000],
                        ['itemId' => 'ITEM-007', 'name' => 'Novel Matahari - Tere Liye', 'quantity' => 1, 'price' => 120000]
                    ],
                    'totalAmount' => 270000,
                    'status' => 'PENDING',
                    'createdAt' => now()->toIso8601String()
                ]
            ]
        ];
        self::saveState($state);
    }

    // --- Order Methods ---

    public static function getOrders()
    {
        $state = self::getState();
        return $state['orders'];
    }

    public static function getOrderById($id)
    {
        $orders = self::getOrders();
        foreach ($orders as $order) {
            if ($order['id'] === $id) {
                return $order;
            }
        }
        return null;
    }

    /**
     * Memproses order ke transaksi setelah mengecek stok ke Inventory Service (Dhika) via HTTP API.
     */
    public static function processOrderToTransaction($orderId)
    {
        $state = self::getState();
        $foundIndex = -1;

        foreach ($state['orders'] as $index => $order) {
            if ($order['id'] === $orderId) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex === -1) {
            throw new Exception("Order dengan ID {$orderId} tidak ditemukan.");
        }

        $order = &$state['orders'][$foundIndex];

        if ($order['status'] !== 'PENDING') {
            throw new Exception("Order dengan ID {$orderId} sudah diproses sebelumnya (Status saat ini: {$order['status']}).");
        }

        // Ambil konfigurasi service luar
        $mockEnabled = config('services.inventory.mock', true);
        $inventoryUrl = config('services.inventory.url', 'http://localhost:3001');
        $inventoryKey = config('services.inventory.key', 'dhika-nim-gudang-2026');

        // Cek ketersediaan stok untuk semua item di order
        foreach ($order['items'] as $orderItem) {
            $stock = 0;
            $itemName = $orderItem['name'];

            if ($mockEnabled) {
                $mockInventories = [
                    'ITEM-001' => 100,
                    'ITEM-002' => 50,
                    'ITEM-003' => 10,
                    'ITEM-004' => 10,
                    'ITEM-005' => 50,
                    'ITEM-006' => 5,
                    'ITEM-007' => 15,
                ];

                $stock = isset($mockInventories[$orderItem['itemId']]) ? $mockInventories[$orderItem['itemId']] : 0;
            } else {
                // KOMUNIKASI HTTP API RIIL (Sesuai Ketentuan Docker Antar-layanan)
                $url = "{$inventoryUrl}/api/v1/inventories/{$orderItem['itemId']}";
                
                try {
                    // Lakukan pemanggilan HTTP GET ke Inventory Service milik Dhika
                    $response = Http::withHeaders([
                        'X-IAE-KEY' => $inventoryKey
                    ])->timeout(3)->get($url);

                    if ($response->failed()) {
                        throw new Exception("Inventory Service mengembalikan status kode error " . $response->status());
                    }

                    $body = $response->json();
                    
                    // Baca field stock dari kontrak respon Dhika
                    if (isset($body['data']['stock'])) {
                        $stock = $body['data']['stock'];
                    } elseif (isset($body['stock'])) {
                        $stock = $body['stock'];
                    } else {
                        throw new Exception("Format respon Inventory Service tidak dikenal.");
                    }

                } catch (Exception $e) {
                    throw new Exception("Gagal menghubungkan ke Inventory Service milik Dhika di {$url}. Detail galat: " . $e->getMessage() . ". Aktifkan kontainer Dhika atau ubah MOCK_EXTERNAL_SERVICES=true di .env untuk simulasi pengujian lokal.");
                }
            }

            // Validasi apakah stok mencukupi
            if ($stock < $orderItem['quantity']) {
                // Gagal, stok tidak mencukupi
                $order['status'] = 'FAILED_STOCK_UNAVAILABLE';
                self::saveState($state);
                throw new Exception("Stok barang '{$itemName}' tidak mencukupi. Tersedia: {$stock}, Dibutuhkan: {$orderItem['quantity']}. Transaksi order dibatalkan.");
            }
        }

        // Potong stok gudang via HTTP POST jika ini adalah koneksi riil ke Dhika (opsional tergantung API Dhika)
        // Dalam pengerjaan ini, kita mengasumsikan transaksi berhasil memotong stok dan mengubah status ke TRANSACTION
        $order['status'] = 'TRANSACTION';
        self::saveState($state);
        return $order;
    }
}
