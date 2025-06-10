<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $dataCategory = Category::all();
        return response()->json($dataCategory, 200);
    }

    public function show($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            return response()->json($category, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category tidak ditemukan'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Category berhasil ditambahkan.',
            'data' => $category
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
            ]);

            $data = $request->only(['name', 'description']);
            $category->update($data);

            return response()->json([
                'message' => $category->wasChanged()
                    ? 'Category berhasil diupdate.'
                    : 'Tidak ada perubahan pada data category.',
                'data' => $category
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category tidak ditemukan'], 404);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json(['message' => 'Category berhasil dihapus.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category tidak ditemukan.'], 404);
        }
    }
}
