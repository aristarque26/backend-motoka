<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Succursale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuccursaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Succursale::with('manager');

        if ($user->role_enum !== 'superAdmin') {
            $query->where('Idagence', $user->Idagence);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role_enum, ['superAdmin', 'adminAgence'])) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'ville' => 'required|string|max:100',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:50',
            'Idmanager' => 'nullable|exists:users,id',
            'Idagence' => $user->role_enum === 'superAdmin' ? 'required|exists:agences,Idagence' : 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agenceId = $user->role_enum === 'superAdmin' ? $request->Idagence : $user->Idagence;

        $succursale = Succursale::create(array_merge(
            $validator->validated(),
            ['Idagence' => $agenceId]
        ));

        return response()->json($succursale, 201);
    }

    public function show(Succursale $succursale)
    {
        $user = auth()->user();
        if ($user->role_enum !== 'superAdmin' && $succursale->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($succursale->load('manager', 'vehicules', 'users'));
    }

    public function update(Request $request, Succursale $succursale)
    {
        $user = $request->user();
        if ($user->role_enum !== 'superAdmin' && $succursale->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:100',
            'ville' => 'sometimes|string|max:100',
            'adresse' => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string|max:50',
            'Idmanager' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $succursale->update($validator->validated());

        return response()->json($succursale);
    }

    public function destroy(Succursale $succursale)
    {
        $user = auth()->user();
        if ($user->role_enum !== 'superAdmin' && $succursale->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $succursale->delete();

        return response()->json(['message' => 'Succursale supprimée']);
    }
}
