<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DatabaseService;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "REST & GraphQL API untuk Order Service (Intan) berbasis Laravel 11. Seluruh API diamankan dengan API Key via Request Header X-IAE-KEY.",
    title: "Order Service API"
)]
#[OA\Server(
    url: "http://localhost:3000",
    description: "Server Lokal (Development)"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    name: "X-IAE-KEY",
    in: "header",
    description: "Kunci otentikasi API Key berupa NIM Anda dikirim lewat header X-IAE-KEY (Contoh: 102022400005)."
)]
class OrderController extends Controller
{
    #[OA\Get(
        path: "/api/v1/orders",
        summary: "Melihat daftar semua order (Intan)",
        security: [["ApiKeyAuth" => []]],
        tags: ["Order Service"]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil mendapatkan daftar order."
    )]
    #[OA\Response(response: 401, description: "Unauthorized. API Key tidak ditemukan.")]
    #[OA\Response(response: 403, description: "Forbidden. API Key tidak valid.")]
    public function index()
    {
        try {
            $orders = DatabaseService::getOrders();
            return response()->json([
                'status' => 'success',
                'message' => 'Operation successful',
                'data' => $orders,
                'meta' => [
                    'service_name' => 'Order-Service',
                    'api_version' => 'v1'
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => null
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/v1/orders/{id}",
        summary: "Validasi / Detail order berdasarkan ID (Intan)",
        security: [["ApiKeyAuth" => []]],
        tags: ["Order Service"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID Order yang dicari",
        schema: new OA\Schema(type: "string", example: "ORD-001")
    )]
    #[OA\Response(
        response: 200,
        description: "Detail order ditemukan."
    )]
    #[OA\Response(response: 401, description: "Unauthorized.")]
    #[OA\Response(response: 403, description: "Forbidden.")]
    #[OA\Response(response: 404, description: "Order tidak ditemukan.")]
    public function show($id)
    {
        try {
            $order = DatabaseService::getOrderById($id);
            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Order dengan ID {$id} tidak ditemukan.",
                    'errors' => null
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Operation successful',
                'data' => $order,
                'meta' => [
                    'service_name' => 'Order-Service',
                    'api_version' => 'v1'
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => null
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/v1/orders",
        summary: "Memproses order ke tahap transaksi setelah stok dipastikan tersedia (Intan)",
        security: [["ApiKeyAuth" => []]],
        tags: ["Order Service"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["orderId"],
            properties: [
                new OA\Property(property: "orderId", type: "string", example: "ORD-001")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Order berhasil diproses ke transaksi."
    )]
    #[OA\Response(response: 400, description: "Bad Request. Stok tidak cukup atau status tidak sesuai.")]
    #[OA\Response(response: 401, description: "Unauthorized.")]
    #[OA\Response(response: 403, description: "Forbidden.")]
    #[OA\Response(response: 404, description: "Order tidak ditemukan.")]
    public function store(Request $request)
    {
        $orderId = $request->input('orderId');

        if (!$orderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'orderId wajib dikirimkan dalam request body.',
                'errors' => null
            ], 400);
        }

        try {
            $updatedOrder = DatabaseService::processOrderToTransaction($orderId);
            return response()->json([
                'status' => 'success',
                'message' => "Operation successful. Order {$orderId} berhasil dilanjutkan ke tahap transaksi. Stok barang berhasil dikurangi.",
                'data' => $updatedOrder,
                'meta' => [
                    'service_name' => 'Order-Service',
                    'api_version' => 'v1'
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => null
            ], 400);
        }
    }
}
