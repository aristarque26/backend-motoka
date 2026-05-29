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
            $query = TransactionFinance::with('course');

            if ($user->role_enum !== 'superAdmin') {
                $query->whereHas('course', function($q) use ($user) {
                    $q->where('Idagence', $user->Idagence);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $query->latest()->paginate(20)
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
