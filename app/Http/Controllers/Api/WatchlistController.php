<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class WatchlistController extends Controller
{
    private array $allowedStatuses = ['watched', 'watching', 'planned'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Watchlist::with('movie')
            ->where('user_id', $user->user_id);

        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Daftar semua watchlist',
                'data' => $query->get(),
            ]);
        }

        $watchlists = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar watchlist',
            'data' => $watchlists->items(),
            'meta' => [
                'current_page' => $watchlists->currentPage(),
                'last_page'    => $watchlists->lastPage(),
                'per_page'     => $watchlists->perPage(),
                'total'        => $watchlists->total(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'movie_id' => [
                'required',
                'integer',
                'exists:movies,movie_id',
                Rule::unique('watchlist')->where(fn ($q) =>
                    $q->where('user_id', $user->user_id)
                ),
            ],
            'status' => [
                'required',
                Rule::in($this->allowedStatuses),
            ],
        ]);

        $validated['user_id'] = $user->user_id;

        $watchlist = Watchlist::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Watchlist berhasil ditambahkan',
            'data' => $watchlist->load('movie'),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $user = request()->user();

        $watchlist = Watchlist::with('movie')
            ->where('user_id', $user->user_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail watchlist',
            'data' => $watchlist,
        ]);
    }

    public function update(Request $request, Watchlist $watchlist): JsonResponse
    {
        $user = $request->user();

        if ($watchlist->user_id !== $user->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke data ini',
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in($this->allowedStatuses),
            ],
        ]);

        $watchlist->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status watchlist berhasil diperbarui',
            'data' => $watchlist->load('movie'),
        ]);
    }

    public function destroy(Watchlist $watchlist): JsonResponse
    {
        $user = request()->user();

        if ($watchlist->user_id !== $user->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke data ini',
            ], 403);
        }

        $watchlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Watchlist berhasil dihapus',
        ]);
    }
}