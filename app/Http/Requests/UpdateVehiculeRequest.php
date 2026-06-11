<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehiculeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $vehicule = $this->route('vehicule'); // Assuming the route parameter is named 'vehicule'
        
        // If the route parameter is just an ID, we might need to fetch it, but usually Laravel does route model binding.
        // Let's check the current controller to see how it's handled. 
        // In the current controller, it's public function update(Request $request, $id).
        
        return in_array($user->role_enum, ['adminAgence', 'superAdmin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicule = $this->route('vehicule');
        $id = $vehicule instanceof \App\Models\Vehicule ? $vehicule->Idvehicule : $vehicule;
        
        return [
            'immatriculation' => 'sometimes|string|max:50|unique:vehicules,immatriculation,' . $id . ',Idvehicule',
            'TypeVehicule' => 'sometimes|in:bus,taxi,camion,moto,minibus',
            'marque' => 'sometimes|string|max:50',
            'modele' => 'nullable|string|max:50',
            'couleur' => 'nullable|string|max:50',
            'annee' => 'nullable|date',
            'Capacite' => 'sometimes|integer|min:1|max:60',
            'CapacitePassagers' => [
                'sometimes',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($vehicule) {
                    $type = $this->input('TypeVehicule') ?? ($vehicule instanceof \App\Models\Vehicule ? $vehicule->TypeVehicule : null);
                    if (!$type && !($vehicule instanceof \App\Models\Vehicule)) {
                        // Fallback if we can't get the type
                        return;
                    }
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
            'statut_enum' => 'sometimes|in:disponible,en_mission,maintenance,hors_service',
            'Date_Expir_Assurance' => 'sometimes|date|after:today',
            'visiteTech' => 'sometimes|date|after:today',
            'kilometrage' => 'sometimes|integer|min:0',
            'proprietaire_type' => 'nullable|in:agence,chauffeur',
            'commission_fixe_course' => 'nullable|numeric|min:0',
            'Idchauffeur' => 'nullable|required_if:proprietaire_type,chauffeur|exists:chauffeurs,Idchauffeur',
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
    }
}
