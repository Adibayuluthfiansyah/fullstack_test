<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $dataCustomer = Customer::all();
            return response()->json([
                'message' => 'Data customer berhasil diambil.',
                'data' => $dataCustomer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data customer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            return response()->json([
                'message' => 'Data customer berhasil ditemukan.',
                'data' => $customer
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data customer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50',
                'email' => 'required|email|max:50|unique:customers,email',
                'password' => 'required|string|min:6|max:50',
                'phone' => 'required|string|max:15',
                'address' => 'required|string|max:255',
            ]);

            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json([
                'message' => 'Customer berhasil ditambahkan.',
                'data' => $customer
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan customer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:50',
                'email' => 'sometimes|email|max:50|unique:customers,email,' . $id,
                'password' => 'sometimes|string|min:6|max:50',
                'phone' => 'sometimes|string|max:15',
                'address' => 'sometimes|string|max:255',
            ]);

            $data = $request->only(['name', 'email', 'phone', 'address']);

            if ($request->has('password') && !empty($request->password)) {
                $data['password'] = bcrypt($request->password);
            }

            $customer->update($data);

            return response()->json([
                'message' => $customer->wasChanged()
                    ? 'Customer berhasil diupdate.'
                    : 'Tidak ada perubahan pada data customer.',
                'data' => $customer->fresh() // Refresh data dari database
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer tidak ditemukan.'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate customer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);

            // Cek apakah customer memiliki orders
            if ($customer->orders()->exists()) {
                return response()->json([
                    'message' => 'Customer tidak dapat dihapus karena memiliki data order.'
                ], 422);
            }

            $customer->delete();

            return response()->json([
                'message' => 'Customer berhasil dihapus.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus customer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
