<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Chauffeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CourseControlleur extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Liste des courses filtrées par agence
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $query = Course::query()->with(['chauffeur', 'client', 'vehicule']);

            // Filtrage par agence
            if ($user->role_enum !== 'superAdmin') {
                $query->where('Idagence', $user->Idagence);
                
                // Restriction par succursale si l'utilisateur y est rattaché
                if ($user->Idsuccursale) {
                    $query->where('Idsuccursale', $user->Idsuccursale);
                }
            }

            // Filtres optionnels
            if ($request->has('statut')) {
                $query->where('statut_enum', $request->statut);
            }

            if ($request->has('chauffeur_id')) {
                $query->where('Idchauffeur', $request->chauffeur_id);
            }

            $courses = $query->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $courses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Création d'une nouvelle course
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'nomCourse' => 'required|string|max:50',
                'type_course' => 'nullable|in:passager,colis,mixte',
                'Iditinerary' => 'nullable|exists:itineraries,Iditinerary',
                'departureTime' => 'nullable|date',
                'passengers' => 'nullable|integer',
                'load' => 'nullable|string',
                'poids_total' => 'nullable|numeric',
                'AdresseDepart' => 'required|string|max:50',
                'LatitudeDepart' => 'nullable|numeric',
                'LongitudeDepart' => 'nullable|numeric',
                'AdresseArrive' => 'required|string|max:50',
                'LatitudeArrivee' => 'nullable|numeric',
                'LongitudeArrive' => 'nullable|numeric',
                'Distance_Km' => 'nullable|numeric',
                'PrixEstime' => 'required|numeric',
                'PrixReel' => 'nullable|numeric',
                'Idclient' => 'required|exists:clients,Idclient',
                'Idchauffeur' => 'required|exists:chauffeurs,Idchauffeur',
                'Idvehicule' => 'nullable|exists:vehicules,Idvehicule',
                'Idsuccursale' => 'nullable|exists:succursales,Idsuccursale',
                'statut_enum' => 'nullable|in:en_attente,en_cours,termine,annulee',
                'Idagence' => $user->role_enum === 'superAdmin' ? 'required|exists:agences,Idagence' : 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            
            // Forcer l'Idagence si non superAdmin
            if ($user->role_enum !== 'superAdmin') {
                $data['Idagence'] = $user->Idagence;
                if ($user->Idsuccursale) {
                    $data['Idsuccursale'] = $user->Idsuccursale;
                }
            }

            $prix = $data['PrixReel'] ?? $data['PrixEstime'];

            // Logique de prix basée sur le poids pour les colis
            if (($data['type_course'] ?? 'passager') === 'colis' && isset($data['poids_total'])) {
                // Si le prix n'est pas explicitement fourni, on peut imaginer un calcul
                // $data['PrixReel'] = $data['poids_total'] * $prix_au_kg; 
                // Mais l'utilisateur demande "flexible", donc on garde ce qui vient du front
            }

            // Logique financière et commissions
            $chauffeur = \App\Models\Chauffeur::find($data['Idchauffeur']);
            $vehicule = isset($data['Idvehicule']) ? \App\Models\Vehicule::find($data['Idvehicule']) : null;
            
            $commission = 0;
            $montant_agence = 0;
            $montant_chauffeur = 0;
            $is_partner = false;

            if ($chauffeur) {
                if ($chauffeur->type_contrat === 'adherent') {
                    $is_partner = true;
                    $commissionPercent = $chauffeur->commission ?? 10;
                    $commission = $prix * ($commissionPercent / 100);
                }
            }

            if ($vehicule) {
                if ($vehicule->proprietaire_type === 'chauffeur') {
                    $is_partner = true;
                    // Frais de suivi / commission fixe pour véhicule adhérent
                    $fraisFixe = $vehicule->commission_fixe_course ?? 0;
                    $commission += $fraisFixe;
                }
            }

            $data['frais_fret'] = $commission;
            $data['montant_agence'] = $commission;
            $data['montant_chauffeur'] = $prix - $commission;

            if (!$request->has('paye_a')) {
                $data['paye_a'] = ($data['type_course'] ?? 'passager') === 'colis' ? 'agence' : 'chauffeur';
            }

            $course = Course::create($data);

            // Gestion de la caisse Agence (Caisse de l'Agence)
            // Si c'est un partenaire (adhérent), l'agence ne touche QUE la commission/frais de suivi
            if ($is_partner) {
                if ($commission > 0) {
                    \App\Models\TransactionFinance::create([
                        'montant' => $commission,
                        'devise' => 'CDF',
                        'mode_paiement_Enum' => $request->mode_paiement ?? 'especes',
                        'Type_Transaction_Enum' => 'autre', // Frais de suivi / Commission
                        'Date_Paiement' => now(),
                        'Idcource' => $course->Idcource,
                        'Idagence' => $data['Idagence'],
                        'Idsuccursale' => $data['Idsuccursale'] ?? null,
                        'description' => "Frais de suivi / Commission Adhérent pour la course " . $course->nomCourse
                    ]);
                }
                // Le reste du montant ($prix - $commission) appartient au chauffeur et n'est pas enregistré en caisse agence
            } else {
                // Si c'est un chauffeur AGENCÉ, TOUT l'argent va dans la caisse de l'agence
                \App\Models\TransactionFinance::create([
                    'montant' => $prix,
                    'devise' => 'CDF',
                    'mode_paiement_Enum' => $request->mode_paiement ?? 'especes',
                    'Type_Transaction_Enum' => ($data['type_course'] ?? 'passager') === 'colis' ? 'colis' : 'course',
                    'Date_Paiement' => now(),
                    'Idcource' => $course->Idcource,
                    'Idagence' => $data['Idagence'],
                    'Idsuccursale' => $data['Idsuccursale'] ?? null,
                    'description' => "Paiement total course Agence " . $course->nomCourse
                ]);
            }

            // Attacher les colis si présents
            if ($request->has('colis_ids')) {
                $course->colis()->attach($request->colis_ids, ['date_transport' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Course créée avec succès',
                'data' => $course
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affichage d'une course spécifique
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $course = Course::with(['chauffeur', 'client', 'vehicule', 'colis'])->findOrFail($id);

            // Vérification des droits d'accès
            if ($user->role_enum !== 'superAdmin' && $course->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $course
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Course non trouvée'], 404);
        }
    }

    /**
     * Mise à jour d'une course
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($id);

            // Vérification des droits d'accès
            if ($user->role_enum !== 'superAdmin' && $course->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'nomCourse' => 'sometimes|string|max:50',
                'type_course' => 'sometimes|in:passager,colis,mixte',
                'poids_total' => 'sometimes|nullable|numeric',
                'Iditinerary' => 'sometimes|nullable|exists:itineraries,Iditinerary',
                'departureTime' => 'sometimes|nullable|date',
                'passengers' => 'sometimes|nullable|integer',
                'load' => 'sometimes|nullable|string',
                'statut_enum' => 'sometimes|in:en_attente,en_cours,termine,annulee',
                'PrixReel' => 'sometimes|numeric',
                'Idchauffeur' => 'sometimes|exists:chauffeurs,Idchauffeur',
                'Idvehicule' => 'sometimes|exists:vehicules,Idvehicule',
                'Idsuccursale' => 'sometimes|nullable|exists:succursales,Idsuccursale',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();

            // Recalculer si le prix ou le chauffeur change
            if ($request->has('PrixReel') || $request->has('PrixEstime') || $request->has('Idchauffeur')) {
                $chauffeurId = $request->Idchauffeur ?? $course->Idchauffeur;
                $chauffeur = Chauffeur::find($chauffeurId);
                if ($chauffeur) {
                    $prix = $request->PrixReel ?? $request->PrixEstime ?? $course->PrixReel ?? $course->PrixEstime;
                    if ($chauffeur->type_contrat === 'adherent') {
                        $commissionPercent = $chauffeur->commission ?? 10;
                        $data['frais_fret'] = $prix * ($commissionPercent / 100);
                        $data['montant_agence'] = $data['frais_fret'];
                        $data['montant_chauffeur'] = $prix - $data['frais_fret'];
                    } else {
                        $data['frais_fret'] = 0;
                        $data['montant_agence'] = $prix;
                        $data['montant_chauffeur'] = 0;
                    }
                }
            }

            $course->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Course mise à jour',
                'data' => $course
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Annulation d'une course
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $course->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $course->statut_enum = 'annulee';
            $course->save();

            return response()->json([
                'success' => true,
                'message' => 'Course annulée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Associer des colis à une course
     */
    public function attacherColis(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $course->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'colis_ids' => 'required|array',
                'colis_ids.*' => 'exists:colis,Idcolis'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Attacher sans détacher les anciens (syncWithoutDetaching)
            $course->colis()->syncWithoutDetaching($request->colis_ids);

            return response()->json([
                'success' => true,
                'message' => 'Colis associés à la course avec succès',
                'data' => $course->load('colis')
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
