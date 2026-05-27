<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use App\Http\Resources\VehiculeResource;
use App\Http\Requests\StoreVehiculeRequest;
use App\Http\Requests\UpdateVehiculeRequest;
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
     * Vérifier si l'utilisateur a le droit de gérer les véhicules
     */
    private function canManageVehicules($user)
    {
        return in_array($user->role_enum, ['adminAgence', 'superAdmin', 'dispatcher']);
    }

    /**
     * Lister les véhicules
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $query = Vehicule::with('agence', 'succursale');

        if ($user->role_enum !== 'superAdmin') {
            $query->where('Idagence', $user->Idagence);
        }

        return VehiculeResource::collection($query->paginate(20));
    }

    /**
     * Créer un véhicule
     */
    public function store(StoreVehiculeRequest $request)
    {
        $vehicule = Vehicule::create($request->validated());

        return (new VehiculeResource($vehicule))
            ->additional(['message' => 'Véhicule créé avec succès'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Afficher un véhicule spécifique
     */
    public function show(Request $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($user->role_enum !== 'superAdmin' && $vehicule->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        return new VehiculeResource($vehicule->load('agence', 'succursale'));
    }

    /**
     * Modifier un véhicule
     */
    public function update(UpdateVehiculeRequest $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if ($user->role_enum !== 'superAdmin' && $vehicule->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $vehicule->update($request->validated());

        return (new VehiculeResource($vehicule))
            ->additional(['message' => 'Véhicule mis à jour avec succès']);
    }

    /**
     * Supprimer un véhicule
     */
    public function destroy(Request $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if (!$this->isAdminOrSuperAdmin($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($user->role_enum !== 'superAdmin' && $vehicule->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $vehicule->delete();

        return response()->json(['message' => 'Véhicule supprimé avec succès']);
    }

    // =============================================
    // GESTION DES ÉTATS
    // =============================================

    public function getDisponibles(Request $request)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $vehicules = Vehicule::where('Idagence', $user->Idagence)
            ->where('statut_enum', 'disponible')
            ->where('Date_Expir_Assurance', '>', now())
            ->where('visiteTech', '>', now())
            ->get();

        return VehiculeResource::collection($vehicules);
    }

    public function getEnMission(Request $request)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $vehicules = Vehicule::where('Idagence', $user->Idagence)
            ->where('statut_enum', 'en_mission')
            ->get();

        return VehiculeResource::collection($vehicules);
    }

    public function getEnMaintenance(Request $request)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $vehicules = Vehicule::where('Idagence', $user->Idagence)
            ->where('statut_enum', 'maintenance')
            ->get();

        return VehiculeResource::collection($vehicules);
    }

    /**
     * Assigner un véhicule à une course
     */
    public function assignerCourse(Request $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($vehicule->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($vehicule->statut_enum !== 'disponible') {
            return response()->json(['message' => 'Ce véhicule n\'est pas disponible'], 400);
        }

        if ($vehicule->Date_Expir_Assurance < now()) {
            return response()->json(['message' => 'Assurance expirée'], 400);
        }

        if ($vehicule->visiteTech < now()) {
            return response()->json(['message' => 'Visite technique expirée'], 400);
        }

        $validator = Validator::make($request->all(), [
            'Idcourse' => 'required|exists:courses,Idcourse',
            'Idchauffeur' => 'required|exists:chauffeurs,Idchauffeur'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicule->update(['statut_enum' => 'en_mission']);

        return (new VehiculeResource($vehicule))
            ->additional(['message' => 'Véhicule assigné à la course avec succès']);
    }

    public function liberer(Request $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($vehicule->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($vehicule->statut_enum !== 'en_mission') {
            return response()->json(['message' => 'Ce véhicule n\'est pas en mission'], 400);
        }

        $vehicule->update(['statut_enum' => 'disponible']);

        return (new VehiculeResource($vehicule))
            ->additional(['message' => 'Véhicule libéré avec succès']);
    }

    public function mettreMaintenance(Request $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

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

        $vehicule->update(['statut_enum' => 'maintenance']);

        return (new VehiculeResource($vehicule))
            ->additional(['message' => 'Véhicule mis en maintenance']);
    }

    public function remettreEnService(Request $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($vehicule->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($vehicule->statut_enum !== 'maintenance') {
            return response()->json(['message' => 'Ce véhicule n\'est pas en maintenance'], 400);
        }

        $vehicule->update(['statut_enum' => 'disponible']);

        return (new VehiculeResource($vehicule))
            ->additional(['message' => 'Véhicule remis en service avec succès']);
    }

    public function updateKilometrage(Request $request, Vehicule $vehicule)
    {
        $user = $request->user();

        if (!$this->canManageVehicules($user)) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        if ($vehicule->Idagence !== $user->Idagence) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $validator = Validator::make($request->all(), [
            'kilometrage' => 'required|integer|min:' . $vehicule->kilometrage
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicule->update(['kilometrage' => $request->kilometrage]);

        return (new VehiculeResource($vehicule))
            ->additional(['message' => 'Kilométrage mis à jour']);
    }
}