<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Passager;
use App\Models\Course;
use App\Models\Colis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PassagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Passager::with('course', 'colis');

        if ($user->role_enum !== 'superAdmin') {
            $query->where('Idagence', $user->Idagence);
        }

        if ($request->has('Idcource')) {
            $query->where('Idcource', $request->Idcource);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'nomPassager' => 'required|string|max:255',
                'telephone' => 'nullable|string|max:20',
                'nombre_sieges' => 'required|integer|min:1',
                'tarif_paye' => 'required|numeric|min:0',
                'devise' => 'nullable|string|max:5',
                'Idcource' => 'required|exists:courses,Idcource',
                'colis' => 'nullable|array',
                'colis.*.Description' => 'required|string',
                'colis.*.Poids' => 'required|numeric',
                'colis.*.prix' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return DB::transaction(function () use ($request, $user) {
                $course = Course::findOrFail($request->Idcource);
                $chauffeur = $course->chauffeur;

                $passager = Passager::create([
                    'nomPassager' => $request->nomPassager,
                    'telephone' => $request->telephone,
                    'nombre_sieges' => $request->nombre_sieges,
                    'tarif_paye' => $request->tarif_paye,
                    'devise' => $request->devise ?? 'CDF',
                    'Idcource' => $request->Idcource,
                    'Idagence' => $user->Idagence
                ]);

                // Si le chauffeur est un salarié de l'agence, l'argent va en caisse
                if ($chauffeur && $chauffeur->type_contrat === 'salarie') {
                    \App\Models\TransactionFinance::create([
                        'montant' => $request->tarif_paye,
                        'devise' => $request->devise ?? 'CDF',
                        'mode_paiement_Enum' => 'especes',
                        'Type_Transaction_Enum' => 'course',
                        'description' => "Paiement passager: " . $request->nomPassager,
                        'Date_Paiement' => now(),
                        'Idcource' => $course->Idcource,
                        'Idagence' => $user->Idagence,
                        'Idsuccursale' => $course->Idsuccursale
                    ]);
                }
                // Si c'est un adhérent, on ne crée pas de transaction agence (il gère son argent)

                // Gestion des colis accompagnés
                if ($request->has('colis')) {
                    foreach ($request->colis as $cData) {
                        $colis = Colis::create([
                            'nomExpediteur' => $request->nomPassager,
                            'TelephoneExpedit' => $request->telephone,
                            'nomDestinateur' => $request->nomPassager,
                            'CodeColis' => 'PKG-' . strtoupper(bin2hex(random_bytes(3))),
                            'statut_enum' => 'en_transit',
                            'Description' => $cData['Description'],
                            'Poids' => $cData['Poids'],
                            'prix' => $cData['prix'],
                            'devise' => $request->devise ?? 'CDF',
                            'Idpassager' => $passager->Idpassager,
                            'Idagence' => $user->Idagence
                        ]);

                        $colis->courses()->attach($course->Idcource, ['date_transport' => now()]);

                        // Même logique financière pour les colis du passager
                        if ($chauffeur && $chauffeur->type_contrat === 'salarie') {
                            \App\Models\TransactionFinance::create([
                                'montant' => $cData['prix'],
                                'devise' => $request->devise ?? 'CDF',
                                'mode_paiement_Enum' => 'especes',
                                'Type_Transaction_Enum' => 'colis',
                                'description' => "Fret passager: " . $request->nomPassager,
                                'Date_Paiement' => now(),
                                'Idcource' => $course->Idcource,
                                'Idagence' => $user->Idagence
                            ]);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Passager enregistré avec ses éventuels colis',
                    'data' => $passager->load('colis')
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $passager = Passager::with('course', 'colis')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $passager]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Passager non trouvé'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $passager = Passager::findOrFail($id);
            $passager->update($request->all());
            return response()->json(['success' => true, 'data' => $passager]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $passager = Passager::findOrFail($id);
            $passager->delete();
            return response()->json(['success' => true, 'message' => 'Passager supprimé']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
