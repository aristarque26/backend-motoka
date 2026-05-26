<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehiculeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Vérifier si l'utilisateur est admin_agence ou super_admin
     */
    private function isAdminOrSuperAdmin($user)
    {
        return in_array($user->role_enum, ['adminAgence', 'superAdmin']);
    }

    /**
     * Vérifier si l'utilisateur est dispatcher
     */
    private function isDispatcher($user)
    {
        return $user->role_enum === 'dispatcher';
    }

    /**
     * Vérifier si l'utilisateur a le droit de gérer les véhicules (admin, super_admin, dispatcher)
     */
    private function canManageVehicules($user)
    {
        return in_array($user->role_enum, ['adminAgence', 'superAdmin', 'dispatcher']);
    }

    // =============================================
    // PARTIE ADMIN & SUPER_ADMIN (CRUD complet)
    // =============================================

    /**
     * Lister les véhicules (admin_agence voit ceux de son agence, super_admin voit tout)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            if ($user->role_enum === 'superAdmin') {
                $vehicules = Vehicule::with('agence')->paginate(20);
            } else {
                $vehicules = Vehicule::where('Idagence', $user->Idagence)
                    ->with('agence')
                    ->paginate(20);
            }

            return response()->json([
                'success' => true,
                'data' => $vehicules
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Créer un véhicule (admin ou super_admin uniquement)
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé. Seuls les administrateurs peuvent créer des véhicules.'], 403);
            }

            $agenceId = $user->role_enum === 'superAdmin'
                ? $request->Idagence
                : $user->Idagence;

            if ($user->role_enum === 'superAdmin' && !$request->Idagence) {
                return response()->json(['message' => 'Idagence requis pour super_admin'], 422);
            }

            // =============================================
            // VALIDATION AVEC RÈGLES PAR TYPE DE VÉHICULE
            // =============================================
            $validator = Validator::make($request->all(), [
                'immatriculation' => 'required|string|max:50|unique:vehicules,immatriculation',
                'TypeVehicule' => 'required|in:bus,taxi,camion,moto,minibus',
                'marque' => 'required|string|max:50',
                'modele' => 'nullable|string|max:50',
                'couleur' => 'nullable|string|max:50',
                'annee' => 'nullable|date',
                'Capacite' => 'required|integer|min:1|max:60',
                'CapacitePassagers' => 'required|integer|min:1|max:60',
                'CapacitePoids' => 'nullable|numeric|min:0',
                'VolumeBagages' => 'nullable|numeric|min:0',
                'statut_enum' => 'nullable|in:disponible,en_mission,maintenance,hors_service',
                'Date_Expir_Assurance' => 'required|date|after:today',
                'visiteTech' => 'required|date|after:today',
                'kilometrage' => 'required|integer|min:0',
                'Idagence' => $user->role_enum === 'superAdmin' ? 'required|exists:agences,Idagence' : 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // =============================================
            // VALIDATION SPÉCIFIQUE PAR TYPE DE VÉHICULE
            // =============================================
            if ($request->TypeVehicule === 'moto' && $request->CapacitePassagers > 1) {
                return response()->json(['message' => 'Une moto ne peut transporter qu\'1 passager'], 422);
            }
            if ($request->TypeVehicule === 'taxi' && $request->CapacitePassagers > 4) {
                return response()->json(['message' => 'Un taxi ne peut transporter que 4 passagers maximum'], 422);
            }
            if ($request->TypeVehicule === 'minibus' && $request->CapacitePassagers > 15) {
                return response()->json(['message' => 'Un minibus ne peut transporter que 15 passagers maximum'], 422);
            }
            if ($request->TypeVehicule === 'bus' && $request->CapacitePassagers > 60) {
                return response()->json(['message' => 'Un bus ne peut transporter que 60 passagers maximum'], 422);
            }

            $vehicule = Vehicule::create([
                'immatriculation' => strtoupper($request->immatriculation),
                'TypeVehicule' => $request->TypeVehicule,
                'marque' => $request->marque,
                'modele' => $request->modele,
                'couleur' => $request->couleur,
                'annee' => $request->annee,
                'Capacite' => $request->Capacite,
                'CapacitePassagers' => $request->CapacitePassagers,
                'CapacitePoids' => $request->CapacitePoids,
                'VolumeBagages' => $request->VolumeBagages,
                'statut_enum' => $request->statut_enum ?? 'disponible',
                'Date_Expir_Assurance' => $request->Date_Expir_Assurance,
                'visiteTech' => $request->visiteTech,
                'kilometrage' => $request->kilometrage,
                'Idagence' => $agenceId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Véhicule créé avec succès',
                'data' => $vehicule
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Afficher un véhicule spécifique
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicule = Vehicule::with('agence')->findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $vehicule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un véhicule (admin ou super_admin uniquement)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé. Seuls les administrateurs peuvent modifier des véhicules.'], 403);
            }

            $vehicule = Vehicule::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'immatriculation' => 'sometimes|string|max:50|unique:vehicules,immatriculation,' . $id . ',Idvehicule',
                'TypeVehicule' => 'sometimes|in:bus,taxi,camion,moto,minibus',
                'marque' => 'sometimes|string|max:50',
                'modele' => 'nullable|string|max:50',
                'couleur' => 'nullable|string|max:50',
                'annee' => 'nullable|date',
                'Capacite' => 'sometimes|integer|min:1|max:60',
                'CapacitePassagers' => 'sometimes|integer|min:1|max:60',
                'CapacitePoids' => 'nullable|numeric|min:0',
                'VolumeBagages' => 'nullable|numeric|min:0',
                'statut_enum' => 'sometimes|in:disponible,en_mission,maintenance,hors_service',
                'Date_Expir_Assurance' => 'sometimes|date|after:today',
                'visiteTech' => 'sometimes|date|after:today',
                'kilometrage' => 'sometimes|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->has('immatriculation')) {
                $request->merge(['immatriculation' => strtoupper($request->immatriculation)]);
            }

            $vehicule->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Véhicule mis à jour avec succès',
                'data' => $vehicule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un véhicule (admin ou super_admin uniquement)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé. Seuls les administrateurs peuvent supprimer des véhicules.'], 403);
            }

            $vehicule = Vehicule::findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =============================================
    // PARTIE DISPATCHER & ADMIN (Gestion des états)
    // =============================================

    /**
     * Dispatcher/Admin : Voir les véhicules disponibles de son agence
     */
    public function getDisponibles(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicules = Vehicule::where('Idagence', $user->Idagence)
                ->where('statut_enum', 'disponible')
                ->where('Date_Expir_Assurance', '>', now())
                ->where('visiteTech', '>', now())
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vehicules,
                'count' => $vehicules->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dispatcher/Admin : Voir les véhicules en mission
     */
    public function getEnMission(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicules = Vehicule::where('Idagence', $user->Idagence)
                ->where('statut_enum', 'en_mission')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vehicules,
                'count' => $vehicules->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dispatcher/Admin : Voir les véhicules en maintenance
     */
    public function getEnMaintenance(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicules = Vehicule::where('Idagence', $user->Idagence)
                ->where('statut_enum', 'maintenance')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vehicules,
                'count' => $vehicules->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dispatcher/Admin : Assigner un véhicule à une course
     */
    public function assignerCourse(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicule = Vehicule::findOrFail($id);

            if ($vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            // Vérifications avant assignation
            if ($vehicule->statut_enum !== 'disponible') {
                return response()->json(['message' => 'Ce véhicule n\'est pas disponible'], 400);
            }

            if ($vehicule->Date_Expir_Assurance < now()) {
                return response()->json(['message' => 'Assurance expirée. Veuillez renouveler.'], 400);
            }

            if ($vehicule->visiteTech < now()) {
                return response()->json(['message' => 'Visite technique expirée. Veuillez effectuer la révision.'], 400);
            }

            $validator = Validator::make($request->all(), [
                'Idcourse' => 'required|exists:courses,Idcourse',
                'Idchauffeur' => 'required|exists:chauffeurs,Idchauffeur'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $vehicule->statut_enum = 'en_mission';
            $vehicule->save();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule assigné à la course avec succès',
                'data' => $vehicule,
                'assignment' => [
                    'Idcourse' => $request->Idcourse,
                    'Idchauffeur' => $request->Idchauffeur
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dispatcher/Admin : Libérer un véhicule (fin de course)
     */
    public function liberer(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicule = Vehicule::findOrFail($id);

            if ($vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            if ($vehicule->statut_enum !== 'en_mission') {
                return response()->json(['message' => 'Ce véhicule n\'est pas en mission'], 400);
            }

            $vehicule->statut_enum = 'disponible';
            $vehicule->save();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule libéré avec succès',
                'data' => $vehicule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dispatcher/Admin : Mettre un véhicule en maintenance
     */
    public function mettreMaintenance(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicule = Vehicule::findOrFail($id);

            if ($vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'motif' => 'required|string|max:255',
                'date_retour_prevu' => 'required|date|after:today'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $vehicule->statut_enum = 'maintenance';
            $vehicule->save();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule mis en maintenance',
                'motif' => $request->motif,
                'date_retour_prevu' => $request->date_retour_prevu,
                'data' => $vehicule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dispatcher/Admin : Remettre un véhicule en service (fin maintenance)
     */
    public function remettreEnService(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicule = Vehicule::findOrFail($id);

            if ($vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            if ($vehicule->statut_enum !== 'maintenance') {
                return response()->json(['message' => 'Ce véhicule n\'est pas en maintenance'], 400);
            }

            $vehicule->statut_enum = 'disponible';
            $vehicule->save();

            return response()->json([
                'success' => true,
                'message' => 'Véhicule remis en service avec succès',
                'data' => $vehicule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dispatcher/Admin : Mettre à jour le kilométrage
     */
    public function updateKilometrage(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->canManageVehicules($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $vehicule = Vehicule::findOrFail($id);

            if ($vehicule->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'kilometrage' => 'required|integer|min:' . $vehicule->kilometrage
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $vehicule->kilometrage = $request->kilometrage;
            $vehicule->save();

            return response()->json([
                'success' => true,
                'message' => 'Kilométrage mis à jour',
                'data' => $vehicule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    } 
}