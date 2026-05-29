<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehiculeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return in_array($user->role_enum, ['adminAgence', 'superAdmin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'immatriculation' => 'required|string|max:50|unique:vehicules,immatriculation',
            'TypeVehicule' => 'required|in:bus,taxi,camion,moto,minibus',
            'marque' => 'required|string|max:50',
            'modele' => 'nullable|string|max:50',
            'couleur' => 'nullable|string|max:50',
            'annee' => 'nullable|date',
            'Capacite' => 'required|integer|min:1|max:60',
            'CapacitePassagers' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $type = $this->input('TypeVehicule');
                    if ($type === 'moto' && $value > 1) {
                        $fail('Une moto ne peut transporter qu\'1 passager.');
                    }
                    if ($type === 'taxi' && $value > 4) {
                        $fail('Un taxi ne peut transporter que 4 passagers maximum.');
                    }
                    if ($type === 'minibus' && $value > 15) {
                        $fail('Un minibus ne peut transporter que 15 passagers maximum.');
                    }
                    if ($type === 'bus' && $value > 60) {
                        $fail('Un bus ne peut transporter que 60 passagers maximum.');
                    }
                },
            ],
            'CapacitePoids' => 'nullable|numeric|min:0',
            'VolumeBagages' => 'nullable|numeric|min:0',
            'statut_enum' => 'nullable|in:disponible,en_mission,maintenance,hors_service',
            'Date_Expir_Assurance' => 'required|date|after:today',
            'visiteTech' => 'required|date|after:today',
            'kilometrage' => 'required|integer|min:0',
            'proprietaire_type' => 'nullable|in:agence,chauffeur',
            'Idchauffeur' => 'nullable|required_if:proprietaire_type,chauffeur|exists:chauffeurs,Idchauffeur',
            'Idagence' => [
                Rule::requiredIf($this->user()->role_enum === 'superAdmin'),
                'exists:agences,Idagence'
            ]
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('immatriculation')) {
            $this->merge([
                'immatriculation' => strtoupper($this->immatriculation),
            ]);
        }

        if ($this->user()->role_enum !== 'superAdmin') {
            $this->merge([
                'Idagence' => $this->user()->Idagence,
            ]);
        }
    }
}
