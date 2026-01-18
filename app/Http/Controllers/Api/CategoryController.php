<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * GET: /categories
     * ?all=true → ambil semua
     * default → pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('movies');

        // Jika ingin semua tanpa pagination
        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Daftar semua kategori',
                'data' => $query->get()
            ]);
        }

        $perPage = $request->get('per_page', 15);
        $categories = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori',
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ]
        ]);
    }

    /**
     * POST: /categories
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:category,category_name',
        ], [
            'category_name.required' => 'Nama kategori harus diisi',
            'category_name.unique'   => 'Nama kategori sudah ada',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data'    => $category,
        ], 201);
    }

    /**
     * GET: /categories/{id}
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::with('movies')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kategori',
            'data'    => $category,
        ]);
    }

    /**
     * PUT/PATCH: /categories/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:category,category_name,' . $id . ',category_id',
        ], [
            'category_name.required' => 'Nama kategori harus diisi',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
            'data'    => $category,
        ]);
    }

    /**
     * DELETE: /categories/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        $name = $category->category_name;
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => "Kategori '{$name}' berhasil dihapus",
        ]);
    }
}