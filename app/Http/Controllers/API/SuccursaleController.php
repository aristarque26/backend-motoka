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
        try {
            $user = $request->user();
            $query = Succursale::with('manager');

            if ($user->role_enum !== 'superAdmin') {
                $query->where('Idagence', $user->Idagence);
            }

            return response()->json([
                'success' => true,
                'data' => $query->get()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
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

        // --- VÉRIFICATION DES LIMITES DU PLAN (DYNAMIQUE) ---
        $agence = \App\Models\Agence::findOrFail($agenceId);
        $currentCount = Succursale::where('Idagence', $agenceId)->count();
        
        $plan = $agence->plan_enum ?: 'starter';
        
        // Chercher les limites dans la table abonnements via le slug
        $abonnement = \App\Models\Abonnement::where('slug', $plan)->first();
        $max = $abonnement ? $abonnement->max_succursales : 3;

        if ($currentCount >= $max) {
            return response()->json([
                'message' => "Limite de succursales atteinte pour votre plan ({$plan}). Maximum autorisé : {$max}."
            ], 403);
        }
        // ----------------------------------------------------

        $succursale = Succursale::create(array_merge(
            $validator->validated(),
            ['Idagence' => $agenceId]
        ));

        // Si un manager est assigné, on met à jour son profil
        if ($request->Idmanager) {
            $manager = \App\Models\User::find($request->Idmanager);
            if ($manager && $manager->Idagence === $agenceId) {
                $manager->update([
                    'Idsuccursale' => $succursale->Idsuccursale,
                    'role_enum' => 'adminSuccursale'
                ]);
            }
        }

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

        $oldManagerId = $succursale->Idmanager;
        $succursale->update($validator->validated());

        // Si le manager a changé
        if ($request->has('Idmanager') && $request->Idmanager != $oldManagerId) {
            // Ancien manager : on peut éventuellement réinitialiser son rôle ou le laisser tel quel
            // Nouveau manager
            if ($request->Idmanager) {
                $newManager = \App\Models\User::find($request->Idmanager);
                if ($newManager && $newManager->Idagence === $succursale->Idagence) {
                    $newManager->update([
                        'Idsuccursale' => $succursale->Idsuccursale,
                        'role_enum' => 'adminSuccursale'
                    ]);
                }
            }
        }

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
