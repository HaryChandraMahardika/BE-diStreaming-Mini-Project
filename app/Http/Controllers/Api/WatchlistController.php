<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;


class WatchlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Watchlist::with(['users', 'movies'])
        ->where('user_id', auth()->id());

        if ($request->boolean('all')) {
            $watchlist = $query->get();
            return response()->json([
                'success' => true,
                'message' => 'Daftar semua watchlist',
                'data' => $watchlist
            ]);
        }

        $watchlist = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar watchlist',
            'data' => $watchlist->items(),
            'meta' => [
                'current_page' => $watchlist->currentPage(),
                'last_page' => $watchlist->lastPage(),
                'per_page' => $watchlist->perPage(),
                'total' => $watchlist->total(),
            ]
        ]);
    }

   public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'user_id' => 'required|integer',
        'movie_id' => 'required|integer',
        'added_date' => 'required|date',
        'status' => [
            'required',
            Rule::in(['AlreadyWatched', 'CurrentlyWatching', 'NotWatchedYet'])
        ],
    ]);

    $validated['user_id'] = auth()->id();
    $watchlist = Watchlist::create($validated);

    return response()->json([
        'success'=> true,
        'message'=> 'Data watchlist berhasil ditambahkan',
        'data'=> $watchlist,
    ], 201);
}

    public function show($id)
    { 

        $watchlist = Watchlist::with(['users', 'movies'])->findOrFail($id);

        if(! $watchlist) {
            return response()->json([
                'success'=> false,
                'message'=> 'Data watchlist tidak berhasil ditemukan',
            ], 404);
        }

        return response()->json([
                'success'=> true,
                'message'=> 'Data watchlist berhasil ditemukan',
                'data' => $watchlist
            ]);
    }

    public function update(Request $request, Watchlist $watchlist)
{
    $validated = $request->validate([
        'user_id'    => 'sometimes|required|integer|exists:users,user_id',
        'movie_id'   => 'sometimes|required|integer|exists:movies,movie_id',
        'added_date' => 'sometimes|required|date',
        'status'     => [
            'required',
            Rule::in(['AlreadyWatched', 'CurrentlyWatching', 'NotWatchedYet'])
        ],
    ]);

    // update data
    $watchlist->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Data watchlist berhasil diupdate',
        'data'    => $watchlist, // model terbaru setelah update
    ], 200);
}

    

    public function destroy(Watchlist $watchlist): JsonResponse
    {
        $watchlistId = $watchlist->watchlist_id;

        $watchlist->delete();
        return response()->json([
            'success'=> true,
            'message'=> "Film '{$watchlistId}' berhasil dihapus",
            'data'=> $watchlist,
        ]);
    }
}
