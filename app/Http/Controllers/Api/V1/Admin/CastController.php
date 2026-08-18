<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CastController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Eager load castable to know which movie/tv show this cast belongs to
        $query = Cast::with('castable');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%')
                  ->orWhere('character', 'like', '%'.$request->input('search').'%');
        }

        $allowedSorts = ['name', 'character', 'id', 'created_at'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);

        $perPage = $request->input('per_page', 20);

        return response()->json($query->paginate($perPage)->toArray());
    }

    public function destroy(Cast $cast): JsonResponse
    {
        $cast->delete();
        return response()->json(null, 204);
    }
}
