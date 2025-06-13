<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $dataOrder = Order::with(['customer', 'orderItems.product'])->get();
        return response()->json($dataOrder, 200);
    }

    public function show($id): JsonResponse
    {
        try {
            $order = Order::with(['customer', 'orderItems.product'])->findOrFail($id);
            return response()->json($order, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id', // Fixed table name
            'order_date' => 'required|date',
            'total_amount' => 'required|integer|min:0',
            'status' => 'sometimes|string|in:pending,processing,completed,cancelled',
        ]);

        $order = Order::create([
            'customer_id' => $request->customer_id,
            'order_date' => $request->order_date,
            'total_amount' => $request->total_amount,
            'status' => $request->status ?? 'pending',
        ]);

        return response()->json([
            'message' => 'Order berhasil ditambahkan.',
            'data' => $order->load(['customer', 'orderItems.product'])
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            $request->validate([
                'customer_id' => 'sometimes|exists:customers,id', // Fixed table name
                'order_date' => 'sometimes|date',
                'total_amount' => 'sometimes|integer|min:0',
                'status' => 'sometimes|string|in:pending,processing,completed,cancelled',
            ]);

            $data = $request->only(['customer_id', 'order_date', 'total_amount', 'status']);
            $order->update($data);

            return response()->json([
                'message' => $order->wasChanged()
                    ? 'Order berhasil diupdate.'
                    : 'Tidak ada perubahan pada data order.',
                'data' => $order->load(['customer', 'orderItems.product'])
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return response()->json(['message' => 'Order berhasil dihapus.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }
    }
}
