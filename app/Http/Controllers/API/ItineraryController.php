<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ItineraryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Itinerary::query();

            if ($user->role_enum !== 'superAdmin') {
                $query->where('Idagence', $user->Idagence);
            }

            $itineraries = $query->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $itineraries
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'adresse_depart' => 'required|string',
                'latitude_depart' => 'nullable|numeric',
                'longitude_depart' => 'nullable|numeric',
                'adresse_arrivee' => 'required|string',
                'latitude_arrivee' => 'nullable|numeric',
                'longitude_arrivee' => 'nullable|numeric',
                'distance_km_estimee' => 'nullable|numeric',
                'prix_estime' => 'nullable|numeric',
                'Idsuccursale' => 'nullable|exists:succursales,Idsuccursale',
                'Idagence' => $user->role_enum === 'superAdmin' ? 'required|exists:agences,Idagence' : 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            if ($user->role_enum !== 'superAdmin') {
                $data['Idagence'] = $user->Idagence;
            }

            $itinerary = Itinerary::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Itinéraire créé avec succès',
                'data' => $itinerary
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            $itinerary = Itinerary::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $itinerary->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $itinerary
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Itinéraire non trouvé'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $itinerary = Itinerary::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $itinerary->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'adresse_depart' => 'sometimes|string',
                'adresse_arrivee' => 'sometimes|string',
                'prix_estime' => 'sometimes|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $itinerary->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Itinéraire mis à jour',
                'data' => $itinerary
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $itinerary = Itinerary::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $itinerary->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $itinerary->delete();

            return response()->json([
                'success' => true,
                'message' => 'Itinéraire supprimé'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
