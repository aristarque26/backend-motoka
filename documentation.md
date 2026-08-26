# Documentation de l'API TransportLogistique

## Hiérarchie et Rôles

1. **SuperAdmin** - Accès complet
2. **AdminAgence** - Gère une agence et ses succursales
3. **AdminSuccursale** - Gère une succursale spécifique
4. **Dispatcher** - Planifie les courses
5. **Chauffeur** - Visualise ses missions
6. **Client** - Consulte ses livraisons

## Authentification

### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

Réponse réussie :
```json
{
  "token": "api_token",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role_enum": "superAdmin",
    "Idagence": 1,
    "Idsuccursale": null
  }
}
```

### Rôles Requis pour les Endpoints
- Les endpoints marqués 🔒 nécessitent un token valide
- Les endpoints marqués ⚠️ nécessitent des permissions spécifiques

## Gestion des Agences

### Création d'une Agence (SuperAdmin ⚠️)
```http
POST /api/agences
{
  "nom": "Agence Dakar",
  "email": "contact@dakar-transport.com",
  "telephone": "771234567",
  "adresse": "Rue 10, Dakar",
  "ville": "Dakar"
}
```

### Création d'un Admin Agence (SuperAdmin ⚠️)
```http
POST /api/users/admin-agence
{
  "name": "Papa Diop",
  "email": "admin@dakar-transport.com",
  "password": "secret123",
  "agence_id": 1
}
```

## Gestion des Succursales

### Création d'une Succursale (AdminAgence ⚠️)
```http
POST /api/succursales
{
  "nom": "Succursale Thies",
  "agence_id": 1,
  "adresse": "Avenue Lamine Gueye",
  "manager_id": 3
}
```

## Ressources Principales (CRUD)

### 1. Véhicules (AdminAgence/AdminSuccursale ⚠️)
Endpoints:
- `GET /api/vehicules` - Liste des véhicules filtrés par agence/succursale
- `GET /api/vehicules/disponibles` - Véhicules disponibles
- `POST /api/vehicules` - Créer un véhicule
- `PUT /api/vehicules/{id}` - Mettre à jour un véhicule

Structure complète :
```json
{
  "id": 1,
  "immatriculation": "AB-123-CD",
  "marque": "Mercedes",
  "modele": "Sprinter",
  "TypeVehicule": "camion",
  "Capacite": 1500,
  "CapacitePassagers": 3,
  "CapacitePoids": 1000,
  "VolumeBagages": 15.5,
  "status": "disponible",
  "proprietaire_type": "agence",
  "commission_fixe_course": 500,
  "Idagence": 1,
  "Idsuccursale": 2,
  "Idchauffeur": null,
  "kilometrage": 12500
}
```

Champs requis à la création :
```json
{
  "immatriculation": "AB-123-CD",
  "marque": "Mercedes",
  "TypeVehicule": "camion",
  "Capacite": 1500
}
```

Types de véhicules : `voiture`, `camion`, `moto`, `fourgon`

### 2. Courses (Dispatcher/Admin ⚠️)
Endpoints:
- `GET /api/courses` - Liste des courses filtrées
- `POST /api/courses` - Créer une course
- `PUT /api/courses/{id}` - Mettre à jour
- `POST /api/courses/{id}/colis` - Attacher des colis
- `GET /api/itineraries` - Liste des itinéraires
- `POST /api/itineraries` - Créer un itinéraire
- `PUT /api/itineraries/{id}` - Mettre à jour un itinéraire

Exemple de création complète avec itinéraire :
```http
POST /api/courses
{
  "nomCourse": "Livraison Dakar",
  "type_course": "mixte",
  "Iditinerary": 1,
  "client_id": 5,
  "departureTime": "2026-06-15 08:00:00",
  "passengers": 2,
  "poids_total": 500.50,
  "Idagence": 1,
  "Idsuccursale": 2,
  "Idvehicule": 3,
  "Idchauffeur": 4,
  "PrixReel": 25000,
  "frais_fret": 5000,
  "montant_chauffeur": 20000
}
```

Champs minimum requis :
```json
{
  "nomCourse": "Livraison Dakar",
  "client_id": 5,
  "Idagence": 1
}
```

#### Types de courses
- `passager` : Transport de passagers uniquement
- `colis` : Transport de colis uniquement  
- `mixte` : Transport combiné passagers et colis

