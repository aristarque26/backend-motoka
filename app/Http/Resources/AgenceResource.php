<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->Idagence,
            'nom' => $this->nom,
            'slug' => $this->slug,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'logo_url' => $this->logo_url,
            'plan' => $this->plan_enum,
            'statut' => $this->statut_enum,
            'date_expiration' => $this->ExpirationDate ? $this->ExpirationDate->toDateString() : null,
        ];
    }
}
