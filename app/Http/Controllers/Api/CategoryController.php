<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('movies');

        if ($request->boolean('all')) {
            $category = $query->get();
            return response()->json([
                'success' => true,
                'message' => 'Daftar semua kategori',
                'data' => $category
            ]);
        }

        $category = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori',
            'data' => $category->items(),
            'meta' => [
                'current_page' => $category->currentPage(),
                'last_page' => $category->lastPage(),
                'per_page' => $category->perPage(),
                'total' => $category->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:movie_category,category_name'
        ], 
        [
            'category_name.required' => 'Nama Kategori harus diisi',
        ]);
        $category = Category::create($validated);
        return response()->json([
            'success'=> true,
            'message'=> 'Kategori berhasil ditambahkan',
            'data'=> $category,
        ],201);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::with('movies')->find($id);
        
        if(!$category) {
            return response()->json([
                'success'=> false,
                'message'=> 'kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
                'success'=> true,
                'message'=> 'Kategori berhasil ditemukan',
                'data' => $category
            ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|required|string|max:100|unique:movie_category,category_name',
        ], 
        [
            'category_name.required' => 'Nama Kategori harus diisi',
        ]);

        $category = $category->update($validated);

        return response()->json([
            'success'=> true,
            'message'=> 'Kategori berhasil diupdate',
            'data'=> $category,
        ],201);
    }

    public function destroy(Category $category): JsonResponse
    {
        $categoryName = $category->category_name;

        $category->delete();

        return response()->json([
            'success'=> true,
            'message'=> "Film '{$categoryName}' berhasil dihapus",
            'data'=> $category,
        ]);
    }

}