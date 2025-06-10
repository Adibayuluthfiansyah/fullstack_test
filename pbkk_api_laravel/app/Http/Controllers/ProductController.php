<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $dataProduct = Product::with('category')->get();
        return response()->json($dataProduct, 200);
    }

    public function show($id): JsonResponse
    {
        try {
            $product = Product::with('category')->findOrFail($id);
            return response()->json($product, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product tidak ditemukan'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id', // Fixed table name
        ]);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Product berhasil ditambahkan.',
            'data' => $product->load('category')
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|integer|min:0',
                'stock' => 'sometimes|integer|min:0',
                'category_id' => 'sometimes|exists:categories,id', // Fixed table name
            ]);

            $data = $request->only(['name', 'description', 'price', 'stock', 'category_id']);
            $product->update($data);

            return response()->json([
                'message' => $product->wasChanged()
                    ? 'Product berhasil diupdate.'
                    : 'Tidak ada perubahan pada data product.',
                'data' => $product->load('category')
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product tidak ditemukan'], 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json(['message' => 'Product berhasil dihapus.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product tidak ditemukan.'], 404);
        }
    }
}
