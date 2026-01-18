<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Movie::with('categories');

        // Filter release year
        if ($request->filled('release_year')) {
            $query->where('release_year', $request->release_year);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('movie_name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('category.category_id', $request->category_id);
            });
        }

        // Sorting
        match ($request->get('sort_by')) {
            'rating' => $query->orderBy('rating', 'desc'),
            'newest' => $query->orderBy('release_year', 'desc'),
            default  => $query->orderBy('movie_id', 'asc'),
        };

        // Without pagination
        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Daftar semua film',
                'data' => $query->get(),
            ]);
        }

        $movies = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar film',
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page'    => $movies->lastPage(),
                'per_page'     => $movies->perPage(),
                'total'        => $movies->total(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'movie_name'     => 'required|string|max:255',
            'release_year'   => 'required|integer|min:1900|max:2030',
            'rating'         => 'required|numeric|min:0|max:10',
            'description'    => 'nullable|string',
            'poster_url'     => 'nullable|url',
            'background_url' => 'nullable|url',
            'category_ids'   => 'required|array',
            'category_ids.*' => 'exists:category,category_id'
        ]);

        $movie = Movie::create($validated);

        $movie->categories()->attach($request->category_ids);

        return response()->json([
            'success' => true,
            'message' => 'Film berhasil ditambahkan',
            'data' => $movie->load('categories'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $movie = Movie::with('categories')->find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Film tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail film',
            'data' => $movie
        ]);
    }

    public function update(Request $request, Movie $movie): JsonResponse
    {
        $validated = $request->validate([
            'movie_name'     => 'sometimes|string|max:255',
            'release_year'   => 'sometimes|integer|min:1900|max:2030',
            'rating'         => 'sometimes|numeric|min:0|max:10',
            'description'    => 'nullable|string',
            'poster_url'     => 'nullable|url',
            'background_url' => 'nullable|url',
            'category_ids'   => 'sometimes|array',
            'category_ids.*' => 'exists:category,category_id'
        ]);

        $movie->update($validated);

        if ($request->has('category_ids')) {
            $movie->categories()->sync($request->category_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'Film berhasil diupdate',
            'data' => $movie->load('categories')
        ]);
    }

    public function destroy(Movie $movie): JsonResponse
    {
        $title = $movie->movie_name;
        $movie->delete();

        return response()->json([
            'success' => true,
            'message' => "Film '{$title}' berhasil dihapus",
        ]);
    }
}
