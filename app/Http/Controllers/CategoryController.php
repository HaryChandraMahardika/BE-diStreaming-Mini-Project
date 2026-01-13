<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/v1/categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        //Search by name
        if ($request->filled('search')){
            $query ->where('name', 'like', '%', $request->search .'%');
        }

        //Include posts count
        $query->withCount('posts');

        //Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order','asc');
        $query->orderBy($sortBy, $sortOrder);

        //Pagination atau all
        if ($request->boolean('all')) {
            $categories = $query->get();
            return response()->json([
                'success' => true,
                'message' => 'Daftar semua kategori',
                'data' => $categories
            ]);
        }

        $categories = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori',
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/v1/categories
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
        'name'=> 'required|string|min:2|max:100|unique:categories,nama',
        'slug'=> 'nullable|string|unique:categories,slug',
        'description' => 'nullable|string|max:500',
        ],
        [
            'name.required'=> 'Nama kategori wajib diisi',
            'name.min'=> 'Nama kategori minimal 2 karakter',
            'name.unique'=> 'Nama kategori sudah digunakan',
        ]);

        //Auto-generate slug jika tidak diisi
        if (empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = Category::create($validated); 

        return response()->json([
            'success'=> true,
            'message'=> 'Kategori berhasil ditambahkan',
            'data'=> $category
        ], 201);
    }

    /**
     * Display the specified category.
     * GET /api/v1/categories/{id}
     */
    public function show(Category $category): JsonResponse
    {
        //Load post count
        $category->loadCount('posts');

        return response()->json([
            'success'=> true,
            'message'=> 'Detail kategori',
            'data'=> $category
        ]);
    }

    /**
     * Update the spesified category.
     * PUT/PATCH /api/v1/categories/{id}
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name'=> 'sometimes|required|string|min:2|max:100|unique:categories,nama' . $category->id,
            'slug'=> 'sometimes|nullable|string|unique:categories,slug'. $category->id,
            'description' => 'nullable|string|max:500',
        ],
        [
            'name.required'=> 'Nama kategori wajib diisi',
            'name.min'=> 'Nama kategori minimal 2 karakter',
            'name.unique'=> 'Nama kategori sudah digunakan',
        ]);

        //Update slug jika name berubah dan slug tidak diisi
        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

         return response()->json([
            'success'=> true,
            'message'=> 'Kategori berhasil diperbaharui',
            'data'=> $category->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/v1/categories/{id}
     */
    public function destroy(Category $category): JsonResponse
    {
        // Cek apakah kategori memiliki posts
        if($category->post()->count() > 0) {
           return response()->json([
            'success'=> false,
            'message'=> 'Kategori tidak dapat dihapus karena masih memiliki posts',
        ], 422); 
        }

        $categoryName = $category->name;
        $category->delete();

        return response()->json([
            'success'=> true,
            'message'=> "Kategori '{$categoryName}' berhasil dihapus",
        ]); 
    }

    /**
     * Get post by category.
     * GET /api/v1/categories/{id}/posts
     */
    public function posts(Category $category, Request $request): JsonResponse
    {
        $query = $category->posts();
        // Filter by status
        if($request->filled('status')) {
           $query->where('status', $request->status);
        }

        //Default: hanya published
        if(!$request->has('status')) {
           $query->published();
        }

        //Sorting
        $query = orderBy('published_at', 'desc');

        $posts = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => "Daftar posts dalam kategori '{$category->name}'",
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }

}
