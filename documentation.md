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
  "status": "disponible",
  "proprietaire_type": "agence",
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

### 2. Courses (Dispatcher/Admin ⚠️)
Endpoints:
- `GET /api/courses` - Liste des courses filtrées
- `POST /api/courses` - Créer une course
- `PUT /api/courses/{id}` - Mettre à jour
- `POST /api/courses/{id}/colis` - Attacher des colis 

Exemple de création complète :
```http
POST /api/courses
{
  "nomCourse": "Livraison Dakar",
  "client_id": 5,
  "departureTime": "2026-06-15 08:00:00",
  "passengers": 2,
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

### 3. Chauffeurs
Endpoints:
- `GET /api/chauffeurs` - Liste des chauffeurs
- `GET /api/chauffeurs/mon-profil` - Profil du chauffeur connecté
- `POST /api/chauffeurs/complete-profil` - Compléter son profil

### 4. Notification
Endpoints:
- `GET /api/notifications` - Liste des notifications
- `POST /api/notifications/marquer-lue/{id}` - Marquer comme lue

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

© 2026 TransportLog | Documentation version 1.0
