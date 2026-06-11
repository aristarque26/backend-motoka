<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TransactionFinance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionFinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = TransactionFinance::with(['course', 'chauffeur', 'succursale']);

            // Scoping par agence
            if ($user->role_enum !== 'superAdmin') {
                $query->where('Idagence', $user->Idagence);
            }

            // Filtrage par succursale si applicable
            if ($request->has('Idsuccursale')) {
                $query->where('Idsuccursale', $request->Idsuccursale);
            } elseif ($user->Idsuccursale) {
                $query->where('Idsuccursale', $user->Idsuccursale);
            }

            // Filtrage par date
            if ($request->has('date')) {
                $query->whereDate('Date_Paiement', $request->date);
            }

            // Filtrage par type
            if ($request->has('type')) {
                $query->where('Type_Transaction_Enum', $request->type);
            }

            $transactions = $query->latest('Date_Paiement')->get();

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'montant' => 'required|numeric',
                'devise' => 'required|string|max:3',
                'mode_paiement_Enum' => 'required|string',
                'Type_Transaction_Enum' => 'required|string',
                'Date_Paiement' => 'required|date',
                'Idcource' => 'required|exists:courses,Idcource'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $transaction = TransactionFinance::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