### 3. Itinéraires
Endpoints:
- `GET /api/itineraries` - Liste des itinéraires
- `POST /api/itineraries` - Créer un itinéraire
- `GET /api/itineraries/{id}` - Détails d'un itinéraire
- `PUT /api/itineraries/{id}` - Mettre à jour
- `DELETE /api/itineraries/{id}` - Supprimer

Structure d'un itinéraire :
```json
{
  "id": 1,
  "nom": "Dakar-Thies",
  "adresse_depart": "Gare Routière Dakar",
  "latitude_depart": -17.45600000,
  "longitude_depart": 14.78900000,
  "adresse_arrivee": "Gare Routière Thies",
  "latitude_arrivee": -16.92000000,
  "longitude_arrivee": 16.20000000,
  "distance_km_estimee": 75.50,
  "prix_estime": 3000,
  "prix_base_passager": 5000,
  "Idagence": 1,
  "Idsuccursale": null,
  "Idsuccursale_depart": null,
  "Idsuccursale_arrivee": null
}
```

### 4. Passagers
Endpoints:
- `GET /api/passagers` - Liste des passagers
- `POST /api/passagers` - Créer un passager (avec colis optionnels)
- `GET /api/passagers/{id}` - Détails d'un passager
- `PUT /api/passagers/{id}` - Mettre à jour
- `DELETE /api/passagers/{id}` - Supprimer

Création d'un passager avec colis accompagnés :
```http
POST /api/passagers
{
  "nomPassager": "Amadou Ba",
  "telephone": "771234567",
  "nombre_sieges": 1,
  "tarif_paye": 3000,
  "devise": "CDF",
  "Idcource": 1,
  "colis": [
    {
      "Description": "Colis express",
      "Poids": 5.5,
      "prix": 1500
    }
  ]
}
```

Structure d'un passager :
```json
{
  "Idpassager": 1,
  "nomPassager": "Amadou Ba",
  "telephone": "771234567",
  "nombre_sieges": 1,
  "tarif_paye": 3000.00,
  "devise": "CDF",
  "Idcource": 1,
  "Idagence": 1,
  "colis": [
    {
      "Idcolis": 12,
      "Description": "Colis express",
      "Poids": 5.50,
      "prix": 1500.00
    }
  ]
}
```

### 5. Chauffeurs
Endpoints:
- `GET /api/admin/chauffeurs` - Liste des chauffeurs (Admin)
- `POST /api/admin/chauffeurs` - Créer un chauffeur (Admin)
- `GET /api/admin/chauffeurs/{id}` - Détails d'un chauffeur
- `PUT /api/admin/chauffeurs/{id}` - Mettre à jour (Admin)
- `DELETE /api/admin/chauffeurs/{id}` - Supprimer (Admin)
- `GET /api/chauffeur/profile` - Profil du chauffeur connecté
- `POST /api/chauffeur/complete-profile` - Compléter son profil
- `PUT /api/chauffeur/profile` - Mettre à jour son profil

Structure d'un chauffeur :
```json
{
  "Idchauffeur": 1,
  "name": "Mamadou Diop",
  "email": "chauffeur@example.com",
  "telephone": "771234567",
  "type_contrat": "salarie",
  "numero_permis": "PERM123456",
  "date_naissance": "1985-03-15",
  "statut_enum": "disponible",
  "Idagence": 1,
  "Idsuccursale": 2
}
```

Types de contrat : `salarie` ou `adhérent`

### 6. Colis
Endpoints:
- `GET /api/colis` - Liste des colis
- `POST /api/colis` - Créer un colis
- `GET /api/colis/{id}` - Détails d'un colis
- `PUT /api/colis/{id}` - Mettre à jour
- `DELETE /api/colis/{id}` - Supprimer

Structure d'un colis :
```json
{
  "Idcolis": 1,
  "nomExpediteur": "Amadou Ba",
  "TelephoneExpedit": "771234567",
  "nomDestinateur": "Marie Claire",
  "CodeColis": "PKG-A4F9C2",
  "Qr_code_Url": "https://storage.example.com/qr/abc.png",
  "statut_enum": "en_transit",
  "Description": "Documents importants",
  "Poids": 2.50,
  "prix": 1500.00,
  "devise": "CDF",
  "methode_calcul_prix": "fixe",
  "Idpassager": 1,
  "Idclient": 5,
  "Idagence": 1
}
```

