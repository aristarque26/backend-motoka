<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Colis;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ColisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Liste des colis filtrés par agence
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Colis::query()->with(['client', 'courses']);

            // Filtrage par agence
            if ($user->role_enum !== 'superAdmin') {
                $query->where('Idagence', $user->Idagence);

                // Restriction par succursale si applicable
                if ($user->Idsuccursale) {
                    $query->whereHas('courses', function($q) use ($user) {
                        $q->where('Idsuccursale', $user->Idsuccursale);
                    });
                }
            }

            // Filtres
            if ($request->has('statut')) {
                $query->where('statut_enum', $request->statut);
            }

            if ($request->has('code')) {
                $query->where('CodeColis', 'LIKE', '%' . $request->code . '%');
            }

            $colis = $query->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $colis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistrement d'un nouveau colis
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'nomExpediteur' => 'required|string|max:50',
                'TelephoneExpedit' => 'required|string|max:50',
                'nomDestinateur' => 'required|string|max:50',
                'Description' => 'nullable|string|max:255',
                'Poids' => 'required|numeric|min:0.1',
                'prix' => 'required|numeric|min:0',
                'devise' => 'nullable|string|max:5',
                'methode_calcul_prix' => 'nullable|in:poids,manuel',
                'Idclient' => 'nullable|exists:clients,Idclient',
                'Idcource' => 'nullable|exists:courses,Idcource',
                'statut_enum' => 'nullable|in:enregistre,en_transit,livre,recupere',
                'Idagence' => $user->role_enum === 'superAdmin' ? 'required|exists:agences,Idagence' : 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Génération d'un code colis unique et d'un OTP
            $codeColis = 'MOT-' . strtoupper(Str::random(8));
            $otp = rand(1000, 9999);

            $agenceId = ($user->role_enum === 'superAdmin') ? $request->Idagence : $user->Idagence;
            $clientId = $request->Idclient;
            if (!$clientId) {
                $client = Client::firstOrCreate(
                    [
                        'telephoneClient' => $request->TelephoneExpedit,
                        'Idagence' => $agenceId,
                    ],
                    [
                        'nomClient' => $request->nomExpediteur,
                        'emailClient' => null,
                        'DateInscription' => now(),
                        'typeClient_ENUM' => 'particulier',
                        'Idutilisateur' => $user->id,
                    ]
                );
                $clientId = $client->Idclient;
            }

            $data = [
                'nomExpediteur' => $request->nomExpediteur,
                'TelephoneExpedit' => $request->TelephoneExpedit,
                'nomDestinateur' => $request->nomDestinateur,
                'CodeColis' => $codeColis,
                'Otp_valide' => $otp,
                'Otp_genere' => now(),
                'statut_enum' => $request->statut_enum ?? 'enregistre',
                'Description' => $request->Description,
                'Poids' => $request->Poids,
                'prix' => $request->prix,
                'devise' => $request->devise ?? 'CDF',
                'methode_calcul_prix' => $request->methode_calcul_prix ?? 'manuel',
                'Idclient' => $clientId,
                'Idagence' => $agenceId
            ];

            $colis = Colis::create($data);

            // Création d'une transaction financière pour le colis
            \App\Models\TransactionFinance::create([
                'montant' => $request->prix,
                'devise' => $request->devise ?? 'CDF',
                'mode_paiement_Enum' => $request->mode_paiement ?? 'especes',
                'Type_Transaction_Enum' => 'colis',
                'description' => "Enregistrement colis " . $codeColis,
                'Date_Paiement' => now(),
                'Idagence' => $data['Idagence']
            ]);

            // Association directe avec une course
            if ($request->has('Idcource')) {
                $colis->courses()->attach($request->Idcource, ['date_transport' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Colis enregistré avec succès',
                'data' => $colis,
                'otp' => $otp // À envoyer par SMS en production
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Détails d'un colis
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $colis = Colis::with(['client', 'courses'])->findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $colis->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $colis
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Colis non trouvé'], 404);
        }
    }

    /**
     * Mise à jour du statut d'un colis
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $colis = Colis::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $colis->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'statut_enum' => 'required|in:enregistre,en_transit,livre,recupere',
                'otp_saisi' => 'required_if:statut_enum,livre|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Validation OTP pour livraison
            if ($request->statut_enum === 'livre' && $request->otp_saisi !== $colis->Otp_valide) {
                return response()->json(['message' => 'Code OTP incorrect'], 400);
            }

            $colis->update($request->only(['statut_enum', 'Description', 'Poids']));

            return response()->json([
                'success' => true,
                'message' => 'Statut du colis mis à jour',
                'data' => $colis
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $colis = Colis::findOrFail($id);

            if (!in_array($user->role_enum, ['superAdmin', 'adminAgence'])) {
                return response()->json(['message' => 'Permission insuffisante'], 403);
            }

            if ($user->role_enum === 'adminAgence' && $colis->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $colis->delete();

            return response()->json([
                'success' => true,
                'message' => 'Colis supprimé'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Erreur lors de la suppression'], 500);
        }
    }
}
