<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class OrderItemController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $dataOrderItem = OrderItem::with(['order.customer', 'product'])->get();
            return response()->json([
                'message' => 'Data order item berhasil diambil.',
                'data' => $dataOrderItem
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data order item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $orderItem = OrderItem::with(['order.customer', 'product'])->findOrFail($id);
            return response()->json([
                'message' => 'Data order item berhasil ditemukan.',
                'data' => $orderItem
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order Item tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data order item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',      // DIPERBAIKI: dari 'order' ke 'orders'
                'product_id' => 'required|exists:products,id',  // DIPERBAIKI: dari 'product' ke 'products'
                'quantity' => 'required|integer|min:1',
                'price' => 'required|integer|min:0',
            ]);

            $orderItem = OrderItem::create([
                'order_id' => $request->order_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $request->price,
            ]);

            return response()->json([
                'message' => 'Order Item berhasil ditambahkan.',
                'data' => $orderItem->load(['order.customer', 'product'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan order item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $orderItem = OrderItem::findOrFail($id);

            $request->validate([
                'order_id' => 'sometimes|exists:orders,id',     // DIPERBAIKI: dari 'order' ke 'orders'
                'product_id' => 'sometimes|exists:products,id', // DIPERBAIKI: dari 'product' ke 'products'
                'quantity' => 'sometimes|integer|min:1',
                'price' => 'sometimes|integer|min:0',
            ]);

            $data = $request->only(['order_id', 'product_id', 'quantity', 'price']);
            $orderItem->update($data);

            return response()->json([
                'message' => $orderItem->wasChanged()
                    ? 'Order Item berhasil diupdate.'
                    : 'Tidak ada perubahan pada data order item.',
                'data' => $orderItem->load(['order.customer', 'product'])
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order Item tidak ditemukan.'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate order item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $orderItem = OrderItem::findOrFail($id);
            $orderItem->delete();

            return response()->json([
                'message' => 'Order Item berhasil dihapus.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order Item tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus order item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
