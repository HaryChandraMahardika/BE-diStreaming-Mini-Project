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

        //Search by Judul Film
        if ($request->filled('search')){
            $query ->where('movie_name', 'like', '%' . $request->search .'%');
        }

        //Filter by id
        if ($request->filled('movie_category_id')){
            $query ->where('movie_category_id', $request->movie_category_id);
        }

        //Sorting
        if ($request->get('sort_by') === 'rating') {
        // Jsort_by=rating, DESC
        $query->orderBy('rating', 'desc');
        } else {
        // Default: urut berdasarkan movie_id ASC
        $query->orderBy('movie_id', 'asc');
        }


        if ($request->boolean('all')) {
            $movie = $query->get();
            return response()->json([
                'success' => true,
                'message' => 'Daftar semua film',
                'data' => $movie
            ], 200);
        }

        $movie = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar film',
            'data' => $movie->items(),
            'meta' => [
                'current_page' => $movie->currentPage(),
                'last_page' => $movie->lastPage(),
                'per_page' => $movie->perPage(),
                'total' => $movie->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_name' => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:2030',
            'rating' => 'required|numeric|min:0|max:10',
            'movie_category_id' => 'required|integer|exists:movie_category,movie_category_id'
        ], 
        [
            'movie_name.required' => 'Nama film harus diisi',
            'release_year.required' => 'Tahun rilis harus diisi',
            'release_year.min' => 'Tahun rilis minimal tahun 1900',
            'release_year.max' => 'Tahun rilis minimal tahun 2030',
            'rating.required' => 'Rating film harus diisi',
            'rating.min' => 'Rating minimal 0',
            'rating.max' => 'Rating maksimal 10',
            'movie_category_id.required' => 'Kategori id harus diisi',
        ]);

        $movie = Movie::create($validated);
        return response()->json([
            'success'=> true,
            'message'=> 'Film berhasil ditambahkan',
            'data'=> $movie,
        ],201);
    }

    public function show(string $id): JsonResponse
    {
        $movie = Movie::with(['categories'])->find($id);

        if(! $movie) {
            return response()->json([
                'success'=> false,
                'message'=> 'Film tidak ditemukan',
            ], 404);
        }

        return response()->json([
                'success'=> true,
                'message'=> 'Film berhasil ditemukan',
                'data' => $movie
            ]);
    }

    public function update(Request $request, Movie $movie): JsonResponse
    {
        $validated = $request->validate([
            'movie_name' => 'sometimes|required|string|max:255',
            'release_year' => 'sometimes|required|integer|min:1900|max:2030',
            'rating' => 'sometimes|required|numeric|min:0|max:10',
            'movie_category_id' => 'sometimes|required|integer|exists:movie_category,movie_category_id'
        ], 
        [
            'movie_name.required' => 'Nama film harus diisi',
            'release_year.required' => 'Tahun rilis harus diisi',
            'release_year.min' => 'Tahun rilis minimal tahun 1900',
            'release_year.max' => 'Tahun rilis minimal tahun 2030',
            'rating.required' => 'Rating film harus diisi',
            'rating.min' => 'Rating minimal 0',
            'rating.max' => 'Rating maksimal 10',
            'movie_category_id.required' => 'Kategori id harus diisi',
        ]);

        $movie->update($validated);

        return response()->json([
            'success'=> true,
            'message'=> 'Film berhasil diupdate',
            'data'=> $movie
        ],200);

    }

    public function destroy(Movie $movie): JsonResponse
    {
        $movieName = $movie->movie_name;
        
        $movie->delete();
        return response()->json([
            'success'=> true,
            'message'=> "Film '{$movieName}' berhasil dihapus",
            'data'=> $movie,
        ], 200);
    }

}
