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

            // Filtrage direct par agence
            if ($user->role_enum !== 'superAdmin') {
                $query->where('Idagence', $user->Idagence);
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
                'AdresseDepart' => 'required|string|max:50',
                'LatitudeDepart' => 'required|numeric',
                'LongitudeDepart' => 'required|numeric',
                'AdresseArrive' => 'required|string|max:50',
                'LatitudeArrivee' => 'required|numeric',
                'LongitudeArrive' => 'required|numeric',
                'Distance_Km' => 'required|numeric',
                'PrixEstime' => 'required|numeric',
                'PrixReel' => 'nullable|numeric',
                'Idclient' => 'required|exists:clients,Idclient',
                'Idchauffeur' => 'required|exists:chauffeurs,Idchauffeur',
                'Idvehicule' => 'nullable|exists:vehicules,Idvehicule',
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
            }

            $course = Course::create($data);

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
                'statut_enum' => 'sometimes|in:en_attente,en_cours,termine,annulee',
                'PrixReel' => 'sometimes|numeric',
                'Idchauffeur' => 'sometimes|exists:chauffeurs,Idchauffeur',
                'Idvehicule' => 'sometimes|exists:vehicules,Idvehicule',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $course->update($request->all());

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
