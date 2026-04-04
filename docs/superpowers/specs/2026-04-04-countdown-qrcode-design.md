# Design — Countdown & QR Code d'invitation

**Date :** 2026-04-04  
**Scope :** Dashboard des époux (utilisateurs authentifiés)  
**Approche retenue :** Tout client-side, React pur + `react-qr-code`

---

## Contexte

Le modèle `Couple` dispose déjà de `invitation_code` (8 chars, auto-généré) et `ceremony_date`.
Le Dashboard affiche actuellement le code texte brut quand le couple n'est pas complet.
L'objectif est d'enrichir ce Dashboard avec :
1. Un QR code générant l'URL de join pré-remplie, pour faciliter l'invitation du 2e époux
2. Un countdown live (J/H/M/S) jusqu'à la date de cérémonie

---

## Composants

### `resources/js/Components/InvitationQRCode.tsx`

**Rôle :** Afficher un QR code SVG pointant vers l'URL de join pré-remplie.

**Props :**
```ts
interface Props {
    invitationCode: string;
    appUrl: string;
}
```

**Comportement :**
- Construit l'URL cible : `{appUrl}/couple/join?code={invitationCode}`
- Rend le QR code via `react-qr-code` (SVG natif, accessible)
- Le code texte reste affiché sous le QR code pour copier/coller

**Emplacement dans le Dashboard :** Dans le bloc "Code d'invitation", conditionné par `!couple.is_full`.

---

### `resources/js/Components/WeddingCountdown.tsx`

**Rôle :** Afficher un countdown live jusqu'à la date de cérémonie.

**Props :**
```ts
interface Props {
    ceremonyDate: string; // ISO date string, ex: "2026-09-12"
}
```

**Comportement :**
- `useEffect` + `setInterval(1000)` pour mettre à jour chaque seconde
- Cleanup du timer dans le return du `useEffect` (pas de memory leak)
- Calcule jours / heures / minutes / secondes restants
- Si la date est dans le **futur** : affiche le countdown formaté
- Si la date est **aujourd'hui ou passée** : affiche "C'est le grand jour !"

**Emplacement dans le Dashboard :** Dans le bloc couple, sous `ceremony_date`, uniquement si `ceremony_date` est non null.

---

## Modifications Dashboard

### `DashboardController`

Ajouter `app_url` dans les props Inertia :
```php
'app_url' => config('app.url'),
```

`ceremony_date` et `invitation_code` sont déjà présents dans `couple` — aucune migration nécessaire.

### `resources/js/Pages/Dashboard.tsx`

Ajouter dans l'interface `Props` :
```ts
app_url: string;
```

Intégrer les deux composants aux emplacements existants :
- `<InvitationQRCode>` dans le bloc `!couple.is_full`
- `<WeddingCountdown>` dans le bloc couple sous la date

---

## Dépendance

```bash
npm install react-qr-code
```

Pas de nouvelle dépendance PHP, pas de migration, pas de nouveau endpoint.

---

## Cas limites

| Situation | Comportement |
|-----------|-------------|
| `ceremony_date` null | `WeddingCountdown` non rendu |
| Date dans le passé ou aujourd'hui | Affiche "C'est le grand jour !" |
| Couple complet (`is_full = true`) | `InvitationQRCode` non rendu |
| Composant démonté avant fin du timer | Cleanup via `useEffect` return |

---

## Tests

- Mettre à jour le test Pest existant sur `DashboardController` pour vérifier que `app_url` est présent dans les props Inertia
- Pas de test unitaire React (pas de setup Vitest dans le projet)