Statuts des colis : `en_attente`, `en_transit`, `livre`, `annule`

### 7. Transactions Financières
Endpoints:
- `GET /api/transactions-finances` - Liste des transactions
- `POST /api/transactions-finances` - Créer une transaction
- `GET /api/transactions-finances/{id}` - Détails d'une transaction

Structure d'une transaction :
```json
{
  "IdTransactionFinance": 1,
  "montant": 25000.00,
  "devise": "CDF",
  "mode_paiement_Enum": "especes",
  "reference_paiement": "REF-12345",
  "description": "Paiement course Dakar-Thies",
  "Date_Paiement": "2026-06-15 10:30:00",
  "Idcource": 1,
  "Idagence": 1,
  "Idchauffeur": null,
  "Idsuccursale": null
}
```

Types de transactions : `course`, `colis`, `salaire`, `depense`
Modes de paiement : `especes`, `mobile_money`, `banque`, `virement`

### 8. Notifications
Endpoints:
- `GET /api/notifications` - Liste des notifications
- `POST /api/notifications/marquer-lue/{id}` - Marquer comme lue

### 9. Dépenses
Endpoints:
- `GET /api/depenses` - Liste des dépenses
- `POST /api/depenses` - Créer une dépense
- `GET /api/depenses/{id}` - Détails d'une dépense
- `PUT /api/depenses/{id}` - Mettre à jour
- `DELETE /api/depenses/{id}` - Supprimer

Structure d'une dépense :
```json
{
  "IdDepense": 1,
  "Libelle": "Carburant véhicule AB-123-CD",
  "Montant": 150000.00,
  "typeDepense_ENUM": "carburant",
  "Date_Depense": "2026-06-15 10:00:00",
  "justificatif_url": "https://storage.example.com/justificatifs/facture123.pdf",
  "Idagence": 1
}
```

Types de dépenses : `carburant`, `salaire`, `maintenance`, `assurance`, `taxe`, `autre`

### 10. Tracking GPS
Endpoints:
- `GET /api/tracking-gps` - Liste des points GPS
- `GET /api/tracking-gps?Idvehicule=1` - Points GPS pour un véhicule

Structure d'un point GPS :
```json
{
  "IdTracking": 1,
  "Latitude": -17.4560,
  "Longitude": 14.7890,
  "Vitesse": 65.50,
  "Position_Km": 12500,
  "altitude": 25.30,
  "precisionGPS": "5m",
  "angle": 90.00,
  "Idvehicule": 1
}
```

### 11. Maintenance
Endpoints:
- `GET /api/maintenances` - Liste des maintenances
- `POST /api/maintenances` - Enregistrer une maintenance
- `GET /api/maintenances/{id}` - Détails d'une maintenance

Structure d'une maintenance :
```json
{
  "id": 1,
  "type_maintenance": "revision",
  "Date_maintenance": "2026-06-10",
  "Date_prochaine_maintenance": "2026-12-10",
  "cout": 250000.00,
  "statut_enum": "terminee",
  "Idvehicule": 1
}
```

Statuts : `planifiee`, `en_cours`, `terminee`, `annulee`

## Gestion des Erreurs

Codes HTTP :
- 200 : Succès
- 401 : Non authentifié
- 403 : Permission refusée
- 404 : Ressource non trouvée
- 422 : Erreur de validation

Exemple d'erreur :
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["Le champ email est obligatoire"]
  }
}
```

## Bonnes Pratiques

1. **Pagination** : Toutes les listes sont paginées
   ```
   GET /api/vehicules?page=2
   ```

2. **Filtres** : Possibilité de filtrer par agence, statut, etc.
   ```
   GET /api/courses?status=terminee
   ```

3. **Webhooks** : Configurez des webhooks pour être alerté des changements

## Exemple d'Intégration (JavaScript)

```javascript
const apiUrl = 'https://votre-domaine.com/api';

async function login(email, password) {
  const response = await fetch(`${apiUrl}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  return await response.json();
}

async function getCourses(token) {
  const response = await fetch(`${apiUrl}/courses`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return await response.json();
}
```

## FAQ

Q: Comment reset un mot de passe ?
R: Envoyer un email à support@transportlog.com

Q: Limitations API ?
R: 100 requêtes/min par IP

---

© 2026 TransportLog | Documentation version 1.2
