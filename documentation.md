# Documentation de l'API TransportLogistique

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
    "role_enum": "superAdmin"
  }
}
```

⚠️ Toutes les requêtes nécessitent le header:
```
Authorization: Bearer api_token
```

## Ressources Principales

### 1. Véhicules
Endpoints:
- `GET /api/vehicules` - Liste tous les véhicules
- `GET /api/vehicules/disponibles` - Véhicules disponibles
- `POST /api/vehicules` - Créer un véhicule
- `PUT /api/vehicules/{id}` - Mettre à jour un véhicule

Structure :
```json
{
  "id": 1,
  "immatriculation": "AB-123-CD",
  "marque": "Mercedes",
  "modele": "Sprinter",
  "type": "camion",
  "capacite": 1500,
  "status": "disponible"
}
```

### 2. Courses
Endpoints:
- `GET /api/courses` - Liste des courses
- `POST /api/courses` - Créer une course
- `POST /api/courses/{id}/colis` - Attacher des colis 

Exemple de création :
```http
POST /api/courses
{
  "nomCourse": "Livraison Dakar",
  "client_id": 5,
  "departureTime": "2026-06-15 08:00:00"
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
