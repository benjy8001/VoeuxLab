# Design : Régénération des vœux & Partage mutuel

Date : 2026-04-03

## Contexte

Deux features à ajouter à l'application de vœux de mariage (Laravel 13 + Inertia/React) :

1. **Régénération** — permettre à l'utilisateur de régénérer son texte de vœux depuis ses réponses, sans modifier le flux de sauvegarde automatique existant.
2. **Partage mutuel** — permettre à chaque époux de lire les vœux de l'autre après que les deux ont explicitement partagé, ou automatiquement après la date de cérémonie.

---

## Feature 1 : Régénération des vœux

### Décision retenue

Bouton explicite uniquement sur la page d'aperçu. Pas d'invalidation automatique lors de la sauvegarde des réponses (pour ne pas écraser un texte retravaillé manuellement).

### Schéma

Aucun changement de schéma.

### Backend

**Nouvelle route :**
```
POST /voeux/regenerate  →  VowsController::regenerate()
```

**Logique du contrôleur :**
- Récupère le `VowsDraft` de l'utilisateur connecté
- `Gate::authorize('update', $draft)`
- Met `generated_text` à `null`
- Redirige vers `voeux.preview`
- Le guard existant `if (!$draft->generated_text)` dans `preview()` déclenche la régénération via `VowsGeneratorService`

### Frontend (`Voeux/Preview.tsx`)

- Bouton "Régénérer depuis mes réponses" ajouté dans la zone d'actions
- Style secondaire (bordure, pas de fond coloré) pour ne pas concurrencer "Télécharger PDF"
- `window.confirm` avant envoi : "Cette action remplacera le texte actuel. Continuer ?"
- Envoi via `router.post(route('voeux.regenerate'))`

### Ce qui n'est pas touché

Auto-save, drag-and-drop, ReadingTimer, VowsInsights, export PDF.

---

## Feature 2 : Partage mutuel des vœux

### Décision retenue

- Chaque époux dispose d'un bouton "Partager mes vœux" sur sa page d'aperçu
- La lecture des vœux du partenaire est déverrouillée si **les deux ont partagé** ou si la **date de cérémonie est passée**
- Le texte du partenaire n'est jamais transmis au frontend tant que la condition n'est pas remplie

### Schéma

Migration : ajout de `shared_at timestamp nullable` sur `vows_drafts`.

```php
$table->timestamp('shared_at')->nullable()->after('generated_text');
```

### Logique de lisibilité

Méthode `VowsDraft::isReadableByPartner(Couple $couple): bool` :

```
(shared_at de cet époux IS NOT NULL ET shared_at du partenaire IS NOT NULL)
OU
(couple.ceremony_date IS NOT NULL ET couple.ceremony_date <= today)
```

### Backend

**Nouvelle route — partage :**
```
POST /voeux/share  →  VowsController::share()
```
- `Gate::authorize('update', $draft)`
- Met `shared_at = now()` si pas déjà renseigné
- Redirige vers `voeux.preview`

**Nouvelle route — lecture partenaire :**
```
GET /voeux/partner  →  VowsController::partner()
```
- Récupère le draft du partenaire (via `Couple`)
- `Gate::authorize('viewPartner', $partnerDraft)` (nouvelle méthode Policy)
- Retourne `Voeux/Partner` avec `generated_text` du partenaire

**`VowsDraftPolicy` — nouvelle méthode :**
```php
public function viewPartner(User $user, VowsDraft $partnerDraft): bool
{
    $couple = $user->couple;
    $myDraft = VowsDraft::where('user_id', $user->id)
        ->where('couple_id', $couple->id)
        ->first();

    return $myDraft?->isReadableByPartner($couple) ?? false;
}
```

**`DashboardController`** : passe un booléen `partner_vows_readable` calculé depuis `isReadableByPartner()`.

### Frontend

**`Voeux/Preview.tsx` :**
- Reçoit deux nouvelles props : `has_shared` (bool) et `partner_has_shared` (bool)
- Si `has_shared === false` : bouton "Partager mes vœux" avec `window.confirm`
- Si `has_shared === true && partner_has_shared === false` : message "En attente du partage de votre partenaire"
- Si les deux ont partagé (ou date passée) : lien vers `/voeux/partner`

**Nouvelle page `Voeux/Partner.tsx` :**
- Affichage lecture seule du `generated_text` du partenaire
- Pas d'export, pas de modification, pas de drag-and-drop
- Lien retour vers `/voeux/preview`

**`Dashboard.tsx` :**
- Si `partner_vows_readable` : lien "Lire les vœux de [partenaire]" dans la section "Mes vœux"

### Sécurité

- Le `generated_text` du partenaire n'est jamais inclus dans les props Inertia sans passer par `Gate::authorize('viewPartner', ...)`
- `VowsDraftPolicy::view()` existant reste inchangé (un époux ne peut jamais accéder au draft de l'autre via la route normale)

---

## Tests à écrire

### Feature 1
- `POST /voeux/regenerate` remet `generated_text` à null
- Visite de `voeux.preview` après régénération déclenche bien `VowsGeneratorService`
- Un époux ne peut pas régénérer le draft de l'autre

### Feature 2
- `POST /voeux/share` renseigne `shared_at`
- `isReadableByPartner()` retourne `false` si un seul époux a partagé
- `isReadableByPartner()` retourne `true` si les deux ont partagé
- `isReadableByPartner()` retourne `true` si `ceremony_date <= today`
- `GET /voeux/partner` retourne 403 tant que les conditions ne sont pas remplies
- `GET /voeux/partner` retourne le texte du partenaire quand les conditions sont remplies
