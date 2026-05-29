<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Idvehicule,
            'immatriculation' => $this->immatriculation,
            'type' => $this->TypeVehicule,
            'marque' => $this->marque,
            'modele' => $this->modele,
            'couleur' => $this->couleur,
            'annee' => $this->annee ? $this->annee->format('Y') : null,
            'capacite' => [
                'globale' => $this->Capacite,
                'passagers' => $this->CapacitePassagers,
                'poids' => $this->CapacitePoids,
                'volume_bagages' => $this->VolumeBagages,
            ],
            'statut' => $this->statut_enum,
            'dates_importantes' => [
                'expiration_assurance' => $this->Date_Expir_Assurance ? $this->Date_Expir_Assurance->toDateString() : null,
                'visite_technique' => $this->visiteTech ? $this->visiteTech->toDateString() : null,
            ],
            'kilometrage' => $this->kilometrage,
            'proprietaire_type' => $this->proprietaire_type,
            'Idchauffeur' => $this->Idchauffeur,
            'proprietaire' => $this->whenLoaded('proprietaire'),
            'agence_id' => $this->Idagence,
            'agence' => new AgenceResource($this->whenLoaded('agence')),
            'succursale_id' => $this->Idsuccursale,
            'succursale' => $this->whenLoaded('succursale'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
